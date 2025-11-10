<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use App\Models\TemporaryUploadAssociate;
use App\Models\TemporaryUpload;
use App\Models\User;
use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\SmsSent;
use App\Models\Payment;

// Initialize Laravel application
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== Testing COD Receipt Creation with Postage ===\n";

DB::beginTransaction();
try {
    // Find a pending COD item
    $pendingItem = TemporaryUploadAssociate::with('temporaryUpload')
        ->where('status', 'pending')
        ->where('service_type', 'cod')
        ->whereNotNull('barcode')
        ->where('barcode', '!=', '')
        ->first();

    if (!$pendingItem) {
        echo "No pending COD items found for testing.\n";
        DB::rollback();
        return;
    }

    $temporaryUpload = $pendingItem->temporaryUpload;
    $pm = User::where('role', 'pm')->where('location_id', $temporaryUpload->location_id)->first();

    if (!$pm) {
        echo "No PM found for this location.\n";
        DB::rollback();
        return;
    }

    echo "Testing COD with:\n";
    echo "PM: {$pm->name}\n";
    echo "Upload ID: {$temporaryUpload->id}\n";
    echo "Item: {$pendingItem->barcode} ({$pendingItem->service_type})\n";
    echo "COD Amount: {$pendingItem->amount}\n";
    echo "Postage: {$pendingItem->postage}\n\n";

    // Get all pending items from this upload
    $pendingItems = TemporaryUploadAssociate::where('temporary_id', $temporaryUpload->id)
        ->where('status', 'pending')
        ->whereNotNull('barcode')
        ->where('barcode', '!=', '')
        ->get();

    // Create ItemBulk
    $itemBulk = ItemBulk::create([
        'sender_name' => $temporaryUpload->user->name,
        'service_type' => $pendingItems->first()->service_type ?? 'cod',
        'location_id' => $temporaryUpload->location_id,
        'created_by' => $pm->id,
        'category' => $temporaryUpload->category,
        'item_quantity' => $pendingItems->count(),
    ]);

    echo "Created ItemBulk ID: {$itemBulk->id}\n";

    // Process each pending item
    foreach ($pendingItems as $tempItem) {
        // Create item
        $item = Item::create([
            'item_bulk_id' => $itemBulk->id,
            'barcode' => $tempItem->barcode,
            'receiver_name' => $tempItem->receiver_name,
            'receiver_address' => $tempItem->receiver_address,
            'status' => 'accept',
            'weight' => $tempItem->weight,
            'amount' => $tempItem->service_type === 'cod' ? $tempItem->amount : 0.00,
            'created_by' => $pm->id,
            'updated_by' => $pm->id,
        ]);

        // Create payment for COD
        if ($tempItem->service_type === 'cod' && $tempItem->amount > 0) {
            Payment::create([
                'item_id' => $item->id,
                'fixed_amount' => $tempItem->amount,
                'commission' => $tempItem->commission ?? 0.00,
                'item_value' => $tempItem->item_value ?? $tempItem->amount,
                'status' => 'accept',
            ]);
        }

        // Create SMS
        SmsSent::create([
            'item_id' => $item->id,
            'sender_mobile' => $temporaryUpload->user->mobile ?? '',
            'receiver_mobile' => $tempItem->contact_number ?? '',
            'status' => 'accept',
        ]);

        // Update temp item status
        $tempItem->update(['status' => 'accept']);

        echo "Processed COD item: {$tempItem->barcode}, Amount: {$tempItem->amount}, Postage: {$tempItem->postage}\n";
    }

    // Create receipt - Testing COD + Postage
    $allItems = $itemBulk->items;
    $codAmount = $allItems->sum('amount');
    $postageAmount = $pendingItems->sum('postage'); // Sum postage from pending items
    $totalAmount = $codAmount + $postageAmount;

    echo "\nCOD Receipt Calculation:\n";
    echo "COD Amount: {$codAmount}\n";
    echo "Postage Amount: {$postageAmount}\n";
    echo "Total Amount (COD + Postage): {$totalAmount}\n";

    // Generate passcode function
    function generatePasscode() {
        return strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8));
    }

    $receipt = Receipt::create([
        'item_quantity' => $allItems->count(),
        'item_bulk_id' => $itemBulk->id,
        'amount' => $codAmount, // COD amount only
        'postage' => $postageAmount, // Postage fees
        'total_amount' => $totalAmount, // Combined total
        'payment_type' => 'cash',
        'passcode' => generatePasscode(),
        'created_by' => $pm->id,
        'location_id' => $temporaryUpload->location_id,
    ]);

    echo "\nCreated COD Receipt ID: {$receipt->id}\n";
    echo "Receipt Amount (COD): {$receipt->amount}\n";
    echo "Receipt Postage: {$receipt->postage}\n";
    echo "Receipt Total (COD + Postage): {$receipt->total_amount}\n";

    echo "\nReceipt URL: http://127.0.0.1:8000/pm/single-item/receipt/{$receipt->id}\n";

    DB::commit();
    echo "\nSuccess! COD Receipt created successfully with both COD amount and postage values.\n";

} catch (Exception $e) {
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
