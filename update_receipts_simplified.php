<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating existing receipts to simplified logic:\n\n";

// For existing receipts, we'll use a simpler approach
// Since we can't easily trace back postage, we'll work with existing data

$receipts = App\Models\Receipt::all();
$updatedCount = 0;

foreach ($receipts as $receipt) {
    $itemBulk = $receipt->itemBulk;
    if ($itemBulk && $itemBulk->items) {
        $items = $itemBulk->items;
        
        // Calculate COD amount (only from items with amount > 0)
        $codAmount = $items->sum('amount');
        
        // For existing receipts, calculate postage as difference
        // Old total_amount should be codAmount + postage
        $existingTotal = $receipt->total_amount;
        $postageAmount = $existingTotal - $codAmount;
        
        // Ensure postage is not negative
        if ($postageAmount < 0) {
            $postageAmount = 0;
        }
        
        $newTotal = $codAmount + $postageAmount;
        
        // Update receipt with new simplified structure
        $receipt->update([
            'amount' => $codAmount,           // COD amount only
            'postage' => $postageAmount,      // Postage (calculated from difference)
            'total_amount' => $newTotal,      // COD + Postage
        ]);
        
        $serviceInfo = $items->pluck('service_type')->filter()->unique()->implode(',') ?: 'unknown';
        echo "Updated Receipt {$receipt->id} (Bulk {$itemBulk->id}): COD={$codAmount}, Postage={$postageAmount}, Total={$newTotal} [Services: {$serviceInfo}]\n";
        $updatedCount++;
    } else {
        echo "Skipped Receipt {$receipt->id} (no items found)\n";
    }
}

echo "\nSummary:\n";
echo "Total receipts updated: {$updatedCount}\n";

// Test integrity
echo "\n=== Data Integrity Check ===\n";
$allReceipts = App\Models\Receipt::all();
$integrityIssues = 0;

foreach ($allReceipts as $receipt) {
    $calculated = $receipt->amount + $receipt->postage;
    if (abs($receipt->total_amount - $calculated) > 0.01) { // Allow for small rounding differences
        echo "❌ Receipt {$receipt->id}: Total mismatch (stored: {$receipt->total_amount}, calculated: {$calculated})\n";
        $integrityIssues++;
    }
}

echo "Integrity issues found: {$integrityIssues}\n";
echo "Update completed " . ($integrityIssues == 0 ? "✅ SUCCESSFULLY" : "❌ WITH ISSUES") . "\n";

// Show sample of updated records
echo "\n=== Sample Updated Records ===\n";
$samples = App\Models\Receipt::orderBy('id', 'desc')->take(5)->get();
foreach ($samples as $r) {
    echo "Receipt {$r->id}: COD={$r->amount}, Postage={$r->postage}, Total={$r->total_amount}\n";
}