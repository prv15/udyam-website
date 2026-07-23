<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Services\HomeService;

class HomeController extends Controller
{
    public function __construct(private readonly HomeService $home)
    {
    }

    public function index(): void
    {
        $content = $this->home->content();
        if ($content !== null) {
            $this->view('website/home-structured', array_merge($content, [
                'title' => $content['page']['seo_title'] ?: $content['page']['title'],
                'metaDescription' => $content['page']['seo_description'] ?: $content['page']['excerpt'],
                'bodyClass' => 'udyam-home',
            ]));
            return;
        }

        $this->view('website/home', [
            'title' => 'Udyam Ventures'
        ]);
    }
}
