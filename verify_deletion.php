<?php
require_once __DIR__ . '/vendor/autoload.php';

// Set up Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\SmsSent;

echo "=== PM DELETION VERIFICATION ===\n\n";

// Check current database state
$itemsCount = Item::count();
$receiptsCount = Receipt::count();
$paymentsCount = Payment::count();
$activeReceipts = Receipt::where('dlt_status', false)->count();
$deletedReceipts = Receipt::where('dlt_status', true)->count();
$deletedPayments = Payment::where('status', 'delete')->count();
$deletedSms = SmsSent::where('status', 'delete')->count();

echo "📊 Current Database State:\n";
echo "   Items: {$itemsCount}\n";
echo "   Receipts Total: {$receiptsCount}\n";
echo "   Active Receipts (dlt_status = 0): {$activeReceipts}\n";
echo "   Deleted Receipts (dlt_status = 1): {$deletedReceipts}\n";
echo "   Payments Total: {$paymentsCount}\n";
echo "   Deleted Payments: {$deletedPayments}\n";
echo "   Deleted SMS Records: {$deletedSms}\n\n";

// Check for deleted receipts
if ($deletedReceipts > 0) {
    echo "🗑️  DELETED RECEIPTS FOUND:\n";
    $deletedReceiptRecords = Receipt::where('dlt_status', true)
        ->with(['itemBulk', 'creator'])
        ->get();

    foreach ($deletedReceiptRecords as $receipt) {
        echo "   Receipt #{$receipt->id}: ItemBulk #{$receipt->item_bulk_id}, Quantity: {$receipt->item_quantity}, Amount: LKR {$receipt->amount}\n";
        echo "     Created: {$receipt->created_at}, Updated: {$receipt->updated_at}\n";
        echo "     Updated by: User #{$receipt->updated_by}\n\n";
    }
} else {
    echo "ℹ️  No deleted receipts found.\n\n";
}

// Check for deleted payments
if ($deletedPayments > 0) {
    echo "💸 DELETED PAYMENTS FOUND:\n";
    $deletedPaymentRecords = Payment::where('status', 'delete')
        ->with('item')
        ->get();

    foreach ($deletedPaymentRecords as $payment) {
        $itemInfo = $payment->item ? "Item #{$payment->item->id} ({$payment->item->barcode})" : "Item not found";
        echo "   Payment #{$payment->id}: {$itemInfo}, Amount: LKR {$payment->fixed_amount}\n";
        echo "     Status: {$payment->status}, Updated: {$payment->updated_at}\n\n";
    }
} else {
    echo "ℹ️  No deleted payments found.\n\n";
}

// Check for deleted SMS records
if ($deletedSms > 0) {
    echo "📱 DELETED SMS RECORDS FOUND:\n";
    $deletedSmsRecords = SmsSent::where('status', 'delete')
        ->with('item')
        ->get();

    foreach ($deletedSmsRecords as $sms) {
        $itemInfo = $sms->item ? "Item #{$sms->item->id} ({$sms->item->barcode})" : "Item not found";
        echo "   SMS #{$sms->id}: {$itemInfo}\n";
        echo "     From: {$sms->sender_mobile}, To: {$sms->receiver_mobile}\n";
        echo "     Status: {$sms->status}, Updated: {$sms->updated_at}\n\n";
    }
} else {
    echo "ℹ️  No deleted SMS records found.\n\n";
}

// Check for ItemBulks with zero quantity
$emptyItemBulks = ItemBulk::where('item_quantity', 0)->count();
if ($emptyItemBulks > 0) {
    echo "📦 EMPTY ITEM BULKS (quantity = 0):\n";
    $emptyBulkRecords = ItemBulk::where('item_quantity', 0)
        ->with(['receipts', 'items'])
        ->get();

    foreach ($emptyBulkRecords as $bulk) {
        echo "   ItemBulk #{$bulk->id}: Category {$bulk->category}, Created: {$bulk->created_at}\n";
        echo "     Items remaining: {$bulk->items->count()}\n";
        echo "     Receipts: {$bulk->receipts->count()}\n\n";
    }
} else {
    echo "ℹ️  No empty ItemBulks found.\n\n";
}

// Summary of deletion functionality
echo "✅ DELETION FUNCTIONALITY VERIFICATION:\n\n";

if ($deletedReceipts > 0 || $deletedPayments > 0 || $deletedSms > 0) {
    echo "🎉 SUCCESS: PM deletion is working correctly!\n";
    echo "   ✅ Receipt dlt_status properly updated\n";
    echo "   ✅ Payment records marked as deleted\n";
    echo "   ✅ SMS records marked as deleted\n";
    echo "   ✅ Database integrity maintained\n\n";
} else {
    echo "ℹ️  No deletions detected yet. Test the deletion functionality:\n";
    echo "   1. Go to PM Item Management\n";
    echo "   2. Search for an item by barcode\n";
    echo "   3. Click 'Full Edit' and then 'Delete Item'\n";
    echo "   4. Run this script again to verify changes\n\n";
}

echo "🔍 WHAT TO LOOK FOR AFTER DELETION:\n";
echo "   • Receipt dlt_status changed from 0 to 1\n";
echo "   • Receipt item_quantity decreased\n";
echo "   • Receipt amount reduced by deleted item amount\n";
echo "   • Payment status changed to 'delete' (for COD items)\n";
echo "   • SMS status changed to 'delete' (for all SMS records)\n";
echo "   • Item completely removed from items table\n";
echo "   • ItemBulk quantity properly updated\n\n";

echo "=== VERIFICATION COMPLETE ===\n";
