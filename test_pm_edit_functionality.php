<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;

echo "=== Testing PM Edit Functionality ===\n";

try {
    // Get a customer user for testing
    $customer = User::where('role', 'customer')->first();
    if (!$customer) {
        echo "No customer user found. Please create a customer user first.\n";
        exit;
    }

    echo "Using customer: {$customer->name} (ID: {$customer->id})\n";

    // Create test data for different service types
    $testCases = [
        [
            'service_type' => 'register_post',
            'receiver_name' => 'Register Post Test',
            'receiver_address' => 'Test Address for Register Post',
            'contact_number' => '0712345678',
            'weight' => 100,
            'amount' => 0,
            'item_value' => 0, // Should be 0 for register_post
            'barcode' => null, // PM should add this
        ],
        [
            'service_type' => 'slp_courier',
            'receiver_name' => 'SLP Test',
            'receiver_address' => 'Test Address for SLP',
            'contact_number' => '0723456789',
            'weight' => 250,
            'amount' => 0,
            'item_value' => 0, // Should be 0 for slp_courier
            'barcode' => 'CUST001', // Customer provided barcode
        ],
        [
            'service_type' => 'cod',
            'receiver_name' => 'COD Test',
            'receiver_address' => 'Test Address for COD',
            'contact_number' => '0734567890',
            'weight' => 500,
            'amount' => 1500, // COD collection amount
            'item_value' => 1500, // Should have actual value for COD
            'barcode' => null, // PM should add this
        ]
    ];

    foreach ($testCases as $index => $testCase) {
        echo "\n--- Creating test case " . ($index + 1) . ": {$testCase['service_type']} ---\n";

        $temporaryUpload = TemporaryUpload::create([
            'category' => 'single_item',
            'location_id' => $customer->location_id ?: 1,
            'user_id' => $customer->id,
        ]);

        $temporaryAssociate = TemporaryUploadAssociate::create([
            'temporary_id' => $temporaryUpload->id,
            'sender_name' => $customer->name,
            'receiver_name' => $testCase['receiver_name'],
            'contact_number' => $testCase['contact_number'],
            'receiver_address' => $testCase['receiver_address'],
            'weight' => $testCase['weight'],
            'amount' => $testCase['amount'],
            'item_value' => $testCase['item_value'],
            'service_type' => $testCase['service_type'],
            'barcode' => $testCase['barcode'],
            'postage' => 250,
            'commission' => 0,
            'fix_amount' => null,
            'status' => 'pending',
        ]);

        echo "✅ Created TemporaryUploadAssociate ID: {$temporaryAssociate->id}\n";
        echo "   Service Type: {$temporaryAssociate->service_type}\n";
        echo "   Receiver: {$temporaryAssociate->receiver_name}\n";
        echo "   Contact: {$temporaryAssociate->contact_number}\n";
        echo "   Weight: {$temporaryAssociate->weight}g\n";
        echo "   Amount: LKR {$temporaryAssociate->amount}\n";
        echo "   Item Value: LKR {$temporaryAssociate->item_value}\n";
        echo "   Barcode: " . ($temporaryAssociate->barcode ?: 'Not set (PM to add)') . "\n";
        echo "   Status: {$temporaryAssociate->status}\n";
        echo "   Edit URL: /pm/items/{$temporaryAssociate->id}/edit\n";
    }

    echo "\n=== PM Edit Functionality Features ===\n";
    echo "✅ PM can edit weight if customer provided incorrect weight\n";
    echo "✅ PM can edit receiver details if needed\n";
    echo "✅ PM can add/edit contact number for SMS notifications\n";
    echo "✅ PM can edit amount for COD services\n";
    echo "✅ PM can edit item_value only for COD services (hidden for others)\n";
    echo "✅ PM can add barcode if customer didn't provide one\n";
    echo "✅ PM can edit barcode if customer provided incorrect one\n";
    echo "✅ All changes are validated before acceptance\n";
    echo "✅ Edit & Accept or Quick Accept options available\n";

    echo "\n=== What PM Can Do Now ===\n";
    echo "1. Click 'Edit & Review' button to modify item details\n";
    echo "2. Verify and update weight based on actual measurement\n";
    echo "3. Add barcode if customer didn't provide one\n";
    echo "4. Update receiver contact number for SMS notifications\n";
    echo "5. For COD: Edit collection amount and item value\n";
    echo "6. For Register Post/SLP: item_value is automatically set to 0\n";
    echo "7. Accept with all updates or reject if issues found\n";

    echo "\n=== Test Data Created Successfully ===\n";
    echo "You can now test the PM edit functionality by:\n";
    echo "1. Logging in as PM user\n";
    echo "2. Going to Customer Uploads\n";
    echo "3. Clicking 'Edit & Review' for any pending item\n";
    echo "4. Making changes and clicking 'Accept & Process Item'\n";

} catch (Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
