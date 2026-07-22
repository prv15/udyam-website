<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

abstract class Model
{
    protected Database $db;

    protected string $table;

    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = new Database();
    }

    public function all(): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table}"
        );
    }

    public function find(int|string $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1",
            [$id]
        );
    }

    public function findBy(string $column, mixed $value): ?array
    {
        return $this->db->first(
            "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1",
            [$value]
        );
    }

    public function create(array $data): bool
    {
        $columns = implode(',', array_keys($data));

        $placeholders = implode(',', array_fill(0, count($data), '?'));

        return $this->db->execute(
            "INSERT INTO {$this->table} ({$columns})
             VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public function update(int|string $id, array $data): bool
    {
        $fields = [];

        foreach ($data as $column => $value) {

            $fields[] = "{$column} = ?";

        }

        $sql = implode(',', $fields);

        $values = array_values($data);

        $values[] = $id;

        return $this->db->execute(
            "UPDATE {$this->table}
             SET {$sql}
             WHERE {$this->primaryKey} = ?",
            $values
        );
    }

    public function delete(int|string $id): bool
    {
        return $this->db->execute(
            "DELETE FROM {$this->table}
             WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
}