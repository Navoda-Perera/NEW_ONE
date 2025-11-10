<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\SlpPricing;
use App\Models\PostPricing;

// Set up Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Postage Calculation Fix ===\n\n";

// Test SLP pricing
echo "1. Testing SLP Pricing:\n";
$weight = 200;
try {
    $slpPrice = SlpPricing::calculatePrice($weight);
    echo "   Weight: {$weight}g -> SLP Price: LKR {$slpPrice}\n";
} catch (Exception $e) {
    echo "   SLP Price Error: " . $e->getMessage() . "\n";
}

// Test Post pricing
echo "\n2. Testing Post Pricing:\n";
try {
    $codPrice = PostPricing::calculatePrice($weight, 'cod');
    echo "   Weight: {$weight}g -> COD Price: LKR {$codPrice}\n";

    $registerPrice = PostPricing::calculatePrice($weight, 'register');
    echo "   Weight: {$weight}g -> Register Price: LKR {$registerPrice}\n";
} catch (Exception $e) {
    echo "   Post Price Error: " . $e->getMessage() . "\n";
}

// Check recent receipts
echo "\n3. Checking Recent Receipts (last 3):\n";
try {
    $receipts = DB::table('receipts')
        ->join('item_bulks', 'receipts.item_bulk_id', '=', 'item_bulks.id')
        ->select('receipts.*', 'item_bulks.service_type')
        ->orderBy('receipts.created_at', 'desc')
        ->take(3)
        ->get();

    foreach ($receipts as $receipt) {
        echo "   Receipt #{$receipt->id} ({$receipt->service_type}):\n";
        echo "     Amount: LKR {$receipt->amount}\n";
        echo "     Postage: LKR " . ($receipt->postage ?? '0.00') . "\n";
        echo "     Total: LKR " . ($receipt->total_amount ?? '0.00') . "\n\n";
    }
} catch (Exception $e) {
    echo "   Database Error: " . $e->getMessage() . "\n";
}

echo "=== Test Complete ===\n";

?>
