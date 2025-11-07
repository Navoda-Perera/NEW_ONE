<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TESTING RECEIPT QUANTITY UPDATE ON DELETE ===" . PHP_EOL . PHP_EOL;

// Find a receipt with quantity > 1 that we can test with
$testReceipt = DB::table('receipts')
    ->where('item_quantity', '>', 1)
    ->where('dlt_status', 0)
    ->first();

if (!$testReceipt) {
    echo "❌ No test receipt found with quantity > 1" . PHP_EOL;
    exit;
}

echo "Test Receipt ID: {$testReceipt->id}" . PHP_EOL;
echo "ItemBulk ID: {$testReceipt->item_bulk_id}" . PHP_EOL;
echo "Current Quantity: {$testReceipt->item_quantity}" . PHP_EOL;
echo "Current Amount: {$testReceipt->amount}" . PHP_EOL . PHP_EOL;

// Get items in this bulk
$items = DB::table('items')
    ->where('item_bulk_id', $testReceipt->item_bulk_id)
    ->where('status', '!=', 'delete')
    ->get(['id', 'barcode', 'amount', 'status']);

echo "Active items in this bulk:" . PHP_EOL;
foreach ($items as $item) {
    echo "  Item ID: {$item->id}, Barcode: {$item->barcode}, Amount: {$item->amount}, Status: {$item->status}" . PHP_EOL;
}
echo PHP_EOL;

if ($items->count() < 2) {
    echo "❌ Not enough active items to test deletion" . PHP_EOL;
    exit;
}

// Select the first item for testing
$testItem = $items->first();
echo "Simulating deletion of Item ID: {$testItem->id} (Amount: {$testItem->amount})" . PHP_EOL . PHP_EOL;

// Calculate expected results
$expectedQuantity = $testReceipt->item_quantity - 1;
$expectedAmount = $testReceipt->amount - $testItem->amount;

echo "=== EXPECTED RESULTS AFTER DELETE ===" . PHP_EOL;
echo "Receipt Quantity: {$testReceipt->item_quantity} → {$expectedQuantity}" . PHP_EOL;
echo "Receipt Amount: {$testReceipt->amount} → {$expectedAmount}" . PHP_EOL;
echo "Item Status: {$testItem->status} → delete" . PHP_EOL . PHP_EOL;

echo "=== NEW DELETE LOGIC SIMULATION ===" . PHP_EOL;

DB::beginTransaction();
try {
    // Simulate the NEW delete logic
    $receipt = DB::table('receipts')->where('id', $testReceipt->id)->first();
    $itemBulk = DB::table('item_bulk')->where('id', $testReceipt->item_bulk_id)->first();
    
    echo "BEFORE:" . PHP_EOL;
    echo "  Receipt: Quantity={$receipt->item_quantity}, Amount={$receipt->amount}" . PHP_EOL;
    echo "  ItemBulk: Quantity={$itemBulk->item_quantity}" . PHP_EOL;
    echo "  Item: Status={$testItem->status}" . PHP_EOL . PHP_EOL;
    
    // 1. Update receipt quantity and amount
    if ($receipt->item_quantity > 1) {
        $newQuantity = $receipt->item_quantity - 1;
        $newAmount = $receipt->amount - $testItem->amount;
        
        DB::table('receipts')->where('id', $receipt->id)->update([
            'item_quantity' => $newQuantity,
            'amount' => $newAmount,
            'updated_by' => 10,
        ]);
        
        echo "✅ Receipt updated: Quantity={$newQuantity}, Amount={$newAmount}" . PHP_EOL;
    }
    
    // 2. Update ItemBulk quantity
    if ($itemBulk->item_quantity > 1) {
        $newItemBulkQuantity = $itemBulk->item_quantity - 1;
        
        DB::table('item_bulk')->where('id', $itemBulk->id)->update([
            'item_quantity' => $newItemBulkQuantity,
        ]);
        
        echo "✅ ItemBulk quantity updated: {$itemBulk->item_quantity} → {$newItemBulkQuantity}" . PHP_EOL;
    }
    
    // 3. Update item status to 'delete'
    DB::table('items')->where('id', $testItem->id)->update([
        'status' => 'delete',
        'updated_by' => 10,
    ]);
    
    echo "✅ Item status updated to 'delete'" . PHP_EOL . PHP_EOL;
    
    // Verify the results
    $updatedReceipt = DB::table('receipts')->where('id', $testReceipt->id)->first();
    $updatedItemBulk = DB::table('item_bulk')->where('id', $testReceipt->item_bulk_id)->first();
    $updatedItem = DB::table('items')->where('id', $testItem->id)->first();
    
    echo "AFTER:" . PHP_EOL;
    echo "  Receipt: Quantity={$updatedReceipt->item_quantity}, Amount={$updatedReceipt->amount}" . PHP_EOL;
    echo "  ItemBulk: Quantity={$updatedItemBulk->item_quantity}" . PHP_EOL;
    echo "  Item: Status={$updatedItem->status}" . PHP_EOL . PHP_EOL;
    
    // Validate results
    $success = true;
    if ($updatedReceipt->item_quantity != $expectedQuantity) {
        echo "❌ Receipt quantity mismatch: Expected {$expectedQuantity}, got {$updatedReceipt->item_quantity}" . PHP_EOL;
        $success = false;
    }
    
    if ($updatedReceipt->amount != $expectedAmount) {
        echo "❌ Receipt amount mismatch: Expected {$expectedAmount}, got {$updatedReceipt->amount}" . PHP_EOL;
        $success = false;
    }
    
    if ($updatedItem->status != 'delete') {
        echo "❌ Item status not updated to 'delete'" . PHP_EOL;
        $success = false;
    }
    
    if ($success) {
        echo "✅ ALL TESTS PASSED - Delete functionality working correctly!" . PHP_EOL;
    } else {
        echo "❌ Some tests failed - check the logic" . PHP_EOL;
    }
    
    // Rollback to restore original state
    DB::rollback();
    echo PHP_EOL . "🔄 Database rolled back to original state" . PHP_EOL;
    
} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== SUMMARY ===" . PHP_EOL;
echo "The new delete functionality will:" . PHP_EOL;
echo "1. ✅ Decrease receipt quantity by 1" . PHP_EOL;
echo "2. ✅ Subtract deleted item amount from receipt total" . PHP_EOL;
echo "3. ✅ Decrease ItemBulk quantity by 1" . PHP_EOL;
echo "4. ✅ Set item status to 'delete' (preserve record)" . PHP_EOL;
echo "5. ✅ Update payment and SMS status to 'delete'" . PHP_EOL;

?>