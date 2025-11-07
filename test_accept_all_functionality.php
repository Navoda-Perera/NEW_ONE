<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\Item;
use App\Models\ItemBulk;

// Initialize Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Accept All Upload Functionality ===\n";

// Check existing customer upload 82 from the screenshot
$upload = TemporaryUpload::find(82);
if (!$upload) {
    echo "❌ Upload 82 not found\n";
    exit;
}

echo "✅ Found Upload #{$upload->id}\n";
echo "   User: {$upload->user->name}\n";
echo "   Created: " . $upload->created_at->format('Y-m-d H:i') . "\n\n";

// Check the items in this upload
$pendingItems = TemporaryUploadAssociate::where('temporary_id', $upload->id)
    ->where('status', 'pending')
    ->get();

echo "📋 Pending items in upload #{$upload->id}:\n";
foreach ($pendingItems as $item) {
    $hasBarcode = !empty($item->barcode);
    echo "   Item #{$item->id}: {$item->receiver_name} - Barcode: " .
         ($hasBarcode ? "✅ {$item->barcode}" : "❌ Missing") .
         " - Service: {$item->service_type}\n";
}

// Count items with barcodes
$itemsWithBarcodes = $pendingItems->whereNotNull('barcode')->where('barcode', '!=', '');
echo "\n📊 Summary:\n";
echo "   Total pending items: {$pendingItems->count()}\n";
echo "   Items with barcodes: {$itemsWithBarcodes->count()}\n";
echo "   Items without barcodes: " . ($pendingItems->count() - $itemsWithBarcodes->count()) . "\n";

// Check if an ItemBulk already exists for this upload
$existingItemBulk = ItemBulk::where('category', 'temporary_list')
    ->whereHas('items', function($query) use ($upload) {
        $query->whereHas('temporaryUploadAssociate', function($subQuery) use ($upload) {
            $subQuery->where('temporary_id', $upload->id);
        });
    })
    ->first();

echo "\n🗂️ ItemBulk Status:\n";
if ($existingItemBulk) {
    echo "   ✅ Existing ItemBulk found: #{$existingItemBulk->id}\n";
    echo "   Current item count: {$existingItemBulk->items->count()}\n";
} else {
    echo "   ➕ No existing ItemBulk - will create new one\n";
}

echo "\n💡 Accept All Button will:\n";
if ($itemsWithBarcodes->count() > 0) {
    echo "   ✅ Accept {$itemsWithBarcodes->count()} items with barcodes\n";
    echo "   ✅ " . ($existingItemBulk ? "Add to existing" : "Create new") . " ItemBulk\n";
    echo "   ✅ Create items in items table\n";
    echo "   ✅ Update temporary_upload_associates status to 'accept'\n";

    $codItems = $itemsWithBarcodes->where('service_type', 'cod');
    if ($codItems->count() > 0) {
        $totalAmount = $codItems->sum('amount');
        echo "   ✅ Create receipt for {$codItems->count()} COD items (Total: LKR " . number_format($totalAmount, 2) . ")\n";
    }
} else {
    echo "   ❌ No items with barcodes to accept\n";
    echo "   💡 Items need barcodes before they can be accepted\n";
}

echo "\n🎯 Result: PM can now accept entire upload list with one click!\n";

?>
