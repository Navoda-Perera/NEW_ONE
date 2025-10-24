<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\Receipt;
use App\Models\ItemBulk;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== RECEIPT FIX TEST ===\n";
echo "Testing receipt generation fixes:\n";
echo "1. Receipts should only show item amounts (no postage)\n";
echo "2. Bulk uploads should have only ONE receipt per ItemBulk\n\n";

// Check current state of receipts for ItemBulk ID 53 (from database screenshot)
echo "=== CURRENT STATE CHECK ===\n";
$itemBulk53 = ItemBulk::find(53);
if ($itemBulk53) {
    echo "ItemBulk 53 found:\n";
    echo "  - Item Quantity: {$itemBulk53->item_quantity}\n";
    echo "  - Category: {$itemBulk53->category}\n";
    echo "  - Service Type: {$itemBulk53->service_type}\n";

    $receipts53 = Receipt::where('item_bulk_id', 53)->get();
    echo "  - Number of receipts: " . $receipts53->count() . "\n";

    if ($receipts53->count() > 0) {
        echo "  - Receipt details:\n";
        foreach ($receipts53 as $receipt) {
            echo "    * Receipt ID: {$receipt->id}, Quantity: {$receipt->item_quantity}, Amount: {$receipt->amount}\n";
        }
    }
} else {
    echo "ItemBulk 53 not found.\n";
}

echo "\n=== RECOMMENDATION ===\n";
echo "If ItemBulk 53 has multiple receipts, they should be consolidated into one receipt.\n";
echo "The receipt amount should only contain item amounts (excluding postage).\n";
echo "The receipt quantity should match the ItemBulk item_quantity.\n\n";

// Find any other ItemBulks with multiple receipts
echo "=== CHECKING FOR OTHER DUPLICATE RECEIPTS ===\n";
$duplicateReceipts = Receipt::selectRaw('item_bulk_id, COUNT(*) as receipt_count')
    ->groupBy('item_bulk_id')
    ->having('receipt_count', '>', 1)
    ->get();

if ($duplicateReceipts->count() > 0) {
    echo "Found ItemBulks with multiple receipts:\n";
    foreach ($duplicateReceipts as $duplicate) {
        echo "  - ItemBulk ID: {$duplicate->item_bulk_id}, Receipt Count: {$duplicate->receipt_count}\n";
    }
} else {
    echo "No ItemBulks with multiple receipts found.\n";
}

echo "\n=== TEST COMPLETE ===\n";
