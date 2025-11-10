<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing new receipt structure with postage separation:\n\n";

// Test 1: Check recent receipts with new structure
echo "=== Recent Receipts Structure ===\n";
$receipts = App\Models\Receipt::select(['id', 'item_bulk_id', 'amount', 'item_amount', 'postage', 'total_amount'])
    ->orderBy('id', 'desc')
    ->take(5)
    ->get();

foreach ($receipts as $receipt) {
    echo "Receipt {$receipt->id}: Bulk {$receipt->item_bulk_id} | Amount: {$receipt->amount} | Item: {$receipt->item_amount} | Postage: {$receipt->postage} | Total: {$receipt->total_amount}\n";
}

// Test 2: Verify data integrity
echo "\n=== Data Integrity Check ===\n";
$integrityIssues = 0;
foreach ($receipts as $receipt) {
    $calculatedTotal = $receipt->item_amount + $receipt->postage;
    if ($receipt->total_amount != $calculatedTotal) {
        echo "❌ Receipt {$receipt->id}: Total mismatch (stored: {$receipt->total_amount}, calculated: {$calculatedTotal})\n";
        $integrityIssues++;
    } else {
        echo "✅ Receipt {$receipt->id}: Data integrity OK\n";
    }
}

// Test 3: Find temp uploads with postage for testing
echo "\n=== Testing with Postage Data ===\n";
$tempWithPostage = App\Models\TemporaryUploadAssociate::where('postage', '>', 0)->take(3)->get();
foreach ($tempWithPostage as $temp) {
    echo "Temp Item {$temp->id}: Amount: {$temp->amount}, Postage: {$temp->postage}, Total: " . ($temp->amount + $temp->postage) . "\n";
}

// Test 4: Check if any receipts have postage > 0
echo "\n=== Receipts with Postage ===\n";
$receiptsWithPostage = App\Models\Receipt::where('postage', '>', 0)->get();
if ($receiptsWithPostage->count() > 0) {
    echo "Found {$receiptsWithPostage->count()} receipts with postage:\n";
    foreach ($receiptsWithPostage as $receipt) {
        echo "  Receipt {$receipt->id}: Postage {$receipt->postage}\n";
    }
} else {
    echo "No receipts found with postage > 0 (this is expected if all items were accepted before the postage fix)\n";
}

echo "\n=== Summary ===\n";
echo "Total receipts checked: " . $receipts->count() . "\n";
echo "Data integrity issues: {$integrityIssues}\n";
echo "Structure enhancement: " . ($integrityIssues == 0 ? "✅ SUCCESSFUL" : "❌ NEEDS ATTENTION") . "\n";