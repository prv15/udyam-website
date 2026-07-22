<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Composer Autoloader
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Load Environment
|--------------------------------------------------------------------------
*/

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
*/

date_default_timezone_set($_ENV['TIMEZONE'] ?? 'Asia/Kolkata');

/*
|--------------------------------------------------------------------------
| Error Reporting
|--------------------------------------------------------------------------
*/

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

if ($debug) {

    ini_set('display_errors', '1');
    error_reporting(E_ALL);

} else {

    ini_set('display_errors', '0');
    error_reporting(0);

}

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

$sessionName = $_ENV['SESSION_NAME'] ?? 'UDYAM_SESSION';

session_name($sessionName);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']),
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/app/Helpers/functions.php';
require_once dirname(__DIR__) . '/app/Helpers/url.php';
require_once dirname(__DIR__) . '/app/Helpers/assets.php';