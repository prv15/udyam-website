<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Request;
use App\Repositories\CustomerPortalRepository;
use App\Services\InvoiceMailer;
use App\Services\PayyantraGateway;

final class PaymentController extends CustomerController
{
    public function __construct(
        private readonly CustomerPortalRepository $portal,
        private readonly PayyantraGateway $gateway,
        private readonly InvoiceMailer $mailer,
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
            $gateway=$this->gateway->createOrder($checkout,$this->customer);
            $this->portal->attachGatewayOrder((int)$checkout['order_id'],$gateway);
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
            if(!$result['already_paid'])$this->sendInvoice((string)$result['invoice_token']);
            $this->redirectSuccess('/invoice/'.$result['invoice_token'],'Payment successful. Your subscription is now active.');
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
        $this->redirectError('/customer/billing','Payment was declined. You can start a new checkout from Subscriptions.');
    }

    public function paymentReturn(): never
    {
        $token=$this->request->string('order');
        $order=$this->portal->paymentOrder($this->id(),$token);
        if(!$order)$this->redirectError('/customer/billing','Payment order was not found.');
        if($order['status']==='paid')$this->redirectSuccess('/invoice/'.$order['invoice_token'],'Payment confirmed.');
        $this->redirectError('/customer/billing','Payment confirmation is pending. The invoice will update automatically after PayYantra confirms it.');
    }

    private function sendInvoice(string $token): void
    {
        $invoice=$this->portal->publicInvoice($token);
        if(!$invoice)return;
        try{$this->mailer->send($invoice);}catch(\Throwable $e){error_log('Invoice email failed: '.$e->getMessage());}
    }

    private function id(): int
    {
        return (int)$this->customer['id'];
    }
}
