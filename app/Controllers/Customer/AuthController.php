<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Middleware\CustomerGuestMiddleware;
use App\Repositories\CustomerPortalRepository;
use App\Services\CustomerAuthService;

final class AuthController extends Controller
{
    public function __construct(
        private readonly CustomerPortalRepository $customers,
        private readonly CustomerAuthService $auth,
        private readonly Request $request
    ) {
    }

    public function entry(): never
    {
        $customer = Session::get('customer_user');
        $this->redirect(is_array($customer) && isset($customer['id']) ? '/customer/dashboard' : '/customer/login');
    }

    public function login(): void
    {
        (new CustomerGuestMiddleware())->handle();
        $this->authView('login', ['title' => 'Customer Login']);
    }

    public function authenticate(): never
    {
        (new CustomerGuestMiddleware())->handle();
        $this->csrf();
        $email = strtolower($this->request->string('email'));
        $identity = $email . '|' . $this->request->ip();
        if ($this->customers->tooManyAttempts($identity, 'login')) {
            $this->redirectError('/customer/login', 'Too many login attempts. Please wait 15 minutes.');
        }
        $customer = $this->customers->findCustomerByEmail($email);
        if (!$customer || !password_verify($this->request->rawString('password'), (string) $customer['password']) || $customer['status'] !== 'active') {
            $this->customers->recordAttempt($identity, 'login');
            $this->redirectError('/customer/login', 'Invalid email or password.');
        }
        if (empty($customer['email_verified_at'])) {
            $this->redirectError('/customer/login', 'Please verify your email before signing in.');
        }
        $this->customers->clearAttempts($identity, 'login');
        unset($customer['password']);
        Session::regenerate();
        Session::set('customer_user', $customer);
        $this->customers->touchLogin((int) $customer['id']);
        $this->customers->log((int) $customer['id'], 'login', 'Customer signed in.');
        $this->redirect('/customer/dashboard');
    }

    public function register(): void
    {
        (new CustomerGuestMiddleware())->handle();
        $this->authView('register', ['title' => 'Create Customer Account']);
    }

    public function storeRegistration(): never
    {
        (new CustomerGuestMiddleware())->handle();
        $this->csrf();
        $data = [
            'full_name' => $this->request->string('full_name'),
            'company_name' => $this->request->string('company_name'),
            'mobile' => $this->request->string('mobile'),
            'email' => strtolower($this->request->string('email')),
            'password' => $this->request->rawString('password'),
        ];
        $errors = [];
        if (mb_strlen($data['full_name']) < 2) $errors[] = 'Please enter your full name.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if (!preg_match('/^[0-9+()\\-\\s]{8,20}$/', $data['mobile'])) $errors[] = 'Please enter a valid mobile number.';
        if (strlen($data['password']) < 8 || !preg_match('/[A-Z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
            $errors[] = 'Password must contain at least 8 characters, one uppercase letter and one number.';
        }
        if ($data['password'] !== $this->request->rawString('password_confirmation')) $errors[] = 'Passwords do not match.';
        if ($this->customers->emailExists($data['email'])) $errors[] = 'An account already exists for this email.';
        if ($errors) {
            Session::flash('form_errors', $errors);
            Session::flash('old', array_diff_key($data, ['password' => true]));
            $this->redirect('/customer/register');
        }
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $id = $this->customers->createCustomer($data);
        try {
            $this->auth->sendVerification($id, $data['email'], $data['full_name']);
        } catch (\Throwable $e) {
            error_log('Customer verification email failed: ' . $e->getMessage());
        }
        $this->customers->log($id, 'registration', 'Customer account created.');
        $this->redirectSuccess('/customer/login', 'Account created. Please check your email to verify it.');
    }

    public function verifyEmail(): never
    {
        $userId = $this->customers->consumeToken($this->request->string('token'), 'verify_email');
        if (!$userId) $this->redirectError('/customer/login', 'This verification link is invalid or expired.');
        $this->customers->verifyEmail($userId);
        $this->customers->log($userId, 'email_verified', 'Email address verified.');
        $this->redirectSuccess('/customer/login', 'Email verified. You can now sign in.');
    }

    public function forgotPassword(): void
    {
        (new CustomerGuestMiddleware())->handle();
        $this->authView('forgot', ['title' => 'Forgot Password']);
    }

    public function sendReset(): never
    {
        $this->csrf();
        $email = strtolower($this->request->string('email'));
        $customer = $this->customers->findCustomerByEmail($email);
        if ($customer) {
            try {
                $this->auth->sendPasswordReset((int) $customer['id'], $email, trim($customer['first_name'] . ' ' . $customer['last_name']));
            } catch (\Throwable $e) {
                error_log('Customer reset email failed: ' . $e->getMessage());
            }
        }
        $this->redirectSuccess('/customer/forgot-password', 'If the account exists, a reset link has been sent.');
    }

    public function resetPassword(): void
    {
        (new CustomerGuestMiddleware())->handle();
        $this->authView('reset', ['title' => 'Reset Password', 'token' => $this->request->string('token')]);
    }

    public function updatePassword(): never
    {
        $this->csrf();
        $password = $this->request->rawString('password');
        if (strlen($password) < 8 || $password !== $this->request->rawString('password_confirmation')) {
            $this->redirectError('/customer/reset-password?token=' . rawurlencode($this->request->string('token')), 'Use a matching password of at least 8 characters.');
        }
        $userId = $this->customers->consumeToken($this->request->string('token'), 'reset_password');
        if (!$userId) $this->redirectError('/customer/forgot-password', 'This reset link is invalid or expired.');
        $this->customers->updatePassword($userId, password_hash($password, PASSWORD_DEFAULT));
        $this->customers->log($userId, 'password_reset', 'Customer password reset.');
        $this->redirectSuccess('/customer/login', 'Password updated. Please sign in.');
    }

    public function logout(): never
    {
        $this->csrf();
        $customer = Session::get('customer_user');
        if (is_array($customer) && isset($customer['id'])) $this->customers->log((int) $customer['id'], 'logout', 'Customer signed out.');
        Session::remove('customer_user');
        Session::regenerate();
        $this->redirectSuccess('/customer/login', 'You have been signed out.');
    }

    private function authView(string $view, array $data): void
    {
        extract($data);
        require VIEW_PATH . '/customer/auth/' . $view . '.php';
    }

    private function csrf(): void
    {
        if (!csrf_validate()) {
            http_response_code(419);
            exit('Your session expired. Please refresh and try again.');
        }
    }
}
