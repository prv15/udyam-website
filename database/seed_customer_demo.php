<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;
use App\Repositories\CustomerPortalRepository;

$email = 'demo.customer@udyamventures.com';
$password = 'UdyamDemo@2026';
$db = Database::connection();
$portal = new CustomerPortalRepository($db);

$existing = $db->prepare(
    "SELECT id,user_type FROM users WHERE LOWER(email)=LOWER(:email) AND deleted_at IS NULL LIMIT 1"
);
$existing->execute(['email' => $email]);
$user = $existing->fetch();

if ($user && $user['user_type'] !== 'customer') {
    throw new RuntimeException('The demo email belongs to a non-customer user and cannot be reused.');
}

if ($user) {
    $customerId = (int) $user['id'];
    $portal->updatePassword($customerId, password_hash($password, PASSWORD_DEFAULT));
    $profileExists = $db->prepare('SELECT COUNT(*) FROM customer_profiles WHERE user_id = :id');
    $profileExists->execute(['id' => $customerId]);
    if ((int) $profileExists->fetchColumn() === 0) {
        $db->prepare(
            "INSERT INTO customer_profiles
             (user_id,company_name,mobile,contact_person,email_verified_at)
             VALUES (:id,'Udyam Demo Enterprise','+91 99999 00000','Demo Customer',NOW())"
        )->execute(['id' => $customerId]);
    }
} else {
    $customerId = $portal->createCustomer([
        'full_name' => 'Demo Customer',
        'company_name' => 'Udyam Demo Enterprise',
        'mobile' => '+91 99999 00000',
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);
}

$portal->verifyEmail($customerId);

$plan = $db->query(
    "SELECT id FROM subscription_plans WHERE slug='business-advisory-demo' AND deleted_at IS NULL LIMIT 1"
)->fetchColumn();
if (!$plan) {
    $statement = $db->prepare(
        "INSERT INTO subscription_plans
         (name,slug,description,billing_cycle,price,gst_rate,benefits,status,sort_order)
         VALUES ('Business Advisory','business-advisory-demo',
         'Structured advisory support for growing organizations.','yearly',25000,18,:benefits,'active',1)"
    );
    $statement->execute([
        'benefits' => json_encode([
            'Quarterly advisory review',
            'Priority document review',
            'Customer portal access',
            'Application and billing tracking',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

echo "Demo customer is ready.\n";
echo "Login: {$email}\n";
echo "Password: {$password}\n";
