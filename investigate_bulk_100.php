<?php

// Check why bulk ID 100 didn't create a receipt
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\Receipt;

echo "=== Investigating Bulk ID 100 Receipt Issue ===\n\n";

// Check if ItemBulk 100 exists
$itemBulk = ItemBulk::find(100);
if ($itemBulk) {
    echo "✅ ItemBulk 100 EXISTS:\n";
    echo "  - ID: {$itemBulk->id}\n";
    echo "  - User ID: {$itemBulk->user_id}\n";
    echo "  - Location ID: {$itemBulk->location_id}\n";
    echo "  - Service Type: {$itemBulk->service_type}\n";
    echo "  - Total Amount: {$itemBulk->total_amount}\n";
    echo "  - Total Weight: {$itemBulk->total_weight}\n";
    echo "  - Created: {$itemBulk->created_at}\n";
    echo "  - Updated: {$itemBulk->updated_at}\n\n";
} else {
    echo "❌ ItemBulk 100 NOT FOUND!\n\n";
}

// Check items with bulk_id 100
$items = Item::where('item_bulk_id', 100)->get();
echo "Items with item_bulk_id = 100: " . $items->count() . "\n";
foreach ($items as $item) {
    echo "  - Item ID: {$item->id}, Barcode: {$item->barcode}, Amount: {$item->amount}, Status: {$item->status}\n";
}
echo "\n";

// Check if there's any receipt for bulk 100
$receipt = Receipt::where('item_bulk_id', 100)->first();
if ($receipt) {
    echo "✅ Receipt EXISTS for bulk 100:\n";
    echo "  - Receipt ID: {$receipt->id}\n";
    echo "  - Amount: {$receipt->amount}\n";
    echo "  - Quantity: {$receipt->item_quantity}\n";
    echo "  - Passcode: {$receipt->passcode}\n";
    echo "  - Created: {$receipt->created_at}\n";
} else {
    echo "❌ NO RECEIPT found for bulk 100!\n\n";
}

// Check the latest receipts to see pattern
echo "Latest 5 receipts:\n";
$latestReceipts = Receipt::orderBy('id', 'desc')->take(5)->get();
foreach ($latestReceipts as $receipt) {
    echo "  - Receipt ID: {$receipt->id}, Bulk ID: {$receipt->item_bulk_id}, Amount: {$receipt->amount}, Qty: {$receipt->item_quantity}\n";
}
echo "\n";

// Check if there are any receipts with NULL item_bulk_id
$nullBulkReceipts = Receipt::whereNull('item_bulk_id')->count();
echo "Receipts with NULL item_bulk_id: {$nullBulkReceipts}\n\n";

// Check ItemBulks that don't have receipts
echo "ItemBulks without receipts:\n";
$bulksWithoutReceipts = ItemBulk::whereDoesntHave('receipts')->get();
foreach ($bulksWithoutReceipts as $bulk) {
    $itemCount = Item::where('item_bulk_id', $bulk->id)->count();
    echo "  - Bulk ID: {$bulk->id}, Service: {$bulk->service_type}, Amount: {$bulk->total_amount}, Items: {$itemCount}, Created: {$bulk->created_at}\n";
}

?>