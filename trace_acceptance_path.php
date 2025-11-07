<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TRACING THE ACCEPTANCE PATH ===" . PHP_EOL . PHP_EOL;

// Let's check if there might be multiple acceptance routes for the same item
// Find the barcode and see all related records

$barcode = 'nh6789'; // The problematic item
echo "Tracing barcode: {$barcode}" . PHP_EOL . PHP_EOL;

// 1. Check TemporaryUploadAssociate
$tempAssoc = DB::table('temporary_upload_associates')->where('barcode', $barcode)->first();
if ($tempAssoc) {
    echo "TemporaryUploadAssociate:" . PHP_EOL;
    echo "  ID: {$tempAssoc->id}" . PHP_EOL;
    echo "  Temporary ID: {$tempAssoc->temporary_id}" . PHP_EOL;
    echo "  Status: {$tempAssoc->status}" . PHP_EOL;
    echo "  Created: {$tempAssoc->created_at}" . PHP_EOL;
    echo "  Updated: {$tempAssoc->updated_at}" . PHP_EOL;
    
    // Get the TemporaryUpload
    $tempUpload = DB::table('temporary_uploads')->where('id', $tempAssoc->temporary_id)->first();
    echo "  -> TemporaryUpload Category: {$tempUpload->category}" . PHP_EOL;
}

echo PHP_EOL;

// 2. Check Item record
$item = DB::table('items')->where('barcode', $barcode)->first();
if ($item) {
    echo "Item:" . PHP_EOL;
    echo "  ID: {$item->id}" . PHP_EOL;
    echo "  ItemBulk ID: {$item->item_bulk_id}" . PHP_EOL;
    echo "  Status: {$item->status}" . PHP_EOL;
    echo "  Created: {$item->created_at}" . PHP_EOL;
    echo "  Updated: {$item->updated_at}" . PHP_EOL;
    
    // Get the ItemBulk
    $itemBulk = DB::table('item_bulk')->where('id', $item->item_bulk_id)->first();
    echo "  -> ItemBulk Category: {$itemBulk->category}" . PHP_EOL;
    echo "  -> ItemBulk Created: {$itemBulk->created_at}" . PHP_EOL;
}

echo PHP_EOL;

// 3. Check if there are logs in the database about this acceptance
// Since we don't have a logs table, let's check timestamps to understand the sequence

echo "Timeline analysis:" . PHP_EOL;
if ($tempAssoc && $item) {
    $tempCreated = strtotime($tempAssoc->created_at);
    $tempUpdated = strtotime($tempAssoc->updated_at);
    $itemCreated = strtotime($item->created_at);
    
    echo "  1. TemporaryUploadAssociate created: {$tempAssoc->created_at}" . PHP_EOL;
    echo "  2. TemporaryUploadAssociate updated: {$tempAssoc->updated_at}" . PHP_EOL;
    echo "  3. Item created: {$item->created_at}" . PHP_EOL;
    
    $timeDiff = $itemCreated - $tempUpdated;
    echo "  -> Time between temp update and item creation: {$timeDiff} seconds" . PHP_EOL;
    
    // If they're very close, it's likely from the same acceptance action
    if ($timeDiff < 60) {
        echo "  -> Likely same acceptance action" . PHP_EOL;
    }
}

echo PHP_EOL . "=== CONCLUSION ===" . PHP_EOL;
echo "The bug appears to be:" . PHP_EOL;
echo "1. Customer creates single item with category='single_item'" . PHP_EOL;
echo "2. PM processes it through some method" . PHP_EOL;
echo "3. Method incorrectly creates ItemBulk with category='temporary_list'" . PHP_EOL;
echo "4. This suggests the wrong method path is being taken" . PHP_EOL;

echo PHP_EOL . "POSSIBLE CAUSES:" . PHP_EOL;
echo "- Single item going through bulk acceptance logic" . PHP_EOL;
echo "- Frontend sending wrong route/parameters" . PHP_EOL;
echo "- Logic error in route conditions" . PHP_EOL;

?>