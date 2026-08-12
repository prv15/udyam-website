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
                    p.profile_photo_path, p.preferred_communication, p.contact_person,
                    p.business_type, p.gst_number, p.pan_number, p.address_line_1,
                    p.address_line_2, p.city, p.state, p.postal_code, p.country, p.annual_turnover_range,
                    p.customer_record_id
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
        $statement=$this->db->prepare('SELECT id,user_type,deleted_at FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1');
        $statement->execute([$email]);$user=$statement->fetch();
        if(!$user||!empty($user['deleted_at']))return false;
        if($user['user_type']!=='customer')return true;
        $record=$this->db->prepare(
            "SELECT COUNT(*) FROM admin_records WHERE module='customers' AND deleted_at IS NULL
             AND (CAST(JSON_UNQUOTE(JSON_EXTRACT(data,'$.user_id')) AS UNSIGNED)=? OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(data,'$.email')))=LOWER(?))"
        );
        $record->execute([(int)$user['id'],$email]);
        return (int)$record->fetchColumn()>0;
    }

    public function findCustomer(int $id): ?array
    {
        $statement = $this->db->prepare(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.status, u.created_at,
                    p.id AS profile_id, p.customer_record_id, p.company_name, p.mobile,
                    p.gst_number, p.pan_number, p.address_line_1, p.address_line_2,
                    p.city, p.state, p.postal_code, p.country, p.logo_path,
                    p.profile_photo_path, p.contact_person, p.business_type,
                    p.social_links, p.preferred_communication, p.annual_turnover_range, p.email_verified_at,
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
            $email=strtolower((string)$data['email']);
            $findUser=$this->db->prepare('SELECT id,user_type,deleted_at FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1 FOR UPDATE');
            $findUser->execute([$email]);$existingUser=$findUser->fetch();
            if($existingUser){
                if($existingUser['user_type']!=='customer'){
                    throw new \InvalidArgumentException('An account already exists for this email.');
                }
                $id=(int)$existingUser['id'];
                if(empty($existingUser['deleted_at'])){
                    $liveRecord=$this->db->prepare(
                        "SELECT COUNT(*) FROM admin_records WHERE module='customers' AND deleted_at IS NULL
                         AND (CAST(JSON_UNQUOTE(JSON_EXTRACT(data,'$.user_id')) AS UNSIGNED)=? OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(data,'$.email')))=LOWER(?))"
                    );
                    $liveRecord->execute([$id,$email]);
                    if((int)$liveRecord->fetchColumn()>0)throw new \InvalidArgumentException('An account already exists for this email.');
                }
                $this->db->prepare(
                    "UPDATE users SET first_name=?,last_name=?,password=?,user_type='customer',status='active',deleted_at=NULL,updated_at=NOW() WHERE id=?"
                )->execute([$firstName,$lastName,(string)$data['password'],$id]);
            }else{
                $user=$this->db->prepare(
                    "INSERT INTO users (first_name,last_name,email,password,user_type,status)
                     VALUES (:first_name,:last_name,:email,:password,'customer','active')"
                );
                $user->execute(['first_name'=>$firstName,'last_name'=>$lastName,'email'=>$email,'password'=>(string)$data['password']]);
                $id=(int)$this->db->lastInsertId();
            }
            $findRecord=$this->db->prepare(
                "SELECT id,data FROM admin_records WHERE module='customers'
                 AND (CAST(JSON_UNQUOTE(JSON_EXTRACT(data,'$.user_id')) AS UNSIGNED)=? OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(data,'$.email')))=LOWER(?))
                 ORDER BY (deleted_at IS NULL) DESC,id DESC LIMIT 1 FOR UPDATE"
            );
            $findRecord->execute([$id,$email]);$existing=$findRecord->fetch();
            $recordData=array_merge($existing?(json_decode((string)$existing['data'],true)?:[]):[],[
                'user_id'=>$id,'email'=>$email,'phone'=>$data['mobile']?:null,
                'company'=>$data['company_name']?:null,'joined_at'=>date('Y-m-d'),
            ]);
            if($existing){
                $recordId=(int)$existing['id'];
                $this->db->prepare("UPDATE admin_records SET title=?,status='active',data=?,deleted_at=NULL,updated_at=NOW() WHERE id=?")
                    ->execute([$data['full_name'],json_encode($recordData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$recordId]);
            }else{
                $record=$this->db->prepare("INSERT INTO admin_records (module,title,slug,status,data) VALUES ('customers',:title,:slug,'active',:data)");
                $record->execute(['title'=>$data['full_name'],'slug'=>'customer-'.$id,'data'=>json_encode($recordData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
                $recordId=(int)$this->db->lastInsertId();
            }
            $profile=$this->db->prepare('SELECT id FROM customer_profiles WHERE user_id=? LIMIT 1 FOR UPDATE');
            $profile->execute([$id]);$profileId=$profile->fetchColumn();
            if($profileId){
                $this->db->prepare('UPDATE customer_profiles SET customer_record_id=?,company_name=?,mobile=?,contact_person=?,email_verified_at=NULL,updated_at=NOW() WHERE id=?')
                    ->execute([$recordId,$data['company_name']?:null,$data['mobile']?:null,$data['full_name'],(int)$profileId]);
            }else{
                $this->db->prepare('INSERT INTO customer_profiles (user_id,customer_record_id,company_name,mobile,contact_person) VALUES (?,?,?,?,?)')
                    ->execute([$id,$recordId,$data['company_name']?:null,$data['mobile']?:null,$data['full_name']]);
            }
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteCustomerRecords(array $recordIds): int
    {
        $recordIds=array_values(array_unique(array_filter(array_map('intval',$recordIds),static fn(int $id):bool=>$id>0)));
        if($recordIds===[])return 0;
        $placeholders=implode(',',array_fill(0,count($recordIds),'?'));
        $this->db->beginTransaction();
        try{
            $statement=$this->db->prepare("SELECT id,data FROM admin_records WHERE module='customers' AND deleted_at IS NULL AND id IN ({$placeholders}) FOR UPDATE");
            $statement->execute($recordIds);$records=$statement->fetchAll();
            foreach($records as $record){
                $data=json_decode((string)$record['data'],true)?:[];
                $userId=(int)($data['user_id']??0);
                if($userId>0){
                    $this->db->prepare("UPDATE users SET status='inactive',deleted_at=NOW(),updated_at=NOW() WHERE id=? AND user_type='customer' AND deleted_at IS NULL")
                        ->execute([$userId]);
                }elseif(!empty($data['email'])){
                    $this->db->prepare("UPDATE users SET status='inactive',deleted_at=NOW(),updated_at=NOW() WHERE LOWER(email)=LOWER(?) AND user_type='customer' AND deleted_at IS NULL")
                        ->execute([(string)$data['email']]);
                }
            }
            $delete=$this->db->prepare("UPDATE admin_records SET deleted_at=NOW(),updated_at=NOW() WHERE module='customers' AND deleted_at IS NULL AND id IN ({$placeholders})");
            $delete->execute($recordIds);$count=$delete->rowCount();
            $this->db->commit();return $count;
        }catch(\Throwable $exception){
            if($this->db->inTransaction())$this->db->rollBack();
            throw $exception;
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
        $months = [];
        for ($offset = 5; $offset >= 0; $offset--) {
            $date = new \DateTimeImmutable("first day of -{$offset} months");
            $months[$date->format('Y-m')] = ['label' => $date->format('M'), 'value' => 0.0];
        }
        foreach ($this->all(
            "SELECT DATE_FORMAT(payment_date,'%Y-%m') period,SUM(amount) amount
             FROM customer_payments WHERE customer_id=? AND status='successful'
               AND payment_date >= DATE_FORMAT(DATE_SUB(CURDATE(),INTERVAL 5 MONTH),'%Y-%m-01')
             GROUP BY DATE_FORMAT(payment_date,'%Y-%m') ORDER BY period",
            [$customerId]
        ) as $row) {
            if (isset($months[(string) $row['period']])) {
                $months[(string) $row['period']]['value'] = (float) $row['amount'];
            }
        }

        return [
            'profile' => $this->one(
                'SELECT city,state,email_verified_at,company_name,mobile,contact_person,gst_number,address_line_1 FROM customer_profiles WHERE user_id=? LIMIT 1',
                [$customerId]
            ),
            'tenders' => array_map(
                function (array $record): array {
                    $data = json_decode((string) ($record['data'] ?? ''), true);
                    return array_merge($record, is_array($data) ? $data : []);
                },
                $this->all(
                    "SELECT * FROM admin_records
                     WHERE module='tenders' AND status IN ('published','active') AND deleted_at IS NULL
                     ORDER BY sort_order, created_at DESC LIMIT 6"
                )
            ),
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
            'unreadNotifications' => $this->scalar(
                'SELECT COUNT(*) FROM customer_notifications WHERE customer_id=? AND read_at IS NULL AND archived_at IS NULL',
                [$customerId]
            ),
            'applicationStatus' => $this->all(
                'SELECT status,COUNT(*) total FROM customer_service_requests WHERE customer_id=? GROUP BY status',
                [$customerId]
            ),
            'billingTrend' => [
                'labels' => array_column($months, 'label'),
                'values' => array_column($months, 'value'),
            ],
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
            $this->one("SELECT id FROM users WHERE id=? AND user_type='customer' LIMIT 1 FOR UPDATE",[$customerId]);
            if($this->one("SELECT id FROM customer_subscriptions WHERE customer_id=? AND status='active' AND (expires_at IS NULL OR expires_at>=CURDATE()) LIMIT 1",[$customerId])){
                throw new \InvalidArgumentException('You already have an active subscription. Request a cancellation or pause before choosing another plan.');
            }
            $existing=$this->one(
                "SELECT po.id order_id,po.public_token order_token,po.invoice_id,po.subscription_id,po.amount,po.checkout_url,
                        i.public_token invoice_token
                 FROM customer_payment_orders po JOIN customer_subscriptions cs ON cs.id=po.subscription_id
                 JOIN customer_invoices i ON i.id=po.invoice_id
                 WHERE po.customer_id=? AND cs.plan_id=? AND cs.status='pending' AND po.status IN ('created','pending')
                   AND (po.expires_at IS NULL OR po.expires_at>NOW()) ORDER BY po.id DESC LIMIT 1 FOR UPDATE",
                [$customerId,$planId]
            );
            if($existing&&!empty($existing['checkout_url'])){
                $this->db->commit();
                return array_merge($existing,['currency'=>'INR','plan'=>$plan,'resume'=>true]);
            }
            if($existing){
                $this->db->prepare("UPDATE customer_payment_orders SET status='failed',failure_reason='Gateway checkout was not initialized.' WHERE id=? AND status='created'")->execute([(int)$existing['order_id']]);
                $this->db->prepare("UPDATE customer_invoices SET status='cancelled' WHERE id=? AND status='issued'")->execute([(int)$existing['invoice_id']]);
                $this->db->prepare("UPDATE customer_subscriptions SET status='cancelled' WHERE id=? AND status='pending'")->execute([(int)$existing['subscription_id']]);
            }
            $this->db->prepare("UPDATE customer_subscriptions cs JOIN customer_payment_orders po ON po.subscription_id=cs.id SET cs.status='cancelled',po.status='expired' WHERE cs.customer_id=? AND cs.status='pending' AND po.status IN ('created','pending') AND po.expires_at<=NOW()")
                ->execute([$customerId]);
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
        $statement=$this->db->prepare(
            'UPDATE customer_payment_orders SET provider_order_id=?,provider_session_id=?,checkout_url=?,
             request_payload=?,response_payload=?,status="pending" WHERE id=? AND status="created"'
        );$statement->execute([
            $gateway['provider_order_id']??null,$gateway['provider_session_id']??null,
            $gateway['checkout_url']??null,json_encode($gateway['request']??[],JSON_UNESCAPED_SLASHES),
            json_encode($gateway['response']??[],JSON_UNESCAPED_SLASHES),$orderId,
        ]);
        if($statement->rowCount()!==1)throw new \RuntimeException('The payment order could not be activated. Please start checkout again.');
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
            $existing=$this->one(
                "SELECT id order_id,public_token order_token,invoice_id,subscription_id,amount,checkout_url
                 FROM customer_payment_orders WHERE customer_id=? AND invoice_id=? AND status IN ('created','pending')
                   AND (expires_at IS NULL OR expires_at>NOW()) ORDER BY id DESC LIMIT 1 FOR UPDATE",[$customerId,$invoiceId]
            );
            if($existing&&!empty($existing['checkout_url'])){
                $this->db->commit();
                return array_merge($existing,['invoice_token'=>(string)($invoice['public_token']??''),'currency'=>'INR','description'=>$invoice['subscription_name']??$invoice['service_name']??$invoice['project_name']??('Invoice '.$invoice['invoice_number']),'resume'=>true]);
            }
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
        return $this->one(
            'SELECT * FROM customer_payment_orders WHERE provider_order_id=? OR provider_session_id=? LIMIT 1',
            [$providerOrderId,$providerOrderId]
        );
    }

    public function pendingPaymentOrderForCustomer(int $customerId): ?array
    {
        return $this->one(
            "SELECT * FROM customer_payment_orders
             WHERE customer_id=? AND status IN ('created','pending','failed','expired')
               AND provider_order_id IS NOT NULL
               AND created_at>=DATE_SUB(NOW(),INTERVAL 14 DAY)
             ORDER BY id DESC LIMIT 1",
            [$customerId]
        );
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
                // Idempotent callbacks must also heal any legacy partial transition where the
                // order was marked paid but its linked invoice/subscription remained pending.
                $this->db->prepare('UPDATE customer_invoices SET paid_amount=total_amount,status="paid",paid_at=COALESCE(paid_at,NOW()) WHERE id=?')
                    ->execute([(int)$order['invoice_id']]);
                if(!empty($order['subscription_id'])){
                    $this->db->prepare(
                        'UPDATE customer_subscriptions
                         SET expires_at=IF(expires_at IS NULL,NULL,DATE_ADD(CURDATE(),INTERVAL GREATEST(1,DATEDIFF(expires_at,starts_at)) DAY)),
                             starts_at=CURDATE(),status="active"
                         WHERE id=? AND status<>"active"'
                    )
                        ->execute([(int)$order['subscription_id']]);
                }
                $this->db->commit();
                return ['invoice_id'=>(int)$invoice['id'],'invoice_token'=>(string)$invoice['public_token'],'customer_id'=>(int)$order['customer_id'],'already_paid'=>true];
            }
            // Verified provider success is authoritative and may heal an earlier timeout,
            // failed callback, or expiry because gateway events can arrive out of order.
            $amount=round((float)$order['amount'],2);
            $receipt='UVR/'.date('Ymd').'/' . strtoupper(bin2hex(random_bytes(3)));
            $existingPayment=$transactionId!==''?$this->one('SELECT * FROM customer_payments WHERE transaction_id=? LIMIT 1 FOR UPDATE',[$transactionId]):null;
            if($existingPayment&&(int)$existingPayment['invoice_id']!==(int)$order['invoice_id'])throw new \RuntimeException('The gateway transaction is already linked to another invoice.');
            if(!$existingPayment){$this->db->prepare(
                'INSERT INTO customer_payments
                 (customer_id,invoice_id,transaction_id,method,amount,status,payment_date,receipt_number,notes)
                 VALUES (?,?,?,"gateway",?,"successful",NOW(),?,"PayYantra checkout")'
            )->execute([(int)$order['customer_id'],(int)$order['invoice_id'],$transactionId,$amount,$receipt]);}
            $this->db->prepare('UPDATE customer_invoices SET paid_amount=total_amount,status="paid",paid_at=NOW() WHERE id=?')
                ->execute([(int)$order['invoice_id']]);
            $this->db->prepare('UPDATE customer_payment_orders SET status="paid",paid_at=COALESCE(paid_at,NOW()),failure_reason=NULL,callback_payload=? WHERE id=?')
                ->execute([json_encode($payload,JSON_UNESCAPED_SLASHES),$orderId]);
            if(!empty($order['subscription_id'])){
                $this->db->prepare(
                    'UPDATE customer_subscriptions
                     SET expires_at=IF(expires_at IS NULL,NULL,DATE_ADD(CURDATE(),INTERVAL GREATEST(1,DATEDIFF(expires_at,starts_at)) DAY)),
                         starts_at=CURDATE(),status="active" WHERE id=?'
                )
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

    public function paymentOrderByToken(string $token): ?array
    {
        return $this->one('SELECT * FROM customer_payment_orders WHERE public_token=? LIMIT 1',[$token]);
    }

    public function reconciliationCandidates(int $limit=100): array
    {
        $limit=max(1,min(500,$limit));
        // Scheduled reconciliation is for current checkouts. Old orders may belong to retired
        // PayYantra credentials/environments and cannot be queried with the current access token.
        // A late verified webhook/return can still heal an older order via completePaymentOrder().
        return $this->all("SELECT * FROM customer_payment_orders WHERE provider_order_id IS NOT NULL AND status IN ('created','pending') AND created_at>=DATE_SUB(NOW(),INTERVAL 48 HOUR) ORDER BY updated_at ASC,id ASC LIMIT {$limit}");
    }

    public function documents(int $customerId): array
    {
        return $this->all('SELECT * FROM customer_documents WHERE customer_id=? AND deleted_at IS NULL ORDER BY created_at DESC', [$customerId]);
    }

    public function tendersForCustomer(int $page=1,int $perPage=10,array $filters=[]): array
    {
        [$where,$params]=$this->tenderConditions($filters);$offset=max(0,($page-1)*$perPage);$sort=$filters['sort']??'latest';
        $date="COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data,'$.closing_date')),'')";
        $order=in_array($sort,['latest','deadline','ongoing'],true)?"{$date} ASC,created_at DESC":"{$date} DESC,created_at DESC";
        $statement=$this->db->prepare("SELECT * FROM admin_records WHERE {$where} ORDER BY {$order} LIMIT :limit OFFSET :offset");
        foreach($params as $key=>$value)$statement->bindValue($key,$value);$statement->bindValue(':limit',$perPage,\PDO::PARAM_INT);$statement->bindValue(':offset',$offset,\PDO::PARAM_INT);$statement->execute();
        return array_map(function(array $record): array { $data=json_decode((string)($record['data']??''),true); return array_merge($record,is_array($data)?$data:[]); },$statement->fetchAll());
    }

    public function tendersTotal(array $filters=[]): int
    {
        [$where,$params]=$this->tenderConditions($filters);return (int)$this->scalar("SELECT COUNT(*) FROM admin_records WHERE {$where}",$params);
    }

    public function tenderFilterOptions(): array
    {
        $rows=$this->all("SELECT JSON_UNQUOTE(JSON_EXTRACT(data,'$.region')) AS region, JSON_UNQUOTE(JSON_EXTRACT(data,'$.invited_by')) AS invited_by FROM admin_records WHERE module='tenders' AND status IN ('published','active','expired') AND deleted_at IS NULL");
        $regions=[];$organisations=[];foreach($rows as $row){$region=trim((string)($row['region']??''));$organisation=trim((string)($row['invited_by']??''));if($region!=='')$regions[$region]=true;if($organisation!=='')$organisations[$organisation]=true;}ksort($regions,SORT_NATURAL|SORT_FLAG_CASE);ksort($organisations,SORT_NATURAL|SORT_FLAG_CASE);
        return ['regions'=>array_keys($regions),'organisations'=>array_keys($organisations)];
    }

    private function tenderConditions(array $filters): array
    {
        $where="module='tenders' AND status IN ('published','active','expired') AND deleted_at IS NULL";$params=[];
        if(($q=trim((string)($filters['q']??'')))!==''){$where.=" AND (LOWER(title) LIKE :q_title OR LOWER(data) LIKE :q_data)";$term='%'.mb_strtolower($q).'%';$params[':q_title']=$term;$params[':q_data']=$term;}
        if(($region=trim((string)($filters['region']??'')))!==''){$where.=" AND JSON_UNQUOTE(JSON_EXTRACT(data,'$.region'))=:region";$params[':region']=$region;}
        if(($invitedBy=trim((string)($filters['invited_by']??'')))!==''){$where.=" AND JSON_UNQUOTE(JSON_EXTRACT(data,'$.invited_by'))=:invited_by";$params[':invited_by']=$invitedBy;}
        $date="COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data,'$.closing_date')),'')";$sort=$filters['sort']??'latest';
        if($sort==='latest')$where.=" AND status IN ('active','published') AND {$date}<>'' AND {$date}>=CURDATE()";
        if($sort==='deadline')$where.=" AND {$date}<>''";
        if($sort==='expired')$where.=" AND {$date}<>'' AND (status='expired' OR {$date}<CURDATE())";
        if($sort==='ongoing')$where.=" AND status IN ('active','published') AND ({$date}='' OR {$date}>=CURDATE())";
        return [$where,$params];
    }

    public function tenderForCustomer(int $id): ?array
    {
        $record=$this->one("SELECT * FROM admin_records WHERE id=? AND module='tenders' AND status IN ('published','active','expired') AND deleted_at IS NULL LIMIT 1",[$id]);
        if(!$record)return null;$data=json_decode((string)($record['data']??''),true);return array_merge($record,is_array($data)?$data:[]);
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

    public function markAllNotifications(int $customerId): void
    {
        $this->db->prepare('UPDATE customer_notifications SET read_at=NOW() WHERE customer_id=? AND read_at IS NULL AND archived_at IS NULL')->execute([$customerId]);
    }

    public function supportTickets(int $customerId): array
    {
        return $this->all('SELECT * FROM customer_support_tickets WHERE customer_id=? ORDER BY created_at DESC LIMIT 50', [$customerId]);
    }

    public function createSupportTicket(int $customerId, string $topic, string $subject, string $message): int
    {
        if (!in_array($topic, ['subscription','billing','application','document','technical','general'], true)) throw new \InvalidArgumentException('Please choose a valid support topic.');
        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 255) throw new \InvalidArgumentException('Please enter a clear subject between 3 and 255 characters.');
        if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) throw new \InvalidArgumentException('Please provide at least 10 characters so the team can help you.');
        $statement=$this->db->prepare('INSERT INTO customer_support_tickets (customer_id,topic,subject,message) VALUES (?,?,?,?)');
        $statement->execute([$customerId,$topic,$subject,$message]);
        return (int)$this->db->lastInsertId();
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

    public function subscriptions(int $customerId): array
    {
        return $this->all(
            'SELECT cs.*,sp.name plan_name,sp.billing_cycle,sp.benefits
             FROM customer_subscriptions cs JOIN subscription_plans sp ON sp.id=cs.plan_id
             WHERE cs.customer_id=? ORDER BY FIELD(cs.status,"active","pending","expired","cancelled","suspended"),cs.updated_at DESC',
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
            'subscriptions'=>$customer?$this->subscriptions((int)$customer['id']):[],
            'invoices'=>$customer?$this->invoices((int)$customer['id']):[],
            'payments'=>$customer?$this->payments((int)$customer['id']):[],
            'plans'=>$this->plans(),
            'activity'=>$customer?$this->activity((int)$customer['id']):[],
            'notifications'=>$customer?$this->notifications((int)$customer['id']):[],
        ];
    }

    public function customerForAdminRecord(int $customerRecordId): ?array
    {
        $record=$this->one("SELECT data FROM admin_records WHERE id=? AND module='customers' AND deleted_at IS NULL LIMIT 1",[$customerRecordId]);
        if(!$record)return null;
        $data=json_decode((string)$record['data'],true)?:[];
        $email=(string)($data['email']??'');
        return $email!==''?$this->findCustomerByEmail($email):null;
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
        $plan=null;
        if($data['invoice_type']==='subscription'&&empty($data['subscription_id'])){
            $plan=$this->plan((int)($data['plan_id']??0));
            if(!$plan)throw new \InvalidArgumentException('Please select an active subscription plan.');
            if($this->one("SELECT id FROM customer_subscriptions WHERE customer_id=? AND status='active' LIMIT 1",[$customerId]))throw new \InvalidArgumentException('This partner already has an active membership.');
        }
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
            $subscriptionId=(int)($data['subscription_id']??0);
            if($data['invoice_type']==='subscription'&&$subscriptionId===0&&$plan){
                $months=$this->cycleMonths((string)$plan['billing_cycle']);
                $this->db->prepare("INSERT INTO customer_subscriptions (customer_id,plan_id,status,starts_at,expires_at,amount) VALUES (?,?,'pending',CURDATE(),DATE_ADD(CURDATE(),INTERVAL ? MONTH),?)")
                    ->execute([$customerId,(int)$plan['id'],$months,(float)$plan['price']]);
                $subscriptionId=(int)$this->db->lastInsertId();
            }
            $this->db->prepare('INSERT INTO customer_invoices (customer_id,subscription_id,service_request_id,invoice_number,public_token,invoice_type,billing_cycle,subtotal,taxable_amount,cgst,sgst,total_amount,status,issue_date,due_date,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"issued",CURDATE(),?,?,?)')->execute([
                $customerId,$subscriptionId?:null,$data['service_request_id']?:null,$number,$publicToken,$data['invoice_type'],$plan['billing_cycle']??($data['billing_cycle']?:null),$subtotal,$subtotal,$cgst,$sgst,$total,$data['due_date'],$data['notes']?:null,$adminId
            ]);
            $id=(int)$this->db->lastInsertId();
            $this->db->prepare('INSERT INTO customer_invoice_items (invoice_id,description,quantity,unit_price,gst_rate,line_total) VALUES (?,?,1,?,?,?)')->execute([$id,$data['description'],$subtotal,$gstRate,$total]);
            $this->db->prepare('INSERT INTO customer_notifications (customer_id,type,title,message,action_url) VALUES (?,"invoice","New invoice issued",?,?)')->execute([$customerId,'Invoice '.$number.' for ₹'.number_format($total,2).' has been issued.','/customer/billing/invoices/'.$id]);
            $this->db->prepare('INSERT INTO customer_activity_logs (customer_id,actor_id,action,subject_type,subject_id,description) VALUES (?,?,?,?,?,?)')->execute([$customerId,$adminId,'invoice_issued','invoice',$id,'A new invoice was issued by the Udyam team.']);
            $this->db->commit();return $id;
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function recordPayment(int $customerId,array $data,int $adminId): int
    {
        $this->db->beginTransaction();
        try{
            $invoice=$this->one(
                "SELECT id,subscription_id,total_amount,paid_amount,status FROM customer_invoices
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
            if($amount >= $outstanding && !empty($invoice['subscription_id'])){
                $this->db->prepare('UPDATE customer_subscriptions SET status="active",starts_at=CURDATE() WHERE id=? AND customer_id=? AND status="pending"')->execute([(int)$invoice['subscription_id'],$customerId]);
                $this->db->prepare('INSERT INTO customer_notifications (customer_id,type,title,message,action_url) VALUES (?,"subscription","Membership activated","Your payment was confirmed and your subscription is now active.","/customer/subscription")')->execute([$customerId]);
            }
            $this->db->prepare('INSERT INTO customer_notifications (customer_id,type,title,message,action_url) VALUES (?,"invoice","Payment recorded",?,?)')->execute([$customerId,'A payment of ₹'.number_format($amount,2).' has been recorded against your invoice.','/customer/billing/invoices/'.$data['invoice_id']]);
            $this->db->prepare('INSERT INTO customer_activity_logs (customer_id,actor_id,action,subject_type,subject_id,description) VALUES (?,?,?,?,?,?)')->execute([$customerId,$adminId,'payment_recorded','payment',$id,'A payment was recorded by the Udyam team.']);
            $this->db->commit();return $id;
        }catch(\Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    public function activatePaidInvoiceSubscription(int $customerId,int $invoiceId,int $planId,int $adminId): void
    {
        $plan=$this->plan($planId);if(!$plan)throw new \InvalidArgumentException('Please select an active subscription plan.');
        $this->db->beginTransaction();
        try{
            $invoice=$this->one('SELECT * FROM customer_invoices WHERE id=? AND customer_id=? LIMIT 1 FOR UPDATE',[$invoiceId,$customerId]);
            if(!$invoice||$invoice['status']!=='paid')throw new \InvalidArgumentException('Only fully paid invoices can activate a membership.');
            if(!empty($invoice['subscription_id']))throw new \InvalidArgumentException('This invoice is already linked to a membership.');
            if($this->one("SELECT id FROM customer_subscriptions WHERE customer_id=? AND status='active' LIMIT 1",[$customerId]))throw new \InvalidArgumentException('This partner already has an active membership.');
            $months=$this->cycleMonths((string)$plan['billing_cycle']);
            $this->db->prepare("INSERT INTO customer_subscriptions (customer_id,plan_id,status,starts_at,expires_at,amount) VALUES (?,?,'active',CURDATE(),DATE_ADD(CURDATE(),INTERVAL ? MONTH),?)")->execute([$customerId,$planId,$months,(float)$plan['price']]);
            $subscriptionId=(int)$this->db->lastInsertId();
            $this->db->prepare('UPDATE customer_invoices SET subscription_id=?,invoice_type="subscription",billing_cycle=? WHERE id=?')->execute([$subscriptionId,$plan['billing_cycle'],$invoiceId]);
            $this->db->prepare('INSERT INTO customer_notifications (customer_id,type,title,message,action_url) VALUES (?,"subscription","Membership activated","Your membership has been activated by the Udyam team.","/customer/subscription")')->execute([$customerId]);
            $this->db->prepare('INSERT INTO customer_activity_logs (customer_id,actor_id,action,subject_type,subject_id,description) VALUES (?,?,?,?,?,?)')->execute([$customerId,$adminId,'subscription_activated','subscription',$subscriptionId,'Membership activated against paid invoice '.$invoice['invoice_number'].'.']);
            $this->db->commit();
        }catch(\Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }

    private function cycleMonths(string $cycle): int
    {
        return ['monthly'=>1,'quarterly'=>3,'half_yearly'=>6,'yearly'=>12,'one_time'=>1200][$cycle]??12;
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
        $fields = ['company_name','mobile','gst_number','pan_number','address_line_1','address_line_2','city','state','postal_code','country','contact_person','business_type','annual_turnover_range','preferred_communication'];
        $assignments = [];
        $params = ['id' => $customerId];
        foreach ($fields as $field) {
            $assignments[] = "{$field}=:{$field}";
            $params[$field] = $data[$field] ?? null;
        }
        $this->db->prepare('UPDATE customer_profiles SET ' . implode(',', $assignments) . ' WHERE user_id=:id')->execute($params);
        $profile=$this->one('SELECT customer_record_id FROM customer_profiles WHERE user_id=? LIMIT 1',[$customerId]);
        if(!empty($profile['customer_record_id'])){
            $record=$this->one('SELECT data FROM admin_records WHERE id=? LIMIT 1',[(int)$profile['customer_record_id']]);$recordData=json_decode((string)($record['data']??''),true)?:[];
            $recordData=array_merge($recordData,['company'=>$data['company_name']??null,'phone'=>$data['mobile']??null,'state'=>$data['state']??null,'annual_turnover_range'=>$data['annual_turnover_range']??null]);
            $this->db->prepare('UPDATE admin_records SET data=?,updated_at=NOW() WHERE id=?')->execute([json_encode($recordData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),(int)$profile['customer_record_id']]);
        }
    }

    public function hasActiveSubscription(int $customerId): bool
    {
        return $this->one(
            "SELECT 1 FROM customer_subscriptions WHERE customer_id=? AND status='active'
             AND (expires_at IS NULL OR expires_at >= CURDATE()) LIMIT 1",
            [$customerId]
        ) !== null;
    }

    public function currentSubscription(int $customerId): ?array
    {
        return $this->one(
            "SELECT cs.*,sp.name plan_name,sp.billing_cycle,sp.benefits,sp.description
             FROM customer_subscriptions cs JOIN subscription_plans sp ON sp.id=cs.plan_id
             WHERE cs.customer_id=? AND cs.status IN ('active','suspended')
             ORDER BY FIELD(cs.status,'active','suspended'),cs.updated_at DESC LIMIT 1",
            [$customerId]
        );
    }

    public function subscriptionRequests(int $customerId): array
    {
        return $this->all(
            "SELECT sr.*,sp.name plan_name FROM subscription_requests sr
             JOIN customer_subscriptions cs ON cs.id=sr.subscription_id
             JOIN subscription_plans sp ON sp.id=cs.plan_id
             WHERE sr.customer_id=? ORDER BY sr.created_at DESC",
            [$customerId]
        );
    }

    public function requestSubscriptionAction(int $customerId, int $subscriptionId, string $type, string $reason): int
    {
        if (!in_array($type, ['pause','resume','cancellation'], true)) throw new \InvalidArgumentException('Invalid subscription request.');
        $subscription = $this->one('SELECT id,status FROM customer_subscriptions WHERE id=? AND customer_id=? LIMIT 1', [$subscriptionId, $customerId]);
        if (!$subscription) throw new \InvalidArgumentException('Subscription not found.');
        if ($type === 'resume' && $subscription['status'] !== 'suspended') throw new \InvalidArgumentException('Only paused subscriptions can be resumed.');
        if ($type !== 'resume' && $subscription['status'] !== 'active') throw new \InvalidArgumentException('This subscription is no longer active.');
        if ($this->one("SELECT id FROM subscription_requests WHERE subscription_id=? AND request_type=? AND status='pending' LIMIT 1", [$subscriptionId, $type])) {
            throw new \InvalidArgumentException('A request of this type is already awaiting review.');
        }
        $statement = $this->db->prepare('INSERT INTO subscription_requests (customer_id,subscription_id,request_type,reason) VALUES (?,?,?,?)');
        $statement->execute([$customerId, $subscriptionId, $type, $reason ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function adminSubscriptionRequests(string $status = 'pending'): array
    {
        $where = in_array($status, ['pending','approved','rejected','cancelled'], true) ? ' AND sr.status=?' : '';
        return $this->all(
            "SELECT sr.*,sp.name plan_name,u.first_name,u.last_name,u.email,cp.company_name
             FROM subscription_requests sr JOIN customer_subscriptions cs ON cs.id=sr.subscription_id
             JOIN subscription_plans sp ON sp.id=cs.plan_id JOIN users u ON u.id=sr.customer_id
             LEFT JOIN customer_profiles cp ON cp.user_id=u.id WHERE 1=1{$where} ORDER BY sr.created_at DESC",
            $where === '' ? [] : [$status]
        );
    }

    public function reviewSubscriptionRequest(int $requestId, bool $approve, string $note, int $adminId): ?array
    {
        $this->db->beginTransaction();
        try {
            $request = $this->one('SELECT * FROM subscription_requests WHERE id=? AND status="pending" LIMIT 1 FOR UPDATE', [$requestId]);
            if (!$request) { $this->db->rollBack(); return null; }
            $status = $approve ? 'approved' : 'rejected';
            $this->db->prepare('UPDATE subscription_requests SET status=?,admin_note=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?')->execute([$status, $note ?: null, $adminId, $requestId]);
            if ($approve) {
                $subscriptionStatus = $request['request_type'] === 'cancellation' ? 'cancelled' : ($request['request_type'] === 'pause' ? 'suspended' : 'active');
                $this->db->prepare('UPDATE customer_subscriptions SET status=? WHERE id=?')->execute([$subscriptionStatus, $request['subscription_id']]);
            }
            $title = 'Subscription request ' . ($approve ? 'approved' : 'rejected');
            $message = 'Your ' . $request['request_type'] . ' request has been ' . ($approve ? 'approved.' : 'rejected.') . ($note ? ' ' . $note : '');
            $this->db->prepare('INSERT INTO customer_notifications (customer_id,type,title,message,action_url) VALUES (?,"subscription",?,?,"/customer/subscription")')->execute([$request['customer_id'], $title, $message]);
            $this->db->prepare('INSERT INTO customer_activity_logs (customer_id,actor_id,action,subject_type,subject_id,description) VALUES (?,?,?,?,?,?)')->execute([$request['customer_id'],$adminId,'subscription_request_reviewed','subscription_request',$requestId,$message]);
            $this->db->commit();
            return ['customer_id' => (int) $request['customer_id'], 'request_type' => (string) $request['request_type'], 'approved' => $approve];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Cross-panel search scoped to a single customer: applications, documents,
     * invoices, notifications and tenders (tenders only for subscribers).
     */
    public function search(int $customerId, string $query, bool $hasSubscription, int $limit = 20): array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query) ?? '');
        if (mb_strlen($query) < 2) {
            return [];
        }
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query) . '%';
        $per = max(3, (int) ceil($limit / 5));
        $results = [];
        $subscriptionIntent = (bool) preg_match('/\b(subscription|subscriptions|plan|plans|membership|memberships|monthly|annual|yearly|renewal)\b/i', $query);

        foreach ($this->all(
            "SELECT r.id,r.application_number,r.project_name,r.status,a.title service_title
             FROM customer_service_requests r JOIN admin_records a ON a.id=r.service_record_id
             WHERE r.customer_id=? AND (r.application_number LIKE ? ESCAPE '\\\\'
                OR r.project_name LIKE ? ESCAPE '\\\\' OR a.title LIKE ? ESCAPE '\\\\')
             ORDER BY r.updated_at DESC LIMIT {$per}",
            [$customerId, $like, $like, $like]
        ) as $row) {
            $results[] = ['group' => 'Applications', 'icon' => 'clipboard-list', 'title' => (string) ($row['project_name'] ?: $row['service_title']), 'meta' => (string) $row['application_number'] . ' · ' . ucfirst(str_replace('_', ' ', (string) $row['status'])), 'url' => url('/customer/applications/' . (int) $row['id'])];
        }

        foreach ($this->all(
            "SELECT id,title,category,status FROM customer_documents
             WHERE customer_id=? AND deleted_at IS NULL AND title LIKE ? ESCAPE '\\\\'
             ORDER BY updated_at DESC LIMIT {$per}",
            [$customerId, $like]
        ) as $row) {
            $results[] = ['group' => 'Documents', 'icon' => 'folder-open', 'title' => (string) $row['title'], 'meta' => ucfirst((string) $row['category']) . ' · ' . ucfirst((string) $row['status']), 'url' => url('/customer/documents')];
        }

        foreach ($this->all(
            "SELECT id,invoice_number,total_amount,status FROM customer_invoices
             WHERE customer_id=? AND invoice_number LIKE ? ESCAPE '\\\\' ORDER BY issue_date DESC LIMIT {$per}",
            [$customerId, $like]
        ) as $row) {
            $results[] = ['group' => 'Billing', 'icon' => 'receipt-indian-rupee', 'title' => (string) $row['invoice_number'], 'meta' => '₹' . number_format((float) $row['total_amount'], 2) . ' · ' . ucfirst((string) $row['status']), 'url' => url('/customer/billing/invoices/' . (int) $row['id'])];
        }

        $subscriptionSql = "SELECT cs.id,cs.status,cs.expires_at,sp.name,sp.billing_cycle,sp.category,sp.subtitle
            FROM customer_subscriptions cs JOIN subscription_plans sp ON sp.id=cs.plan_id
            WHERE cs.customer_id=?";
        $subscriptionParams = [$customerId];
        if (!$subscriptionIntent) {
            $subscriptionSql .= " AND (sp.name LIKE ? ESCAPE '\\\\' OR sp.category LIKE ? ESCAPE '\\\\'
                OR sp.subtitle LIKE ? ESCAPE '\\\\' OR sp.billing_cycle LIKE ? ESCAPE '\\\\')";
            array_push($subscriptionParams, $like, $like, $like, $like);
        }
        $subscriptionSql .= " ORDER BY FIELD(cs.status,'active','pending','expired','cancelled','suspended'), cs.updated_at DESC LIMIT {$per}";
        $seenSubscriptionPlans = [];
        foreach ($this->all($subscriptionSql, $subscriptionParams) as $row) {
            $planKey = (string) $row['name'] . '|' . (string) $row['billing_cycle'];
            if (isset($seenSubscriptionPlans[$planKey])) {
                continue;
            }
            $seenSubscriptionPlans[$planKey] = true;
            $results[] = [
                'group' => 'Subscriptions', 'icon' => 'badge-indian-rupee', 'title' => (string) $row['name'],
                'meta' => ucfirst((string) $row['status']) . ' · ' . ucfirst(str_replace('_', ' ', (string) $row['billing_cycle']))
                    . (!empty($row['expires_at']) ? ' · Renews ' . date('d M Y', strtotime((string) $row['expires_at'])) : ''),
                'url' => url('/customer/plans'),
            ];
        }

        $planSql = "SELECT id,name,category,subtitle,billing_cycle,price
            FROM subscription_plans WHERE status='active' AND deleted_at IS NULL";
        $planParams = [];
        if (!$subscriptionIntent) {
            $planSql .= " AND (name LIKE ? ESCAPE '\\\\' OR category LIKE ? ESCAPE '\\\\'
                OR subtitle LIKE ? ESCAPE '\\\\' OR description LIKE ? ESCAPE '\\\\' OR billing_cycle LIKE ? ESCAPE '\\\\')";
            $planParams = [$like, $like, $like, $like, $like];
        }
        $planSql .= " ORDER BY sort_order,name LIMIT {$per}";
        foreach ($this->all($planSql, $planParams) as $row) {
            $results[] = [
                'group' => 'Available Plans', 'icon' => 'sparkles', 'title' => (string) $row['name'],
                'meta' => '₹' . number_format((float) $row['price'], 0) . ' · '
                    . ucfirst(str_replace('_', ' ', (string) $row['billing_cycle']))
                    . (!empty($row['subtitle']) ? ' · ' . (string) $row['subtitle'] : ''),
                'url' => url('/customer/plans'),
            ];
        }

        foreach ($this->all(
            "SELECT id,title FROM admin_records
             WHERE module='services' AND status IN ('published','active') AND deleted_at IS NULL
               AND title LIKE ? ESCAPE '\\\\' ORDER BY sort_order,title LIMIT {$per}",
            [$like]
        ) as $row) {
            $results[] = [
                'group' => 'Services', 'icon' => 'briefcase-business', 'title' => (string) $row['title'],
                'meta' => 'View service and apply online', 'url' => url('/customer/services/' . (int) $row['id'] . '/apply'),
            ];
        }

        foreach ($this->all(
            "SELECT p.id,p.transaction_id,p.amount,p.status,p.payment_date,i.invoice_number
             FROM customer_payments p JOIN customer_invoices i ON i.id=p.invoice_id
             WHERE p.customer_id=? AND (p.transaction_id LIKE ? ESCAPE '\\\\' OR i.invoice_number LIKE ? ESCAPE '\\\\')
             ORDER BY p.payment_date DESC LIMIT {$per}",
            [$customerId, $like, $like]
        ) as $row) {
            $results[] = [
                'group' => 'Payments', 'icon' => 'circle-check-big',
                'title' => (string) ($row['transaction_id'] ?: $row['invoice_number']),
                'meta' => '₹' . number_format((float) $row['amount'], 2) . ' · ' . ucfirst((string) $row['status']),
                'url' => url('/customer/billing/payments'),
            ];
        }

        foreach ($this->all(
            "SELECT id,title,message FROM customer_notifications
             WHERE customer_id=? AND (title LIKE ? ESCAPE '\\\\' OR message LIKE ? ESCAPE '\\\\')
             ORDER BY created_at DESC LIMIT {$per}",
            [$customerId, $like, $like]
        ) as $row) {
            $results[] = ['group' => 'Notifications', 'icon' => 'bell', 'title' => (string) $row['title'], 'meta' => mb_substr((string) $row['message'], 0, 60), 'url' => url('/customer/notifications')];
        }

        foreach ($this->all(
            "SELECT id,data FROM admin_records
             WHERE module='tenders' AND status IN ('published','active') AND deleted_at IS NULL
               AND data LIKE ? ESCAPE '\\\\' ORDER BY created_at DESC LIMIT {$per}",
            [$like]
        ) as $row) {
            $data = json_decode((string) $row['data'], true) ?: [];
            $results[] = [
                'group' => 'Notices & Tenders',
                'icon' => 'megaphone',
                'title' => (string) ($data['title'] ?? 'Tender'),
                'meta' => $hasSubscription ? ucfirst((string) ($data['type'] ?? '')) : 'Subscribe to view details',
                'url' => $hasSubscription ? ((string) ($data['document_url'] ?? url('/customer/dashboard'))) : url('/customer/plans'),
            ];
        }

        return array_slice($results, 0, $limit);
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
