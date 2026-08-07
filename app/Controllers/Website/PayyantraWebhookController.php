<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Repositories\CustomerPortalRepository;
use App\Services\InvoiceMailer;
use App\Services\PayyantraGateway;

final class PayyantraWebhookController extends Controller
{
    public function __construct(
        private readonly CustomerPortalRepository $portal,
        private readonly PayyantraGateway $gateway,
        private readonly InvoiceMailer $mailer
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
        if(!is_array($payload))$this->json(['ok'=>false,'message'=>'Invalid payload'],422);
        $data=is_array($payload['data']??null)?$payload['data']:$payload;
        $providerOrderId=(string)($data['orderId']??$data['order_id']??'');
        if($providerOrderId===''){
            // The exact webhook payload shape isn't documented — log it once so the first real
            // UAT delivery confirms the field name and this fallback can be tightened.
            error_log('PayYantra webhook: could not find an order id. Payload: '.substr($raw,0,2000));
            $this->json(['ok'=>false,'message'=>'Missing order id'],422);
        }
        $order=$this->portal->paymentOrderByProviderId($providerOrderId);
        if(!$order)$this->json(['ok'=>false,'message'=>'Order not found'],404);
        $this->reconcile((int)$order['id'],$providerOrderId,$payload);
        $this->json(['ok'=>true]);
    }

    private function reconcile(int $orderId,string $providerOrderId,array $rawPayload): void
    {
        $verified=$this->gateway->verifyOrder($providerOrderId);
        $status=$verified['status'];
        if(in_array($status,['SUCCESS','PAID','CAPTURED'],true)){
            $transactionId=$verified['transaction_id']?:('PY-'.hash('sha256',json_encode($rawPayload)));
            $result=$this->portal->completePaymentOrder($orderId,$transactionId,['webhook'=>$rawPayload,'verified'=>$verified['raw']]);
            if(!$result['already_paid']){
                $invoice=$this->portal->publicInvoice((string)$result['invoice_token']);
                if($invoice){try{$this->mailer->send($invoice);}catch(\Throwable $e){error_log('Webhook invoice email failed: '.$e->getMessage());}}
            }
            return;
        }
        if(in_array($status,['FAILED','DECLINED','CANCELLED','EXPIRED'],true)){
            $this->portal->failPaymentOrder($orderId,'PayYantra reported '.$status.'.',['webhook'=>$rawPayload,'verified'=>$verified['raw']]);
        }
        // INITIATED / PENDING: leave the order open; another webhook delivery or the customer's
        // return-page visit will re-check later.
    }
}
