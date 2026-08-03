<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\CustomerPortalRepository;

final class CustomerBillingController extends AdminController
{
    public function __construct(private readonly CustomerPortalRepository $portal,private readonly Request $request){parent::__construct();}

    public function workspace(int $id):void
    {
        $workspace=$this->portal->adminWorkspace($id);if(!$workspace)$this->abort404();
        $this->render('customers/workspace',['title'=>'Customer Overview','workspace'=>$workspace,'tab'=>$this->request->string('tab','overview')]);
    }

    public function plans():void{$this->render('customers/plans',['title'=>'Subscription Plans','plans'=>$this->portal->adminPlans()]);}
    public function planForm():void{$this->render('customers/plan-form',['title'=>'Create Subscription Plan','plan'=>[]]);}
    public function editPlan(int $id):void
    {
        $plan=$this->portal->adminPlan($id);if(!$plan)$this->abort404();
        $this->render('customers/plan-form',['title'=>'Edit Subscription Plan','plan'=>$plan]);
    }
    public function savePlan():never
    {
        $this->csrf();
        try{$this->portal->savePlan($this->planData(),null,(int)$this->shared['user']['id']);}
        catch(\Throwable $e){error_log('Plan creation failed: '.$e->getMessage());$this->redirectError('/admin/subscription-plans/create','Plan could not be created. Check the slug and amounts.');}
        $this->redirectSuccess('/admin/subscription-plans','Subscription plan created.');
    }
    public function updatePlan(int $id):never
    {
        $this->csrf();if(!$this->portal->adminPlan($id))$this->abort404();
        try{$this->portal->savePlan($this->planData(),$id,(int)$this->shared['user']['id']);}
        catch(\Throwable $e){error_log('Plan update failed: '.$e->getMessage());$this->redirectError('/admin/subscription-plans/edit/'.$id,'Plan could not be updated. Check the slug and amounts.');}
        $this->redirectSuccess('/admin/subscription-plans','Subscription plan updated.');
    }
    public function archivePlan(int $id):never
    {
        $this->csrf();$this->portal->archivePlan($id,(int)$this->shared['user']['id']);
        $this->redirectSuccess('/admin/subscription-plans','Subscription plan archived.');
    }
    public function invoice(int $id):never
    {
        $this->csrf();$workspace=$this->portal->adminWorkspace($id);if(!$workspace)$this->abort404();$customerId=(int)($workspace['customer']['id']??0);
        if(!$customerId)$this->redirectError('/admin/customers/workspace/'.$id,'This customer has no linked portal account.');
        try{
            $invoiceId=$this->portal->generateInvoice($customerId,[
                'invoice_type'=>$this->request->string('invoice_type','service'),'subscription_id'=>$this->request->integer('subscription_id'),
                'service_request_id'=>$this->request->integer('service_request_id'),'billing_cycle'=>$this->request->string('billing_cycle'),
                'description'=>$this->request->string('description'),'amount'=>$this->request->float('amount'),'gst_rate'=>$this->request->float('gst_rate',18),
                'due_date'=>$this->request->string('due_date'),'notes'=>$this->request->string('notes')
            ],(int)$this->shared['user']['id']);
        }catch(\InvalidArgumentException $e){
            $this->redirectError('/admin/customers/workspace/'.$id.'?tab=billing',$e->getMessage());
        }catch(\Throwable $e){
            error_log('Invoice generation failed: '.$e->getMessage());
            $this->redirectError('/admin/customers/workspace/'.$id.'?tab=billing','Invoice generation failed. Please try again.');
        }
        $this->redirectSuccess('/admin/customers/workspace/'.$id.'?tab=billing','Invoice generated: #'.$invoiceId);
    }
    public function payment(int $id):never
    {
        $this->csrf();$workspace=$this->portal->adminWorkspace($id);if(!$workspace)$this->abort404();$customerId=(int)($workspace['customer']['id']??0);
        if(!$customerId)$this->redirectError('/admin/customers/workspace/'.$id,'This customer has no linked portal account.');
        try{
            $this->portal->recordPayment($customerId,[
                'invoice_id'=>$this->request->integer('invoice_id'),'transaction_id'=>$this->request->string('transaction_id'),
                'method'=>$this->request->string('method','bank_transfer'),'amount'=>$this->request->float('amount'),
                'payment_date'=>str_replace('T',' ',$this->request->string('payment_date')?:date('Y-m-d H:i:s')),'notes'=>$this->request->string('notes')
            ],(int)$this->shared['user']['id']);
        }catch(\InvalidArgumentException $e){
            $this->redirectError('/admin/customers/workspace/'.$id.'?tab=payments',$e->getMessage());
        }catch(\Throwable $e){
            error_log('Payment recording failed: '.$e->getMessage());
            $this->redirectError('/admin/customers/workspace/'.$id.'?tab=payments','Payment could not be recorded. Please try again.');
        }
        $this->redirectSuccess('/admin/customers/workspace/'.$id.'?tab=payments','Payment recorded.');
    }
    private function planData():array
    {
        $name=$this->request->string('name');
        $price=max(0,$this->request->float('price'));
        return [
            'name'=>$name,'slug'=>$this->request->string('slug')?:slugify($name),
            'category'=>$this->request->string('category'),'subtitle'=>$this->request->string('subtitle'),
            'badge'=>$this->request->string('badge'),'description'=>$this->request->string('description'),
            'ideal_for'=>$this->request->string('ideal_for'),'highlight_text'=>$this->request->string('highlight_text'),
            'billing_cycle'=>$this->request->string('billing_cycle','yearly'),'price'=>$price,
            'gst_rate'=>min(100,max(0,$this->request->float('gst_rate',18))),
            'gst_inclusive'=>$this->request->boolean('gst_inclusive'),
            'initial_payment'=>$this->request->float('initial_payment')?:$price,
            'followup_payment'=>$this->request->float('followup_payment'),
            'followup_due_days'=>$this->request->integer('followup_due_days'),
            'featured'=>$this->request->boolean('featured'),'benefits'=>$this->request->string('benefits'),
            'disclaimer'=>$this->request->string('disclaimer'),'status'=>$this->request->string('status','draft'),
            'sort_order'=>$this->request->integer('sort_order'),
        ];
    }
    private function csrf():void{if(!csrf_validate()){http_response_code(419);exit('Session expired.');}}
}
