<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\User;

final class UsersController extends AdminController
{
    public function __construct(private readonly User $users, private readonly Request $request)
    {
        parent::__construct();
    }

    public function index(): void
    {
        $page = max(1, $this->request->integer('page', 1));
        $search = $this->request->string('search');
        $this->render('users/index', [
            'title' => 'Users & Roles', 'users' => $this->users->paginate($page, 20, $search),
            'total' => $this->users->total($search), 'page' => $page, 'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->renderForm([], 'create');
    }

    public function store(): void
    {
        $this->csrf();
        [$data, $errors] = $this->validateInput(true);
        if ($errors !== []) {
            $this->formError('/admin/users/create', $data, $errors);
        }
        $data['password'] = password_hash($this->request->string('password'), PASSWORD_DEFAULT);
        $this->users->create($data);
        $this->redirectSuccess('/admin/users', 'User created successfully.');
    }

    public function edit(int $id): void
    {
        $user = $this->users->find($id);
        if ($user === null) {
            $this->abort404();
        }
        unset($user['password']);
        $this->renderForm($user, 'edit');
    }

    public function update(int $id): void
    {
        $this->csrf();
        if ($this->users->find($id) === null) {
            $this->abort404();
        }
        [$data, $errors] = $this->validateInput(false, $id);
        $password = $this->request->string('password');
        if ($password !== '') {
            if (mb_strlen($password) < 8) {
                $errors['password'][] = 'Password must be at least 8 characters.';
            } else {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
        }
        if ($errors !== []) {
            $this->formError('/admin/users/edit/' . $id, $data, $errors);
        }
        $this->users->update($id, $data);
        if ((int) Session::get('user')['id'] === $id) {
            $fresh = $this->users->find($id);
            unset($fresh['password']);
            Session::set('user', $fresh);
        }
        $this->redirectSuccess('/admin/users', 'User updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->csrf();
        if ((int) Session::get('user')['id'] === $id) {
            $this->redirectError('/admin/users', 'You cannot delete your own account.');
        }
        $this->users->delete($id);
        $this->redirectSuccess('/admin/users', 'User deleted successfully.');
    }

    private function renderForm(array $user, string $mode): void
    {
        $this->render('users/form', [
            'title' => ($mode === 'edit' ? 'Edit' : 'Create') . ' User',
            'record' => $user, 'mode' => $mode,
        ]);
    }

    private function validateInput(bool $passwordRequired, ?int $ignoreId = null): array
    {
        $data = [
            'first_name' => $this->request->string('first_name'),
            'last_name' => $this->request->string('last_name'),
            'email' => strtolower($this->request->string('email')),
            'user_type' => $this->request->string('user_type'),
            'status' => $this->request->string('status'),
        ];
        $errors = [];
        foreach (['first_name', 'email', 'user_type', 'status'] as $field) {
            if ($data[$field] === '') {
                $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Enter a valid email address.';
        } elseif ($data['email'] !== '' && $this->users->emailExists($data['email'], $ignoreId)) {
            $errors['email'][] = 'This email is already in use.';
        }
        if (!in_array($data['user_type'], ['admin', 'customer'], true)) {
            $errors['user_type'][] = 'Select a valid role.';
        }
        if (!in_array($data['status'], ['active', 'inactive'], true)) {
            $errors['status'][] = 'Select a valid status.';
        }
        if ($passwordRequired && mb_strlen($this->request->string('password')) < 8) {
            $errors['password'][] = 'Password must be at least 8 characters.';
        }
        return [$data, $errors];
    }

    private function formError(string $path, array $old, array $errors): never
    {
        Session::set('old', $old);
        Session::set('errors', $errors);
        $this->redirect($path);
    }

    private function csrf(): void
    {
        if (!csrf_validate()) {
            http_response_code(419);
            exit('Your session expired.');
        }
    }
}
