<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Services\PageService;

final class PageController extends Controller
{
    public function __construct(private readonly PageService $pages)
    {
    }

    public function show(string $slug): void
    {
        $page = $this->pages->getPage($slug);
        if ($page === null) {
            $this->abort404();
        }

        $view = ($page['template'] ?? 'default') === 'sectioned'
            ? 'website/page-sectioned'
            : 'website/page';
        $chrome = $this->pages->siteChrome();

        $this->view($view, [
            'title' => $page['seo_title'] ?: $page['title'],
            'metaDescription' => $page['seo_description'] ?: $page['excerpt'],
            'metaKeywords' => $page['seo_keywords'],
            'page' => $page,
            'sections' => $this->pages->sections((int) $page['id']),
            'header' => $chrome['header'],
            'footer' => $chrome['footer'],
            'preview' => false,
            'bodyClass' => 'udyam-home inner-website-page',
        ]);
    }
}
