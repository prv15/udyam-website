<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

final class CustomerAuthMiddleware
{
    public function handle(): void
    {
        $customer = Session::get('customer_user');
        if (!is_array($customer) || !isset($customer['id']) || ($customer['user_type'] ?? '') !== 'customer') {
            Session::flash('error', 'Please sign in to access your customer portal.');
            header('Location: ' . url('/customer/login'));
            exit;
        }
    }
}
