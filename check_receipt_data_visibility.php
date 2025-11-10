<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking receipt data visibility and integrity:\n\n";

// Check total count of receipts
$totalReceipts = App\Models\Receipt::count();
echo "Total receipts in database: {$totalReceipts}\n\n";

// Check if data exists across different ID ranges
echo "=== Receipt Data Distribution ===\n";
$ranges = [
    '1-10' => App\Models\Receipt::whereBetween('id', [1, 10])->count(),
    '11-20' => App\Models\Receipt::whereBetween('id', [11, 20])->count(),
    '21-30' => App\Models\Receipt::whereBetween('id', [21, 30])->count(),
    '31-40' => App\Models\Receipt::whereBetween('id', [31, 40])->count(),
    '41-50' => App\Models\Receipt::whereBetween('id', [41, 50])->count(),
    '51-60' => App\Models\Receipt::whereBetween('id', [51, 60])->count(),
    '61-70' => App\Models\Receipt::whereBetween('id', [61, 70])->count(),
    '71-80' => App\Models\Receipt::whereBetween('id', [71, 80])->count(),
    '81-90' => App\Models\Receipt::whereBetween('id', [81, 90])->count(),
    '91-100' => App\Models\Receipt::whereBetween('id', [91, 100])->count(),
];

foreach ($ranges as $range => $count) {
    echo "Receipt IDs {$range}: {$count} records\n";
}

// Check for any NULL or problematic values
echo "\n=== Data Quality Check ===\n";
$nullAmounts = App\Models\Receipt::whereNull('amount')->count();
$nullPostage = App\Models\Receipt::whereNull('postage')->count();
$nullTotals = App\Models\Receipt::whereNull('total_amount')->count();

echo "NULL amount values: {$nullAmounts}\n";
echo "NULL postage values: {$nullPostage}\n";
echo "NULL total_amount values: {$nullTotals}\n";

// Check some sample records from different ranges
echo "\n=== Sample Records from Different Ranges ===\n";
$samples = [
    App\Models\Receipt::whereBetween('id', [1, 10])->first(),
    App\Models\Receipt::whereBetween('id', [40, 50])->first(),
    App\Models\Receipt::whereBetween('id', [80, 90])->first(),
];

foreach ($samples as $receipt) {
    if ($receipt) {
        echo "Receipt {$receipt->id}: Amount={$receipt->amount}, Postage={$receipt->postage}, Total={$receipt->total_amount}, Bulk={$receipt->item_bulk_id}\n";
    }
}

// Check if the issue is with the specific visible receipts
echo "\n=== Recent Receipts (90-92) ===\n";
$recentReceipts = App\Models\Receipt::whereIn('id', [90, 91, 92])->get();
foreach ($recentReceipts as $receipt) {
    echo "Receipt {$receipt->id}: Amount={$receipt->amount}, Postage={$receipt->postage}, Total={$receipt->total_amount}, Created={$receipt->created_at}\n";
}

// Check for any potential database issues
echo "\n=== Database Connection Test ===\n";
try {
    $firstReceipt = App\Models\Receipt::orderBy('id')->first();
    $lastReceipt = App\Models\Receipt::orderBy('id', 'desc')->first();
    
    if ($firstReceipt && $lastReceipt) {
        echo "First receipt: ID {$firstReceipt->id}, Created: {$firstReceipt->created_at}\n";
        echo "Last receipt: ID {$lastReceipt->id}, Created: {$lastReceipt->created_at}\n";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}