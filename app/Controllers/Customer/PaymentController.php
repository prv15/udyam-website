<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Request;
use App\Repositories\CustomerPortalRepository;
use App\Services\InvoiceMailer;
use App\Services\PayyantraGateway;
use App\Services\NotificationService;

final class PaymentController extends CustomerController
{
    public function __construct(
        private readonly CustomerPortalRepository $portal,
        private readonly PayyantraGateway $gateway,
        private readonly InvoiceMailer $mailer,
        private readonly NotificationService $notifications,
        private readonly Request $request
    ){
        parent::__construct();
    }

    public function demo(string $token): void
    {
        if(!$this->gateway->isDemo())$this->abort404();
        $order=$this->portal->paymentOrder($this->id(),$token);
        if(!$order)$this->abort404();
        $this->render('payment-demo',['title'=>'Secure Checkout','order'=>$order]);
    }

    public function payInvoice(int $id): never
    {
        $this->csrf();$checkout=null;
        try{
            $checkout=$this->portal->startInvoiceCheckout($this->id(),$id);
            if(!empty($checkout['resume'])&&!empty($checkout['checkout_url'])){$gateway=['checkout_url'=>$checkout['checkout_url']];}
            else{$gateway=$this->gateway->createOrder($checkout,$this->customer);$this->portal->attachGatewayOrder((int)$checkout['order_id'],$gateway);}
            $this->portal->log($this->id(),'invoice_checkout_started','Invoice checkout started.','invoice',$id);
            header('Location: '.$gateway['checkout_url']);
            exit;
        }catch(\InvalidArgumentException $e){
            if($checkout)$this->portal->failPaymentOrder((int)$checkout['order_id'],'Checkout initialization failed.');
            $this->redirectError('/customer/billing',$e->getMessage());
        }catch(\Throwable $e){
            if($checkout){try{$this->portal->failPaymentOrder((int)$checkout['order_id'],'Payment gateway initialization failed.');}catch(\Throwable){}}
            error_log('Invoice checkout failed: '.$e->getMessage());
            $this->redirectError('/customer/billing','Invoice checkout could not be started. Please try again.');
        }
    }

    public function demoComplete(string $token): never
    {
        $this->csrf();
        if(!$this->gateway->isDemo())$this->abort404();
        $order=$this->portal->paymentOrder($this->id(),$token);
        if(!$order)$this->abort404();
        try{
            $transaction='PY-DEMO-TXN-'.strtoupper(bin2hex(random_bytes(5)));
            $result=$this->portal->completePaymentOrder((int)$order['id'],$transaction,['mode'=>'demo','status'=>'paid']);
            $this->portal->log($this->id(),'payment_completed','PayYantra demo payment completed.','invoice',(int)$result['invoice_id']);
            $this->notifications->notifyAdmin('payment', 'Payment received', 'A partner completed a PayYantra payment.', '/admin/customers', $this->id(), 'invoice', (int)$result['invoice_id']);
            if(!$result['already_paid'])$this->sendInvoice((string)$result['invoice_token']);
            $this->redirectSuccess($this->successDestination($order),'Payment successful. Your subscription is now active.');
        }catch(\Throwable $e){
            error_log('Demo payment completion failed: '.$e->getMessage());
            $this->redirectError('/customer/payments/demo/'.$token,'Payment could not be completed. Please try again.');
        }
    }

    public function demoFail(string $token): never
    {
        $this->csrf();
        if(!$this->gateway->isDemo())$this->abort404();
        $order=$this->portal->paymentOrder($this->id(),$token);
        if(!$order)$this->abort404();
        $this->portal->failPaymentOrder((int)$order['id'],'Demo payment declined.',['mode'=>'demo','status'=>'failed']);
        $this->portal->log($this->id(),'payment_failed','PayYantra demo payment declined.','invoice',(int)$order['invoice_id']);
        $this->notifications->notifyAdmin('payment', 'Payment failed', 'A partner payment attempt was declined.', '/admin/customers', $this->id(), 'invoice', (int)$order['invoice_id']);
        $this->redirectError('/customer/billing','Payment was declined. You can start a new checkout from Subscriptions.');
    }

    public function paymentReturn(): never
    {
        $token=$this->request->string('order');
        $order=$this->portal->paymentOrder($this->id(),$token);
        if(!$order)$this->redirectError('/customer/billing','Payment order was not found.');
        if($order['status']==='paid')$this->redirectSuccess($this->successDestination($order),'Payment confirmed. Your subscription is active.');
        // The webhook may not have arrived yet (or at all) by the time the browser returns here.
        // PayYantra's own settlement can lag the redirect by a second or two, so retry a few times
        // with short pauses before treating it as genuinely pending — this avoids showing a
        // "pending" message to a customer whose payment actually succeeds moments later.
        if(in_array($order['status'],['created','pending'],true)&&!empty($order['provider_order_id'])){
            for($attempt=1;$attempt<=3;$attempt++){
                try{
                    $this->reconcile(
                        (int)$order['id'],
                        (string)$order['provider_order_id'],
                        (string)($order['provider_session_id']??''),
                        $this->gateway->merchantOrderIdFromStoredResponse($order['response_payload']??null)
                    );
                    $order=$this->portal->paymentOrder($this->id(),$token) ?? $order;
                }catch(\Throwable $e){
                    error_log('Payment return verification failed: '.$e->getMessage());
                    break;
                }
                if($order['status']!=='created'&&$order['status']!=='pending')break;
                if($attempt<3)usleep(1200000);
            }
        }
        if($order['status']==='paid')$this->redirectSuccess($this->successDestination($order),'Payment confirmed. Your subscription is active.');
        if($order['status']==='failed')$this->redirectError('/customer/billing','Payment was not successful. You can start a new checkout from Subscriptions.');
        $this->redirectInfo('/customer/billing','Payment confirmation is still processing. The invoice will update automatically within a few minutes once PayYantra confirms it.');
    }

    private function reconcile(int $orderId,string $providerOrderId,?string $providerTransactionId=null,?string $providerMerchantOrderId=null): void
    {
        $verified=$this->gateway->verifyOrder($providerOrderId,$providerTransactionId,$providerMerchantOrderId);
        $status=$verified['status'];
        if($this->gateway->isSuccessfulStatus($status)){
            $transactionId=$verified['transaction_id']?:('PY-'.$providerOrderId);
            $result=$this->portal->completePaymentOrder($orderId,$transactionId,['verified'=>$verified['raw']]);
            if(!$result['already_paid'])$this->sendInvoice((string)$result['invoice_token']);
            if(!$result['already_paid'])$this->notifications->notifyAdmin('payment', 'Payment received', 'A PayYantra payment was verified successfully.', '/admin/customers', $this->id(), 'invoice', (int)$result['invoice_id']);
            return;
        }
        if($this->gateway->isFailedStatus($status)){
            $this->portal->failPaymentOrder($orderId,'PayYantra reported '.$status.'.',['verified'=>$verified['raw']]);
        }
    }

    private function sendInvoice(string $token): void
    {
        $invoice=$this->portal->publicInvoice($token);
        if(!$invoice)return;
        try{$this->mailer->send($invoice);}catch(\Throwable $e){error_log('Invoice email failed: '.$e->getMessage());}
    }

    private function successDestination(array $order): string
    {
        return !empty($order['subscription_id'])
            ? '/customer/subscription'
            : '/customer/billing/invoices/' . (int)$order['invoice_id'];
    }

    private function id(): int
    {
        return (int)$this->customer['id'];
    }
}
