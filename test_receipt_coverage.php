<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing receipt creation for all service types:\n\n";

// Check all recent ItemBulks to see if they have receipts
$recentBulks = App\Models\ItemBulk::orderBy('id', 'desc')->take(10)->get();

foreach ($recentBulks as $bulk) {
    $receipt = App\Models\Receipt::where('item_bulk_id', $bulk->id)->first();
    $items = $bulk->items;
    
    $serviceTypes = $items->pluck('service_type')->unique()->filter()->values();
    $hasAmount = $items->where('amount', '>', 0)->count() > 0;
    $hasPostage = $items->where('postage', '>', 0)->count() > 0;
    
    echo "Bulk ID: {$bulk->id}, Items: {$items->count()}, Services: [" . $serviceTypes->implode(', ') . "], ";
    echo "Has Amount: " . ($hasAmount ? 'Yes' : 'No') . ", Has Postage: " . ($hasPostage ? 'Yes' : 'No') . ", ";
    echo "Receipt: " . ($receipt ? "Yes (ID: {$receipt->id}, Amount: {$receipt->amount})" : 'No') . "\n";
}

echo "\nSummary:\n";
$totalBulks = $recentBulks->count();
$bulksWithReceipts = $recentBulks->filter(function($bulk) {
    return App\Models\Receipt::where('item_bulk_id', $bulk->id)->exists();
})->count();

echo "Total recent bulks: {$totalBulks}\n";
echo "Bulks with receipts: {$bulksWithReceipts}\n";
echo "Coverage: " . round(($bulksWithReceipts / $totalBulks) * 100, 1) . "%\n";