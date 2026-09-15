<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Core\Request;
use App\Repositories\CustomerPortalRepository;
use App\Services\PayyantraGateway;

/** The random 256-bit order token makes the gateway return safe without a login cookie. */
final class PaymentReturnController extends Controller
{
    public function __construct(private readonly CustomerPortalRepository $portal,private readonly PayyantraGateway $gateway,private readonly Request $request){}

    public function handle(): never
    {
        $token=$this->request->string('local_order')?:$this->request->string('order');$order=$token!==''?$this->portal->paymentOrderByToken($token):null;
        if(!$order)$this->redirectError('/customer/login','Payment order was not found. Please sign in and check Billing.');
        if($order['status']!=='paid'&&!empty($order['provider_order_id'])){
            for($attempt=1;$attempt<=4;$attempt++){
                try{
                    $verified=$this->gateway->verifyOrder((string)$order['provider_order_id'],(string)($order['provider_session_id']??''),$this->gateway->merchantOrderIdFromStoredResponse($order['response_payload']??null));
                    $status=(string)$verified['status'];
                    if($this->gateway->isSuccessfulStatus($status)){
                        $this->portal->completePaymentOrder((int)$order['id'],(string)($verified['transaction_id']?:('PY-'.$order['provider_order_id'])),['return_verification'=>$verified['raw']]);break;
                    }
                    if($this->gateway->isFailedStatus($status)){$this->portal->failPaymentOrder((int)$order['id'],'PayYantra reported '.$status.'.',['return_verification'=>$verified['raw']]);break;}
                }catch(\Throwable $exception){error_log('Public payment return verification failed: '.$exception->getMessage());}
                if($attempt<4)usleep(1000000);
            }
        }
        $fresh=$this->portal->paymentOrderByToken($token)??$order;$destination=!empty($fresh['subscription_id'])?'/customer/subscription':'/customer/billing/invoices/'.(int)$fresh['invoice_id'];
        if($fresh['status']==='paid')$this->redirectSuccess($destination,'Payment confirmed successfully. Your invoice and subscription have been updated.');
        if($fresh['status']==='failed')$this->redirectError('/customer/billing','The gateway has not confirmed this payment. If money was debited, please do not pay again; reconciliation will continue automatically.');
        $this->redirectInfo('/customer/billing','Payment confirmation is processing. Please do not pay again; the invoice will update automatically after gateway confirmation.');
    }
}
