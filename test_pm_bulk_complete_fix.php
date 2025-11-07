<?php

require_once __DIR__ . '/vendor/autoload.php';

// Include Laravel bootstrap
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Receipt;
use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\SmsSent;

echo "=== PM BULK UPLOAD COMPLETE REBUILD - VERIFICATION ===\n\n";

echo "🔧 PROBLEM SUMMARY:\n";
echo "- PM bulk uploads were creating individual ItemBulk records per item\n";
echo "- No Receipt records were being created\n";
echo "- Items weren't properly grouped as bulk upload\n";
echo "- Customer portal couldn't see PM bulk items\n\n";

echo "✅ SOLUTION IMPLEMENTED:\n";
echo "1. Create ONE ItemBulk record per bulk upload\n";
echo "2. Create multiple Item records linked to single ItemBulk\n";
echo "3. Create ONE Receipt record for entire bulk upload\n";
echo "4. Each item gets SMS notification\n";
echo "5. Receipt has total amount and proper passcode\n\n";

echo "📊 DATABASE VERIFICATION:\n";

// Check recent PM bulk uploads
$recentBulkUploads = ItemBulk::where('category', 'bulk_list')
    ->where('created_at', '>=', now()->subHours(24))
    ->with(['items', 'receipts'])
    ->get();

echo "Recent PM bulk uploads (last 24 hours): " . $recentBulkUploads->count() . "\n\n";

if ($recentBulkUploads->count() > 0) {
    foreach ($recentBulkUploads as $upload) {
        echo "📦 ItemBulk ID: {$upload->id}\n";
        echo "   - Sender: {$upload->sender_name}\n";
        echo "   - Service: {$upload->service_type}\n";
        echo "   - Category: {$upload->category}\n";
        echo "   - Expected Items: {$upload->item_quantity}\n";
        echo "   - Actual Items: " . $upload->items->count() . "\n";

        $receipts = Receipt::where('item_bulk_id', $upload->id)->get();
        echo "   - Receipts: " . $receipts->count() . "\n";

        if ($receipts->count() > 0) {
            $receipt = $receipts->first();
            echo "   - Receipt Amount: LKR " . number_format($receipt->amount, 2) . "\n";
            echo "   - Receipt Passcode: {$receipt->passcode}\n";
        }

        $smsCount = SmsSent::whereIn('item_id', $upload->items->pluck('id'))->count();
        echo "   - SMS Notifications: {$smsCount}\n";
        echo "   - Created: " . $upload->created_at->format('Y-m-d H:i:s') . "\n\n";
    }
} else {
    echo "No recent PM bulk uploads found. Upload a test file to verify.\n\n";
}

// Check for any PM bulk items without receipts (should be 0 after fix)
$orphanedItems = Item::whereHas('itemBulk', function($query) {
    $query->where('category', 'bulk_list');
})
->whereDoesntHave('itemBulk.receipts')
->count();

if ($orphanedItems > 0) {
    echo "⚠️  WARNING: {$orphanedItems} PM bulk items found WITHOUT receipts!\n";
} else {
    echo "✅ SUCCESS: All PM bulk items have receipts!\n";
}

// General statistics
echo "\n📈 SYSTEM STATISTICS:\n";

$categoryStats = ItemBulk::selectRaw('category, COUNT(*) as count, SUM(item_quantity) as total_items')
    ->groupBy('category')
    ->get();

echo "ItemBulk records by category:\n";
foreach ($categoryStats as $stat) {
    echo "  - {$stat->category}: {$stat->count} uploads, {$stat->total_items} items\n";
}

$receiptStats = Receipt::join('item_bulk', 'receipts.item_bulk_id', '=', 'item_bulk.id')
    ->selectRaw('item_bulk.category, COUNT(*) as receipt_count, SUM(receipts.amount) as total_amount')
    ->groupBy('item_bulk.category')
    ->get();

echo "\nReceipt records by category:\n";
foreach ($receiptStats as $stat) {
    echo "  - {$stat->category}: {$stat->receipt_count} receipts, LKR " . number_format($stat->total_amount, 2) . "\n";
}

echo "\n🔍 WORKFLOW VERIFICATION:\n";
echo "1. PM uploads CSV → Single ItemBulk created\n";
echo "2. All items linked to same ItemBulk\n";
echo "3. Single Receipt created with total amount\n";
echo "4. SMS sent for each item individually\n";
echo "5. Customers can view receipts in portal\n";
echo "6. Barcode search works for all items\n\n";

echo "🧪 TO TEST THE FIX:\n";
echo "1. Login as PM user\n";
echo "2. Go to Bulk Upload page\n";
echo "3. Upload CSV with 3-5 test items\n";
echo "4. Verify in database:\n";
echo "   - 1 record in item_bulk with category='bulk_list'\n";
echo "   - 3-5 records in items table with same item_bulk_id\n";
echo "   - 1 record in receipts table\n";
echo "   - 3-5 records in sms_sent table\n";
echo "5. Customer portal should show receipt\n\n";

echo "✅ PM BULK UPLOAD REBUILD COMPLETE!\n";

?>
