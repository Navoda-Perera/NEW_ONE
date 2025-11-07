<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check for recent items that can be used for testing
$items = DB::table('items')
    ->where('status', 'accept')
    ->where('created_at', '>', '2025-11-04')
    ->limit(3)
    ->get(['id', 'barcode', 'status', 'item_bulk_id', 'receiver_name']);

echo "Recent items that can be used for testing:" . PHP_EOL;
foreach ($items as $item) {
    echo "Item ID: {$item->id}, Barcode: {$item->barcode}, Status: {$item->status}, Bulk ID: {$item->item_bulk_id}" . PHP_EOL;
}

?>
