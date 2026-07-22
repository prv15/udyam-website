<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;

class PageService extends Service
{
    private Page $page;

    public function __construct()
    {
        $this->page = new Page();
    }

    public function getPage(string $slug): ?array
    {
        return $this->page->findBy(
            'slug',
            $slug
        );
    }
}