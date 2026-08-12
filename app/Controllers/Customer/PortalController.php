<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\CustomerPortalRepository;
use App\Services\PayyantraGateway;
use App\Services\NotificationService;

final class PortalController extends CustomerController
{
    public function __construct(
        private readonly CustomerPortalRepository $portal,
        private readonly Request $request,
        private readonly PayyantraGateway $gateway,
        private readonly NotificationService $notifications
    )
    {
        parent::__construct();
    }

    public function dashboard(): void
    {
        $this->render('dashboard', ['title'=>'Dashboard','data'=>$this->portal->dashboard($this->id())]);
    }

    public function tenders(): void
    {
        if(!$this->portal->hasActiveSubscription($this->id())){$this->redirectInfo('/customer/plans','Choose a subscription plan to access notices and tenders.');}
        $perPage=$this->perPage();$page=max(1,$this->request->integer('page',1));
        $filters=[
            'q'=>mb_substr($this->request->string('q'),0,120),
            'region'=>mb_substr($this->request->string('region'),0,120),
            'invited_by'=>mb_substr($this->request->string('invited_by'),0,160),
            'sort'=>in_array($this->request->string('sort','latest'),['latest','deadline','expired','ongoing'],true)?$this->request->string('sort','latest'):'latest',
        ];
        $total=$this->portal->tendersTotal($filters);$pages=max(1,(int)ceil($total/$perPage));$page=min($page,$pages);
        $this->render('tenders',['title'=>'Notices & Tenders','tenders'=>$this->portal->tendersForCustomer($page,$perPage,$filters),'total'=>$total,'page'=>$page,'perPage'=>$perPage,'filters'=>$filters,'filterOptions'=>$this->portal->tenderFilterOptions()]);
    }

    public function tender(int $id): void
    {
        if(!$this->portal->hasActiveSubscription($this->id())){$this->redirectInfo('/customer/plans','Choose a subscription plan to access notices and tenders.');}
        $tender=$this->portal->tenderForCustomer($id);if(!$tender)$this->abort404();
    $fullTitle=(string)$tender['title'];$words=preg_split('/\s+/',trim($fullTitle))?:[];$shortTitle=count($words)>7?implode(' ',array_slice($words,0,7)).'…':$fullTitle;
        $this->render('tender',['title'=>$shortTitle,'tender'=>$tender,'shortTitle'=>$shortTitle,'fullTitle'=>$fullTitle]);
    }

    private function perPage(): int
    {
        $value=$this->request->integer('per_page',10);return in_array($value,[10,50,100],true)?$value:10;
    }

    public function search(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: private, no-store, max-age=0');
        try {
            $query = mb_substr($this->request->string('q'), 0, 120);
            $hasSubscription = $this->portal->hasActiveSubscription($this->id());
            $results = $this->portal->search($this->id(), $query, $hasSubscription);
            // JSON_INVALID_UTF8_SUBSTITUTE: a single stray non-UTF-8 byte in any matched title
            // (common with copy-pasted content) otherwise makes json_encode() fail outright and
            // silently return an empty body — this substitutes the bad byte instead of failing.
            $encoded = json_encode(['results' => $results], JSON_INVALID_UTF8_SUBSTITUTE);
            if ($encoded === false) {
                error_log('Portal search JSON encode failed: ' . json_last_error_msg());
                $encoded = json_encode(['results' => []]);
            }
            echo $encoded;
        } catch (\Throwable $e) {
            error_log('Portal search failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['results' => [], 'error' => 'Search is temporarily unavailable.']);
        }
        exit;
    }

    public function profile(): void
    {
        $this->render('profile', ['title'=>'Company Profile','profile'=>$this->portal->findCustomer($this->id())]);
    }

    public function updateProfile(): never
    {
        $this->csrf();
        $this->portal->updateProfile($this->id(), $this->request->only([
            'company_name','mobile','gst_number','pan_number','address_line_1','address_line_2','city',
            'state','postal_code','country','contact_person','business_type','preferred_communication',
            'annual_turnover_range',
        ]));
        $this->portal->log($this->id(),'profile_updated','Customer profile updated.');
        $this->notifications->notifyAdmin('profile', 'Partner profile updated', 'A partner updated their company profile.', '/admin/customers', $this->id(), 'profile', $this->id());
        $this->redirectSuccess('/customer/profile','Profile updated successfully.');
    }

    public function plans(): void
    {
        $this->render('plans', ['title'=>'Subscription Plans','plans'=>$this->portal->plans(),'subscription'=>$this->portal->currentSubscription($this->id())]);
    }

    public function subscription(): void
    {
        $this->reconcilePendingPayment();
        $this->render('subscription', ['title' => 'My Subscription', 'subscription' => $this->portal->currentSubscription($this->id()), 'requests' => $this->portal->subscriptionRequests($this->id())]);
    }

    public function requestSubscriptionAction(): never
    {
        $this->csrf();
        try {
            $requestId = $this->portal->requestSubscriptionAction($this->id(), $this->request->integer('subscription_id'), $this->request->string('request_type'), $this->request->string('reason'));
            $this->portal->log($this->id(), 'subscription_request', 'Subscription ' . $this->request->string('request_type') . ' request submitted.', 'subscription_request', $requestId);
            $this->notifications->notifyAdmin('subscription', 'Subscription ' . $this->request->string('request_type') . ' request', 'A partner has requested a subscription ' . $this->request->string('request_type') . '.', '/admin/subscription-requests', $this->id(), 'subscription_request', $requestId);
            $this->redirectSuccess('/customer/subscription', 'Your request has been sent to the Udyam team for review.');
        } catch (\InvalidArgumentException $e) {
            $this->redirectError('/customer/subscription', $e->getMessage());
        }
    }

    public function subscribe(int $id): never
    {
        $this->csrf();
        if($this->request->string('disclaimer_accepted')!=='1')$this->redirectError('/customer/plans','Please read and accept the subscription terms before continuing to payment.');
        $checkout=null;
        try{
            $checkout=$this->portal->startSubscriptionCheckout($this->id(),$id);
            if(!empty($checkout['resume'])&&!empty($checkout['checkout_url'])){$gateway=['checkout_url'=>$checkout['checkout_url']];}
            else{$gateway=$this->gateway->createOrder($checkout,$this->customer);$this->portal->attachGatewayOrder((int)$checkout['order_id'],$gateway);}
            $this->portal->log($this->id(),'subscription_checkout_started','Subscription checkout started.','subscription',(int)$checkout['subscription_id']);
            $this->notifications->notifyAdmin('subscription', 'New subscription checkout', 'A partner has started checkout for ' . (string) $checkout['plan']['name'] . '.', '/admin/customers', $this->id(), 'subscription', (int) $checkout['subscription_id']);
            header('Location: '.$gateway['checkout_url']);
            exit;
        }catch(\InvalidArgumentException $e){
            if($checkout)$this->portal->failPaymentOrder((int)$checkout['order_id'],'Checkout initialization failed.');
            $this->redirectError('/customer/plans',$e->getMessage());
        }catch(\Throwable $e){
            if($checkout){try{$this->portal->failPaymentOrder((int)$checkout['order_id'],'Payment gateway initialization failed.');}catch(\Throwable){}}
            error_log('Subscription checkout failed: '.$e->getMessage());
            // TEMPORARY (local debug only, gated on APP_DEBUG): show the real exception instead
            // of hunting for the PHP error log. Remove this branch before deploying.
            $message=\App\Config\App::debug()
                ? 'Checkout could not be started: '.$e->getMessage()
                : 'Checkout could not be started. Please try again.';
            $this->redirectError('/customer/plans',$message);
        }
    }

    public function services(): void
    {
        $this->render('services', ['title'=>'Services','services'=>$this->portal->services()]);
    }

    public function apply(int $id): void
    {
        $service = null;
        foreach ($this->portal->services() as $candidate) if ((int)$candidate['id']===$id) $service=$candidate;
        if (!$service) $this->abort404();
        $this->render('apply', ['title'=>'Apply for Service','service'=>$service]);
    }

    public function submitApplication(int $id): never
    {
        $this->csrf();
        $requestId = $this->portal->applyForService($this->id(),$id,$this->request->string('project_name'),$this->request->string('remarks'));
        $this->portal->log($this->id(),'application_submitted','Service application submitted.','service_request',$requestId);
        $this->notifications->notifyAdmin('application', 'New service application', 'A partner submitted a new service application.', '/admin/applications', $this->id(), 'service_request', $requestId);
        $this->redirectSuccess('/customer/applications/' . $requestId,'Application submitted successfully.');
    }

    public function applications(): void
    {
        $this->render('applications', ['title'=>'Applications','applications'=>$this->portal->applications($this->id())]);
    }

    public function application(int $id): void
    {
        $application=$this->portal->application($this->id(),$id);
        if (!$application) $this->abort404();
        $this->render('application', ['title'=>$application['application_number'],'application'=>$application]);
    }

    public function documents(): void
    {
        $this->render('documents', ['title'=>'Documents','documents'=>$this->portal->documents($this->id()),'applications'=>$this->portal->applications($this->id())]);
    }

    public function uploadDocument(): never
    {
        $this->csrf();
        $file=$this->request->file('document');
        if (!$file || ($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) $this->redirectError('/customer/documents','Please select a valid file.');
        $allowed=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($allowed[$mime]) || (int)$file['size']>10*1024*1024) $this->redirectError('/customer/documents','Only PDF, JPG, PNG or WEBP files up to 10 MB are allowed.');
        $folder='uploads/customer-documents/' . $this->id() . '/' . date('Y/m');
        $absolute=PUBLIC_PATH . '/' . $folder;
        if (!is_dir($absolute) && !mkdir($absolute,0770,true) && !is_dir($absolute)) $this->redirectError('/customer/documents','Upload storage is unavailable.');
        $stored=bin2hex(random_bytes(18)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'],$absolute . '/' . $stored)) $this->redirectError('/customer/documents','Document upload failed.');
        $id=$this->portal->addDocument($this->id(),[
            'service_request_id'=>$this->request->integer('service_request_id'),'category'=>$this->request->string('category','others'),
            'title'=>$this->request->string('title') ?: pathinfo($file['name'],PATHINFO_FILENAME),'original_name'=>basename($file['name']),
            'stored_name'=>$stored,'file_path'=>'/' . $folder . '/' . $stored,'mime_type'=>$mime,'file_size'=>(int)$file['size'],
        ]);
        $this->portal->log($this->id(),'document_uploaded','Document uploaded.','document',$id);
        $this->notifications->notifyAdmin('document', 'New partner document', 'A partner uploaded a document for review.', '/admin/documents', $this->id(), 'document', $id);
        $this->redirectSuccess('/customer/documents','Document uploaded securely.');
    }

    public function deleteDocument(int $id): never
    {
        $this->csrf();
        $path=$this->portal->deleteOwnDocument($this->id(),$id);
        if ($path) {
            $absolute=PUBLIC_PATH . '/' . ltrim($path,'/');
            if (is_file($absolute)) @unlink($absolute);
            $this->portal->log($this->id(),'document_deleted','Customer document deleted.','document',$id);
        }
        $this->redirectSuccess('/customer/documents','Document removed.');
    }

    public function downloadDocument(int $id): never
    {
        $document=$this->portal->documentForCustomer($this->id(),$id);
        if(!$document)$this->abort404();
        $base=realpath(BASE_PATH);
        $absolute=realpath(BASE_PATH . '/' . ltrim((string)$document['file_path'],'/'));
        if(!$base||!$absolute||!str_starts_with($absolute,$base . DIRECTORY_SEPARATOR)||!is_file($absolute)){
            $this->abort404();
        }
        $this->portal->log($this->id(),'document_downloaded','Customer downloaded a document.','document',$id);
        header('Content-Type: ' . ((string)$document['mime_type'] ?: 'application/octet-stream'));
        header('Content-Length: ' . (string)filesize($absolute));
        header('Content-Disposition: attachment; filename="' . rawurlencode(basename((string)$document['original_name'])) . '"');
        header('Cache-Control: private, no-store, max-age=0');
        readfile($absolute);
        exit;
    }

    public function notifications(): void
    {
        $this->render('notifications', ['title'=>'Notifications','notifications'=>$this->portal->notifications($this->id())]);
    }

    public function notificationFeed(): never
    {
        $items=array_slice($this->portal->notifications($this->id()),0,10);
        foreach ($items as &$item) $item['action_url'] = !empty($item['action_url']) ? url((string) $item['action_url']) : url('/customer/notifications');
        unset($item);
        $unread=count(array_filter($items,static fn(array $item): bool => empty($item['read_at'])));
        $this->json(['count'=>$unread,'notifications'=>$items]);
    }

    public function markAllNotifications(): never
    {
        $this->csrf();
        $this->portal->markAllNotifications($this->id());
        $this->json(['ok'=>true,'count'=>0]);
    }

    public function notification(int $id): never
    {
        $this->csrf();
        $this->portal->markNotification($this->id(),$id,$this->request->boolean('archive'));
        $this->redirect('/customer/notifications');
    }

    public function billing(): void
    {
        $this->reconcilePendingPayment();
        $this->render('billing', ['title'=>'Billing','summary'=>$this->portal->billingSummary($this->id()),'invoices'=>$this->portal->invoices($this->id()),'payments'=>$this->portal->payments($this->id())]);
    }

    public function invoices(): void
    {
        $this->reconcilePendingPayment();
        $this->render('invoices', ['title'=>'Invoices','invoices'=>$this->portal->invoices($this->id())]);
    }

    public function invoice(int $id): void
    {
        $invoice=$this->portal->invoice($this->id(),$id);
        if (!$invoice) $this->abort404();
        $this->render('invoice', ['title'=>'Invoice ' . $invoice['invoice_number'],'invoice'=>$invoice,'profile'=>$this->portal->findCustomer($this->id())]);
    }

    public function payments(): void
    {
        $this->render('payments', ['title'=>'Payment History','payments'=>$this->portal->payments($this->id())]);
    }

    private function reconcilePendingPayment(): void
    {
        if($this->gateway->isDemo())return;
        $order=$this->portal->pendingPaymentOrderForCustomer($this->id());
        if(!$order)return;
        try{
            $verified=$this->gateway->verifyOrder(
                (string)$order['provider_order_id'],
                (string)($order['provider_session_id']??''),
                $this->gateway->merchantOrderIdFromStoredResponse($order['response_payload']??null)
            );
            $status=(string)$verified['status'];
            if($this->gateway->isSuccessfulStatus($status)){
                $transactionId=(string)($verified['transaction_id']?:('PY-'.$order['provider_order_id']));
                $result=$this->portal->completePaymentOrder((int)$order['id'],$transactionId,['portal_reconciliation'=>$verified['raw']]);
                if(!$result['already_paid']){
                    $this->portal->log($this->id(),'payment_completed','PayYantra payment reconciled automatically.','invoice',(int)$result['invoice_id']);
                    $this->notifications->notifyAdmin('payment','Payment received','A PayYantra payment was reconciled from the partner portal.','/admin/customers',$this->id(),'invoice',(int)$result['invoice_id']);
                }
                return;
            }
            if($this->gateway->isFailedStatus($status)){
                $this->portal->failPaymentOrder((int)$order['id'],'PayYantra reported '.$status.'.',['portal_reconciliation'=>$verified['raw']]);
            }
        }catch(\Throwable $exception){
            // Billing must remain available during a temporary gateway outage. The return page,
            // webhook, or a later portal visit will retry reconciliation.
            error_log('Portal payment reconciliation failed: '.$exception->getMessage());
        }
    }

    public function activity(): void
    {
        $this->render('activity', ['title'=>'Activity','activity'=>$this->portal->activity($this->id())]);
    }

    public function support(): void
    {
        $this->render('support', ['title' => 'Help & Support', 'tickets' => $this->portal->supportTickets($this->id())]);
    }

    public function submitSupport(): never
    {
        $this->csrf();
        try {
            $ticketId=$this->portal->createSupportTicket($this->id(),$this->request->string('topic','general'),$this->request->string('subject'),$this->request->string('message'));
            $this->portal->log($this->id(),'support_ticket_created','Support request submitted.','support_ticket',$ticketId);
            $this->notifications->notifyAdmin('support','New support request',$this->request->string('subject'),'/admin/notification-center',$this->id(),'support_ticket',$ticketId);
            $this->redirectSuccess('/customer/support','Your support request was sent to the Udyam team. We will respond here and by email if needed.');
        } catch (\InvalidArgumentException $e) {
            $this->redirectError('/customer/support',$e->getMessage());
        }
    }

    private function id(): int { return (int)$this->customer['id']; }
}
