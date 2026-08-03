<?php

declare(strict_types=1);

use App\Controllers\Website\HomeController;
use App\Controllers\Website\AboutController;
use App\Controllers\Website\BlogController;
use App\Controllers\Website\PageController;
use App\Controllers\Website\NewsletterController;
use App\Controllers\Website\ConsultationController;
use App\Controllers\Website\InvoiceController;
use App\Controllers\Website\PayyantraWebhookController;
use App\Controllers\Website\SubscriptionPlansController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

$router->get('/about', [AboutController::class, 'index']);
$router->get('/subscription-plans', [SubscriptionPlansController::class, 'index']);

$router->get('/blog/{slug}', [BlogController::class, 'show']);
$router->post('/newsletter/subscribe', [NewsletterController::class, 'store']);
$router->post('/consultation/request', [ConsultationController::class, 'store']);
$router->get('/invoice/{token}', [InvoiceController::class, 'show']);
$router->get('/invoice/{token}/pdf', [InvoiceController::class, 'pdf']);
$router->post('/payments/payyantra/webhook', [PayyantraWebhookController::class, 'handle']);

// Keep this route last so specific website routes always take precedence.
$router->get('/{slug}', [PageController::class, 'show']);
