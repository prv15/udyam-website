<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AdminRecord extends Model
{
    protected string $table = 'admin_records';

    public function paginate(string $module, int $page, int $perPage, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT * FROM admin_records WHERE module = :module AND deleted_at IS NULL';
        $params = ['module' => $module];
        if ($search !== '') {
            $sql .= ' AND (title LIKE :search OR data LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    public function total(string $module, string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM admin_records WHERE module = :module AND deleted_at IS NULL';
        $params = ['module' => $module];
        if ($search !== '') {
            $sql .= ' AND (title LIKE :search OR data LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    public function findForModule(string $module, int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM admin_records WHERE id = :id AND module = :module AND deleted_at IS NULL LIMIT 1'
        );
        $statement->execute(['id' => $id, 'module' => $module]);
        $record = $statement->fetch();
        return $record ? $this->hydrate($record) : null;
    }

    public function publishedForModule(string $module, int $limit = 12): array
    {
        $statement = $this->db->prepare(
            "SELECT * FROM admin_records
             WHERE module = :module AND status IN ('published', 'active') AND deleted_at IS NULL
             ORDER BY sort_order, created_at DESC LIMIT :limit"
        );
        $statement->bindValue(':module', $module);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    private function hydrate(array $record): array
    {
        $data = json_decode((string) $record['data'], true);
        return array_merge($record, is_array($data) ? $data : []);
    }
}
