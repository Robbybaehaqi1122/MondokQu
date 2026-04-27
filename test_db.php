<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing database connection...\n";

try {
    DB::connection()->getPdo();
    echo "Connected to database successfully!\n";
    echo "Database: " . DB::connection()->getDatabaseName() . "\n";
} catch (Exception $e) {
    echo "Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}
