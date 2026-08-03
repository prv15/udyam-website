<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CustomerPortalRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function findCustomerByEmail(string $email): ?array
    {
        $statement = $this->db->prepare(
            "SELECT u.*, p.company_name, p.mobile, p.email_verified_at, p.logo_path,
                    p.profile_photo_path, p.preferred_communication
             FROM users u
             LEFT JOIN customer_profiles p ON p.user_id = u.id
             WHERE LOWER(u.email) = LOWER(:email) AND u.user_type = 'customer'
               AND u.deleted_at IS NULL LIMIT 1"
        );
        $statement->execute(['email' => $email]);
        return $statement->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $statement=$this->db->prepare('SELECT COUNT(*) FROM users WHERE LOWER(email)=LOWER(?) AND deleted_at IS NULL');
        $statement->execute([$email]);
        return (int)$statement->fetchColumn()>0;
    }

    public function findCustomer(int $id): ?array
    {
        $statement = $this->db->prepare(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.status, u.created_at,
                    p.id AS profile_id, p.customer_record_id, p.company_name, p.mobile,
                    p.gst_number, p.pan_number, p.address_line_1, p.address_line_2,
                    p.city, p.state, p.postal_code, p.country, p.logo_path,
                    p.profile_photo_path, p.contact_person, p.business_type,
                    p.social_links, p.preferred_communication, p.email_verified_at,
                    p.last_login_at, p.created_at AS profile_created_at,
                    p.updated_at AS profile_updated_at
             FROM users u LEFT JOIN customer_profiles p ON p.user_id = u.id
             WHERE u.id = :id AND u.user_type = 'customer' AND u.deleted_at IS NULL LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function createCustomer(array $data): int
    {
        $this->db->beginTransaction();
        try {
            [$firstName, $lastName] = $this->splitName((string) $data['full_name']);
            $user = $this->db->prepare(
                "INSERT INTO users (first_name,last_name,email,password,user_type,status)
                 VALUES (:first_name,:last_name,:email,:password,'customer','active')"
            );
            $user->execute([
                'first_name' => $firstName, 'last_name' => $lastName,
                'email' => strtolower((string) $data['email']),
                'password' => (string) $data['password'],
            ]);
            $id = (int) $this->db->lastInsertId();
            $findRecord=$this->db->prepare("SELECT id,data FROM admin_records WHERE module='customers' AND deleted_at IS NULL AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(data,'$.email')))=LOWER(?) LIMIT 1");
            $findRecord->execute([$data['email']]);$existing=$findRecord->fetch();
            $recordData=array_merge($existing?(json_decode((string)$existing['data'],true)?:[]):[],[
                'user_id'=>$id,'email'=>strtolower((string)$data['email']),'phone'=>$data['mobile']?:null,
                'company'=>$data['company_name']?:null,'joined_at'=>date('Y-m-d'),
            ]);
            if($existing){
                $recordId=(int)$existing['id'];
                $this->db->prepare('UPDATE admin_records SET title=?,data=?,updated_at=NOW() WHERE id=?')->execute([$data['full_name'],json_encode($recordData),$recordId]);
            }else{
                $record=$this->db->prepare("INSERT INTO admin_records (module,title,slug,status,data) VALUES ('customers',:title,:slug,'active',:data)");
                $record->execute(['title'=>$data['full_name'],'slug'=>'customer-'.$id,'data'=>json_encode($recordData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
                $recordId=(int)$this->db->lastInsertId();
            }
            $profile = $this->db->prepare(
                'INSERT INTO customer_profiles (user_id,customer_record_id,company_name,mobile,contact_person)
                 VALUES (:user_id,:customer_record_id,:company_name,:mobile,:contact_person)'
            );
            $profile->execute([
                'user_id' => $id, 'customer_record_id'=>$recordId, 'company_name' => $data['company_name'] ?: null,
                'mobile' => $data['mobile'] ?: null, 'contact_person' => $data['full_name'],
            ]);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function createToken(int $userId, string $purpose, string $plainToken, \DateTimeImmutable $expires): void
    {
        $this->db->prepare(
            'UPDATE customer_auth_tokens SET used_at = NOW()
             WHERE user_id = :user_id AND purpose = :purpose AND used_at IS NULL'
        )->execute(['user_id' => $userId, 'purpose' => $purpose]);
        $this->db->prepare(
            'INSERT INTO customer_auth_tokens (user_id,token_hash,purpose,expires_at)
             VALUES (:user_id,:token_hash,:purpose,:expires_at)'
        )->execute([
            'user_id' => $userId, 'token_hash' => hash('sha256', $plainToken),
            'purpose' => $purpose, 'expires_at' => $expires->format('Y-m-d H:i:s'),
        ]);
    }

    public function consumeToken(string $plainToken, string $purpose): ?int
    {
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'SELECT id,user_id FROM customer_auth_tokens
                 WHERE token_hash = :hash AND purpose = :purpose AND used_at IS NULL
                   AND expires_at > NOW() LIMIT 1 FOR UPDATE'
            );
            $statement->execute(['hash' => hash('sha256', $plainToken), 'purpose' => $purpose]);
            $token = $statement->fetch();
            if (!$token) {
                $this->db->rollBack();
                return null;
            }
            $this->db->prepare('UPDATE customer_auth_tokens SET used_at = NOW() WHERE id = :id')
                ->execute(['id' => $token['id']]);
            $this->db->commit();
            return (int) $token['user_id'];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function verifyEmail(int $userId): void
    {
        $this->db->prepare('UPDATE customer_profiles SET email_verified_at = COALESCE(email_verified_at,NOW()) WHERE user_id = :id')
            ->execute(['id' => $userId]);
    }

    public function updatePassword(int $userId, string $hash): void
    {
        $this->db->prepare('UPDATE users SET password = :password WHERE id = :id AND user_type = "customer"')
            ->execute(['password' => $hash, 'id' => $userId]);
    }

    public function touchLogin(int $userId): void
    {
        $this->db->prepare('UPDATE customer_profiles SET last_login_at = NOW() WHERE user_id = :id')
            ->execute(['id' => $userId]);
    }

    public function tooManyAttempts(string $identity, string $action, int $limit = 5, int $minutes = 15): bool
    {
        $hash = hash('sha256', strtolower($identity));
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM customer_auth_attempts
             WHERE identity_hash = :hash AND action = :action
               AND attempted_at >= :cutoff'
        );
        $statement->execute([
            'hash' => $hash,
            'action' => $action,
            'cutoff' => (new \DateTimeImmutable("-{$minutes} minutes"))->format('Y-m-d H:i:s'),
        ]);
        return (int) $statement->fetchColumn() >= $limit;
    }

    public function recordAttempt(string $identity, string $action): void
    {
        $this->db->prepare('INSERT INTO customer_auth_attempts (identity_hash,action) VALUES (:hash,:action)')
            ->execute(['hash' => hash('sha256', strtolower($identity)), 'action' => $action]);
    }

    public function clearAttempts(string $identity, string $action): void
    {
        $this->db->prepare('DELETE FROM customer_auth_attempts WHERE identity_hash = :hash AND action = :action')
            ->execute(['hash' => hash('sha256', strtolower($identity)), 'action' => $action]);
    }

    public function dashboard(int $customerId): array
    {
        return [
            'subscription' => $this->one(
                "SELECT cs.*,sp.name plan_name,sp.benefits FROM customer_subscriptions cs
                 JOIN subscription_plans sp ON sp.id=cs.plan_id
                 WHERE cs.customer_id=? AND cs.status='active' ORDER BY cs.expires_at DESC LIMIT 1",
                [$customerId]
            ),
            'pendingApplications' => $this->scalar(
                "SELECT COUNT(*) FROM customer_service_requests WHERE customer_id=? AND status IN ('submitted','under_review','information_requested','in_progress')",
                [$customerId]
            ),
            'approvedServices' => $this->scalar(
                "SELECT COUNT(*) FROM customer_service_requests WHERE customer_id=? AND status IN ('approved','in_progress','completed')",
                [$customerId]
            ),
            'outstanding' => $this->scalar(
                "SELECT COALESCE(SUM(total_amount-paid_amount),0) FROM customer_invoices
                 WHERE customer_id=? AND status IN ('issued','partial','overdue')",
                [$customerId]
            ),
            'invoices' => $this->all(
                'SELECT * FROM customer_invoices WHERE customer_id=? ORDER BY issue_date DESC,id DESC LIMIT 5',
                [$customerId]
            ),
            'notifications' => $this->all(
                'SELECT * FROM customer_notifications WHERE customer_id=? AND archived_at IS NULL ORDER BY created_at DESC LIMIT 5',
                [$customerId]
            ),
            'activity' => $this->all(
                'SELECT * FROM customer_activity_logs WHERE customer_id=? ORDER BY created_at DESC LIMIT 8',
                [$customerId]
            ),
        ];
    }

    public function plans(): array
    {
        return $this->all("SELECT * FROM subscription_plans WHERE status='active' AND deleted_at IS NULL ORDER BY sort_order,name");
    }

    public function plan(int $id, bool $activeOnly = true): ?array
    {
        $sql='SELECT * FROM subscription_plans WHERE id=? AND deleted_at IS NULL';
        if($activeOnly)$sql.=" AND status='active'";
        return $this->one($sql.' LIMIT 1',[$id]);
    }

    public function services(): array
    {
        return $this->all("SELECT * FROM admin_records WHERE module='services' AND status IN ('published','active') AND deleted_at IS NULL ORDER BY sort_order,title");
    }

    public function applications(int $customerId): array
    {
        return $this->all(
            'SELECT r.*,a.title service_title,u.first_name assigned_first_name,u.last_name assigned_last_name
             FROM customer_service_requests r JOIN admin_records a ON a.id=r.service_record_id
             LEFT JOIN users u ON u.id=r.assigned_user_id WHERE r.customer_id=?
             ORDER BY r.submitted_at DESC',
            [$customerId]
        );
    }

    public function applyForService(int $customerId, int $serviceId, string $projectName, string $remarks): int
    {
        $service = $this->one("SELECT id,title FROM admin_records WHERE id=? AND module='services' AND status IN ('published','active') AND deleted_at IS NULL", [$serviceId]);
        if (!$service) throw new \InvalidArgumentException('Service not found.');
        $number = 'UDY-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $this->db->beginTransaction();
        try {
            $application = $this->db->prepare(
                "INSERT INTO admin_records (module,title,slug,status,data)
                 VALUES ('applications',:title,:slug,'submitted',:data)"
            );
            $application->execute([
                'title' => $projectName ?: $service['title'],
                'slug' => strtolower($number),
                'data' => json_encode([
                    'customer_id' => $customerId, 'application_type' => $service['title'],
                    'reference' => $number, 'notes' => $remarks, 'submitted_at' => date('Y-m-d'),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $applicationId = (int) $this->db->lastInsertId();
            $request = $this->db->prepare(
                'INSERT INTO customer_service_requests
                 (customer_id,service_record_id,application_record_id,application_number,project_name,customer_remarks)
                 VALUES (?,?,?,?,?,?)'
            );
            $request->execute([$customerId,$serviceId,$applicationId,$number,$projectName ?: $service['title'],$remarks ?: null]);
            $requestId = (int) $this->db->lastInsertId();
            $this->db->prepare(
                "INSERT INTO customer_application_timeline
                 (service_request_id,status,title,remarks,visible_to_customer,created_by)
                 VALUES (?,'submitted','Application submitted',?,1,?)"
            )->execute([$requestId,$remarks ?: null,$customerId]);
            $this->db->commit();
            return $requestId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function application(int $customerId, int $id): ?array
    {
        $request = $this->one(
            'SELECT r.*,s.title service_title,u.first_name assigned_first_name,u.last_name assigned_last_name
             FROM customer_service_requests r JOIN admin_records s ON s.id=r.service_record_id
             LEFT JOIN users u ON u.id=r.assigned_user_id WHERE r.id=? AND r.customer_id=? LIMIT 1',
            [$id,$customerId]
        );
        if (!$request) return null;
        $request['timeline'] = $this->all(
            'SELECT * FROM customer_application_timeline WHERE service_request_id=? AND visible_to_customer=1 ORDER BY created_at',
            [$id]
        );
        $request['documents'] = $this->all(
            'SELECT * FROM customer_documents WHERE service_request_id=? AND customer_id=? AND deleted_at IS NULL ORDER BY created_at DESC',
            [$id,$customerId]
        );
        return $request;
    }

    public function subscribe(int $customerId, int $planId): int
    {
        $plan = $this->one("SELECT * FROM subscription_plans WHERE id=? AND status='active' AND deleted_at IS NULL", [$planId]);
        if (!$plan) throw new \InvalidArgumentException('Plan not found.');
        $months = ['monthly'=>1,'quarterly'=>3,'half_yearly'=>6,'yearly'=>12,'one_time'=>1200][$plan['billing_cycle']] ?? 12;
        $statement = $this->db->prepare(
            "INSERT INTO customer_subscriptions (customer_id,plan_id,status,starts_at,expires_at,amount)
             VALUES (?,?,'pending',CURDATE(),DATE_ADD(CURDATE(),INTERVAL ? MONTH),?)"
        );
        $statement->execute([$customerId,$planId,$months,$plan['price']]);
        return (int) $this->db->lastInsertId();
    }

    public function startSubscriptionCheckout(int $customerId, int $planId): array
    {
        $plan=$this->plan($planId);
        if(!$plan)throw new \InvalidArgumentException('Subscription plan not found.');
        $charge=round((float)($plan['initial_payment']?:$plan['price']),2);
        if($charge<=0)throw new \InvalidArgumentException('This subscription plan is not available for online checkout.');
        $gstRate=round((float)$plan['gst_rate'],2);
        if((int)($plan['gst_inclusive']??0)===1){
            $total=$charge;
            $taxable=round($total/(1+($gstRate/100)),2);
            $gst=round($total-$taxable,2);
        }else{
            $taxable=$charge;
            $gst=round($taxable*$gstRate/100,2);
            $total=$taxable+$gst;
        }
        $months=['monthly'=>1,'quarterly'=>3,'half_yearly'=>6,'yearly'=>12,'one_time'=>1200][$plan['billing_cycle']]??12;
        $invoiceToken=bin2hex(random_bytes(32));
        $orderToken=bin2hex(random_bytes(32));
        $number='UV/'.date('Y-m').'/' . str_pad((string)((int)$this->scalar('SELECT COUNT(*)+1 FROM customer_invoices')),4,'0',STR_PAD_LEFT) . '-' . strtoupper(bin2hex(random_bytes(2)));
        $cgst=round($gst/2,2);
        $sgst=round($gst-$cgst,2);
        $this->db->beginTransaction();
        try{
            $this->db->prepare(
                "INSERT INTO customer_subscriptions (customer_id,plan_id,status,starts_at,expires_at,amount)
                 VALUES (?,?,'pending',CURDATE(),DATE_ADD(CURDATE(),INTERVAL ? MONTH),?)"
            )->execute([$customerId,$planId,$months,$plan['price']]);
            $subscriptionId=(int)$this->db->lastInsertId();
            $this->db->prepare(
                'INSERT INTO customer_invoices
                 (customer_id,subscription_id,invoice_number,public_token,invoice_type,billing_cycle,installment_number,
                  subtotal,taxable_amount,cgst,sgst,total_amount,status,issue_date,due_date,notes)
                 VALUES (?,?,?,?,"subscription",?,1,?,?,?,?,?,"issued",CURDATE(),CURDATE(),?)'
            )->execute([
                $customerId,$subscriptionId,$number,$invoiceToken,$plan['billing_cycle'],$charge,$taxable,
                $cgst,$sgst,$total,
                !empty($plan['followup_payment'])
                    ? 'Initial instalment. A follow-up invoice will be due after '.(int)$plan['followup_due_days'].' days.'
                    : 'Subscription payment for '.$plan['name'].'.',
            ]);
            $invoiceId=(int)$this->db->lastInsertId();
            $this->db->prepare(
                'INSERT INTO customer_invoice_items (invoice_id,description,quantity,unit_price,gst_rate,line_total)
                 VALUES (?,?,1,?,?,?)'
            )->execute([$invoiceId,$plan['name'].(!empty($plan['followup_payment'])?' - Initial instalment':''),$taxable,$gstRate,$total]);
            $this->db->prepare(
                'INSERT INTO customer_payment_orders
                 (customer_id,invoice_id,subscription_id,public_token,amount,status,expires_at)
                 VALUES (?,?,?,?,?,"created",DATE_ADD(NOW(),INTERVAL 30 MINUTE))'
            )->execute([$customerId,$invoiceId,$subscriptionId,$orderToken,$total]);
            $orderId=(int)$this->db->lastInsertId();
            $this->db->commit();
            return [
                'order_id'=>$orderId,'order_token'=>$orderToken,'invoice_id'=>$invoiceId,
                'invoice_token'=>$invoiceToken,'subscription_id'=>$subscriptionId,'amount'=>$total,
                'currency'=>'INR','plan'=>$plan,
            ];
        }catch(\Throwable $e){
            if($this->db->inTransaction())$this->db->rollBack();
            throw $e;
        }
    }

    public function attachGatewayOrder(int $orderId, array $gateway): void
    {
        $this->db->prepare(
            'UPDATE customer_payment_orders SET provider_order_id=?,provider_session_id=?,checkout_url=?,
             request_payload=?,response_payload=?,status="pending" WHERE id=? AND status="created"'
        )->execute([
            $gateway['provider_order_id']??null,$gateway['provider_session_id']??null,
            $gateway['checkout_url']??null,json_encode($gateway['request']??[],JSON_UNESCAPED_SLASHES),
            json_encode($gateway['response']??[],JSON_UNESCAPED_SLASHES),$orderId,
        ]);
    }

    public function startInvoiceCheckout(int $customerId, int $invoiceId): array
    {
        $this->db->beginTransaction();
        try{
            $invoice=$this->one(
                "SELECT i.*,sp.name subscription_name,sr.project_name,svc.title service_name
                 FROM customer_invoices i
                 LEFT JOIN customer_subscriptions cs ON cs.id=i.subscription_id
                 LEFT JOIN subscription_plans sp ON sp.id=cs.plan_id
                 LEFT JOIN customer_service_requests sr ON sr.id=i.service_request_id
                 LEFT JOIN admin_records svc ON svc.id=sr.service_record_id
                 WHERE i.id=? AND i.customer_id=? LIMIT 1 FOR UPDATE",
                [$invoiceId,$customerId]
            );
            if(!$invoice)throw new \InvalidArgumentException('Invoice not found.');
            if(!in_array($invoice['status'],['issued','partial','overdue'],true))throw new \InvalidArgumentException('This invoice is not available for payment.');
            $amount=round((float)$invoice['total_amount']-(float)$invoice['paid_amount'],2);
            if($amount<=0)throw new \InvalidArgumentException('This invoice has no outstanding balance.');
            $invoiceToken=(string)($invoice['public_token']??'');
            if($invoiceToken===''){
                $invoiceToken=bin2hex(random_bytes(32));
                $this->db->prepare('UPDATE customer_invoices SET public_token=? WHERE id=?')->execute([$invoiceToken,$invoiceId]);
            }
            $orderToken=bin2hex(random_bytes(32));
            $this->db->prepare(
                'INSERT INTO customer_payment_orders
                 (customer_id,invoice_id,subscription_id,public_token,amount,status,expires_at)
                 VALUES (?,?,?,?,?,"created",DATE_ADD(NOW(),INTERVAL 30 MINUTE))'
            )->execute([$customerId,$invoiceId,$invoice['subscription_id']?:null,$orderToken,$amount]);
            $orderId=(int)$this->db->lastInsertId();
            $this->db->commit();
            return [
                'order_id'=>$orderId,'order_token'=>$orderToken,'invoice_id'=>$invoiceId,
                'invoice_token'=>$invoiceToken,'subscription_id'=>$invoice['subscription_id']?:null,
                'amount'=>$amount,'currency'=>'INR',
                'description'=>$invoice['subscription_name']??$invoice['service_name']??$invoice['project_name']??('Invoice '.$invoice['invoice_number']),
            ];
        }catch(\Throwable $e){
            if($this->db->inTransaction())$this->db->rollBack();
            throw $e;
        }
    }

    public function paymentOrder(int $customerId, string $token): ?array
    {
        return $this->one(
            "SELECT po.*,i.invoice_number,i.public_token invoice_token,i.total_amount,i.status invoice_status,
                    COALESCE(sp.name,svc.title,sr.project_name,'Udyam Invoice') plan_name,
                    COALESCE(sp.subtitle,'Service Invoice') plan_subtitle,sp.badge plan_badge
             FROM customer_payment_orders po
             JOIN customer_invoices i ON i.id=po.invoice_id
             LEFT JOIN customer_subscriptions cs ON cs.id=po.subscription_id
             LEFT JOIN subscription_plans sp ON sp.id=cs.plan_id
             LEFT JOIN customer_service_requests sr ON sr.id=i.service_request_id
             LEFT JOIN admin_records svc ON svc.id=sr.service_record_id
             WHERE po.customer_id=? AND po.public_token=? LIMIT 1",
            [$customerId,$token]
        );
    }

    public function paymentOrderByProviderId(string $providerOrderId): ?array
    {
        return $this->one('SELECT * FROM customer_payment_orders WHERE provider_order_id=? LIMIT 1',[$providerOrderId]);
    }

    public function completePaymentOrder(int $orderId, string $transactionId, array $payload = []): array
    {
        $this->db->beginTransaction();
        try{
            $order=$this->one('SELECT * FROM customer_payment_orders WHERE id=? LIMIT 1 FOR UPDATE',[$orderId]);
            if(!$order)throw new \InvalidArgumentException('Payment order not found.');
            $invoice=$this->one('SELECT * FROM customer_invoices WHERE id=? LIMIT 1 FOR UPDATE',[(int)$order['invoice_id']]);
            if(!$invoice)throw new \RuntimeException('Invoice not found for payment order.');
            if($order['status']==='paid'){
                $this->db->commit();
                return ['invoice_id'=>(int)$invoice['id'],'invoice_token'=>(string)$invoice['public_token'],'customer_id'=>(int)$order['customer_id'],'already_paid'=>true];
            }
            if(!in_array($order['status'],['created','pending'],true))throw new \InvalidArgumentException('This payment order can no longer be completed.');
            $amount=round((float)$order['amount'],2);
            $receipt='UVR/'.date('Ymd').'/' . strtoupper(bin2hex(random_bytes(3)));
            $this->db->prepare(
                'INSERT INTO customer_payments
                 (customer_id,invoice_id,transaction_id,method,amount,status,payment_date,receipt_number,notes)
                 VALUES (?,?,?,"gateway",?,"successful",NOW(),?,"PayYantra checkout")'
            )->execute([(int)$order['customer_id'],(int)$order['invoice_id'],$transactionId,$amount,$receipt]);
            $this->db->prepare('UPDATE customer_invoices SET paid_amount=total_amount,status="paid",paid_at=NOW() WHERE id=?')
                ->execute([(int)$order['invoice_id']]);
            $this->db->prepare('UPDATE customer_payment_orders SET status="paid",paid_at=NOW(),callback_payload=? WHERE id=?')
                ->execute([json_encode($payload,JSON_UNESCAPED_SLASHES),$orderId]);
            if(!empty($order['subscription_id'])){
                $this->db->prepare('UPDATE customer_subscriptions SET status="active",starts_at=CURDATE() WHERE id=?')
                    ->execute([(int)$order['subscription_id']]);
            }
            $this->db->prepare(
                'INSERT INTO customer_notifications (customer_id,type,title,message,action_url)
                 VALUES (?,"invoice","Payment received",?,?)'
            )->execute([
                (int)$order['customer_id'],'Payment for invoice '.$invoice['invoice_number'].' was successful.',
                '/customer/billing/invoices/'.$invoice['id'],
            ]);
            $this->db->commit();
            return ['invoice_id'=>(int)$invoice['id'],'invoice_token'=>(string)$invoice['public_token'],'customer_id'=>(int)$order['customer_id'],'already_paid'=>false];
        }catch(\Throwable $e){
            if($this->db->inTransaction())$this->db->rollBack();
            throw $e;
        }
    }

    public function failPaymentOrder(int $orderId, string $reason, array $payload = []): void
    {
        $this->db->beginTransaction();
        try{
            $order=$this->one('SELECT * FROM customer_payment_orders WHERE id=? LIMIT 1 FOR UPDATE',[$orderId]);
            if($order&&in_array($order['status'],['created','pending'],true)){
                $this->db->prepare('UPDATE customer_payment_orders SET status="failed",failure_reason=?,callback_payload=? WHERE id=?')
                    ->execute([$reason,json_encode($payload,JSON_UNESCAPED_SLASHES),$orderId]);
                $this->db->prepare('UPDATE customer_invoices SET status="cancelled" WHERE id=? AND status="issued"')
                    ->execute([(int)$order['invoice_id']]);
                if(!empty($order['subscription_id'])){
                    $this->db->prepare('UPDATE customer_subscriptions SET status="cancelled" WHERE id=? AND status="pending"')
                        ->execute([(int)$order['subscription_id']]);
                }
            }
            $this->db->commit();
        }catch(\Throwable $e){
            if($this->db->inTransaction())$this->db->rollBack();
            throw $e;
        }
    }

    public function documents(int $customerId): array
    {
        return $this->all('SELECT * FROM customer_documents WHERE customer_id=? AND deleted_at IS NULL ORDER BY created_at DESC', [$customerId]);
    }

    public function addDocument(int $customerId, array $file): int
    {
        $serviceRequestId=(int)($file['service_request_id']??0);
        if($serviceRequestId&&!$this->one('SELECT id FROM customer_service_requests WHERE id=? AND customer_id=?',[$serviceRequestId,$customerId])){
            throw new \InvalidArgumentException('The selected application does not belong to this customer.');
        }
        $statement = $this->db->prepare(
            'INSERT INTO customer_documents
             (customer_id,service_request_id,category,title,original_name,stored_name,file_path,mime_type,file_size,source,uploaded_by)
             VALUES (:customer,:request,:category,:title,:original,:stored,:path,:mime,:size,"customer",:uploader)'
        );
        $statement->execute([
            'customer'=>$customerId,'request'=>$serviceRequestId ?: null,'category'=>$file['category'],
            'title'=>$file['title'],'original'=>$file['original_name'],'stored'=>$file['stored_name'],
            'path'=>$file['file_path'],'mime'=>$file['mime_type'],'size'=>$file['file_size'],'uploader'=>$customerId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function documentForCustomer(int $customerId, int $documentId): ?array
    {
        return $this->one(
            'SELECT * FROM customer_documents WHERE id=? AND customer_id=? AND deleted_at IS NULL LIMIT 1',
            [$documentId,$customerId]
        );
    }

    public function deleteOwnDocument(int $customerId, int $documentId): ?string
    {
        $document = $this->one("SELECT file_path FROM customer_documents WHERE id=? AND customer_id=? AND source='customer' AND deleted_at IS NULL", [$documentId,$customerId]);
        if (!$document) return null;
        $this->db->prepare('UPDATE customer_documents SET deleted_at=NOW() WHERE id=? AND customer_id=?')->execute([$documentId,$customerId]);
        return (string) $document['file_path'];
    }

    public function notifications(int $customerId): array
    {
        return $this->all('SELECT * FROM customer_notifications WHERE customer_id=? AND archived_at IS NULL ORDER BY created_at DESC', [$customerId]);
    }

    public function markNotification(int $customerId, int $id, bool $archive = false): void
    {
        $column = $archive ? 'archived_at' : 'read_at';
        $this->db->prepare("UPDATE customer_notifications SET {$column}=NOW(),read_at=COALESCE(read_at,NOW()) WHERE id=? AND customer_id=?")
            ->execute([$id,$customerId]);
    }

    public function invoices(int $customerId): array
    {
        return $this->all(
            'SELECT i.*,sp.name subscription_name,sr.project_name,svc.title service_name
             FROM customer_invoices i
             LEFT JOIN customer_subscriptions cs ON cs.id=i.subscription_id
             LEFT JOIN subscription_plans sp ON sp.id=cs.plan_id
             LEFT JOIN customer_service_requests sr ON sr.id=i.service_request_id
             LEFT JOIN admin_records svc ON svc.id=sr.service_record_id
             WHERE i.customer_id=? ORDER BY i.issue_date DESC,i.id DESC',
            [$customerId]
        );
    }

    public function invoice(int $customerId, int $invoiceId): ?array
    {
        $invoice = $this->one('SELECT * FROM customer_invoices WHERE id=? AND customer_id=? LIMIT 1', [$invoiceId, $customerId]);
        if (!$invoice) return null;
        $invoice['items'] = $this->all('SELECT * FROM customer_invoice_items WHERE invoice_id=? ORDER BY sort_order,id', [$invoiceId]);
        $invoice['payments'] = $this->all('SELECT * FROM customer_payments WHERE invoice_id=? ORDER BY payment_date', [$invoiceId]);
        return $invoice;
    }

    public function publicInvoice(string $token): ?array
    {
        $invoice=$this->one(
            'SELECT i.*,u.first_name,u.last_name,u.email,p.company_name,p.mobile,p.gst_number,p.pan_number,
                    p.address_line_1,p.address_line_2,p.city,p.state,p.postal_code,p.country,
                    sp.name subscription_name,sr.project_name,svc.title service_name
             FROM customer_invoices i
             JOIN users u ON u.id=i.customer_id
             LEFT JOIN customer_profiles p ON p.user_id=u.id
             LEFT JOIN customer_subscriptions cs ON cs.id=i.subscription_id
             LEFT JOIN subscription_plans sp ON sp.id=cs.plan_id
             LEFT JOIN customer_service_requests sr ON sr.id=i.service_request_id
             LEFT JOIN admin_records svc ON svc.id=sr.service_record_id
             WHERE i.public_token=? LIMIT 1',
            [$token]
        );
        if(!$invoice)return null;
        $invoice['items']=$this->all('SELECT * FROM customer_invoice_items WHERE invoice_id=? ORDER BY sort_order,id',[(int)$invoice['id']]);
        $invoice['payments']=$this->all('SELECT * FROM customer_payments WHERE invoice_id=? ORDER BY payment_date',[(int)$invoice['id']]);
        return $invoice;
    }

    public function payments(int $customerId): array
    {
        return $this->all(
            'SELECT p.*,i.invoice_number FROM customer_payments p JOIN customer_invoices i ON i.id=p.invoice_id
             WHERE p.customer_id=? ORDER BY p.payment_date DESC',
            [$customerId]
        );
    }

    public function billingSummary(int $customerId): array
    {
        return [
            'totalPaid' => $this->scalar("SELECT COALESCE(SUM(amount),0) FROM customer_payments WHERE customer_id=? AND status='successful'", [$customerId]),
            'pending' => $this->scalar("SELECT COALESCE(SUM(total_amount-paid_amount),0) FROM customer_invoices WHERE customer_id=? AND status IN ('issued','partial','overdue')", [$customerId]),
            'gst' => $this->scalar("SELECT COALESCE(SUM(cgst+sgst+igst),0) FROM customer_invoices WHERE customer_id=? AND status <> 'cancelled'", [$customerId]),
            'lastPayment' => $this->one("SELECT * FROM customer_payments WHERE customer_id=? AND status='successful' ORDER BY payment_date DESC LIMIT 1", [$customerId]),
            'renewal' => $this->one("SELECT cs.expires_at,sp.name FROM customer_subscriptions cs JOIN subscription_plans sp ON sp.id=cs.plan_id WHERE cs.customer_id=? AND cs.status='active' ORDER BY cs.expires_at LIMIT 1", [$customerId]),
        ];
    }

    public function activity(int $customerId): array
    {
        return $this->all('SELECT * FROM customer_activity_logs WHERE customer_id=? ORDER BY created_at DESC LIMIT 100', [$customerId]);
    }

    public function adminWorkspace(int $customerRecordId): ?array
    {
        $record=$this->one("SELECT * FROM admin_records WHERE id=? AND module='customers' AND deleted_at IS NULL",[$customerRecordId]);
        if(!$record)return null;
        $data=json_decode((string)$record['data'],true)?:[];
        $email=(string)($data['email']??'');
        $customer=$email!==''?$this->findCustomerByEmail($email):null;
        return [
            'record'=>array_merge($record,$data),'customer'=>$customer,
            'dashboard'=>$customer?$this->dashboard((int)$customer['id']):[],
            'applications'=>$customer?$this->applications((int)$customer['id']):[],
            'documents'=>$customer?$this->documents((int)$customer['id']):[],
            'invoices'=>$customer?$this->invoices((int)$customer['id']):[],
            'payments'=>$customer?$this->payments((int)$customer['id']):[],
            'activity'=>$customer?$this->activity((int)$customer['id']):[],
            'notifications'=>$customer?$this->notifications((int)$customer['id']):[],
        ];
    }

    public function adminPlans(): array
    {
        return $this->all('SELECT * FROM subscription_plans WHERE deleted_at IS NULL ORDER BY sort_order,name');
    }

    public function adminPlan(int $id): ?array
    {
        return $this->plan($id,false);
    }

    public function savePlan(array $data, ?int $id, int $adminId): int
    {
        $params=[
            'name'=>$data['name'],'slug'=>$data['slug'],'category'=>$data['category']?:null,
            'subtitle'=>$data['subtitle']?:null,'badge'=>$data['badge']?:null,
            'description'=>$data['description']?:null,'ideal_for'=>$data['ideal_for']?:null,
            'highlight_text'=>$data['highlight_text']?:null,
            'billing_cycle'=>$data['billing_cycle'],'price'=>$data['price'],'gst_rate'=>$data['gst_rate'],
            'gst_inclusive'=>(int)$data['gst_inclusive'],'initial_payment'=>$data['initial_payment']?:null,
            'followup_payment'=>$data['followup_payment']?:null,'followup_due_days'=>$data['followup_due_days']?:null,
            'featured'=>(int)$data['featured'],'benefits'=>json_encode($this->parseBenefits((string)$data['benefits']),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'disclaimer'=>$data['disclaimer']?:null,'status'=>$data['status'],'sort_order'=>$data['sort_order'],'admin'=>$adminId,
        ];
        if($id){
            $params['id']=$id;
            $this->db->prepare(
                'UPDATE subscription_plans SET name=:name,slug=:slug,category=:category,subtitle=:subtitle,badge=:badge,
                 description=:description,ideal_for=:ideal_for,highlight_text=:highlight_text,billing_cycle=:billing_cycle,
                 price=:price,gst_rate=:gst_rate,gst_inclusive=:gst_inclusive,initial_payment=:initial_payment,
                 followup_payment=:followup_payment,followup_due_days=:followup_due_days,featured=:featured,
                 benefits=:benefits,disclaimer=:disclaimer,status=:status,sort_order=:sort_order,updated_by=:admin WHERE id=:id'
            )->execute($params);
            return $id;
        }
        $this->db->prepare(
            'INSERT INTO subscription_plans
             (name,slug,category,subtitle,badge,description,ideal_for,highlight_text,billing_cycle,price,gst_rate,
              gst_inclusive,initial_payment,followup_payment,followup_due_days,featured,benefits,disclaimer,status,
              sort_order,created_by,updated_by)
             VALUES
             (:name,:slug,:category,:subtitle,:badge,:description,:ideal_for,:highlight_text,:billing_cycle,:price,:gst_rate,
              :gst_inclusive,:initial_payment,:followup_payment,:followup_due_days,:featured,:benefits,:disclaimer,:status,
              :sort_order,:admin,:admin)'
        )->execute($params);
        return (int)$this->db->lastInsertId();
    }

    public function archivePlan(int $id, int $adminId): void
    {
        $this->db->prepare('UPDATE subscription_plans SET status="archived",updated_by=? WHERE id=? AND deleted_at IS NULL')
            ->execute([$adminId,$id]);
    }

    public function generateInvoice(int $customerId,array $data,int $adminId): int
    {
        $subtotal=round((float)$data['amount'],2);
        $gstRate=round((float)$data['gst_rate'],2);
        if($subtotal<=0)throw new \InvalidArgumentException('Invoice amount must be greater than zero.');
        if($gstRate<0||$gstRate>100)throw new \InvalidArgumentException('GST rate must be between 0 and 100.');
        $dueDate=\DateTimeImmutable::createFromFormat('!Y-m-d',(string)$data['due_date']);
        if(!$dueDate||$dueDate->format('Y-m-d')!==(string)$data['due_date'])throw new \InvalidArgumentException('Please select a valid due date.');
        if(empty($data['description']))throw new \InvalidArgumentException('Invoice description is required.');
        if(!in_array($data['invoice_type'],['subscription','service'],true))throw new \InvalidArgumentException('Invalid invoice type.');
        if(!empty($data['subscription_id'])&&!$this->one('SELECT id FROM customer_subscriptions WHERE id=? AND customer_id=?',[(int)$data['subscription_id'],$customerId])){
            throw new \InvalidArgumentException('The selected subscription does not belong to this customer.');
        }
        if(!empty($data['service_request_id'])&&!$this->one('SELECT id FROM customer_service_requests WHERE id=? AND customer_id=?',[(int)$data['service_request_id'],$customerId])){
            throw new \InvalidArgumentException('The selected application does not belong to this customer.');
        }
        $gst=round($subtotal*$gstRate/100,2);$total=$subtotal+$gst;
        $cgst=round($gst/2,2);$sgst=round($gst-$cgst,2);
        $number='UV/'.date('Y-m').'/' . str_pad((string)((int)$this->scalar('SELECT COUNT(*)+1 FROM customer_invoices')),4,'0',STR_PAD_LEFT) . '-' . strtoupper(bin2hex(random_bytes(2)));
        $publicToken=bin2hex(random_bytes(32));
        $this->db->beginTransaction();
        try{
            $this->db->prepare('INSERT INTO customer_invoices (customer_id,subscription_id,service_request_id,invoice_number,public_token,invoice_type,billing_cycle,subtotal,taxable_amount,cgst,sgst,total_amount,status,issue_date,due_date,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"issued",CURDATE(),?,?,?)')->execute([
                $customerId,$data['subscription_id']?:null,$data['service_request_id']?:null,$number,$publicToken,$data['invoice_type'],$data['billing_cycle']?:null,$subtotal,$subtotal,$cgst,$sgst,$total,$data['due_date'],$data['notes']?:null,$adminId
            ]);
            $id=(int)$this->db->lastInsertId();
            $this->db->prepare('INSERT INTO customer_invoice_items (invoice_id,description,quantity,unit_price,gst_rate,line_total) VALUES (?,?,1,?,?,?)')->execute([$id,$data['description'],$subtotal,$gstRate,$total]);
            $this->db->commit();return $id;
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function recordPayment(int $customerId,array $data,int $adminId): int
    {
        $this->db->beginTransaction();
        try{
            $invoice=$this->one(
                "SELECT id,total_amount,paid_amount,status FROM customer_invoices
                 WHERE id=? AND customer_id=? AND status NOT IN ('cancelled','refunded') LIMIT 1 FOR UPDATE",
                [(int)$data['invoice_id'],$customerId]
            );
            $amount=round((float)$data['amount'],2);
            if(!$invoice)throw new \InvalidArgumentException('Invoice not found for this customer.');
            if($amount<=0)throw new \InvalidArgumentException('Payment amount must be greater than zero.');
            $outstanding=round((float)$invoice['total_amount']-(float)$invoice['paid_amount'],2);
            if($amount>$outstanding)throw new \InvalidArgumentException('Payment amount cannot exceed the outstanding balance.');
            $receipt='UVR/'.date('Ymd').'/' . strtoupper(bin2hex(random_bytes(2)));
            $this->db->prepare('INSERT INTO customer_payments (customer_id,invoice_id,transaction_id,method,amount,status,payment_date,receipt_number,notes,recorded_by) VALUES (?,?,?,?,?,"successful",?,?,?,?)')->execute([
                $customerId,$data['invoice_id'],$data['transaction_id']?:null,$data['method'],$amount,$data['payment_date'],$receipt,$data['notes']?:null,$adminId
            ]);
            $id=(int)$this->db->lastInsertId();
            $this->db->prepare("UPDATE customer_invoices SET paid_amount=paid_amount+?,status=IF(paid_amount+?>=total_amount,'paid','partial'),paid_at=IF(paid_amount+?>=total_amount,NOW(),paid_at) WHERE id=? AND customer_id=?")->execute([$amount,$amount,$amount,$data['invoice_id'],$customerId]);
            $this->db->commit();return $id;
        }catch(\Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    public function bridgeLegacyRecord(string $module,int $recordId,array $data,string $status):void
    {
        $email=strtolower(trim((string)($data['customer_email']??$data['recipient']??'')));
        if(!filter_var($email,FILTER_VALIDATE_EMAIL))return;
        $customer=$this->findCustomerByEmail($email);if(!$customer)return;$customerId=(int)$customer['id'];
        if($module==='notifications'){
            $this->db->prepare('INSERT INTO customer_notifications (customer_id,notification_record_id,type,title,message) VALUES (?,?,"general",?,?)')->execute([$customerId,$recordId,$data['title']??'Notification',$data['message']??'']);
        }elseif($module==='documents'&&!empty($data['file_url'])){
            $path=(string)$data['file_url'];
            $this->db->prepare('INSERT INTO customer_documents (customer_id,document_record_id,category,title,original_name,stored_name,file_path,mime_type,file_size,source,status) VALUES (?,?,"others",?,?,?,?, "application/octet-stream",0,"admin",?)')->execute([$customerId,$recordId,$data['title']??'Document',basename($path),basename($path),$path,$status==='verified'?'verified':'uploaded']);
        }elseif($module==='applications'){
            $service=$this->one("SELECT id,title FROM admin_records WHERE module='services' AND deleted_at IS NULL AND LOWER(title)=LOWER(?) LIMIT 1",[(string)($data['application_type']??'')]);
            if(!$service)return;
            $number=(string)($data['reference']??'')?:'UDY-'.date('Ym').'-'.strtoupper(bin2hex(random_bytes(3)));
            $this->db->prepare('INSERT INTO customer_service_requests (customer_id,service_record_id,application_record_id,application_number,project_name,status,customer_remarks) VALUES (?,?,?,?,?,?,?)')->execute([$customerId,$service['id'],$recordId,$number,$data['title']??$service['title'],$status,$data['notes']??null]);
        }
    }

    public function syncLegacyRecord(string $module,int $recordId,array $data,string $status,?int $adminId=null):void
    {
        if($module!=='applications')return;
        $allowed=['submitted','under_review','information_requested','approved','in_progress','completed','rejected','closed'];
        $portalStatus=in_array($status,$allowed,true)?$status:'under_review';
        $request=$this->one('SELECT id,customer_id,status FROM customer_service_requests WHERE application_record_id=? LIMIT 1',[$recordId]);
        if(!$request)return;
        $this->db->prepare('UPDATE customer_service_requests SET status=?,assigned_user_id=?,admin_remarks=? WHERE id=?')->execute([
            $portalStatus,($data['assigned_staff_id']??'')!==''?(int)$data['assigned_staff_id']:null,$data['customer_visible_update']??$data['notes']??null,$request['id']
        ]);
        if($request['status']!==$portalStatus||!empty($data['customer_visible_update'])){
            $this->db->prepare('INSERT INTO customer_application_timeline (service_request_id,status,title,remarks,visible_to_customer,created_by) VALUES (?,?,?,?,1,?)')->execute([
                $request['id'],$portalStatus,ucwords(str_replace('_',' ',$portalStatus)),$data['customer_visible_update']??null,$adminId
            ]);
            $this->db->prepare('INSERT INTO customer_notifications (customer_id,type,title,message,action_url) VALUES (?,"application",?,?,?)')->execute([
                $request['customer_id'],'Application updated','Your application status is now '.str_replace('_',' ',$portalStatus),'/customer/applications/'.$request['id']
            ]);
        }
    }

    public function log(int $customerId, string $action, string $description, ?string $type = null, ?int $subjectId = null): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO customer_activity_logs
             (customer_id,actor_id,action,subject_type,subject_id,description,ip_address,user_agent)
             VALUES (:customer,:actor,:action,:type,:subject,:description,:ip,:agent)'
        );
        $statement->execute([
            'customer' => $customerId, 'actor' => $customerId, 'action' => $action,
            'type' => $type, 'subject' => $subjectId, 'description' => $description,
            'ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
            'agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);
    }

    public function updateProfile(int $customerId, array $data): void
    {
        $fields = ['company_name','mobile','gst_number','pan_number','address_line_1','address_line_2','city','state','postal_code','country','contact_person','business_type','preferred_communication'];
        $assignments = [];
        $params = ['id' => $customerId];
        foreach ($fields as $field) {
            $assignments[] = "{$field}=:{$field}";
            $params[$field] = $data[$field] ?? null;
        }
        $this->db->prepare('UPDATE customer_profiles SET ' . implode(',', $assignments) . ' WHERE user_id=:id')->execute($params);
    }

    private function one(string $sql, array $params = []): ?array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetch() ?: null;
    }

    private function all(string $sql, array $params = []): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    private function scalar(string $sql, array $params = []): float|int
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        $value = $statement->fetchColumn();
        return is_numeric($value) ? (float) $value : 0;
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: ['Customer'];
        return [$parts[0], $parts[1] ?? ''];
    }

    private function parseBenefits(string $value): array
    {
        $benefits=[];
        foreach(preg_split('/\R/',$value)?:[] as $line){
            $line=trim($line);
            if($line==='')continue;
            [$title,$description]=array_pad(array_map('trim',explode('|',$line,2)),2,'');
            $benefits[]=$description!==''?['title'=>$title,'description'=>$description]:$title;
        }
        return $benefits;
    }
}
