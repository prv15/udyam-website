<?php

declare(strict_types=1);

use App\Controllers\Website\HomeController;
use App\Controllers\Website\AboutController;
use App\Controllers\Website\BlogController;

$router->get('/', [HomeController::class, 'index']);

$router->get('/about', [AboutController::class, 'index']);

$router->get('/blog/{slug}', [BlogController::class, 'show']);