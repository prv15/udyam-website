<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;

class BlogController extends Controller
{
    public function show(string $slug): void
    {
        $this->view('website/blog', [
            'title' => 'Blog',
            'slug'  => $slug
        ]);
    }
}