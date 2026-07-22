<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\Database as DBConfig;
use PDO;

class Database
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = DBConfig::connection();
    }

    public function query(string $sql, array $params = []): array
    {
        $statement = $this->db->prepare($sql);

        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function first(string $sql, array $params = []): ?array
    {
        $statement = $this->db->prepare($sql);

        $statement->execute($params);

        $result = $statement->fetch();

        return $result ?: null;
    }

    public function execute(string $sql, array $params = []): bool
    {
        $statement = $this->db->prepare($sql);

        return $statement->execute($params);
    }

    public function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}