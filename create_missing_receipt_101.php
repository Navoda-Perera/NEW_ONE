<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Creating missing receipt for ItemBulk 101:\n";

$bulk = App\Models\ItemBulk::find(101);
if ($bulk) {
    // Check if receipt already exists
    $existingReceipt = App\Models\Receipt::where('item_bulk_id', 101)->first();
    if ($existingReceipt) {
        echo "Receipt already exists for ItemBulk 101\n";
    } else {
        $items = $bulk->items;
        echo "Found {$items->count()} items in bulk 101\n";

        // Calculate total amount including postage
        $totalAmount = $items->sum(function($item) {
            return ($item->amount ?: 0) + ($item->postage ?: 0);
        });

        echo "Total amount: {$totalAmount}\n";

        // Find the first item to get location and creation details
        $firstItem = $items->first();
        if ($firstItem) {
            echo "Creating receipt...\n";

            function generatePasscode() {
                return rand(100000, 999999);
            }

            $receipt = App\Models\Receipt::create([
                'item_quantity' => $items->count(),
                'item_bulk_id' => $bulk->id,
                'amount' => $totalAmount,
                'payment_type' => 'cash',
                'passcode' => generatePasscode(),
                'created_by' => $bulk->created_by,
                'location_id' => $bulk->location_id,
                'created_at' => $bulk->created_at,
                'updated_at' => $bulk->updated_at,
            ]);

            echo "Receipt created successfully! ID: {$receipt->id}, Passcode: {$receipt->passcode}\n";
        } else {
            echo "No items found in ItemBulk 101\n";
        }
    }
} else {
    echo "ItemBulk 101 not found\n";
}
