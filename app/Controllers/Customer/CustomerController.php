<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\CustomerAuthMiddleware;

abstract class CustomerController extends Controller
{
    protected array $customer;

    public function __construct()
    {
        (new CustomerAuthMiddleware())->handle();
        $this->customer = Session::get('customer_user');
    }

    protected function render(string $view, array $data = []): void
    {
        extract(array_merge(['customer' => $this->customer], $data));
        $content = VIEW_PATH . '/customer/' . $view . '.php';
        if (!is_file($content)) throw new \RuntimeException("Customer view {$view} not found.");
        require VIEW_PATH . '/customer/layouts/portal.php';
    }

    protected function csrf(): void
    {
        if (!csrf_validate()) {
            http_response_code(419);
            exit('Your session expired. Please refresh and try again.');
        }
    }
}
