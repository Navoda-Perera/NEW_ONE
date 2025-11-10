<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating existing receipts to new logic (removing item_amount, fixing calculations):\n\n";

$receipts = App\Models\Receipt::all();
$updatedCount = 0;

foreach ($receipts as $receipt) {
    $itemBulk = $receipt->itemBulk;
    if ($itemBulk && $itemBulk->items) {
        $items = $itemBulk->items;

        // Get service types to understand receipt logic
        $serviceTypes = $items->pluck('service_type')->filter()->unique();
        $hasCOD = $items->where('amount', '>', 0)->count() > 0;

        // Calculate amounts based on new logic
        $codAmount = $items->sum('amount'); // COD amount from items

        // For postage, try to get from related temp uploads
        $postageAmount = 0;

        // Try to find related temporary upload associates
        $tempUploads = App\Models\TemporaryUploadAssociate::where('status', 'accept')
            ->whereHas('item', function($query) use ($itemBulk) {
                $query->where('item_bulk_id', $itemBulk->id);
            })->get();

        if ($tempUploads->count() > 0) {
            $postageAmount = $tempUploads->sum('postage');
            echo "Found temp uploads for Bulk {$itemBulk->id}: Postage {$postageAmount}\n";
        } else {
            // Fallback: assume postage is 0 for existing records
            $postageAmount = 0;
        }

        $totalAmount = $codAmount + $postageAmount;

        // Update receipt with new logic
        $receipt->update([
            'amount' => $codAmount,      // COD amount only
            'postage' => $postageAmount, // Postage fees
            'total_amount' => $totalAmount, // Combined total
        ]);

        echo "Updated Receipt {$receipt->id}: COD={$codAmount}, Postage={$postageAmount}, Total={$totalAmount}\n";
        $updatedCount++;
    } else {
        echo "Skipped Receipt {$receipt->id} (no items found)\n";
    }
}

echo "\nSummary:\n";
echo "Total receipts updated: {$updatedCount}\n";
echo "New logic applied successfully!\n";

// Test the new structure
echo "\n=== Testing New Structure ===\n";
$recent = App\Models\Receipt::orderBy('id', 'desc')->take(3)->get();
foreach ($recent as $r) {
    $calculated = $r->amount + $r->postage;
    $integrity = ($r->total_amount == $calculated) ? '✅' : '❌';
    echo "Receipt {$r->id}: COD={$r->amount}, Postage={$r->postage}, Total={$r->total_amount} {$integrity}\n";
}
