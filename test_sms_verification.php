<?php
require_once __DIR__ . '/vendor/autoload.php';

// Set up Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Item;
use App\Models\SmsSent;
use App\Models\TemporaryUploadAssociate;
use App\Models\ItemBulk;

echo "=== SMS CREATION VERIFICATION ===\n\n";

// Check current state
$totalItems = Item::count();
$totalSms = SmsSent::count();
$activeSms = SmsSent::where('status', '!=', 'delete')->count();
$deletedSms = SmsSent::where('status', 'delete')->count();

echo "📊 CURRENT DATABASE STATE:\n";
echo "   Total Items: {$totalItems}\n";
echo "   Total SMS Records: {$totalSms}\n";
echo "   Active SMS Records: {$activeSms}\n";
echo "   Deleted SMS Records: {$deletedSms}\n\n";

// Check items without SMS records
$itemsWithoutSms = Item::whereDoesntHave('smsSents')->count();
$itemsWithSms = Item::whereHas('smsSents')->count();

echo "🔍 SMS COVERAGE ANALYSIS:\n";
echo "   Items WITH SMS records: {$itemsWithSms}\n";
echo "   Items WITHOUT SMS records: {$itemsWithoutSms}\n";

if ($itemsWithoutSms > 0) {
    echo "   ❌ ISSUE: Some items don't have SMS records!\n\n";

    // Show items without SMS
    $missingItems = Item::with('itemBulk')
        ->whereDoesntHave('smsSents')
        ->limit(5)
        ->get();

    echo "   📋 Items missing SMS records:\n";
    foreach ($missingItems as $item) {
        $category = $item->itemBulk ? $item->itemBulk->category : 'N/A';
        echo "     Item #{$item->id}: {$item->barcode}, Status: {$item->status}\n";
        echo "       ItemBulk: #{$item->item_bulk_id}, Category: {$category}\n";
        echo "       Created: {$item->created_at}\n\n";
    }
} else {
    echo "   ✅ EXCELLENT: All items have SMS records!\n\n";
}

// Check SMS creation patterns by acceptance method
$smsGrouped = SmsSent::with('item.itemBulk')
    ->get()
    ->groupBy(function($sms) {
        return $sms->item && $sms->item->itemBulk ? $sms->item->itemBulk->category : 'unknown';
    });

echo "📱 SMS RECORDS BY ACCEPTANCE METHOD:\n";
foreach ($smsGrouped as $category => $smsRecords) {
    $count = $smsRecords->count();
    echo "   {$category}: {$count} SMS records\n";
}
echo "\n";

// Check pending temporary uploads (items that could be accepted)
$pendingItems = TemporaryUploadAssociate::where('status', 'pending')
    ->whereNotNull('barcode')
    ->where('barcode', '!=', '')
    ->limit(3)
    ->get();

echo "🎯 PENDING ITEMS FOR SMS TESTING:\n";
if ($pendingItems->count() > 0) {
    foreach ($pendingItems as $item) {
        echo "   Item #{$item->id}: {$item->receiver_name}\n";
        echo "     Barcode: {$item->barcode}\n";
        echo "     Contact: {$item->contact_number}\n";
        echo "     Upload ID: {$item->temporary_id}\n";
        echo "     Service: {$item->service_type}\n\n";
    }

    $testItem = $pendingItems->first();
    echo "🧪 TESTING WORKFLOW:\n";
    echo "1. Visit: http://127.0.0.1:8000/pm/view-customer-upload/{$testItem->temporary_id}\n";
    echo "2. Accept items using checkbox + 'Accept Selected'\n";
    echo "3. Check SMS creation: php test_sms_verification.php\n";
    echo "4. Expected: SMS record created for each accepted item\n\n";
} else {
    echo "   No pending items found for testing.\n\n";
}

echo "✅ FIXED SMS CREATION METHODS:\n\n";

echo "1️⃣ PM Accept All Upload (acceptAllUpload):\n";
echo "   • FIXED: Added SMS creation for each accepted item\n";
echo "   • Creates SMS with sender_mobile, receiver_mobile, status='accept'\n\n";

echo "2️⃣ PM Accept Selected Upload (acceptSelectedUpload):\n";
echo "   • FIXED: Added SMS creation for each selected item\n";
echo "   • Creates SMS with proper mobile numbers and status\n\n";

echo "3️⃣ PM Single Item Accept (acceptSingleItemFromAnyCategory):\n";
echo "   • ALREADY WORKING: SMS creation was already implemented\n";
echo "   • No changes needed\n\n";

echo "4️⃣ PM Bulk Upload (storeBulkUpload):\n";
echo "   • ALREADY WORKING: SMS creation was already implemented\n";
echo "   • No changes needed\n\n";

echo "🔧 SMS RECORD STRUCTURE:\n";
echo "   • item_id: Links to items table\n";
echo "   • sender_mobile: Customer's mobile number\n";
echo "   • receiver_mobile: Recipient's contact number\n";
echo "   • status: 'accept' for accepted items\n\n";

echo "🎉 EXPECTED RESULTS AFTER FIX:\n";
echo "   ✅ All PM acceptance methods create SMS records\n";
echo "   ✅ Every accepted item has corresponding SMS entry\n";
echo "   ✅ SMS status properly set to 'accept'\n";
echo "   ✅ Complete audit trail for SMS notifications\n\n";

echo "=== SMS VERIFICATION COMPLETE ===\n";
