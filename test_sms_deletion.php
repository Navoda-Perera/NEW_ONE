<?php
require_once __DIR__ . '/vendor/autoload.php';

// Set up Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Item;
use App\Models\SmsSent;
use App\Models\Payment;

echo "=== PM DELETION WITH SMS HANDLING ===\n\n";

// Check current SMS records
$allSms = SmsSent::with('item')->get();
$activeSms = SmsSent::where('status', '!=', 'delete')->count();
$deletedSms = SmsSent::where('status', 'delete')->count();

echo "📱 CURRENT SMS RECORDS:\n";
echo "   Total SMS records: {$allSms->count()}\n";
echo "   Active SMS records: {$activeSms}\n";
echo "   Deleted SMS records: {$deletedSms}\n\n";

if ($allSms->count() > 0) {
    echo "📋 SMS RECORDS BREAKDOWN:\n";
    foreach ($allSms as $sms) {
        $item = $sms->item;
        $itemInfo = $item ? "Item #{$item->id} ({$item->barcode})" : "Item deleted";
        echo "   SMS #{$sms->id}: {$itemInfo}\n";
        echo "     From: {$sms->sender_mobile}, To: {$sms->receiver_mobile}\n";
        echo "     Status: {$sms->status}, Created: {$sms->created_at}\n\n";
    }
} else {
    echo "ℹ️  No SMS records found.\n\n";
}

// Find items with SMS records for testing
$itemsWithSms = Item::with(['smsSents', 'payments', 'itemBulk.receipts'])
    ->whereHas('smsSents', function($query) {
        $query->where('status', '!=', 'delete');
    })
    ->whereIn('status', ['accept', 'pending'])
    ->limit(3)
    ->get();

echo "🎯 ITEMS WITH SMS RECORDS FOR DELETION TESTING:\n";
if ($itemsWithSms->count() > 0) {
    foreach ($itemsWithSms as $item) {
        $smsCount = $item->smsSents->where('status', '!=', 'delete')->count();
        $paymentCount = $item->payments->count();
        $isCOD = $item->amount > 0;
        
        echo "   Item #{$item->id}: {$item->barcode}\n";
        echo "     Type: " . ($isCOD ? "COD (LKR {$item->amount})" : "Regular") . "\n";
        echo "     SMS Records: {$smsCount} active\n";
        echo "     Payment Records: {$paymentCount}\n";
        echo "     Perfect for testing comprehensive deletion!\n\n";
    }
} else {
    echo "   No items with active SMS records found.\n\n";
}

echo "🔧 ENHANCED DELETION PROCESS:\n\n";

echo "✅ Step 1 - Item Validation:\n";
echo "   • Check deletion permissions\n";
echo "   • Verify item status (not dispatched/delivered)\n";
echo "   • Load item with SMS, payments, and receipts\n\n";

echo "✅ Step 2 - Receipt Update:\n";
echo "   • Set receipt.dlt_status = 1\n";
echo "   • Decrease receipt.item_quantity\n";
echo "   • Subtract item amount from receipt.amount\n\n";

echo "✅ Step 3 - Payment Update:\n";
echo "   • Set payment.status = 'delete' (for COD items)\n";
echo "   • Preserve payment audit trail\n\n";

echo "✅ Step 4 - SMS Update (NEW!):\n";
echo "   • Set sms_sents.status = 'delete'\n";
echo "   • Update timestamps\n";
echo "   • Preserve SMS history for audit\n\n";

echo "✅ Step 5 - ItemBulk & Item Cleanup:\n";
echo "   • Decrease ItemBulk.item_quantity\n";
echo "   • Delete item from items table\n";
echo "   • Transaction safety\n\n";

echo "📋 SMS STATUS ENUM VALUES:\n";
echo "   • 'accept' - SMS accepted/sent\n";
echo "   • 'addbeat' - Added to delivery beat\n";
echo "   • 'delivered' - Item delivered\n";
echo "   • 'undelivered' - Delivery failed\n";
echo "   • 'return' - Item returned\n";
echo "   • 'delete' - SMS/Item deleted\n\n";

echo "🧪 TESTING INSTRUCTIONS:\n";
echo "1. Visit: http://127.0.0.1:8000/pm/item-management\n";
echo "2. Search for an item with SMS records\n";
echo "3. Click 'Full Edit' → 'Delete Item'\n";
echo "4. Verify deletion success\n";
echo "5. Run: php verify_deletion.php\n";
echo "6. Check SMS records marked as 'delete'\n\n";

echo "🎉 EXPECTED RESULTS:\n";
echo "   ✅ Receipt dlt_status = 1\n";
echo "   ✅ Payment status = 'delete' (if COD)\n";
echo "   ✅ SMS status = 'delete' (all SMS records)\n";
echo "   ✅ Item removed from items table\n";
echo "   ✅ Complete audit trail maintained\n\n";

echo "=== COMPREHENSIVE DELETION READY ===\n";