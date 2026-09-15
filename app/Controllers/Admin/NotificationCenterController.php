<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Services\NotificationService;

final class NotificationCenterController extends AdminController
{
    public function __construct(private readonly NotificationService $notifications, private readonly Request $request)
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->render('notifications/index', ['title' => 'Notification Center', 'notifications' => $this->notifications->adminInbox(100)]);
    }

    public function feed(): never
    {
        $items = $this->notifications->adminInbox(10);
        foreach ($items as &$item) $item['action_url'] = !empty($item['action_url']) ? url((string) $item['action_url']) : url('/admin/notification-center');
        unset($item);
        $this->json(['count' => $this->notifications->adminUnreadCount(), 'notifications' => $items]);
    }

    public function read(int $id): never
    {
        $this->csrf();
        $this->notifications->markAdminRead($id);
        $this->json(['ok' => true, 'count' => $this->notifications->adminUnreadCount()]);
    }

    public function readAll(): never
    {
        $this->csrf();
        $this->notifications->markAllAdminRead();
        $this->json(['ok' => true, 'count' => 0]);
    }

    public function delete(int $id): never
    {
        $this->csrf();
        $this->notifications->deleteAdmin($id);
        $this->json(['ok' => true, 'count' => $this->notifications->adminUnreadCount()]);
    }

    private function csrf(): void
    {
        if (!csrf_validate()) $this->json(['ok' => false, 'message' => 'Session expired.'], 419);
    }
}
