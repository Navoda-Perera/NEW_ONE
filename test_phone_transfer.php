<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\SmsSent;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

echo "=== Testing Phone Number Fix ===\n";

try {
    // Get a customer user for testing
    $customer = User::where('role', 'customer')->first();
    if (!$customer) {
        echo "No customer user found. Please create a customer user first.\n";
        exit;
    }

    echo "Using customer: {$customer->name} (ID: {$customer->id})\n";

    // Simulate creating a single item submission (like the fixed storeSingleItem method)
    DB::beginTransaction();

    $temporaryUpload = TemporaryUpload::create([
        'category' => 'single_item',
        'location_id' => $customer->location_id ?: 1,
        'user_id' => $customer->id,
    ]);

    $testPhone = '0712345678'; // Test phone number

    $temporaryAssociate = TemporaryUploadAssociate::create([
        'temporary_id' => $temporaryUpload->id,
        'sender_name' => $customer->name,
        'receiver_name' => 'Test Receiver',
        'contact_number' => $testPhone, // This should store the phone number
        'receiver_address' => 'Test Address',
        'weight' => 100,
        'amount' => 0,
        'item_value' => 0,
        'service_type' => 'register_post',
        'barcode' => 'TEST' . time(),
        'postage' => 250,
        'commission' => 0,
        'fix_amount' => null,
        'status' => 'pending',
    ]);

    echo "Created test TemporaryUploadAssociate with ID: {$temporaryAssociate->id}\n";
    echo "Contact number stored: '{$temporaryAssociate->contact_number}'\n";

    // Now simulate PM accepting this item (like the PMItemController logic)
    $pmUser = User::where('role', 'pm')->first();
    if (!$pmUser) {
        echo "No PM user found. Simulating acceptance without PM user.\n";
        $pmUserId = 1;
    } else {
        $pmUserId = $pmUser->id;
    }

    // Simulate the SMS creation logic from PMItemController
    $testSms = SmsSent::create([
        'item_id' => 999, // Using dummy item ID for test
        'sender_mobile' => $customer->mobile ?? '',
        'receiver_mobile' => $temporaryAssociate->contact_number ?? '', // This should have the phone number
        'status' => 'accept',
    ]);

    echo "Created test SMS record with ID: {$testSms->id}\n";
    echo "SMS receiver_mobile: '{$testSms->receiver_mobile}'\n";

    DB::rollback(); // Rollback to avoid cluttering the database

    echo "\n=== Test Results ===\n";
    if ($testSms->receiver_mobile === $testPhone) {
        echo "✅ SUCCESS: Phone number correctly transferred from contact_number to receiver_mobile\n";
        echo "   Expected: {$testPhone}\n";
        echo "   Got: {$testSms->receiver_mobile}\n";
    } else {
        echo "❌ FAILED: Phone number not correctly transferred\n";
        echo "   Expected: {$testPhone}\n";
        echo "   Got: '{$testSms->receiver_mobile}'\n";
    }

} catch (Exception $e) {
    DB::rollback();
    echo "Error during test: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
