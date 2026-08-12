<?php

declare(strict_types=1);

if(PHP_SAPI!=='cli'){http_response_code(404);exit;}

$sessionPath=dirname(__DIR__).'/storage/sessions';
if(!is_dir($sessionPath))mkdir($sessionPath,0770,true);
ini_set('session.save_path',$sessionPath);

require_once dirname(__DIR__).'/app/bootstrap.php';

use App\Config\Database;
use App\Repositories\CustomerPortalRepository;
use App\Services\PayyantraGateway;

$portal=new CustomerPortalRepository(Database::connection());$gateway=new PayyantraGateway();
if($gateway->isDemo()){fwrite(STDOUT,"Payment reconciliation skipped in demo mode.\n");exit(0);}
$summary=['checked'=>0,'paid'=>0,'failed'=>0,'pending'=>0,'skipped'=>0,'errors'=>0];
foreach($portal->reconciliationCandidates(100) as $order){
    $summary['checked']++;
    try{
        $verified=$gateway->verifyOrder((string)$order['provider_order_id'],(string)($order['provider_session_id']??''),$gateway->merchantOrderIdFromStoredResponse($order['response_payload']??null));$status=(string)$verified['status'];
        if($gateway->isSuccessfulStatus($status)){$portal->completePaymentOrder((int)$order['id'],(string)($verified['transaction_id']?:('PY-'.$order['provider_order_id'])),['scheduled_reconciliation'=>$verified['raw']]);$summary['paid']++;}
        elseif($gateway->isFailedStatus($status)){$portal->failPaymentOrder((int)$order['id'],'PayYantra reported '.$status.'.',['scheduled_reconciliation'=>$verified['raw']]);$summary['failed']++;}
        else{$summary['pending']++;}
    }catch(Throwable $exception){
        $message=$exception->getMessage();
        if(str_contains(strtolower($message),'invalid order access token')||str_contains(strtolower($message),'order not found')){
            $summary['skipped']++;
            error_log('Scheduled payment reconciliation skipped inaccessible legacy order '.(int)$order['id'].'.');
        }else{
            $summary['errors']++;error_log('Scheduled payment reconciliation failed for order '.(int)$order['id'].': '.$message);
        }
    }
}
fwrite(STDOUT,json_encode($summary,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL);
exit($summary['errors']>0?1:0);
