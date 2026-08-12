<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\AdminRecord;
use App\Config\Database;
use App\Services\NotificationService;
use App\Models\Role;
use App\Services\PermissionService;

abstract class AdminController extends Controller
{
    protected array $shared = [];

    public function __construct()
    {
        (new AuthMiddleware())->handle(true);

        $permissionService = new PermissionService(new Role());
        $permissionService->authorizeCurrentRequest();

        $currentUser = Session::get('user');
        $menu = require CONFIG_PATH . '/admin-menu.php';

        $this->shared = [

            'title' => 'Udyam CMS',

            'user' => $currentUser,

            'adminMenu' => $permissionService->filterMenu($menu, $currentUser),
            'permissionService' => $permissionService,

            'unreadContactCount' => (new AdminRecord())->countByStatuses('contact-messages', ['new', 'active']),
            'adminNotificationCount' => (new NotificationService(Database::connection()))->adminUnreadCount(),

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
