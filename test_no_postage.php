<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Receipt;

echo "Testing updated payment summary (no postage)...\n\n";

$receipt = Receipt::with(['itemBulk.items'])->where('id', 7)->first(); // COD receipt

if ($receipt && $receipt->itemBulk && $receipt->itemBulk->items->count() > 0) {
    $totalCodAmount = $receipt->itemBulk->items->sum('amount');

    echo "Receipt ID: {$receipt->id}\n";
    echo "Service Type: {$receipt->itemBulk->service_type}\n";
    echo "Items Count: {$receipt->itemBulk->items->count()}\n\n";

    echo "Updated Payment Summary (no postage):\n";
    echo "✅ COD Amount: LKR " . number_format($totalCodAmount, 2) . "\n";
    echo "✅ No. of Items: {$receipt->itemBulk->items->count()}\n";
    echo "✅ Total Amount: LKR " . number_format($totalCodAmount, 2) . "\n";
    echo "❌ Postage: [REMOVED]\n";

} else {
    echo "Receipt not found.\n";
}

echo "\nTest completed!\n";
