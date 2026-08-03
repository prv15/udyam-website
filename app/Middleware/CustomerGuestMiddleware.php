<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

final class CustomerGuestMiddleware
{
    public function handle(): void
    {
        $customer = Session::get('customer_user');
        if (is_array($customer) && isset($customer['id']) && ($customer['user_type'] ?? '') === 'customer') {
            header('Location: ' . url('/customer/dashboard'));
            exit;
        }
    }
}
