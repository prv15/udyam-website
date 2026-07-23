<?php

namespace App\Models;

use PDO;

class Page extends Model
{
    /**
     * Database table.
     */
    protected string $table = 'pages';

    /**
     * Find page by slug.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->first([
            'slug' => $slug,
            'deleted_at' => null
        ]);
    }

    /**
     * Get paginated pages.
     */
    public function paginate(
        int $page = 1,
        int $perPage = 10,
        string $search = '',
        string $status = ''
    ): array {

        $offset = ($page - 1) * $perPage;

        $where = ['p.deleted_at IS NULL'];
        $params = [];

        if (!empty($search)) {
            $where[] = '(p.title LIKE :title_search OR p.slug LIKE :slug_search)';
            $params['title_search'] = "%{$search}%";
            $params['slug_search'] = "%{$search}%";
        }

        if (!empty($status)) {
            $where[] = 'p.status = :status';
            $params['status'] = $status;
        }

        $sql = "
            SELECT p.*, CONCAT_WS(' ', u.first_name, u.last_name) AS author
            FROM {$this->table} p
            LEFT JOIN users u ON u.id = p.updated_by
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count filtered pages.
     */
    public function total(
        string $search = '',
        string $status = ''
    ): int {

        $where = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($search)) {
            $where[] = '(title LIKE :title_search OR slug LIKE :slug_search)';
            $params['title_search'] = "%{$search}%";
            $params['slug_search'] = "%{$search}%";
        }

        if (!empty($status)) {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        $sql = "
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE " . implode(' AND ', $where);

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if slug already exists.
     */
    public function slugExists(
        string $slug,
        ?int $ignoreId = null
    ): bool {

        $sql = "
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE slug = :slug
        ";

        $params = [
            'slug' => $slug
        ];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Get published pages.
     */
    public function published(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM {$this->table}
            WHERE status='published'
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get draft pages.
     */
    public function drafts(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM {$this->table}
            WHERE status='draft'
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get archived pages.
     */
    public function archived(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM {$this->table}
            WHERE status='archived'
            AND deleted_at IS NULL
            ORDER BY created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
