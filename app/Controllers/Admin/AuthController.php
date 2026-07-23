<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Core\Session;
use App\Middleware\GuestMiddleware;

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function login(): void
    {
        (new GuestMiddleware())->handle();

        $this->renderLogin();
    }

    public function authenticate(): void
    {
        (new GuestMiddleware())->handle();

        if (!csrf_validate()) {
            http_response_code(419);
            $this->renderLogin('Your session expired. Please try again.');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->service->findUser($email);

        if (
            !$user
            || ($user['status'] ?? '') !== 'active'
            || ($user['user_type'] ?? '') !== 'admin'
        ) {

            $this->renderLogin('Invalid email or password.');

            return;
        }

        if (!password_verify($password, $user['password'])) {

            $this->renderLogin('Invalid email or password.');

            return;
        }

        Session::regenerate();
        unset($user['password']);
        Session::set('user', $user);

        header('Location: ' . url('/admin/dashboard'));
        exit;
    }
    public function logout(): void
{
    if (!csrf_validate()) {
        http_response_code(419);
        exit('Your session expired. Please refresh the page and try again.');
    }

    Session::destroy();

    header('Location: ' . url('/admin/login'));
    exit;
}

    private function renderLogin(?string $error = null): void
    {
        require VIEW_PATH . '/admin/auth/login.php';
    }
}
