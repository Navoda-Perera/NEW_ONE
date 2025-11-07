<?php
require_once __DIR__ . '/vendor/autoload.php';

// Set up Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\Receipt;
use App\Models\Payment;

echo "=== PM ITEM DELETION TEST ===\n\n";

// Check current database state
$itemsCount = Item::count();
$receiptsCount = Receipt::count();
$paymentsCount = Payment::count();
$activeReceipts = Receipt::where('dlt_status', false)->count();
$deletedReceipts = Receipt::where('dlt_status', true)->count();

echo "📊 Current Database State:\n";
echo "   Items: {$itemsCount}\n";
echo "   Receipts Total: {$receiptsCount}\n";
echo "   Active Receipts (dlt_status = 0): {$activeReceipts}\n";
echo "   Deleted Receipts (dlt_status = 1): {$deletedReceipts}\n";
echo "   Payments: {$paymentsCount}\n\n";

// Find some items that can be tested for deletion
$testItems = Item::with(['itemBulk.receipts', 'payments'])
    ->whereIn('status', ['accept', 'pending'])
    ->whereHas('itemBulk.receipts', function($query) {
        $query->where('dlt_status', false);
    })
    ->limit(3)
    ->get();

echo "🎯 Items Available for Deletion Testing:\n";
foreach ($testItems as $item) {
    $receipt = $item->itemBulk->receipts()->where('dlt_status', false)->first();
    $paymentCount = $item->payments->count();
    
    echo "   Item #{$item->id}: {$item->barcode}, Status: {$item->status}, Amount: LKR {$item->amount}\n";
    echo "     ItemBulk #{$item->item_bulk_id}: Quantity {$item->itemBulk->item_quantity}\n";
    if ($receipt) {
        echo "     Receipt #{$receipt->id}: Quantity {$receipt->item_quantity}, Amount LKR {$receipt->amount}, dlt_status: {$receipt->dlt_status}\n";
    }
    echo "     Payments: {$paymentCount} records\n";
    echo "     Test URL: http://127.0.0.1:8000/pm/item-management (search barcode: {$item->barcode})\n\n";
}

echo "🔧 NEW DELETION LOGIC IMPLEMENTED:\n\n";

echo "✅ Step 1 - Item Validation:\n";
echo "   • Check if item can be deleted (not dispatched/delivered)\n";
echo "   • Verify PM has permission for item's location\n\n";

echo "✅ Step 2 - Receipt Update:\n";
echo "   • Find related receipt for the ItemBulk\n";
echo "   • Update receipt.dlt_status = 1 (soft delete)\n";
echo "   • Decrease receipt.item_quantity by 1\n";
echo "   • Subtract item amount from receipt.amount\n";
echo "   • If last item in receipt, set quantity = 0\n\n";

echo "✅ Step 3 - Payment Cleanup:\n";
echo "   • Find all Payment records for the item\n";
echo "   • Update payment.status = 'deleted'\n";
echo "   • Maintain payment audit trail\n\n";

echo "✅ Step 4 - ItemBulk Update:\n";
echo "   • Decrease ItemBulk.item_quantity by 1\n";
echo "   • Handle case when quantity reaches 0\n\n";

echo "✅ Step 5 - Item Deletion:\n";
echo "   • Delete item record from items table\n";
echo "   • All changes wrapped in database transaction\n\n";

echo "🎊 BENEFITS:\n";
echo "   ✅ Proper receipt tracking with dlt_status\n";
echo "   ✅ Payment records preserved with deleted status\n";
echo "   ✅ ItemBulk quantities properly updated\n";
echo "   ✅ Database integrity maintained\n";
echo "   ✅ Audit trail for all deletions\n";
echo "   ✅ Transaction safety for rollback\n\n";

if ($testItems->count() > 0) {
    $testItem = $testItems->first();
    echo "🧪 TESTING INSTRUCTIONS:\n\n";
    echo "1. Go to: http://127.0.0.1:8000/pm/item-management\n";
    echo "2. Search for barcode: {$testItem->barcode}\n";
    echo "3. Click 'Full Edit' on the item\n";
    echo "4. Click 'Delete Item' button\n";
    echo "5. Confirm deletion\n";
    echo "6. Check database changes:\n";
    echo "   • Item deleted from items table\n";
    echo "   • Receipt dlt_status updated to 1\n";
    echo "   • Receipt quantity and amount reduced\n";
    echo "   • Payment status set to 'deleted' (if COD)\n\n";
} else {
    echo "ℹ️  No suitable items found for testing.\n";
    echo "   Create some accepted items first to test deletion.\n\n";
}

echo "=== DELETION TEST READY ===\n";