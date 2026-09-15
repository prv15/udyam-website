<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    public function handle(bool $adminOnly = false): void
    {
        $user = Session::get('user');

        if (!is_array($user) || !isset($user['id'])) {
            header('Location: ' . url('/admin/login'));
            exit;
        }

        if ($adminOnly && !in_array(($user['user_type'] ?? ''), ['admin', 'staff'], true)) {
            http_response_code(403);
            exit('403 - Forbidden');
        }
    }
}
