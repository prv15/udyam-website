<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

try {
    $sql = (string) file_get_contents(__DIR__ . '/migrations/20260807_staff_business_cards.sql');
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        Database::connection()->exec($statement);
    }
    echo "Staff Management and Digital Business Cards migration completed successfully.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Migration failed: {$exception->getMessage()}\n");
    exit(1);
}
