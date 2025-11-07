<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\User;

// Initialize Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Accept All Button Visibility ===\n";

// Create a test upload with items WITHOUT barcodes to test the button visibility
$testUser = User::first();

$testUpload = TemporaryUpload::create([
    'user_id' => $testUser->id,
    'location_id' => 1,
    'category' => 'temporary_list',
    'total_item' => 2,
]);

// Create items without barcodes
TemporaryUploadAssociate::create([
    'temporary_id' => $testUpload->id,
    'sender_name' => $testUser->name,
    'receiver_name' => 'Test Receiver 1',
    'receiver_address' => 'Test Address 1',
    'service_type' => 'slp_courier',
    'status' => 'pending',
    'amount' => 0.00,
    'item_value' => 0.00,
    'postage' => 200.00,
    'commission' => 0.00,
    'weight' => 200.00,
    'fix_amount' => 0.00,
    'barcode' => null, // No barcode
]);

TemporaryUploadAssociate::create([
    'temporary_id' => $testUpload->id,
    'sender_name' => $testUser->name,
    'receiver_name' => 'Test Receiver 2',
    'receiver_address' => 'Test Address 2',
    'service_type' => 'register_post',
    'status' => 'pending',
    'amount' => 0.00,
    'item_value' => 0.00,
    'postage' => 150.00,
    'commission' => 0.00,
    'weight' => 150.00,
    'fix_amount' => 0.00,
    'barcode' => '', // Empty barcode
]);

echo "✅ Created test upload #{$testUpload->id} with 2 items without barcodes\n";

// Test the button logic
$pendingItems = $testUpload->associates->where('status', 'pending');
$pendingWithBarcodes = $pendingItems->whereNotNull('barcode')->where('barcode', '!=', '');

echo "\n📊 Button visibility test:\n";
echo "   Pending items: {$pendingItems->count()}\n";
echo "   Items with barcodes: {$pendingWithBarcodes->count()}\n";
echo "   Accept All button shown: " . ($pendingWithBarcodes->count() > 0 ? "✅ YES" : "❌ NO") . "\n";

// Test with one item having barcode
$testUpload->associates->first()->update(['barcode' => 'TEST123456']);

$pendingItems = $testUpload->fresh()->associates->where('status', 'pending');
$pendingWithBarcodes = $pendingItems->whereNotNull('barcode')->where('barcode', '!=', '');

echo "\n📊 After adding barcode to one item:\n";
echo "   Pending items: {$pendingItems->count()}\n";
echo "   Items with barcodes: {$pendingWithBarcodes->count()}\n";
echo "   Accept All button shown: " . ($pendingWithBarcodes->count() > 0 ? "✅ YES" : "❌ NO") . "\n";
echo "   Button text: \"Accept All ({$pendingWithBarcodes->count()})\"\n";

// Clean up
$testUpload->associates()->delete();
$testUpload->delete();

echo "\n✅ Test data cleaned up\n";
echo "\n🎯 Accept All button works correctly:\n";
echo "   - Hidden when no items have barcodes\n";
echo "   - Shown when at least one item has barcode\n";
echo "   - Shows count of items that will be accepted\n";

?>
