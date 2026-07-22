<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;

class AboutController extends Controller
{
    public function index(): void
    {
        $this->view('website/about', [
            'title' => 'About Us'
        ]);
    }
}