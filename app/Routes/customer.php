<?php

declare(strict_types=1);

use App\Controllers\Customer\AuthController;
use App\Controllers\Customer\PaymentController;
use App\Controllers\Customer\PortalController;
use App\Controllers\Website\PaymentReturnController;

$router->get('/customer', [AuthController::class,'entry']);
$router->get('/customer/login', [AuthController::class,'login']);
$router->post('/customer/login', [AuthController::class,'authenticate']);
$router->get('/customer/register', [AuthController::class,'register']);
$router->post('/customer/register', [AuthController::class,'storeRegistration']);
$router->get('/customer/verify-email', [AuthController::class,'verifyEmail']);
$router->post('/customer/resend-verification', [AuthController::class,'resendVerification']);
$router->get('/customer/forgot-password', [AuthController::class,'forgotPassword']);
$router->post('/customer/forgot-password', [AuthController::class,'sendReset']);
$router->get('/customer/reset-password', [AuthController::class,'resetPassword']);
$router->post('/customer/reset-password', [AuthController::class,'updatePassword']);
$router->post('/customer/logout', [AuthController::class,'logout']);

$router->get('/customer/dashboard', [PortalController::class,'dashboard']);
$router->get('/customer/tenders', [PortalController::class,'tenders']);
$router->get('/customer/tenders/{id}', [PortalController::class,'tender']);
$router->get('/customer/search', [PortalController::class,'search']);
$router->get('/customer/profile', [PortalController::class,'profile']);
$router->post('/customer/profile', [PortalController::class,'updateProfile']);
$router->get('/customer/plans', [PortalController::class,'plans']);
$router->get('/customer/subscription', [PortalController::class,'subscription']);
$router->post('/customer/subscription/request', [PortalController::class,'requestSubscriptionAction']);
$router->post('/customer/plans/{id}/subscribe', [PortalController::class,'subscribe']);
$router->get('/customer/payments/demo/{token}', [PaymentController::class,'demo']);
$router->post('/customer/payments/demo/{token}/complete', [PaymentController::class,'demoComplete']);
$router->post('/customer/payments/demo/{token}/fail', [PaymentController::class,'demoFail']);
$router->get('/customer/payments/return', [PaymentReturnController::class,'handle']);
$router->get('/customer/services', [PortalController::class,'services']);
$router->get('/customer/services/{id}/apply', [PortalController::class,'apply']);
$router->post('/customer/services/{id}/apply', [PortalController::class,'submitApplication']);
$router->get('/customer/applications', [PortalController::class,'applications']);
$router->get('/customer/applications/{id}', [PortalController::class,'application']);
$router->get('/customer/documents', [PortalController::class,'documents']);
$router->post('/customer/documents/upload', [PortalController::class,'uploadDocument']);
$router->get('/customer/documents/{id}/download', [PortalController::class,'downloadDocument']);
$router->post('/customer/documents/{id}/delete', [PortalController::class,'deleteDocument']);
$router->get('/customer/notifications', [PortalController::class,'notifications']);
$router->get('/customer/notifications/feed', [PortalController::class,'notificationFeed']);
$router->post('/customer/notifications/read-all', [PortalController::class,'markAllNotifications']);
$router->post('/customer/notifications/{id}', [PortalController::class,'notification']);
$router->get('/customer/billing', [PortalController::class,'billing']);
$router->get('/customer/billing/invoices', [PortalController::class,'invoices']);
$router->get('/customer/billing/invoices/{id}', [PortalController::class,'invoice']);
$router->post('/customer/billing/invoices/{id}/pay', [PaymentController::class,'payInvoice']);
$router->get('/customer/billing/payments', [PortalController::class,'payments']);
$router->get('/customer/activity', [PortalController::class,'activity']);
$router->get('/customer/support', [PortalController::class,'support']);
$router->post('/customer/support', [PortalController::class,'submitSupport']);
