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

echo "\nTesting with temporary upload associates that have postage:\n";
// Find a temporary upload associate with postage to test
$tempWithPostage = App\Models\TemporaryUploadAssociate::whereNotNull('postage')->where('postage', '>', 0)->first();
if ($tempWithPostage) {
    echo "Found temp item with postage: ID {$tempWithPostage->id}, Amount: {$tempWithPostage->amount}, Postage: {$tempWithPostage->postage}\n";
} else {
    echo "No temporary upload associates with postage found in database.\n";
}

echo "\nChecking item table structure:\n";
$sampleItem = App\Models\Item::first();
if ($sampleItem) {
    $attributes = $sampleItem->getAttributes();
    echo "Sample item attributes: " . implode(', ', array_keys($attributes)) . "\n";
} else {
    echo "No items found.\n";
}
