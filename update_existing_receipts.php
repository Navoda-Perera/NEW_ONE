<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating existing receipts with separate item_amount and postage columns:\n\n";

$receipts = App\Models\Receipt::all();
$updatedCount = 0;

foreach ($receipts as $receipt) {
    $itemBulk = $receipt->itemBulk;
    if ($itemBulk && $itemBulk->items) {
        $items = $itemBulk->items;

        // Calculate separate amounts
        $itemAmount = $items->sum('amount');
        $postageAmount = $items->sum('postage');
        $totalAmount = $itemAmount + $postageAmount;

        // Update the receipt with new column values
        $receipt->update([
            'item_amount' => $itemAmount,
            'postage' => $postageAmount,
            'total_amount' => $totalAmount,
        ]);

        echo "Updated Receipt ID: {$receipt->id}, Bulk ID: {$itemBulk->id}, Item Amount: {$itemAmount}, Postage: {$postageAmount}, Total: {$totalAmount}\n";
        $updatedCount++;
    } else {
        echo "Skipped Receipt ID: {$receipt->id} (no items found)\n";
    }
}

echo "\nSummary:\n";
echo "Total receipts updated: {$updatedCount}\n";
echo "Migration to new column structure completed!\n";
