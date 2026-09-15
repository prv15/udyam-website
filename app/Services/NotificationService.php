<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

/**
 * Cross-panel notification gateway. It is deliberately additive: customer-facing
 * notices stay in customer_notifications; administrator notices live separately.
 */
final class NotificationService
{
    private ?bool $adminTableAvailable = null;

    public function __construct(private readonly PDO $db)
    {
    }

    public function notifyAdmin(string $type, string $title, string $message, ?string $actionUrl = null, ?int $customerId = null, ?string $sourceType = null, ?int $sourceId = null): void
    {
        if (!$this->adminTableExists()) return;
        $statement = $this->db->prepare(
            'INSERT INTO admin_notifications (type,title,message,action_url,customer_id,source_type,source_id)
             VALUES (:type,:title,:message,:url,:customer,:source_type,:source_id)'
        );
        $statement->execute([
            'type' => mb_substr($type, 0, 80), 'title' => mb_substr($title, 0, 255),
            'message' => $message, 'url' => $actionUrl, 'customer' => $customerId,
            'source_type' => $sourceType, 'source_id' => $sourceId,
        ]);
    }

    public function adminInbox(int $limit = 12, bool $unreadOnly = false): array
    {
        if (!$this->adminTableExists()) return [];
        $where = 'n.deleted_at IS NULL' . ($unreadOnly ? ' AND n.read_at IS NULL' : '');
        $statement = $this->db->prepare(
            "SELECT n.*,u.first_name,u.last_name,cp.company_name
             FROM admin_notifications n
             LEFT JOIN users u ON u.id=n.customer_id
             LEFT JOIN customer_profiles cp ON cp.user_id=n.customer_id
             WHERE {$where} ORDER BY n.created_at DESC LIMIT :limit"
        );
        $statement->bindValue(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function adminUnreadCount(): int
    {
        if (!$this->adminTableExists()) return 0;
        return (int) $this->db->query('SELECT COUNT(*) FROM admin_notifications WHERE deleted_at IS NULL AND read_at IS NULL')->fetchColumn();
    }

    public function markAdminRead(int $id): void
    {
        if (!$this->adminTableExists()) return;
        $this->db->prepare('UPDATE admin_notifications SET read_at=COALESCE(read_at,NOW()) WHERE id=? AND deleted_at IS NULL')->execute([$id]);
    }

    public function markAllAdminRead(): void
    {
        if (!$this->adminTableExists()) return;
        $this->db->exec('UPDATE admin_notifications SET read_at=NOW() WHERE read_at IS NULL AND deleted_at IS NULL');
    }

    public function deleteAdmin(int $id): void
    {
        if (!$this->adminTableExists()) return;
        $this->db->prepare('UPDATE admin_notifications SET deleted_at=NOW() WHERE id=?')->execute([$id]);
    }

    private function adminTableExists(): bool
    {
        if ($this->adminTableAvailable !== null) return $this->adminTableAvailable;
        try {
            $statement = $this->db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name="admin_notifications"');
            $statement->execute();
            return $this->adminTableAvailable = (int) $statement->fetchColumn() > 0;
        } catch (PDOException) {
            return $this->adminTableAvailable = false;
        }
    }
}
