<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking ItemBulk 101 items:\n";
$bulk = App\Models\ItemBulk::find(101);
if ($bulk) {
    $items = $bulk->items;
    echo "Total items: " . $items->count() . "\n";
    foreach ($items as $item) {
        echo "Item ID: {$item->id}, Service: {$item->service_type}, Amount: " . ($item->amount ?: '0') . ", Weight: {$item->weight}, Postage: {$item->postage}\n";
    }

    echo "\nChecking for existing receipts:\n";
    $receipts = App\Models\Receipt::where('item_bulk_id', 101)->get();
    echo "Found " . $receipts->count() . " receipts\n";
    foreach ($receipts as $receipt) {
        echo "Receipt ID: {$receipt->id}, Amount: {$receipt->amount}, Items: {$receipt->item_quantity}\n";
    }
} else {
    echo "ItemBulk 101 not found\n";
}
