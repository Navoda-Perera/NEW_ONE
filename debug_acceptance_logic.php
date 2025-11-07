<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUGGING ACCEPTANCE LOGIC ===" . PHP_EOL . PHP_EOL;

// Let's simulate what happens in acceptSingleItemFromAnyCategory
$itemId = 161; // From our previous trace - this item has wrong category

echo "Debugging Item ID: {$itemId}" . PHP_EOL;

// Get the item data
$item = DB::table('items')->where('id', $itemId)->first();
if (!$item) {
    echo "Item not found!" . PHP_EOL;
    exit;
}

echo "Item details:" . PHP_EOL;
echo "  Barcode: {$item->barcode}" . PHP_EOL;
echo "  ItemBulk ID: {$item->item_bulk_id}" . PHP_EOL;

// Get the ItemBulk
$itemBulk = DB::table('item_bulk')->where('id', $item->item_bulk_id)->first();
echo "ItemBulk details:" . PHP_EOL;
echo "  Category: {$itemBulk->category}" . PHP_EOL;
echo "  Sender: {$itemBulk->sender_name}" . PHP_EOL;

// Find the corresponding TemporaryUploadAssociate
$tempAssociate = DB::table('temporary_upload_associates')->where('barcode', $item->barcode)->first();
if (!$tempAssociate) {
    echo "TemporaryUploadAssociate not found!" . PHP_EOL;
    exit;
}

echo "TemporaryUploadAssociate details:" . PHP_EOL;
echo "  ID: {$tempAssociate->id}" . PHP_EOL;
echo "  Temporary ID: {$tempAssociate->temporary_id}" . PHP_EOL;
echo "  Status: {$tempAssociate->status}" . PHP_EOL;

// Get the TemporaryUpload
$tempUpload = DB::table('temporary_uploads')->where('id', $tempAssociate->temporary_id)->first();
echo "TemporaryUpload details:" . PHP_EOL;
echo "  ID: {$tempUpload->id}" . PHP_EOL;
echo "  Category: {$tempUpload->category} <- THIS is what should determine ItemBulk category" . PHP_EOL;
echo "  User ID: {$tempUpload->user_id}" . PHP_EOL;

echo PHP_EOL . "=== ANALYSIS ===" . PHP_EOL;
echo "Expected logic:" . PHP_EOL;
echo "  if (temporaryUpload.category === 'temporary_list') {" . PHP_EOL;
echo "    itemBulk.category = 'temporary_list'" . PHP_EOL;
echo "  } else {" . PHP_EOL;
echo "    itemBulk.category = 'single_item'" . PHP_EOL;
echo "  }" . PHP_EOL . PHP_EOL;

echo "Current case:" . PHP_EOL;
echo "  temporaryUpload.category = '{$tempUpload->category}'" . PHP_EOL;
echo "  itemBulk.category = '{$itemBulk->category}'" . PHP_EOL;

if ($tempUpload->category === 'single_item' && $itemBulk->category === 'temporary_list') {
    echo "  ❌ BUG CONFIRMED: single_item was incorrectly set to temporary_list!" . PHP_EOL;
} elseif ($tempUpload->category === $itemBulk->category) {
    echo "  ✅ Category assignment is correct" . PHP_EOL;
} else {
    echo "  ❓ Unexpected category mismatch" . PHP_EOL;
}

?>
