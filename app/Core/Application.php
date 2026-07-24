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

        // Register namespaced application areas before the public single-slug
        // fallback route. Otherwise "/admin" is consumed as a website page slug.
        require dirname(__DIR__) . '/Routes/admin.php';
        require dirname(__DIR__) . '/Routes/customer.php';
        require dirname(__DIR__) . '/Routes/api.php';
        require dirname(__DIR__) . '/Routes/web.php';
    }

    public function run(): void
    {
        $this->router->dispatch();
    }
}
