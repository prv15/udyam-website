<?php

declare(strict_types=1);
$router->get('/admin', [
    \App\Controllers\Admin\AuthController::class,
    'entry'
]);
$router->get('/admin/login', [
    \App\Controllers\Admin\AuthController::class,
    'login'
]);

$router->post('/admin/login', [
    \App\Controllers\Admin\AuthController::class,
    'authenticate'
]);

$router->get('/admin/dashboard', [
    \App\Controllers\Admin\DashboardController::class,
    'index'
]);
$router->get('/admin/search', [
    \App\Controllers\Admin\GlobalSearchController::class,
    'index'
]);
$router->get('/admin/notification-center', [\App\Controllers\Admin\NotificationCenterController::class, 'index']);
$router->get('/admin/notification-center/feed', [\App\Controllers\Admin\NotificationCenterController::class, 'feed']);
$router->post('/admin/notification-center/read-all', [\App\Controllers\Admin\NotificationCenterController::class, 'readAll']);
$router->post('/admin/notification-center/{id}/read', [\App\Controllers\Admin\NotificationCenterController::class, 'read']);
$router->post('/admin/notification-center/{id}/delete', [\App\Controllers\Admin\NotificationCenterController::class, 'delete']);
$router->get('/admin/system/migrations', [\App\Controllers\Admin\SystemMigrationController::class, 'index']);
$router->post('/admin/system/migrations/enterprise-erp', [\App\Controllers\Admin\SystemMigrationController::class, 'applyEnterpriseExtension']);
$router->post('/admin/system/migrations/staff-management', [\App\Controllers\Admin\SystemMigrationController::class, 'applyStaffManagement']);

$router->post('/admin/logout', [
    \App\Controllers\Admin\AuthController::class,
    'logout'
]);

$router->get('/admin/pages', [
    \App\Controllers\Admin\PagesController::class,
    'index'
]);

$router->get('/admin/pages/create', [
    \App\Controllers\Admin\PagesController::class,
    'create'
]);

$router->get('/admin/pages/edit/{id}', [
    \App\Controllers\Admin\PagesController::class,
    'edit'
]);

$router->get('/admin/pages/preview/{id}', [
    \App\Controllers\Admin\PagesController::class,
    'preview'
]);
$router->get('/admin/pages/sections/{pageId}', [\App\Controllers\Admin\PageSectionsController::class, 'index']);
$router->get('/admin/pages/sections/{pageId}/edit/{sectionKey}', [\App\Controllers\Admin\PageSectionsController::class, 'edit']);
$router->post('/admin/pages/sections/{pageId}/update/{sectionKey}', [\App\Controllers\Admin\PageSectionsController::class, 'update']);
$router->post('/admin/pages/sections/{pageId}/upload/{sectionKey}', [\App\Controllers\Admin\PageSectionsController::class, 'upload']);
$router->post('/admin/pages/sections/{pageId}/reorder', [\App\Controllers\Admin\PageSectionsController::class, 'reorder']);

$router->get('/admin/media', [
    \App\Controllers\Admin\MediaController::class,
    'index'
]);

$router->post('/admin/media/upload', [
    \App\Controllers\Admin\MediaController::class,
    'upload'
]);

$router->post('/admin/media/delete/{id}', [
    \App\Controllers\Admin\MediaController::class,
    'delete'
]);

$router->post('/admin/pages/store', [
    \App\Controllers\Admin\PagesController::class,
    'store'
]);

$router->post('/admin/pages/delete/{id}', [
    \App\Controllers\Admin\PagesController::class,
    'destroy'
]);

$router->post('/admin/pages/update/{id}', [
    \App\Controllers\Admin\PagesController::class,
    'update'
]);

$router->get('/admin/users', [\App\Controllers\Admin\UsersController::class, 'index']);
$router->get('/admin/users/create', [\App\Controllers\Admin\UsersController::class, 'create']);
$router->post('/admin/users/store', [\App\Controllers\Admin\UsersController::class, 'store']);
$router->get('/admin/users/edit/{id}', [\App\Controllers\Admin\UsersController::class, 'edit']);
$router->post('/admin/users/update/{id}', [\App\Controllers\Admin\UsersController::class, 'update']);
$router->post('/admin/users/delete/{id}', [\App\Controllers\Admin\UsersController::class, 'destroy']);
$router->get('/admin/roles', [\App\Controllers\Admin\RolesController::class, 'index']);
$router->get('/admin/roles/create', [\App\Controllers\Admin\RolesController::class, 'create']);
$router->post('/admin/roles/store', [\App\Controllers\Admin\RolesController::class, 'store']);
$router->get('/admin/roles/{id}/edit', [\App\Controllers\Admin\RolesController::class, 'edit']);
$router->post('/admin/roles/{id}/update', [\App\Controllers\Admin\RolesController::class, 'update']);
$router->post('/admin/roles/{id}/delete', [\App\Controllers\Admin\RolesController::class, 'destroy']);
$router->get('/admin/profile', [\App\Controllers\Admin\AccountController::class, 'profile']);
$router->post('/admin/profile/update', [\App\Controllers\Admin\AccountController::class, 'updateProfile']);
$router->get('/admin/change-password', [\App\Controllers\Admin\AccountController::class, 'password']);
$router->post('/admin/change-password/update', [\App\Controllers\Admin\AccountController::class, 'updatePassword']);

$router->get('/admin/customers/workspace/{id}', [\App\Controllers\Admin\CustomerBillingController::class,'workspace']);
$router->post('/admin/customers/workspace/{id}/invoice', [\App\Controllers\Admin\CustomerBillingController::class,'invoice']);
$router->post('/admin/customers/workspace/{id}/payment', [\App\Controllers\Admin\CustomerBillingController::class,'payment']);
$router->post('/admin/customers/workspace/{id}/activate-subscription', [\App\Controllers\Admin\CustomerBillingController::class,'activateInvoiceSubscription']);
$router->get('/admin/customers/workspace/{id}/documents/{documentId}/view', [\App\Controllers\Admin\CustomerBillingController::class,'viewDocument']);
$router->get('/admin/customers/workspace/{id}/documents/{documentId}/download', [\App\Controllers\Admin\CustomerBillingController::class,'downloadDocument']);
$router->get('/admin/subscription-requests', [\App\Controllers\Admin\CustomerBillingController::class,'subscriptionRequests']);
$router->post('/admin/subscription-requests/{id}/review', [\App\Controllers\Admin\CustomerBillingController::class,'reviewSubscriptionRequest']);
$router->get('/admin/subscription-plans', [\App\Controllers\Admin\CustomerBillingController::class,'plans']);
$router->get('/admin/subscription-plans/create', [\App\Controllers\Admin\CustomerBillingController::class,'planForm']);
$router->post('/admin/subscription-plans/store', [\App\Controllers\Admin\CustomerBillingController::class,'savePlan']);
$router->get('/admin/subscription-plans/edit/{id}', [\App\Controllers\Admin\CustomerBillingController::class,'editPlan']);
$router->post('/admin/subscription-plans/update/{id}', [\App\Controllers\Admin\CustomerBillingController::class,'updatePlan']);
$router->post('/admin/subscription-plans/archive/{id}', [\App\Controllers\Admin\CustomerBillingController::class,'archivePlan']);

// Internal Udyam Ventures team. Keep these ahead of the generic module routes.
$router->get('/admin/staff', [\App\Controllers\Admin\StaffController::class, 'index']);
$router->get('/admin/staff/export', [\App\Controllers\Admin\StaffController::class, 'export']);
$router->get('/admin/staff/create', [\App\Controllers\Admin\StaffController::class, 'create']);
$router->post('/admin/staff/store', [\App\Controllers\Admin\StaffController::class, 'store']);
$router->get('/admin/staff/{id}', [\App\Controllers\Admin\StaffController::class, 'show']);
$router->get('/admin/staff/{id}/edit', [\App\Controllers\Admin\StaffController::class, 'edit']);
$router->post('/admin/staff/{id}/update', [\App\Controllers\Admin\StaffController::class, 'update']);
$router->post('/admin/staff/{id}/status', [\App\Controllers\Admin\StaffController::class, 'status']);

$router->get('/admin/digital-business-cards', [\App\Controllers\Admin\DigitalBusinessCardsController::class, 'index']);
$router->get('/admin/digital-business-cards/{id}/edit', [\App\Controllers\Admin\DigitalBusinessCardsController::class, 'edit']);
$router->post('/admin/digital-business-cards/{id}/update', [\App\Controllers\Admin\DigitalBusinessCardsController::class, 'update']);
$router->post('/admin/digital-business-cards/{id}/toggle', [\App\Controllers\Admin\DigitalBusinessCardsController::class, 'toggle']);
$router->post('/admin/digital-business-cards/{id}/regenerate', [\App\Controllers\Admin\DigitalBusinessCardsController::class, 'regenerate']);
$router->get('/admin/digital-business-cards/{id}/qr', [\App\Controllers\Admin\DigitalBusinessCardsController::class, 'qr']);
$router->get('/admin/digital-business-cards/{id}/vcard', [\App\Controllers\Admin\DigitalBusinessCardsController::class, 'vcard']);

$router->get('/admin/tenders/import', [\App\Controllers\Admin\ModulesController::class, 'importForm']);
$router->get('/admin/tenders/import-template', [\App\Controllers\Admin\ModulesController::class, 'importTemplate']);
$router->post('/admin/tenders/import', [\App\Controllers\Admin\ModulesController::class, 'import']);
$router->post('/admin/{module}/bulk-delete', [\App\Controllers\Admin\ModulesController::class, 'bulkDelete']);

$router->get('/admin/{module}', [
    \App\Controllers\Admin\ModulesController::class,
    'index'
]);
$router->get('/admin/{module}/create', [
    \App\Controllers\Admin\ModulesController::class,
    'create'
]);
$router->post('/admin/{module}/store', [
    \App\Controllers\Admin\ModulesController::class,
    'store'
]);
$router->get('/admin/{module}/edit/{id}', [
    \App\Controllers\Admin\ModulesController::class,
    'edit'
]);
$router->post('/admin/{module}/update/{id}', [
    \App\Controllers\Admin\ModulesController::class,
    'update'
]);
$router->post('/admin/{module}/delete/{id}', [
    \App\Controllers\Admin\ModulesController::class,
    'destroy'
]);
