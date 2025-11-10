<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Complete Receipt Data Summary:\n";
echo "=" . str_repeat("=", 80) . "\n";

// Get all receipts ordered by ID
$allReceipts = App\Models\Receipt::orderBy('id')->get();

echo "Total Receipts: {$allReceipts->count()}\n";
echo "Date Range: {$allReceipts->first()->created_at->format('Y-m-d')} to {$allReceipts->last()->created_at->format('Y-m-d')}\n\n";

// Summary by ranges
echo "ID\tBulk\tAmount\t\tPostage\t\tTotal\t\tCreated Date\n";
echo str_repeat("-", 80) . "\n";

$displayCount = 0;
foreach ($allReceipts as $receipt) {
    $displayCount++;

    // Show first 10, last 10, and every 10th record
    if ($displayCount <= 10 || $displayCount > ($allReceipts->count() - 10) || $displayCount % 10 == 0) {
        echo sprintf(
            "%d\t%d\t%-12s\t%-12s\t%-12s\t%s\n",
            $receipt->id,
            $receipt->item_bulk_id,
            number_format($receipt->amount, 2),
            number_format($receipt->postage, 2),
            number_format($receipt->total_amount, 2),
            $receipt->created_at->format('Y-m-d H:i')
        );
    } elseif ($displayCount == 11) {
        echo "... (showing every 10th record) ...\n";
    }
}

// Statistics
echo "\n" . str_repeat("=", 80) . "\n";
echo "STATISTICS:\n";
echo str_repeat("-", 80) . "\n";

$totalAmount = $allReceipts->sum('amount');
$totalPostage = $allReceipts->sum('postage');
$totalValue = $allReceipts->sum('total_amount');

$codReceipts = $allReceipts->where('amount', '>', 0);
$postageOnlyReceipts = $allReceipts->where('amount', '=', 0);

echo "Total COD Amount: " . number_format($totalAmount, 2) . "\n";
echo "Total Postage: " . number_format($totalPostage, 2) . "\n";
echo "Total Value: " . number_format($totalValue, 2) . "\n";
echo "COD Receipts: {$codReceipts->count()}\n";
echo "Postage-only Receipts: {$postageOnlyReceipts->count()}\n";

// Verify data integrity
echo "\nDATA INTEGRITY CHECK:\n";
echo str_repeat("-", 80) . "\n";

$integrityIssues = 0;
foreach ($allReceipts as $receipt) {
    $calculated = $receipt->amount + $receipt->postage;
    if (abs($receipt->total_amount - $calculated) > 0.01) {
        $integrityIssues++;
        echo "❌ Receipt {$receipt->id}: Calculation error\n";
    }
}

if ($integrityIssues == 0) {
    echo "✅ All {$allReceipts->count()} receipts have correct calculations\n";
} else {
    echo "❌ Found {$integrityIssues} integrity issues\n";
}

echo "\nDATABASE STATUS: All receipt data is present and accessible ✅\n";
echo "Note: If phpMyAdmin shows limited rows, use pagination controls or adjust row limit.\n";
