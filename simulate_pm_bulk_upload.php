<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

echo "=== SIMULATING PM BULK UPLOAD PROCESS ===\n\n";

// Test data similar to the CSV
$testData = [
    [
        'receiver_name' => 'John Doe',
        'receiver_address' => '123 Main Street Colombo 07',
        'item_value' => '2500.00',
        'weight' => '500',
        'postage' => '',
        'barcode' => 'JD001',
        'contact_number' => '0771234567',
        'sender_name' => 'Test Company',
        'notes' => 'Fragile item'
    ],
    [
        'receiver_name' => 'Jane Smith',
        'receiver_address' => '456 Kandy Road Kandy',
        'item_value' => '1500.00',
        'weight' => '300',
        'postage' => '',
        'barcode' => 'JS002',
        'contact_number' => '0772345678',
        'sender_name' => 'Test Company',
        'notes' => 'Express delivery'
    ]
];

// Simulate the PM bulk upload logic
try {
    DB::beginTransaction();

    // Simulate user and location (replace with actual values from your system)
    $userId = 10; // PM user ID from the database
    $locationId = 9; // Location ID from the database
    $serviceType = 'cod';

    echo "📤 SIMULATING BULK UPLOAD:\n";
    echo "   User ID: {$userId}\n";
    echo "   Location ID: {$locationId}\n";
    echo "   Service Type: {$serviceType}\n";
    echo "   Items to process: " . count($testData) . "\n\n";

    // Validate test data
    $validItems = [];
    foreach ($testData as $index => $item) {
        if (empty($item['receiver_name']) || empty($item['receiver_address']) ||
            empty($item['weight']) || empty($item['contact_number'])) {
            echo "❌ Row " . ($index + 1) . " has missing required fields\n";
            continue;
        }
        $validItems[] = [
            'data' => $item,
            'service_type' => $serviceType,
            'row_number' => $index + 1
        ];
    }

    echo "✅ Valid items: " . count($validItems) . "\n\n";

    if (empty($validItems)) {
        throw new Exception("No valid items to process");
    }

    // Create single ItemBulk record
    echo "📦 CREATING ITEMBULK:\n";
    $itemBulk = ItemBulk::create([
        'sender_name' => 'Test PM User', // Would be actual PM name
        'service_type' => $serviceType,
        'location_id' => $locationId,
        'created_by' => $userId,
        'category' => 'bulk_list',
        'item_quantity' => count($validItems),
    ]);

    echo "   Created ItemBulk ID: {$itemBulk->id}\n";
    echo "   Category: {$itemBulk->category}\n";
    echo "   Expected items: {$itemBulk->item_quantity}\n\n";

    $totalAmount = 0;
    $itemsCreated = 0;

    // Create items
    echo "📋 CREATING ITEMS:\n";
    foreach ($validItems as $validItem) {
        $item = $validItem['data'];

        $weight = floatval($item['weight']);
        $postage = ceil($weight / 250) * 290; // COD pricing
        $finalPostage = !empty($item['postage']) ? floatval($item['postage']) : $postage;

        $barcode = !empty($item['barcode']) ? $item['barcode'] :
                  'COD' . time() . str_pad($itemsCreated + 1, 4, '0', STR_PAD_LEFT);

        $newItem = Item::create([
            'item_bulk_id' => $itemBulk->id,
            'barcode' => $barcode,
            'receiver_name' => trim($item['receiver_name']),
            'receiver_address' => trim($item['receiver_address']),
            'contact_number' => trim($item['contact_number']),
            'status' => 'accept',
            'weight' => $weight,
            'amount' => floatval($item['item_value']),
            'postage' => $finalPostage,
            'service_type' => $serviceType,
            'origin_post_office_id' => $locationId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        echo "   Item {$newItem->id}: {$newItem->receiver_name}, Amount: {$newItem->amount}\n";

        $totalAmount += floatval($item['item_value']);
        $itemsCreated++;
    }

    echo "\n💰 CREATING RECEIPT:\n";
    $receipt = Receipt::create([
        'item_quantity' => $itemsCreated,
        'item_bulk_id' => $itemBulk->id,
        'amount' => $totalAmount,
        'payment_type' => 'cash',
        'created_by' => $userId,
        'location_id' => $locationId,
        'passcode' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT)
    ]);

    echo "   Receipt ID: {$receipt->id}\n";
    echo "   Total Amount: {$receipt->amount}\n";
    echo "   Passcode: {$receipt->passcode}\n\n";

    DB::commit();

    echo "✅ SIMULATION COMPLETED SUCCESSFULLY!\n\n";

    // Verify the results
    echo "🔍 VERIFICATION:\n";
    $createdBulk = ItemBulk::with(['items', 'receipts'])->find($itemBulk->id);
    echo "   ItemBulk {$createdBulk->id}:\n";
    echo "     - Items expected: {$createdBulk->item_quantity}\n";
    echo "     - Items actual: " . $createdBulk->items->count() . "\n";
    echo "     - Receipts: " . $createdBulk->receipts->count() . "\n";
    echo "     - Total amount in receipt: " . $createdBulk->receipts->first()->amount . "\n";

    foreach ($createdBulk->items as $item) {
        echo "     - Item {$item->id}: Amount={$item->amount}, Barcode={$item->barcode}\n";
    }

} catch (Exception $e) {
    DB::rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>
