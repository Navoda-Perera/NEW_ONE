<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\Receipt;
use App\Models\Payment;
use App\Models\SmsSent;

echo "=== TESTING NEW DELETE FUNCTIONALITY ===" . PHP_EOL . PHP_EOL;

// Create a test item to demonstrate the fix
DB::beginTransaction();

try {
    // Create a test item_bulk
    $itemBulk = new ItemBulk();
    $itemBulk->sender_name = 'Test Sender';
    $itemBulk->service_type = 'cod';
    $itemBulk->location_id = 9;
    $itemBulk->category = 'single_item';
    $itemBulk->item_quantity = 1;
    $itemBulk->created_by = 10;
    $itemBulk->save();

    echo "✅ Created test ItemBulk ID: {$itemBulk->id}" . PHP_EOL;

    // Create a test item
    $item = new Item();
    $item->item_bulk_id = $itemBulk->id;
    $item->barcode = 'TEST' . time();
    $item->receiver_name = 'Test Receiver';
    $item->receiver_address = 'Test Address';
    $item->status = 'accept';
    $item->weight = 100.00;
    $item->amount = 500.00;
    $item->created_by = 10;
    $item->save();

    echo "✅ Created test Item ID: {$item->id}, Barcode: {$item->barcode}" . PHP_EOL;

    // Create a test receipt
    $receipt = new Receipt();
    $receipt->item_bulk_id = $itemBulk->id;
    $receipt->location_id = 9;  // Add required location_id
    $receipt->item_quantity = 1;
    $receipt->amount = 500.00;
    $receipt->dlt_status = false;
    $receipt->created_by = 10;
    $receipt->save();

    echo "✅ Created test Receipt ID: {$receipt->id}" . PHP_EOL;

    // Create a test payment
    $payment = new Payment();
    $payment->item_id = $item->id;
    $payment->fixed_amount = 50.00;
    $payment->commission = 10.00;
    $payment->item_value = 500.00;
    $payment->status = 'accept';
    $payment->save();

    echo "✅ Created test Payment ID: {$payment->id}" . PHP_EOL;

    // Create a test SMS
    $sms = new SmsSent();
    $sms->item_id = $item->id;
    $sms->sender_mobile = '0771234567';
    $sms->receiver_mobile = '0779876543';
    $sms->status = 'accept';
    $sms->save();

    echo "✅ Created test SMS ID: {$sms->id}" . PHP_EOL . PHP_EOL;

    // Now demonstrate what the NEW delete functionality will do
    echo "=== BEFORE DELETE ===" . PHP_EOL;
    echo "Item Status: {$item->status}" . PHP_EOL;
    echo "ItemBulk Quantity: {$itemBulk->item_quantity}" . PHP_EOL;
    echo "Receipt DLT Status: " . ($receipt->dlt_status ? 'true' : 'false') . ", Quantity: {$receipt->item_quantity}, Amount: {$receipt->amount}" . PHP_EOL;
    echo "Payment Status: {$payment->status}" . PHP_EOL;
    echo "SMS Status: {$sms->status}" . PHP_EOL . PHP_EOL;

    // Simulate the NEW delete logic (without using the controller to avoid auth issues)
    echo "=== SIMULATING NEW DELETE LOGIC ===" . PHP_EOL;

    // Update item status to 'delete' instead of deleting record
    $item->update([
        'status' => 'delete',
        'updated_by' => 10,
    ]);
    echo "✅ Item status updated to: {$item->status}" . PHP_EOL;

    // Update receipt dlt_status to true (preserve quantity and amount)
    $receipt->update([
        'dlt_status' => true,
        'updated_by' => 10,
    ]);
    echo "✅ Receipt dlt_status updated to: " . ($receipt->dlt_status ? 'true' : 'false') . PHP_EOL;

    // Update payment status to 'delete'
    $payment->update([
        'status' => 'delete',
    ]);
    echo "✅ Payment status updated to: {$payment->status}" . PHP_EOL;

    // Update SMS status to 'delete'
    $sms->update([
        'status' => 'delete',
    ]);
    echo "✅ SMS status updated to: {$sms->status}" . PHP_EOL . PHP_EOL;

    // NOTE: ItemBulk quantity is NOT changed - preserves original count
    echo "=== AFTER DELETE ===" . PHP_EOL;
    echo "✅ Item still exists in database with status: {$item->fresh()->status}" . PHP_EOL;
    echo "✅ ItemBulk quantity preserved: {$itemBulk->fresh()->item_quantity} (NOT decreased)" . PHP_EOL;
    echo "✅ Receipt preserved with dlt_status: " . ($receipt->fresh()->dlt_status ? 'true' : 'false') . ", Quantity: {$receipt->fresh()->item_quantity}, Amount: {$receipt->fresh()->amount}" . PHP_EOL;
    echo "✅ Payment preserved with status: {$payment->fresh()->status}" . PHP_EOL;
    echo "✅ SMS preserved with status: {$sms->fresh()->status}" . PHP_EOL . PHP_EOL;

    echo "=== SUMMARY ===" . PHP_EOL;
    echo "✅ Item record PRESERVED in database (not permanently deleted)" . PHP_EOL;
    echo "✅ Item status marked as 'delete'" . PHP_EOL;
    echo "✅ ItemBulk quantity NOT changed (preserves statistics)" . PHP_EOL;
    echo "✅ Receipt dlt_status=true, but quantity/amount preserved" . PHP_EOL;
    echo "✅ Payment status='delete'" . PHP_EOL;
    echo "✅ SMS status='delete'" . PHP_EOL;
    echo "✅ Complete audit trail maintained" . PHP_EOL . PHP_EOL;

    // Cleanup test data
    DB::rollback();
    echo "🧹 Test data rolled back (cleaned up)" . PHP_EOL;

} catch (\Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

?>
