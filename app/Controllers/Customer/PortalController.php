<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\CustomerPortalRepository;
use App\Services\PayyantraGateway;

final class PortalController extends CustomerController
{
    public function __construct(
        private readonly CustomerPortalRepository $portal,
        private readonly Request $request,
        private readonly PayyantraGateway $gateway
    )
    {
        parent::__construct();
    }

    public function dashboard(): void
    {
        $this->render('dashboard', ['title'=>'Dashboard','data'=>$this->portal->dashboard($this->id())]);
    }

    public function search(): void
    {
        header('Content-Type: application/json');
        try {
            $query = $this->request->string('q');
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
        ]));
        $this->portal->log($this->id(),'profile_updated','Customer profile updated.');
        $this->redirectSuccess('/customer/profile','Profile updated successfully.');
    }

    public function plans(): void
    {
        $this->render('plans', ['title'=>'Subscription Plans','plans'=>$this->portal->plans()]);
    }

    public function subscribe(int $id): never
    {
        $this->csrf();
        $checkout=null;
        try{
            $checkout=$this->portal->startSubscriptionCheckout($this->id(),$id);
            $gateway=$this->gateway->createOrder($checkout,$this->customer);
            $this->portal->attachGatewayOrder((int)$checkout['order_id'],$gateway);
            $this->portal->log($this->id(),'subscription_checkout_started','Subscription checkout started.','subscription',(int)$checkout['subscription_id']);
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

    public function notification(int $id): never
    {
        $this->csrf();
        $this->portal->markNotification($this->id(),$id,$this->request->boolean('archive'));
        $this->redirect('/customer/notifications');
    }

    public function billing(): void
    {
        $this->render('billing', ['title'=>'Billing','summary'=>$this->portal->billingSummary($this->id()),'invoices'=>$this->portal->invoices($this->id()),'payments'=>$this->portal->payments($this->id())]);
    }

    public function invoices(): void
    {
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

    public function activity(): void
    {
        $this->render('activity', ['title'=>'Activity','activity'=>$this->portal->activity($this->id())]);
    }

    private function id(): int { return (int)$this->customer['id']; }
}