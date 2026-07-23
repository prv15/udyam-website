<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Application;
use App\Core\Session;

Session::start();

$app = new Application();

$app->run();