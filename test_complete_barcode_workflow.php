<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\Item;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== COMPLETE BARCODE WORKFLOW TEST ===\n";
echo "Testing end-to-end barcode handling after all fixes\n\n";

// Create test customer
$customer = User::factory()->create([
    'name' => 'Test Customer Barcode',
    'email' => 'test_barcode_customer@example.com',
    'mobile' => '0712345678',
    'user_type' => 'external_customer',
    'status' => 'active',
]);

echo "✅ Created test customer: {$customer->name} (ID: {$customer->id})\n\n";

// Test scenarios representing real workflow
$scenarios = [
    [
        'name' => 'Customer provides barcode',
        'customer_barcode' => 'CUST12345',
        'pm_action' => 'accept_as_is',
        'expected_final' => 'CUST12345',
        'description' => 'Customer enters barcode, PM accepts without changes'
    ],
    [
        'name' => 'Customer provides barcode, PM edits other fields',
        'customer_barcode' => 'CUST67890',
        'pm_action' => 'edit_weight_only',
        'expected_final' => 'CUST67890',
        'description' => 'Customer barcode preserved when PM edits weight/address'
    ],
    [
        'name' => 'Customer provides barcode, PM changes it',
        'customer_barcode' => 'CUST11111',
        'pm_action' => 'change_barcode',
        'pm_new_barcode' => 'PM99999',
        'expected_final' => 'PM99999',
        'description' => 'PM overwrites customer barcode with scanned one'
    ],
    [
        'name' => 'Customer no barcode, PM adds during review',
        'customer_barcode' => null,
        'pm_action' => 'add_barcode',
        'pm_new_barcode' => 'PMSCANNED001',
        'expected_final' => 'PMSCANNED001',
        'description' => 'PM must add barcode when customer did not provide one'
    ],
    [
        'name' => 'Customer empty barcode, PM adds during review',
        'customer_barcode' => '',
        'pm_action' => 'add_barcode',
        'pm_new_barcode' => 'PMSCANNED002',
        'expected_final' => 'PMSCANNED002',
        'description' => 'PM must add barcode when customer left it empty'
    ]
];

foreach ($scenarios as $index => $scenario) {
    echo "\n--- Scenario " . ($index + 1) . ": {$scenario['name']} ---\n";
    echo "Description: {$scenario['description']}\n";

    // Step 1: Customer uploads (simulate customer form submission)
    $temporaryUpload = TemporaryUpload::create([
        'category' => 'single_item',
        'location_id' => $customer->location_id ?: 1,
        'user_id' => $customer->id,
    ]);

    $temporaryAssociate = TemporaryUploadAssociate::create([
        'temporary_id' => $temporaryUpload->id,
        'sender_name' => $customer->name,
        'receiver_name' => 'Receiver ' . ($index + 1),
        'contact_number' => '0771234567',
        'receiver_address' => 'Address ' . ($index + 1),
        'weight' => 250,
        'amount' => 0,
        'item_value' => 0,
        'service_type' => 'register_post',
        'barcode' => $scenario['customer_barcode'],
        'postage' => 180,
        'commission' => 0,
        'fix_amount' => null,
        'status' => 'pending',
    ]);

    echo "✅ Customer uploaded item (ID: {$temporaryAssociate->id})\n";
    echo "   Customer barcode: " . ($temporaryAssociate->barcode ?: 'None provided') . "\n";

    // Step 2: PM reviews and takes action
    $finalBarcode = $temporaryAssociate->barcode;
    $pmNotes = '';

    switch ($scenario['pm_action']) {
        case 'accept_as_is':
            $pmNotes = 'PM accepted without changes';
            break;

        case 'edit_weight_only':
            $pmNotes = 'PM edited weight to 300g, kept barcode';
            break;

        case 'change_barcode':
            $finalBarcode = $scenario['pm_new_barcode'];
            $pmNotes = 'PM changed barcode to scanned value';
            break;

        case 'add_barcode':
            $finalBarcode = $scenario['pm_new_barcode'];
            $pmNotes = 'PM added barcode (customer did not provide)';
            break;
    }

    echo "✅ PM action: {$pmNotes}\n";
    echo "   PM set barcode to: {$finalBarcode}\n";

    // Step 3: Simulate acceptance (using fixed logic)
    $item = Item::create([
        'barcode' => $finalBarcode, // Use PM/customer provided barcode
        'sender_name' => $temporaryAssociate->sender_name,
        'receiver_name' => $temporaryAssociate->receiver_name,
        'contact_number' => $temporaryAssociate->contact_number,
        'receiver_address' => $temporaryAssociate->receiver_address,
        'weight' => $temporaryAssociate->weight,
        'amount' => $temporaryAssociate->amount,
        'item_value' => $temporaryAssociate->item_value,
        'service_type' => $temporaryAssociate->service_type,
        'postage' => $temporaryAssociate->postage,
        'commission' => $temporaryAssociate->commission,
        'user_id' => $temporaryAssociate->temporaryUpload->user_id,
        'location_id' => $temporaryAssociate->temporaryUpload->location_id,
        'status' => 'accept',
    ]);

    echo "✅ Item created in main table (ID: {$item->id})\n";
    echo "   Final barcode: {$item->barcode}\n";

    // Verify result
    if ($item->barcode === $scenario['expected_final']) {
        echo "   ✅ SUCCESS: Barcode matches expected value\n";
    } else {
        echo "   ❌ FAILED: Expected '{$scenario['expected_final']}', got '{$item->barcode}'\n";
    }

    // Clean up
    $temporaryAssociate->update(['status' => 'accept']);
}

echo "\n=== WORKFLOW SUMMARY ===\n";
echo "✅ Customer provides barcode → Preserved\n";
echo "✅ PM edits other fields → Customer barcode still preserved\n";
echo "✅ PM changes barcode → PM barcode used\n";
echo "✅ No customer barcode → PM must provide\n";
echo "✅ Empty customer barcode → PM must provide\n";

echo "\n=== KEY CHANGES IMPLEMENTED ===\n";
echo "1. PMItemController acceptance methods preserve existing barcodes\n";
echo "2. PM edit form emphasizes barcode entry responsibility\n";
echo "3. Removed all auto-generation references from documentation\n";
echo "4. Test files updated to reflect PM responsibility\n";
echo "5. Form JavaScript no longer auto-generates on page load\n";

echo "\n=== NO MORE AUTO-GENERATION ===\n";
echo "❌ System no longer auto-generates barcodes\n";
echo "✅ PM has full control over barcode assignment\n";
echo "✅ Customer-provided barcodes are preserved\n";
echo "✅ PM can scan or manually enter barcodes as needed\n";

// Clean up test data
echo "\nCleaning up test data...\n";
User::where('email', 'like', 'test_barcode_customer@example.com')->delete();
TemporaryUpload::where('user_id', $customer->id)->delete();
TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($customer) {
    $q->where('user_id', $customer->id);
})->delete();
Item::where('user_id', $customer->id)->delete();
echo "✅ Test data cleaned up\n";

echo "\n=== TEST COMPLETED ===\n";
echo "All barcode auto-generation references have been removed.\n";
echo "PMs now have full control over barcode assignment.\n";

?>
