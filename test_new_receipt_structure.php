<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing new receipt structure with separate columns:\n\n";

// Check the receipt table structure
$receipts = App\Models\Receipt::select(['id', 'item_bulk_id', 'amount', 'item_amount', 'postage', 'total_amount'])
    ->orderBy('id', 'desc')
    ->take(5)
    ->get();

echo "Recent receipts with new column structure:\n";
echo "ID\tBulk\tOld Amount\tItem Amount\tPostage\tTotal Amount\n";
echo "---\t----\t----------\t-----------\t-------\t------------\n";

foreach ($receipts as $receipt) {
    echo "{$receipt->id}\t{$receipt->item_bulk_id}\t{$receipt->amount}\t\t{$receipt->item_amount}\t\t{$receipt->postage}\t{$receipt->total_amount}\n";
}

echo "\nVerifying data consistency:\n";
foreach ($receipts as $receipt) {
    $isConsistent = ($receipt->amount == $receipt->total_amount) ? '✓' : '✗';
    echo "Receipt {$receipt->id}: {$isConsistent} (amount: {$receipt->amount}, total_amount: {$receipt->total_amount})\n";
}

echo "\nTesting with items that have postage:\n";
// Find an item with postage to test
$itemWithPostage = App\Models\Item::whereNotNull('postage')->where('postage', '>', 0)->first();
if ($itemWithPostage) {
    echo "Found item with postage: ID {$itemWithPostage->id}, Amount: {$itemWithPostage->amount}, Postage: {$itemWithPostage->postage}\n";
} else {
    echo "No items with postage found in database.\n";
}