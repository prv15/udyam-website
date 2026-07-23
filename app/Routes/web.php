<?php

declare(strict_types=1);

use App\Controllers\Website\HomeController;
use App\Controllers\Website\AboutController;
use App\Controllers\Website\BlogController;
use App\Controllers\Website\PageController;
use App\Controllers\Website\NewsletterController;
use App\Controllers\Website\ConsultationController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

$router->get('/about', [AboutController::class, 'index']);

$router->get('/blog/{slug}', [BlogController::class, 'show']);
$router->post('/newsletter/subscribe', [NewsletterController::class, 'store']);
$router->post('/consultation/request', [ConsultationController::class, 'store']);

// Keep this route last so specific website routes always take precedence.
$router->get('/{slug}', [PageController::class, 'show']);
