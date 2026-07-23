<?php

declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/Views');
define('CONFIG_PATH', APP_PATH . '/Config');
define('ROUTES_PATH', APP_PATH . '/Routes');
define('ASSET_PATH', BASE_PATH . '/assets');
define('PUBLIC_PATH', BASE_PATH);

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

if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
    $dotenv->required(['APP_URL', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME']);
}

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

$sessionPath = trim((string) ($_ENV['SESSION_PATH'] ?? ''));

if ($sessionPath !== '') {
    if (!is_dir($sessionPath) && !mkdir($sessionPath, 0770, true) && !is_dir($sessionPath)) {
        throw new RuntimeException('Unable to create the configured session directory.');
    }

    if (!is_writable($sessionPath)) {
        throw new RuntimeException('The configured session directory is not writable.');
    }

    session_save_path($sessionPath);
}

session_name($sessionName);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/app/Helpers/functions.php';
require_once dirname(__DIR__) . '/app/Helpers/url.php';
require_once dirname(__DIR__) . '/app/Helpers/assets.php';
require_once dirname(__DIR__) . '/app/Helpers/config.php';
require_once dirname(__DIR__) . '/app/Helpers/flash.php';
require_once dirname(__DIR__) . '/app/Helpers/slug.php';
require_once dirname(__DIR__) . '/app/helpers.php';

\App\Core\App::container()->bind(
    \PDO::class,
    static fn (): \PDO => \App\Config\Database::connection()
);
