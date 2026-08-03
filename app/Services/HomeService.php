<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdminRecord;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Core\Session;
use App\Config\Database;

final class HomeService
{
    public function __construct(
        private readonly Page $pages,
        private readonly PageSection $sections,
        private readonly AdminRecord $records,
        private readonly Media $media
    ) {
    }

    public function content(): ?array
    {
        $page = $this->pages->first([
            'template' => 'home', 'status' => 'published', 'deleted_at' => null,
        ]);
        if ($page === null) {
            return null;
        }

        $sections = [];
        foreach ($this->sections->forPage((int) $page['id']) as $section) {
            if (!(bool) $section['is_enabled']) {
                continue;
            }
            $data = $section['data'];
            foreach ($data as $key => $value) {
                if (str_contains($key, 'image') || in_array($key, ['artwork', 'cover_image', 'logo'], true)) {
                    $data[$key . '_url'] = $this->mediaUrl(is_numeric($value) ? (int) $value : 0);
                }
            }
            $sections[$section['section_key']] = $data;
        }

        $viewer = Session::get('user', []);
        $hasTenderAccess = false;
        if (is_array($viewer) && ($viewer['user_type'] ?? '') === 'customer' && !empty($viewer['id'])) {
            $statement = Database::connection()->prepare(
                "SELECT 1 FROM customer_subscriptions WHERE customer_id = ? AND status = 'active' AND (expires_at IS NULL OR expires_at >= CURDATE()) LIMIT 1"
            );
            $statement->execute([(int) $viewer['id']]);
            $hasTenderAccess = $statement->fetchColumn() !== false;
        }

        return [
            'page' => $page,
            'sections' => $sections,
            'services' => $this->records->publishedForModule('services', $this->limit($sections, 'services', 6)),
            'focusAreas' => $this->records->publishedForModule('focus-areas', $this->limit($sections, 'focus_areas', 8)),
            'tenders' => $this->records->publishedForModule('tenders', $this->limit($sections, 'tenders', 5)),
            'partners' => $this->records->publishedForModule('partners', $this->limit($sections, 'partners', 10)),
            'insights' => $this->records->publishedForModule('blog', $this->limit($sections, 'knowledge_centre', 4)),
            'hasTenderAccess' => $hasTenderAccess,
            'subscriptionPlans' => Database::connection()->query("SELECT * FROM subscription_plans WHERE status='active' AND deleted_at IS NULL ORDER BY sort_order, name LIMIT 3")->fetchAll(),
        ];
    }

    private function limit(array $sections, string $key, int $default): int
    {
        $limit = (int) ($sections[$key]['limit'] ?? $default);
        return max(1, min(50, $limit ?: $default));
    }

    private function mediaUrl(int $id): ?string
    {
        if ($id < 1) return null;
        $media = $this->media->find($id);
        return $media ? media_url($media) : null;
    }
}
