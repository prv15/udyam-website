<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Services\PageService;

class AboutController extends Controller
{
    private PageService $service;

    public function __construct()
    {
        $this->service = new PageService();
    }

    public function index(): void
    {
        $page = $this->service->getPage('about');

        if ($page === null) {
            $this->abort404();
        }

        $this->view(
            'website/about',
            [
                'title' => $page['title'],
                'page' => $page
            ]
        );
    }
}
