<?php

declare(strict_types=1);

namespace App\Controllers\Website;

use App\Core\Controller;
use App\Services\HomeService;

class HomeController extends Controller
{
    private HomeService $service;

    public function __construct()
    {
        $this->service = new HomeService();
    }

    public function index(): void
    {
        $data = $this->service->getHomeData();

        $this->view('website/home', [
            'title' => 'Udyam Ventures',
            'time'  => $data['serverTime']
        ]);
    }
}