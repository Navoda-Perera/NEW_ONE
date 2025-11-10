<?php

// Fix missing receipt for bulk ID 100
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\Receipt;

echo "=== Fixing Missing Receipt for Bulk ID 100 ===\n\n";

// Get the item bulk and item details
$itemBulk = ItemBulk::find(100);
$item = Item::where('item_bulk_id', 100)->first();

if (!$itemBulk || !$item) {
    echo "❌ Cannot find ItemBulk 100 or its items!\n";
    exit(1);
}

echo "📋 Item Bulk Details:\n";
echo "  - Bulk ID: {$itemBulk->id}\n";
echo "  - Service Type: {$itemBulk->service_type}\n";
echo "  - Location ID: {$itemBulk->location_id}\n";
echo "  - Created By: {$itemBulk->created_by}\n";

echo "\n📦 Item Details:\n";
echo "  - Item ID: {$item->id}\n";
echo "  - Barcode: {$item->barcode}\n";
echo "  - Amount: {$item->amount}\n";
echo "  - Status: {$item->status}\n";

// Generate a passcode
function generatePasscode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Create the missing receipt
try {
    $receipt = Receipt::create([
        'item_quantity' => 1,
        'item_bulk_id' => $itemBulk->id,
        'amount' => $item->amount, // Use the item's amount
        'payment_type' => 'cash',
        'created_by' => $itemBulk->created_by ?: 10, // Use bulk creator or default to user 10
        'location_id' => $itemBulk->location_id,
        'passcode' => generatePasscode()
    ]);

    echo "\n✅ Receipt created successfully!\n";
    echo "  - Receipt ID: {$receipt->id}\n";
    echo "  - Amount: Rs. {$receipt->amount}\n";
    echo "  - Passcode: {$receipt->passcode}\n";
    echo "  - Location ID: {$receipt->location_id}\n";

    // Update the item bulk with proper totals
    $itemBulk->update([
        'total_amount' => $item->amount,
        'total_weight' => $item->weight,
        'user_id' => $itemBulk->created_by ?: 10
    ]);

    echo "\n✅ ItemBulk 100 updated with totals:\n";
    echo "  - Total Amount: {$item->amount}\n";
    echo "  - Total Weight: {$item->weight}\n";
    echo "  - User ID: " . ($itemBulk->created_by ?: 10) . "\n";

} catch (Exception $e) {
    echo "\n❌ Error creating receipt: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Fix completed successfully! Bulk ID 100 now has a receipt.\n";

?>
