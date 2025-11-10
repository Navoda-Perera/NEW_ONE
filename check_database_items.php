<?php

// Simple database check script
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Item;
use App\Models\TemporaryUploadAssociate;
use App\Models\ItemBulk;

echo "=== Database Check ===\n";

// Check total items
$totalItems = Item::count();
echo "Total items in database: $totalItems\n";

// Check items with barcodes like 'bn'
$bnItems = Item::where('barcode', 'like', 'bn%')->count();
echo "Items with barcodes starting with 'bn': $bnItems\n";

// Check specific barcode
$specificItem = Item::where('barcode', 'bn675111')->first();
if ($specificItem) {
    echo "Found item with barcode 'bn675111': ID {$specificItem->id}\n";
    echo "  - Receiver: {$specificItem->receiver_name}\n";
    echo "  - Weight: {$specificItem->weight}g\n";
    echo "  - Amount: Rs. {$specificItem->amount}\n";

    // Check if it has an item bulk
    $itemBulk = $specificItem->itemBulk;
    if ($itemBulk) {
        echo "  - Item Bulk ID: {$itemBulk->id}\n";
        echo "  - Location ID: {$itemBulk->location_id}\n";
    } else {
        echo "  - No item bulk associated\n";
    }
} else {
    echo "Item with barcode 'bn675111' NOT FOUND\n";
}

// Check temporary uploads
$totalTemp = TemporaryUploadAssociate::count();
echo "Total temporary upload associates: $totalTemp\n";

$specificTemp = TemporaryUploadAssociate::where('barcode', 'bn675111')->first();
if ($specificTemp) {
    echo "Found temporary item with barcode 'bn675111': ID {$specificTemp->id}\n";
} else {
    echo "Temporary item with barcode 'bn675111' NOT FOUND\n";
}

// Show some sample barcodes
echo "\nSample barcodes from items table:\n";
$sampleItems = Item::take(5)->get(['id', 'barcode', 'receiver_name']);
foreach ($sampleItems as $item) {
    echo "  - {$item->barcode} ({$item->receiver_name})\n";
}

echo "\nSample barcodes from temporary uploads:\n";
$sampleTemp = TemporaryUploadAssociate::take(5)->get(['id', 'barcode', 'receiver_name']);
foreach ($sampleTemp as $temp) {
    echo "  - {$temp->barcode} ({$temp->receiver_name})\n";
}

?>
