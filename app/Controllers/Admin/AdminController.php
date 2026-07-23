<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;

abstract class AdminController extends Controller
{
    protected array $shared = [];

    public function __construct()
    {
        (new AuthMiddleware())->handle(true);

        $this->shared = [

            'title' => 'Udyam CMS',

            'user' => Session::get('user'),

            'adminMenu' => require CONFIG_PATH . '/admin-menu.php',

        ];
    }

    protected function render(string $view, array $data = []): void
    {
        $this->adminView(
            $view,
            array_merge($this->shared, $data)
        );
    }
}
