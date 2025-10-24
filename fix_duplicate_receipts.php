<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Application;
use App\Models\Receipt;
use App\Models\ItemBulk;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== RECEIPT CONSOLIDATION FIX ===\n";
echo "This script will fix existing duplicate receipts for bulk uploads.\n\n";

DB::beginTransaction();
try {
    // Find all ItemBulks with multiple receipts
    $duplicateReceipts = Receipt::selectRaw('item_bulk_id, COUNT(*) as receipt_count')
        ->groupBy('item_bulk_id')
        ->having('receipt_count', '>', 1)
        ->get();

    echo "Found " . $duplicateReceipts->count() . " ItemBulks with multiple receipts.\n\n";

    foreach ($duplicateReceipts as $duplicate) {
        $itemBulkId = $duplicate->item_bulk_id;
        $itemBulk = ItemBulk::find($itemBulkId);
        
        if (!$itemBulk) {
            echo "Warning: ItemBulk {$itemBulkId} not found, skipping...\n";
            continue;
        }

        echo "Processing ItemBulk {$itemBulkId} ({$itemBulk->category}):\n";
        
        // Get all receipts for this ItemBulk
        $receipts = Receipt::where('item_bulk_id', $itemBulkId)->get();
        
        // Get all items for this ItemBulk to calculate correct total amount
        $items = Item::where('item_bulk_id', $itemBulkId)->get();
        $totalItemAmount = $items->sum('amount'); // Only item amounts, no postage
        $actualItemCount = $items->count();
        
        echo "  - Current receipts: {$receipts->count()}\n";
        echo "  - Actual items: {$actualItemCount}\n";
        echo "  - ItemBulk quantity: {$itemBulk->item_quantity}\n";
        echo "  - Total item amount (no postage): {$totalItemAmount}\n";
        
        // Keep the first receipt and update it with correct values
        $firstReceipt = $receipts->first();
        $firstReceipt->update([
            'item_quantity' => $actualItemCount,
            'amount' => $totalItemAmount // Only item amounts, no postage
        ]);
        
        echo "  - Updated receipt {$firstReceipt->id} with quantity: {$actualItemCount}, amount: {$totalItemAmount}\n";
        
        // Delete all other receipts
        $deletedCount = 0;
        foreach ($receipts->skip(1) as $receipt) {
            $receipt->delete();
            $deletedCount++;
            echo "  - Deleted duplicate receipt {$receipt->id}\n";
        }
        
        echo "  - Deleted {$deletedCount} duplicate receipts\n";
        
        // Update ItemBulk quantity to match actual items if needed
        if ($itemBulk->item_quantity != $actualItemCount) {
            $itemBulk->update(['item_quantity' => $actualItemCount]);
            echo "  - Updated ItemBulk quantity from {$itemBulk->item_quantity} to {$actualItemCount}\n";
        }
        
        echo "  ✅ Fixed ItemBulk {$itemBulkId}\n\n";
    }

    DB::commit();
    echo "=== FIX COMPLETED SUCCESSFULLY ===\n";
    echo "All duplicate receipts have been consolidated.\n";

} catch (\Exception $e) {
    DB::rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "All changes have been rolled back.\n";
}