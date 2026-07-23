<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class User extends Model
{
    protected string $table = 'users';

    protected string $primaryKey = 'id';

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function paginate(int $page, int $perPage, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT id, first_name, last_name, email, user_type, status, created_at
                FROM users WHERE deleted_at IS NULL';
        $params = [];
        if ($search !== '') {
            $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function total(string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE deleted_at IS NULL';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND deleted_at IS NULL';
        $params = ['email' => $email];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn() > 0;
    }
}
