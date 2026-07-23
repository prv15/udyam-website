<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class GuestMiddleware
{
    public function handle(): void
    {
        if (Session::has('user')) {

            $user = Session::get('user');

            if ($user['user_type'] === 'admin') {

                header('Location: ' . url('/admin/dashboard'));

            } else {

                header('Location: ' . url('/customer/dashboard'));

            }

            exit;
        }
    }
}
