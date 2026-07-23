<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use App\Models\Media;
use App\Models\PageSection;

class PageService extends Service
{
    public function __construct(
        private readonly Page $page,
        private readonly Media $media,
        private readonly PageSection $sections
    )
    {
    }

    public function getPage(string $slug): ?array
    {
        $page = $this->page->first([
            'slug' => $slug,
            'status' => 'published',
            'deleted_at' => null,
        ]);
        if ($page === null) {
            return null;
        }
        $page['featured_image_url'] = null;
        if (!empty($page['featured_image'])) {
            $image = $this->media->find((int) $page['featured_image']);
            if ($image !== null) {
                $page['featured_image_url'] = media_url($image);
            }
        }
        return $page;
    }

    public function sections(int $pageId): array
    {
        $result = [];
        foreach ($this->sections->forPage($pageId) as $section) {
            if (!(bool) $section['is_enabled']) continue;
            $data = $section['data'];
            foreach ($data as $key => $value) {
                if ((str_contains($key, 'image') || in_array($key, ['artwork', 'logo'], true)) && is_numeric($value)) {
                    $media = $this->media->find((int) $value);
                    $data[$key . '_url'] = $media ? media_url($media) : null;
                }
            }
            $result[$section['section_key']] = $data;
        }
        return $result;
    }

    public function siteChrome(): array
    {
        $home = $this->page->first([
            'template' => 'home',
            'status' => 'published',
            'deleted_at' => null,
        ]);
        if ($home === null) return ['header' => [], 'footer' => []];

        $chrome = ['header' => [], 'footer' => []];
        foreach ($this->sections->forPage((int) $home['id']) as $section) {
            $key = (string) $section['section_key'];
            if (!array_key_exists($key, $chrome) || !(bool) $section['is_enabled']) continue;
            $data = $section['data'];
            foreach ($data as $field => $value) {
                if ((str_contains($field, 'image') || in_array($field, ['logo', 'artwork'], true)) && is_numeric($value)) {
                    $media = $this->media->find((int) $value);
                    $data[$field . '_url'] = $media ? media_url($media) : null;
                }
            }
            $chrome[$key] = $data;
        }
        return $chrome;
    }
}
