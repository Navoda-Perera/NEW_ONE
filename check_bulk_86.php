<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING BULK_ID 86 STATUS ===" . PHP_EOL . PHP_EOL;

// Check if bulk_id 86 exists
$bulk = DB::table('item_bulk')->where('id', 86)->first();
if ($bulk) {
    echo "✅ ItemBulk 86 FOUND:" . PHP_EOL;
    echo "   Sender: " . $bulk->sender_name . PHP_EOL;
    echo "   Service: " . $bulk->service_type . PHP_EOL;
    echo "   Location ID: " . $bulk->location_id . PHP_EOL;
    echo "   Category: " . $bulk->category . PHP_EOL;
    echo "   Item Quantity: " . $bulk->item_quantity . PHP_EOL;
    echo "   Created: " . $bulk->created_at . PHP_EOL;
    echo "   Updated: " . $bulk->updated_at . PHP_EOL;
} else {
    echo "❌ ItemBulk 86 NOT FOUND - it has been permanently deleted!" . PHP_EOL;
}

echo PHP_EOL . "=== ITEMS FOR BULK_ID 86 ===" . PHP_EOL;
// Check for any items that belonged to bulk_id 86
$items = DB::table('items')->where('item_bulk_id', 86)->get();
echo "Items count: " . $items->count() . PHP_EOL;
foreach ($items as $item) {
    echo "  Item ID: {$item->id}, Barcode: {$item->barcode}, Status: {$item->status}" . PHP_EOL;
}

echo PHP_EOL . "=== RECEIPTS FOR BULK_ID 86 ===" . PHP_EOL;
// Check receipts related to bulk_id 86
$receipts = DB::table('receipts')->where('item_bulk_id', 86)->get();
echo "Receipts count: " . $receipts->count() . PHP_EOL;
foreach ($receipts as $receipt) {
    echo "  Receipt ID: {$receipt->id}, DLT Status: {$receipt->dlt_status}, Item Quantity: {$receipt->item_quantity}, Amount: {$receipt->amount}" . PHP_EOL;
}

echo PHP_EOL . "=== PAYMENTS FOR BULK_ID 86 ===" . PHP_EOL;
// Check payments through items
$payments = DB::table('payments')
    ->join('items', 'payments.item_id', '=', 'items.id')
    ->where('items.item_bulk_id', 86)
    ->select('payments.*')
    ->get();
echo "Payments count: " . $payments->count() . PHP_EOL;
foreach ($payments as $payment) {
    echo "  Payment ID: {$payment->id}, Status: {$payment->status}, Amount: {$payment->amount}" . PHP_EOL;
}

echo PHP_EOL . "=== SMS RECORDS FOR BULK_ID 86 ===" . PHP_EOL;
// Check SMS records through items
$smsRecords = DB::table('sms_sents')
    ->join('items', 'sms_sents.item_id', '=', 'items.id')
    ->where('items.item_bulk_id', 86)
    ->select('sms_sents.*')
    ->get();
echo "SMS records count: " . $smsRecords->count() . PHP_EOL;
foreach ($smsRecords as $sms) {
    echo "  SMS ID: {$sms->id}, Status: {$sms->status}, Mobile: {$sms->receiver_mobile}" . PHP_EOL;
}

?>
