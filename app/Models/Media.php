<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Media extends Model
{
    protected string $table = 'media';

    public function paginate(int $page = 1, int $perPage = 24, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(original_name LIKE :original_name_search OR title LIKE :title_search OR alt_text LIKE :alt_text_search)';
            $term = '%' . $search . '%';
            $params = ['original_name_search' => $term, 'title_search' => $term, 'alt_text_search' => $term];
        }

        $statement = $this->db->prepare(
            'SELECT * FROM media WHERE ' . implode(' AND ', $where)
            . ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function total(string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM media WHERE deleted_at IS NULL';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (original_name LIKE :original_name_search OR title LIKE :title_search OR alt_text LIKE :alt_text_search)';
            $term = '%' . $search . '%';
            $params = ['original_name_search' => $term, 'title_search' => $term, 'alt_text_search' => $term];
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }
}
