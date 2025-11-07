<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\Receipt;

echo "=== EXAMINING EXISTING PM BULK UPLOADS ===\n\n";

// Check ItemBulk 75 and 76 in detail
$bulks = ItemBulk::whereIn('id', [75, 76])->with(['items', 'receipts'])->get();

foreach ($bulks as $bulk) {
    echo "📦 ItemBulk {$bulk->id}:\n";
    echo "   - Sender: '{$bulk->sender_name}'\n";
    echo "   - Service: {$bulk->service_type}\n";
    echo "   - Category: {$bulk->category}\n";
    echo "   - Expected items: {$bulk->item_quantity}\n";
    echo "   - Actual items: " . $bulk->items->count() . "\n";
    echo "   - Created: {$bulk->created_at}\n";
    echo "   - Created by: {$bulk->created_by}\n";
    
    echo "   - Items:\n";
    foreach ($bulk->items as $item) {
        echo "     * Item {$item->id}: {$item->receiver_name}, Amount: {$item->amount}, Barcode: {$item->barcode}\n";
    }
    
    echo "   - Receipts: " . $bulk->receipts->count() . "\n";
    foreach ($bulk->receipts as $receipt) {
        echo "     * Receipt {$receipt->id}: Amount: {$receipt->amount}, Passcode: {$receipt->passcode}\n";
    }
    echo "\n";
}

// Let's also check if there are any errors in the current PMDashboardController code
echo "🔍 TESTING CODE EXECUTION:\n";

try {
    // Test if the controller can be instantiated
    $controller = new \App\Http\Controllers\PM\PMDashboardController();
    echo "✅ Controller instantiated successfully\n";
    
    // Check if the models can be accessed
    $testBulk = new \App\Models\ItemBulk();
    echo "✅ ItemBulk model accessible\n";
    
    $testReceipt = new \App\Models\Receipt();
    echo "✅ Receipt model accessible\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>