<?php

declare(strict_types=1);

namespace App\Core;

class Application
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        $router = $this->router;

        require dirname(__DIR__) . '/Routes/web.php';
        require dirname(__DIR__) . '/Routes/admin.php';
        require dirname(__DIR__) . '/Routes/api.php';
    }

    public function run(): void
    {
        $this->router->dispatch();
    }
}