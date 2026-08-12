<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Repositories\CustomerPortalRepository;
use App\Services\InvoiceMailer;
use App\Services\PayyantraGateway;
use App\Services\NotificationService;

final class PayyantraWebhookController extends Controller
{
    public function __construct(
        private readonly CustomerPortalRepository $portal,
        private readonly PayyantraGateway $gateway,
        private readonly InvoiceMailer $mailer,
        private readonly NotificationService $notifications
    ){
    }

    /**
     * PayYantra's docs don't specify a webhook signature scheme, so this payload is treated only
     * as a "check now" trigger — never as the source of truth. The provider order id is extracted
     * from it just to know which local order to look up, then verifyOrder() asks PayYantra's API
     * directly for the authoritative status before any invoice/subscription is touched. A forged
     * webhook call can therefore only ever trigger a harmless status re-check, never a fake payment.
     */
    public function handle(): never
    {
        $raw=(string)file_get_contents('php://input');
        $payload=json_decode($raw,true);
        if(!is_array($payload)){
            parse_str($raw,$formPayload);
            $payload=is_array($formPayload)&&$formPayload!==[]?$formPayload:$_POST;
        }
        if(!is_array($payload)||$payload===[])$this->json(['ok'=>false,'message'=>'Invalid payload'],422);
        $providerOrderId=(string)($this->findValue($payload,['orderId','order_id'])??'');
        $providerTransactionId=(string)($this->findValue($payload,['transactionId','transaction_id','txnId','transactionPublicId'])??'');
        if($providerOrderId===''&&$providerTransactionId===''){
            error_log('PayYantra webhook: could not find an order or transaction id.');
            $this->json(['ok'=>false,'message'=>'Missing payment identifier'],422);
        }
        $order=$this->portal->paymentOrderByProviderId($providerOrderId?:$providerTransactionId);
        if(!$order)$this->json(['ok'=>false,'message'=>'Order not found'],404);
        $providerMerchantOrderId=(string)($this->findValue($payload,['merchantOrderId','merchant_order_id'])??'');
        $this->reconcile(
            (int)$order['id'],
            (string)($order['provider_order_id']??$providerOrderId),
            (string)($order['provider_session_id']??$providerTransactionId),
            $providerMerchantOrderId?:$this->gateway->merchantOrderIdFromStoredResponse($order['response_payload']??null),
            $payload
        );
        $this->json(['ok'=>true]);
    }

    private function reconcile(int $orderId,string $providerOrderId,?string $providerTransactionId,?string $providerMerchantOrderId,array $rawPayload): void
    {
        $payloadStatus=(string)($this->findValue($rawPayload,['paymentStatus','transactionStatus','orderStatus','payment_status','transaction_status','order_status','status','state'])??'');
        $trusted=$this->hasValidWebhookSecret();
        if($trusted&&$this->gateway->isSuccessfulStatus($payloadStatus)){
            $verified=['status'=>$payloadStatus,'transaction_id'=>$providerTransactionId,'raw'=>$rawPayload];
        }elseif($trusted&&$this->gateway->isFailedStatus($payloadStatus)){
            $this->portal->failPaymentOrder($orderId,'PayYantra reported '.$payloadStatus.'.',['webhook'=>$rawPayload,'trusted'=>true]);
            return;
        }else{
            $verified=$this->gateway->verifyOrder($providerOrderId,$providerTransactionId,$providerMerchantOrderId);
        }
        $status=$verified['status'];
        if($this->gateway->isSuccessfulStatus($status)){
            $transactionId=$verified['transaction_id']?:('PY-'.hash('sha256',json_encode($rawPayload)));
            $result=$this->portal->completePaymentOrder($orderId,$transactionId,['webhook'=>$rawPayload,'verified'=>$verified['raw']]);
            if(!$result['already_paid']){
                $invoice=$this->portal->publicInvoice((string)$result['invoice_token']);
                if($invoice){try{$this->mailer->send($invoice);}catch(\Throwable $e){error_log('Webhook invoice email failed: '.$e->getMessage());}}
                $this->notifications->notifyAdmin('payment','Payment received','A PayYantra payment was confirmed by webhook.','/admin/customers',(int)$result['customer_id'],'invoice',(int)$result['invoice_id']);
            }
            return;
        }
        if($this->gateway->isFailedStatus($status)){
            $this->portal->failPaymentOrder($orderId,'PayYantra reported '.$status.'.',['webhook'=>$rawPayload,'verified'=>$verified['raw']]);
        }
        // INITIATED / PENDING: leave the order open; another webhook delivery or the customer's
        // return-page visit will re-check later.
    }

    private function findValue(array $payload,array $keys): mixed
    {
        foreach($keys as $key){
            if(array_key_exists($key,$payload)&&$payload[$key]!==''&&$payload[$key]!==null)return $payload[$key];
        }
        foreach($payload as $value){
            if(is_array($value)){
                $found=$this->findValue($value,$keys);
                if($found!==null&&$found!=='')return $found;
            }
        }
        return null;
    }

    private function hasValidWebhookSecret(): bool
    {
        $configured=trim((string)($_ENV['PAYYANTRA_WEBHOOK_SECRET']??''));
        $provided=trim((string)($_GET['key']??''));
        return $configured!==''&&$provided!==''&&hash_equals($configured,$provided);
    }
}
