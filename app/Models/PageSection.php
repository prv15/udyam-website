<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class PageSection extends Model
{
    protected string $table = 'page_sections';

    public function forPage(int $pageId): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM page_sections WHERE page_id = :page_id ORDER BY sort_order, id'
        );
        $statement->execute(['page_id' => $pageId]);
        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    public function findForPage(int $pageId, string $sectionKey): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM page_sections WHERE page_id = :page_id AND section_key = :section_key LIMIT 1'
        );
        $statement->execute(['page_id' => $pageId, 'section_key' => $sectionKey]);
        $section = $statement->fetch();
        return $section ? $this->hydrate($section) : null;
    }

    public function seed(int $pageId, array $definitions): void
    {
        $statement = $this->db->prepare(
            'INSERT IGNORE INTO page_sections (page_id, section_key, sort_order, is_enabled, data)
             VALUES (:page_id, :section_key, :sort_order, 1, :data)'
        );
        $order = 0;
        foreach ($definitions as $key => $definition) {
            $statement->execute([
                'page_id' => $pageId, 'section_key' => $key,
                'sort_order' => $order++, 'data' => '{}',
            ]);
        }
    }

    public function saveSection(int $pageId, string $sectionKey, array $data, bool $enabled): void
    {
        $statement = $this->db->prepare(
            'UPDATE page_sections SET data = :data, is_enabled = :enabled
             WHERE page_id = :page_id AND section_key = :section_key'
        );
        $statement->execute([
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'enabled' => $enabled ? 1 : 0, 'page_id' => $pageId, 'section_key' => $sectionKey,
        ]);
    }

    public function reorder(int $pageId, array $keys): void
    {
        $statement = $this->db->prepare(
            'UPDATE page_sections SET sort_order = :sort_order WHERE page_id = :page_id AND section_key = :section_key'
        );
        foreach (array_values($keys) as $order => $key) {
            $statement->execute(['sort_order' => $order, 'page_id' => $pageId, 'section_key' => $key]);
        }
    }

    private function hydrate(array $section): array
    {
        $data = json_decode((string) $section['data'], true);
        $section['data'] = is_array($data) ? $data : [];
        return $section;
    }
}
