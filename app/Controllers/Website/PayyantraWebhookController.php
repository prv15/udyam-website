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

    public function handle(): never
    {
        $raw=(string)file_get_contents('php://input');
        $signature=(string)($_SERVER['HTTP_X_PAYYANTRA_SIGNATURE']??'');
        if(!$this->gateway->validWebhook($raw,$signature))$this->json(['ok'=>false,'message'=>'Invalid signature'],401);
        $payload=json_decode($raw,true);
        if(!is_array($payload))$this->json(['ok'=>false,'message'=>'Invalid payload'],422);
        $providerOrderId=(string)($payload['order_id']??$payload['data']['order_id']??'');
        $status=strtolower((string)($payload['status']??$payload['data']['status']??''));
        $transactionId=(string)($payload['transaction_id']??$payload['payment_id']??$payload['data']['transaction_id']??'');
        $order=$providerOrderId!==''?$this->portal->paymentOrderByProviderId($providerOrderId):null;
        if(!$order)$this->json(['ok'=>false,'message'=>'Order not found'],404);
        if(in_array($status,['paid','success','successful','captured'],true)){
            if($transactionId==='')$transactionId='PY-'.hash('sha256',$raw);
            $result=$this->portal->completePaymentOrder((int)$order['id'],$transactionId,$payload);
            if(!$result['already_paid']){
                $invoice=$this->portal->publicInvoice((string)$result['invoice_token']);
                if($invoice){try{$this->mailer->send($invoice);}catch(\Throwable $e){error_log('Webhook invoice email failed: '.$e->getMessage());}}
            }
            $this->json(['ok'=>true]);
        }
        if(in_array($status,['failed','declined','cancelled'],true)){
            $this->portal->failPaymentOrder((int)$order['id'],'PayYantra reported '.$status.'.',$payload);
        }
        $this->json(['ok'=>true]);
    }
}
