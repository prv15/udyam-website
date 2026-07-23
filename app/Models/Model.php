<?php

namespace App\Models;
use App\Config\Database;
use PDO;

abstract class Model
{
    /** @var array<string, bool> */
    private static array $softDeleteSupport = [];

    protected PDO $db;

    protected string $table;

    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        if ($this->supportsSoftDeletes()) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch() ?: null;
    }

    public function findBy(string $column, mixed $value): ?array
    {
        return $this->first([$column => $value]);
    }

    public function first(array $conditions = []): ?array
    {
        $where = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $this->assertColumn($column);

            if ($value === null) {
                $where[] = "{$column} IS NULL";
                continue;
            }

            $parameter = 'where_' . $column;
            $where[] = "{$column} = :{$parameter}";
            $params[$parameter] = $value;
        }

        $sql = "SELECT * FROM {$this->table}";
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() ?: null;
    }

    public function create(array $data): int
    {
        if ($data === []) {
            throw new \InvalidArgumentException('Cannot create an empty record.');
        }

        foreach (array_keys($data) as $column) {
            $this->assertColumn($column);
        }

        $columns = array_keys($data);
        $parameters = array_map(static fn (string $column): string => ':' . $column, $columns);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, implode(', ', $columns), implode(', ', $parameters));

        $statement = $this->db->prepare($sql);
        $statement->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function insert(array $data): int
    {
        return $this->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById($id, $data);
    }

    public function updateById(int $id, array $data): bool
    {
        if ($data === []) {
            return false;
        }

        foreach (array_keys($data) as $column) {
            $this->assertColumn($column);
        }

        $assignments = array_map(static fn (string $column): string => "{$column} = :{$column}", array_keys($data));
        $data['__id'] = $id;
        $sql = sprintf('UPDATE %s SET %s WHERE %s = :__id', $this->table, implode(', ', $assignments), $this->primaryKey);
        if ($this->supportsSoftDeletes()) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $statement = $this->db->prepare($sql);

        return $statement->execute($data);
    }

    public function delete(int $id): bool
    {
        if (!$this->supportsSoftDeletes()) {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            return $stmt->execute(['id' => $id]);
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET deleted_at = NOW()
            WHERE {$this->primaryKey} = :id
        ");

        return $stmt->execute([
            'id' => $id
        ]);
    }

    public function restore(int $id): bool
    {
        if (!$this->supportsSoftDeletes()) {
            return false;
        }

        $statement = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NULL WHERE {$this->primaryKey} = :id");

        return $statement->execute(['id' => $id]);
    }

    private function assertColumn(string $column): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column)) {
            throw new \InvalidArgumentException('Invalid database column.');
        }
    }

    private function supportsSoftDeletes(): bool
    {
        if (array_key_exists($this->table, self::$softDeleteSupport)) {
            return self::$softDeleteSupport[$this->table];
        }

        $statement = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'deleted_at'");

        return self::$softDeleteSupport[$this->table] = $statement->fetch() !== false;
    }
}
