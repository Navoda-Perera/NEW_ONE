<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Item;

echo "=== COD ITEMS FOR DELETION TESTING ===\n\n";

$codItems = Item::where('amount', '>', 0)
    ->whereIn('status', ['accept', 'pending'])
    ->with(['itemBulk.receipts', 'payments'])
    ->limit(3)
    ->get();

echo "COD Items Available for Testing Payment Deletion:\n";
foreach ($codItems as $item) {
    $receipt = $item->itemBulk->receipts()->where('dlt_status', false)->first();
    $paymentCount = $item->payments->count();
    
    echo "   Item #{$item->id}: {$item->barcode}, Amount: LKR {$item->amount}\n";
    echo "     Payments: {$paymentCount} records\n";
    if ($receipt) {
        echo "     Receipt #{$receipt->id}: dlt_status = {$receipt->dlt_status}\n";
    }
    echo "     Perfect for testing COD payment deletion!\n\n";
}

if ($codItems->count() == 0) {
    echo "No COD items found. Test with regular items first.\n";
}
echo "=== TEST READY ===\n";