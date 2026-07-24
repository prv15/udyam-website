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
$router->get('/admin/profile', [\App\Controllers\Admin\AccountController::class, 'profile']);
$router->post('/admin/profile/update', [\App\Controllers\Admin\AccountController::class, 'updateProfile']);
$router->get('/admin/change-password', [\App\Controllers\Admin\AccountController::class, 'password']);
$router->post('/admin/change-password/update', [\App\Controllers\Admin\AccountController::class, 'updatePassword']);

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
