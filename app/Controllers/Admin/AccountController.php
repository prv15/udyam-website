<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\User;

final class AccountController extends AdminController
{
    public function __construct(private readonly User $users, private readonly Request $request)
    {
        parent::__construct();
    }

    public function profile(): void
    {
        $this->render('account/profile', ['title' => 'My Profile', 'record' => Session::get('user')]);
    }

    public function updateProfile(): void
    {
        $this->csrf();
        $id = (int) Session::get('user')['id'];
        $data = [
            'first_name' => $this->request->string('first_name'),
            'last_name' => $this->request->string('last_name'),
            'email' => strtolower($this->request->string('email')),
        ];
        if ($data['first_name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->redirectError('/admin/profile', 'Enter a valid name and email address.');
        }
        if ($this->users->emailExists($data['email'], $id)) {
            $this->redirectError('/admin/profile', 'That email address is already in use.');
        }
        $this->users->update($id, $data);
        $fresh = $this->users->find($id);
        unset($fresh['password']);
        Session::set('user', $fresh);
        $this->redirectSuccess('/admin/profile', 'Profile updated successfully.');
    }

    public function password(): void
    {
        $this->render('account/password', ['title' => 'Change Password']);
    }

    public function updatePassword(): void
    {
        $this->csrf();
        $user = $this->users->find((int) Session::get('user')['id']);
        $current = $this->request->string('current_password');
        $password = $this->request->string('password');
        if (!$user || !password_verify($current, $user['password'])) {
            $this->redirectError('/admin/change-password', 'Current password is incorrect.');
        }
        if (mb_strlen($password) < 8 || $password !== $this->request->string('password_confirmation')) {
            $this->redirectError('/admin/change-password', 'New passwords must match and contain at least 8 characters.');
        }
        $this->users->update((int) $user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        Session::regenerate();
        $this->redirectSuccess('/admin/change-password', 'Password changed successfully.');
    }

    private function csrf(): void
    {
        if (!csrf_validate()) {
            http_response_code(419);
            exit('Your session expired.');
        }
    }
}
