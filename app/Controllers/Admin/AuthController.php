<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AuthService;
use App\Core\Session;
use App\Middleware\GuestMiddleware;
use App\Models\Role;
use App\Services\PermissionService;

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

    public function entry(): never
    {
        $user = Session::get('user');
        $destination = is_array($user) && isset($user['id'])
            ? (new PermissionService(new Role()))->landingPath($user)
            : '/admin/login';
        header('Location: ' . url($destination));
        exit;
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
            || !in_array(($user['user_type'] ?? ''), ['admin', 'staff'], true)
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

        header('Location: ' . url((new PermissionService(new Role()))->landingPath($user)));
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
