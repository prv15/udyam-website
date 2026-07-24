<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class DashboardService
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function summary(): array
    {
        return [
            'pages' => $this->count('SELECT COUNT(*) FROM pages WHERE deleted_at IS NULL'),
            'services' => $this->moduleCount('services'),
            'applications' => $this->moduleCount('applications'),
            'customers' => $this->moduleCount('customers'),
            'media' => $this->count('SELECT COUNT(*) FROM media WHERE deleted_at IS NULL'),
            'pendingApplications' => $this->moduleCount('applications', 'draft'),
            'newMessages' => $this->moduleCountByStatuses('contact-messages', ['new', 'active']),
        ];
    }

    public function moduleDistribution(): array
    {
        $modules = ['services', 'blog', 'team', 'customers', 'applications', 'contact-messages'];
        $labels = [];
        $values = [];
        foreach ($modules as $module) {
            $labels[] = ucwords(str_replace('-', ' ', $module));
            $values[] = $this->moduleCount($module);
        }
        return ['labels' => $labels, 'values' => $values];
    }

    public function recentActivity(int $limit = 8): array
    {
        $recordStatement = $this->db->prepare(
            'SELECT module AS source, title, status AS action_status, updated_at
             FROM admin_records
             WHERE deleted_at IS NULL
             ORDER BY updated_at DESC
             LIMIT :limit'
        );
        $recordStatement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $recordStatement->execute();

        $pageStatement = $this->db->prepare(
            "SELECT 'pages' AS source, title, status AS action_status, updated_at
             FROM pages
             WHERE deleted_at IS NULL
             ORDER BY updated_at DESC
             LIMIT :limit"
        );
        $pageStatement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $pageStatement->execute();

        $activities = array_merge(
            $recordStatement->fetchAll(),
            $pageStatement->fetchAll()
        );

        usort(
            $activities,
            static fn (array $left, array $right): int =>
                strcmp((string) $right['updated_at'], (string) $left['updated_at'])
        );

        return array_slice($activities, 0, $limit);
    }

    public function systemStatus(): array
    {
        $total = @disk_total_space(BASE_PATH);
        $free = @disk_free_space(BASE_PATH);
        return [
            'database' => true,
            'php' => PHP_VERSION,
            'disk' => ($total && $free !== false) ? (int) round((($total - $free) / $total) * 100) : null,
            'https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
            'environment' => (string) ($_ENV['APP_ENV'] ?? 'production'),
        ];
    }

    private function moduleCount(string $module, ?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM admin_records WHERE module = :module AND deleted_at IS NULL';
        $params = ['module' => $module];
        if ($status !== null) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    private function moduleCountByStatuses(string $module, array $statuses): int
    {
        $statuses = array_values(array_filter($statuses, 'is_string'));
        if ($statuses === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $statement = $this->db->prepare(
            "SELECT COUNT(*) FROM admin_records
             WHERE module = ? AND deleted_at IS NULL AND status IN ({$placeholders})"
        );
        $statement->execute(array_merge([$module], $statuses));

        return (int) $statement->fetchColumn();
    }

    private function count(string $sql): int
    {
        return (int) $this->db->query($sql)->fetchColumn();
    }
}
