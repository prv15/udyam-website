<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

$db = Database::connection();
try {
    $migrations = glob(__DIR__ . '/migrations/*.sql') ?: [];
    sort($migrations, SORT_STRING);
    foreach ($migrations as $migration) {
        $sql = (string) file_get_contents($migration);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            $db->exec($statement);
        }
    }
    echo "Customer portal and billing migration completed successfully.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Migration failed: {$exception->getMessage()}\n");
    exit(1);
}
