<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$statuses = DB::table('items')->select('status')->distinct()->get();
echo "Item status values in database:" . PHP_EOL;
foreach ($statuses as $status) {
    echo "- " . $status->status . PHP_EOL;
}

// Also check if 'delete' status already exists
$deletedItems = DB::table('items')->where('status', 'delete')->count();
echo PHP_EOL . "Items with 'delete' status: " . $deletedItems . PHP_EOL;

?>
