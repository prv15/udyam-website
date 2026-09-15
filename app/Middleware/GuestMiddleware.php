<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Models\Role;
use App\Services\PermissionService;

class GuestMiddleware
{
    public function handle(): void
    {
        if (Session::has('user')) {

            $user = Session::get('user');

            if (in_array($user['user_type'] ?? '', ['admin', 'staff'], true)) {

                header('Location: ' . url((new PermissionService(new Role()))->landingPath($user)));

            } else {

                header('Location: ' . url('/customer/dashboard'));

            }

            exit;
        }
    }
}
