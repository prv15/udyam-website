<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;

final class PayyantraGateway
{
    public function isDemo(): bool
    {
        return strtolower((string)($_ENV['PAYYANTRA_MODE']??'demo'))!=='live';
    }

    public function createOrder(array $checkout, array $customer): array
    {
        $request=[
            'amount'=>(float)$checkout['amount'],
            'currency'=>$checkout['currency']??'INR',
            'reference_id'=>'UV-CHECKOUT-'.$checkout['order_id'],
            'description'=>$checkout['description']??(($checkout['plan']['name']??'Udyam').' subscription'),
            'customer'=>[
                'name'=>trim(($customer['first_name']??'').' '.($customer['last_name']??'')),
                'email'=>$customer['email']??'',
                'phone'=>$customer['mobile']??'',
            ],
            'return_url'=>$this->absolute('/customer/payments/return?order='.$checkout['order_token']),
            'notify_url'=>$this->absolute('/payments/payyantra/webhook'),
            'metadata'=>[
                'local_order_id'=>$checkout['order_id'],
                'invoice_id'=>$checkout['invoice_id'],
                'subscription_id'=>$checkout['subscription_id'],
            ],
        ];

        if($this->isDemo()){
            $providerOrderId='PY-DEMO-'.str_pad((string)$checkout['order_id'],8,'0',STR_PAD_LEFT);
            return [
                'provider_order_id'=>$providerOrderId,
                'provider_session_id'=>'PYS-'.bin2hex(random_bytes(8)),
                'checkout_url'=>url('/customer/payments/demo/'.$checkout['order_token']),
                'request'=>$request,
                'response'=>['mode'=>'demo','order_id'=>$providerOrderId,'status'=>'created'],
            ];
        }

        $endpoint=trim((string)($_ENV['PAYYANTRA_ORDER_ENDPOINT']??''));
        $apiKey=trim((string)($_ENV['PAYYANTRA_API_KEY']??''));
        if($endpoint===''||$apiKey==='')throw new \RuntimeException('PayYantra live credentials are not configured.');
        $handle=curl_init($endpoint);
        curl_setopt_array($handle,[
            CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,
            CURLOPT_HTTPHEADER=>['Accept: application/json','Content-Type: application/json','Authorization: Bearer '.$apiKey],
            CURLOPT_POSTFIELDS=>json_encode($request,JSON_UNESCAPED_SLASHES),
        ]);
        $raw=curl_exec($handle);
        $status=(int)curl_getinfo($handle,CURLINFO_HTTP_CODE);
        $error=curl_error($handle);
        curl_close($handle);
        if($raw===false||$status<200||$status>=300){
            throw new \RuntimeException('PayYantra order creation failed'.($error!==''?': '.$error:'.'));
        }
        $response=json_decode((string)$raw,true);
        if(!is_array($response))throw new \RuntimeException('PayYantra returned an invalid response.');
        $orderId=(string)($response['order_id']??$response['id']??'');
        $sessionId=(string)($response['session_id']??$response['order_session_id']??'');
        $checkoutUrl=(string)($response['checkout_url']??$response['payment_url']??$response['redirect_url']??'');
        if($orderId===''||$checkoutUrl==='')throw new \RuntimeException('PayYantra response is missing the order or checkout URL.');
        return [
            'provider_order_id'=>$orderId,'provider_session_id'=>$sessionId?:null,'checkout_url'=>$checkoutUrl,
            'request'=>$request,'response'=>$response,
        ];
    }

    public function validWebhook(string $rawBody, string $signature): bool
    {
        $secret=(string)($_ENV['PAYYANTRA_WEBHOOK_SECRET']??'');
        return $secret!==''&&$signature!==''&&hash_equals(hash_hmac('sha256',$rawBody,$secret),$signature);
    }

    private function absolute(string $path): string
    {
        return App::url().'/'.ltrim($path,'/');
    }
}
