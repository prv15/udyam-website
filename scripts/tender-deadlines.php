<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$sessionPath=dirname(__DIR__).'/storage/sessions';
if(!is_dir($sessionPath))mkdir($sessionPath,0770,true);
ini_set('session.save_path',$sessionPath);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;
use App\Services\TenderDeadlineAutomationService;

$options = getopt('', ['dry-run', 'date:']);
$date = isset($options['date']) ? DateTimeImmutable::createFromFormat('!Y-m-d', (string) $options['date']) : null;
if (isset($options['date']) && !$date) {
    fwrite(STDERR, "Invalid --date value. Use YYYY-MM-DD.\n");
    exit(2);
}

try {
    $summary = (new TenderDeadlineAutomationService(Database::connection()))
        ->run($date ?: null, array_key_exists('dry-run', $options));
    fwrite(STDOUT, json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Tender deadline automation failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
