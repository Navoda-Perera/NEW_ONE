<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ANALYZING RECEIPT QUANTITY PATTERNS ===" . PHP_EOL . PHP_EOL;

// Check receipts with quantity > 1 to understand the pattern
$receipts = DB::table('receipts')
    ->where('item_quantity', '>', 1)
    ->where('dlt_status', 0)
    ->limit(5)
    ->get(['id', 'item_bulk_id', 'item_quantity', 'amount', 'dlt_status']);

echo "Receipts with quantity > 1:" . PHP_EOL;
foreach ($receipts as $receipt) {
    echo "Receipt ID: {$receipt->id}, Bulk ID: {$receipt->item_bulk_id}, Quantity: {$receipt->item_quantity}, Amount: {$receipt->amount}" . PHP_EOL;

    // Check how many items exist for this bulk
    $itemCount = DB::table('items')->where('item_bulk_id', $receipt->item_bulk_id)->count();
    echo "  -> Items in bulk: {$itemCount}" . PHP_EOL;

    // Check items status
    $itemStatuses = DB::table('items')
        ->where('item_bulk_id', $receipt->item_bulk_id)
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

    echo "  -> Item statuses: ";
    foreach ($itemStatuses as $statusGroup) {
        echo "{$statusGroup->status}({$statusGroup->count}) ";
    }
    echo PHP_EOL . PHP_EOL;
}

echo "=== CHECKING SPECIFIC CASE ===" . PHP_EOL;
// Let's look at Receipt ID 80 from the database screenshot (quantity: 2, amount: 6000)
$receiptId = 80;
$receipt = DB::table('receipts')->where('id', $receiptId)->first();

if ($receipt) {
    echo "Receipt ID {$receiptId}:" . PHP_EOL;
    echo "  ItemBulk ID: {$receipt->item_bulk_id}" . PHP_EOL;
    echo "  Quantity: {$receipt->item_quantity}" . PHP_EOL;
    echo "  Amount: {$receipt->amount}" . PHP_EOL;
    echo "  DLT Status: {$receipt->dlt_status}" . PHP_EOL . PHP_EOL;

    // Check items in this bulk
    $items = DB::table('items')
        ->where('item_bulk_id', $receipt->item_bulk_id)
        ->get(['id', 'barcode', 'status', 'amount']);

    echo "Items in this bulk:" . PHP_EOL;
    $totalItemAmount = 0;
    foreach ($items as $item) {
        echo "  Item ID: {$item->id}, Barcode: {$item->barcode}, Status: {$item->status}, Amount: {$item->amount}" . PHP_EOL;
        if ($item->status !== 'delete') {
            $totalItemAmount += $item->amount;
        }
    }

    echo "  -> Total amount from active items: {$totalItemAmount}" . PHP_EOL;
    echo "  -> Receipt amount: {$receipt->amount}" . PHP_EOL;

    if ($totalItemAmount == $receipt->amount) {
        echo "  ✅ Receipt amount matches active items" . PHP_EOL;
    } else {
        echo "  ❌ Receipt amount doesn't match active items" . PHP_EOL;
    }
}

?>
