<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\Receipt;

echo "=== DEBUGGING PM BULK UPLOAD ISSUE ===\n\n";

// Check recent PM bulk uploads from today
echo "PM bulk uploads from today (2025-11-04):\n";
$todayBulks = ItemBulk::where('category', 'bulk_list')
    ->where('created_at', '>=', '2025-11-04 00:00:00')
    ->with(['items', 'receipts'])
    ->get();

if ($todayBulks->count() > 0) {
    foreach ($todayBulks as $bulk) {
        echo "\n📦 ItemBulk ID: {$bulk->id}\n";
        echo "   - Expected items: {$bulk->item_quantity}\n";
        echo "   - Actual items: " . $bulk->items->count() . "\n";
        echo "   - Service type: {$bulk->service_type}\n";
        echo "   - Sender: {$bulk->sender_name}\n";
        echo "   - Category: {$bulk->category}\n";
        echo "   - Created: {$bulk->created_at}\n";

        // Check items
        foreach ($bulk->items as $item) {
            echo "     Item {$item->id}: {$item->receiver_name}, Amount: {$item->amount}, Barcode: {$item->barcode}\n";
        }

        // Check receipts
        echo "   - Receipts: " . $bulk->receipts->count() . "\n";
        foreach ($bulk->receipts as $receipt) {
            echo "     Receipt {$receipt->id}: Amount: {$receipt->amount}, Passcode: {$receipt->passcode}\n";
        }
    }
} else {
    echo "No PM bulk uploads found for today.\n";
}

// Check if there are items with item_bulk_id 59 (from the screenshot)
echo "\n\nItems with item_bulk_id = 59:\n";
$items59 = Item::where('item_bulk_id', 59)->get();
foreach ($items59 as $item) {
    echo "Item {$item->id}: {$item->receiver_name}, Amount: {$item->amount}, Created: {$item->created_at}\n";
}

// Check ItemBulk 59
$bulk59 = ItemBulk::find(59);
if ($bulk59) {
    echo "\nItemBulk 59 details:\n";
    echo "Category: {$bulk59->category}\n";
    echo "Service: {$bulk59->service_type}\n";
    echo "Items: {$bulk59->item_quantity}\n";
    echo "Created: {$bulk59->created_at}\n";
}

echo "\n=== END DEBUG ===\n";

?>
