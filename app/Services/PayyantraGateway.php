<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\App;

final class PayyantraGateway
{
    private const UAT_BASE_URL = 'https://payin-api-uat.payyantra.com';
    private const LIVE_BASE_URL = 'https://payin-api.payyantra.com';

    public function isDemo(): bool
    {
        return strtolower((string)($_ENV['PAYYANTRA_MODE']??'demo'))==='demo';
    }

    public function createOrder(array $checkout, array $customer): array
    {
        $referenceId='UV-CHECKOUT-'.$checkout['order_id'];
        $request=[
            'referenceId'=>$referenceId,
            'amount'=>(float)$checkout['amount'],
            'currency'=>$checkout['currency']??'INR',
            'customerName'=>trim(($customer['first_name']??'').' '.($customer['last_name']??'')) ?: 'Udyam Customer',
            'customerEmail'=>$customer['email']??'',
            'customerPhone'=>$this->phone($customer['mobile']??''),
            'notifyUrl'=>$this->absolute('/payments/payyantra/webhook'),
            'returnUrl'=>$this->absolute('/customer/payments/return?order='.$checkout['order_token']),
            'allowedPaymentMethods'=>['UPI','CREDIT_CARD','DEBIT_CARD','INTERNET_BANKING'],
        ];

        if($this->isDemo()){
            $providerOrderId='PY-DEMO-'.str_pad((string)$checkout['order_id'],8,'0',STR_PAD_LEFT);
            return [
                'provider_order_id'=>$providerOrderId,
                'provider_session_id'=>'PYS-'.bin2hex(random_bytes(8)),
                'checkout_url'=>url('/customer/payments/demo/'.$checkout['order_token']),
                'request'=>$request,
                'response'=>['mode'=>'demo','orderId'=>$providerOrderId,'status'=>'INITIATED'],
            ];
        }

        $response=$this->call('POST','/api/v2/merchant/orders',$request);
        $data=$response['data']??null;
        if(!is_array($data))throw new \RuntimeException('PayYantra returned an invalid order response.');
        $orderId=(string)($data['orderId']??'');
        $checkoutUrl=(string)($data['checkoutUrl']??'');
        $transactionId=(string)($data['transactionId']??'');
        if($orderId===''||$checkoutUrl==='')throw new \RuntimeException('PayYantra response is missing the order id or checkout URL.');
        return [
            'provider_order_id'=>$orderId,'provider_session_id'=>$transactionId?:null,'checkout_url'=>$checkoutUrl,
            'request'=>$request,'response'=>$response,
        ];
    }

    /**
     * Asks PayYantra directly for the authoritative status of an order. This is the source of
     * truth for activating a subscription — never trust a webhook body's status field alone,
     * since PayYantra's webhook docs do not specify a signature we can verify. Both the webhook
     * handler and the customer return page call this before crediting any payment.
     */
    public function verifyOrder(string $providerOrderId): array
    {
        if($this->isDemo()){
            return ['status'=>'UNKNOWN','order_id'=>$providerOrderId,'transaction_id'=>null,'raw'=>[]];
        }
        $response=$this->call('GET','/api/pay/status/'.rawurlencode($providerOrderId));
        $data=$response['data']??[];
        if(!is_array($data))$data=[];
        return [
            'status'=>strtoupper((string)($data['status']??'')),
            'order_id'=>(string)($data['orderId']??$providerOrderId),
            'transaction_id'=>(string)($data['pspOrderId']??$data['transactionPublicId']??'')?:null,
            'raw'=>$data,
        ];
    }

    private function call(string $method,string $path,?array $body=null): array
    {
        $endpoint=rtrim($this->baseUrl(),'/').$path;
        $token=$this->token();
        $handle=curl_init($endpoint);
        $headers=['Accept: application/json','Authorization: Bearer '.$token];
        $options=[
            CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,CURLOPT_CUSTOMREQUEST=>$method,
        ];
        if($body!==null){
            $headers[]='Content-Type: application/json';
            $options[CURLOPT_POSTFIELDS]=json_encode($body,JSON_UNESCAPED_SLASHES);
        }
        $options[CURLOPT_HTTPHEADER]=$headers;
        curl_setopt_array($handle,$options);
        $raw=curl_exec($handle);
        $status=(int)curl_getinfo($handle,CURLINFO_HTTP_CODE);
        $error=curl_error($handle);
        curl_close($handle);
        if($raw===false)throw new \RuntimeException('PayYantra request failed: '.$error);
        $response=json_decode((string)$raw,true);
        if(!is_array($response))throw new \RuntimeException('PayYantra returned an invalid response.');
        if($status<200||$status>=300){
            $message=(string)($response['message']??$response['error']??'HTTP '.$status);
            throw new \RuntimeException('PayYantra request failed: '.$message);
        }
        return $response;
    }

    /**
     * PayYantra uses short-lived (~30 min) JWTs from a client-credentials token endpoint rather
     * than a static bearer key. A fresh token is requested per call; checkout and status checks
     * happen at most a couple of times per payment, so this is simple and correct rather than
     * adding token-cache invalidation complexity for negligible latency gain.
     */
    private function token(): string
    {
        $clientId=trim((string)($_ENV['PAYYANTRA_CLIENT_ID']??''));
        $clientSecret=trim((string)($_ENV['PAYYANTRA_CLIENT_SECRET']??''));
        if($clientId===''||$clientSecret==='')throw new \RuntimeException('PayYantra live credentials are not configured.');
        $handle=curl_init(rtrim($this->baseUrl(),'/').'/api/auth/token');
        curl_setopt_array($handle,[
            CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,
            CURLOPT_HTTPHEADER=>['Accept: application/json','x-client-id: '.$clientId,'x-client-secret: '.$clientSecret],
            CURLOPT_POSTFIELDS=>'{}',
        ]);
        $raw=curl_exec($handle);
        $status=(int)curl_getinfo($handle,CURLINFO_HTTP_CODE);
        $error=curl_error($handle);
        curl_close($handle);
        if($raw===false||$status<200||$status>=300){
            $detail=$error!==''?$error:('HTTP '.$status.' — '.substr((string)$raw,0,300));
            throw new \RuntimeException('PayYantra authentication failed: '.$detail);
        }
        $response=json_decode((string)$raw,true);
        $token=(string)($response['data']['token']??$response['token']??'');
        if($token==='')throw new \RuntimeException('PayYantra authentication response is missing a token.');
        return $token;
    }

    private function baseUrl(): string
    {
        $override=trim((string)($_ENV['PAYYANTRA_BASE_URL']??''));
        if($override!=='')return $override;
        return strtolower((string)($_ENV['PAYYANTRA_MODE']??'demo'))==='live' ? self::LIVE_BASE_URL : self::UAT_BASE_URL;
    }

    private function phone(string $raw): string
    {
        $digits=preg_replace('/\D+/','',$raw) ?? '';
        return substr($digits,-10);
    }

    private function absolute(string $path): string
    {
        return App::url().'/'.ltrim($path,'/');
    }
}