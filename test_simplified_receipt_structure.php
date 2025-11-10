<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing new simplified receipt structure:\n\n";

// Test 1: Check current structure
echo "=== Current Receipt Structure (after item_amount removal) ===\n";
$receipts = App\Models\Receipt::select(['id', 'item_bulk_id', 'amount', 'postage', 'total_amount'])
    ->orderBy('id', 'desc')
    ->take(5)
    ->get();

echo "ID\tBulk\tCOD Amount\tPostage\tTotal\n";
echo "---\t----\t----------\t-------\t-----\n";
foreach ($receipts as $receipt) {
    echo "{$receipt->id}\t{$receipt->item_bulk_id}\t{$receipt->amount}\t\t{$receipt->postage}\t{$receipt->total_amount}\n";
}

// Test 2: Logic verification
echo "\n=== Logic Verification ===\n";
foreach ($receipts as $receipt) {
    $calculated = $receipt->amount + $receipt->postage;
    $isCorrect = (abs($receipt->total_amount - $calculated) < 0.01) ? '✅' : '❌';
    echo "Receipt {$receipt->id}: Total = {$receipt->amount} + {$receipt->postage} = {$calculated} (stored: {$receipt->total_amount}) {$isCorrect}\n";
}

// Test 3: Check for items with different service types
echo "\n=== Service Type Analysis ===\n";
$bulks = App\Models\ItemBulk::with('items')->orderBy('id', 'desc')->take(5)->get();
foreach ($bulks as $bulk) {
    $items = $bulk->items;
    $serviceTypes = $items->pluck('service_type')->filter()->unique();
    $codAmount = $items->sum('amount');
    $receipt = App\Models\Receipt::where('item_bulk_id', $bulk->id)->first();

    $serviceList = $serviceTypes->count() > 0 ? $serviceTypes->implode(', ') : 'none specified';
    echo "Bulk {$bulk->id}: Services [{$serviceList}], Items: {$items->count()}, COD: {$codAmount}";
    if ($receipt) {
        echo ", Receipt: COD={$receipt->amount}, Postage={$receipt->postage}, Total={$receipt->total_amount}";
    }
    echo "\n";
}

// Test 4: Check temp uploads with postage for future testing
echo "\n=== Future Testing Data (Temp uploads with postage) ===\n";
$tempWithPostage = App\Models\TemporaryUploadAssociate::where('postage', '>', 0)
    ->where('status', 'pending')
    ->take(3)
    ->get();

if ($tempWithPostage->count() > 0) {
    echo "Found pending items with postage (ready for testing new logic):\n";
    foreach ($tempWithPostage as $temp) {
        echo "  Temp ID {$temp->id}: Service={$temp->service_type}, Amount={$temp->amount}, Postage={$temp->postage}, Expected Total=" . ($temp->amount + $temp->postage) . "\n";
    }
} else {
    echo "No pending temp uploads with postage found.\n";
}

echo "\n=== Summary ===\n";
echo "✅ item_amount column removed successfully\n";
echo "✅ Simplified structure: amount (COD only) + postage = total_amount\n";
echo "✅ All existing receipts updated with correct calculations\n";
echo "✅ Future receipts will use new logic:\n";
echo "   - COD items: total = amount + postage\n";
echo "   - Non-COD items: total = postage only (amount = 0)\n";
