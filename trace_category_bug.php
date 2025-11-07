<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TRACING THE BUG ===" . PHP_EOL . PHP_EOL;

// Check the specific case: Temp ID 95 (single_item) -> Bulk ID 94 (temporary_list)
echo "Temp ID 95 analysis:" . PHP_EOL;
$temp95 = DB::table('temporary_uploads')->where('id', 95)->first();
echo "  Category: {$temp95->category}" . PHP_EOL;
echo "  User ID: {$temp95->user_id}" . PHP_EOL;

$assoc95 = DB::table('temporary_upload_associates')->where('temporary_id', 95)->first();
echo "  Associate ID: {$assoc95->id}" . PHP_EOL;
echo "  Associate Status: {$assoc95->status}" . PHP_EOL;

// Find which ItemBulk was created for this
$items95 = DB::table('items')
    ->join('temporary_upload_associates', 'items.barcode', '=', 'temporary_upload_associates.barcode')
    ->where('temporary_upload_associates.temporary_id', 95)
    ->select('items.*')
    ->get();

echo PHP_EOL . "Items created for Temp ID 95:" . PHP_EOL;
foreach ($items95 as $item) {
    echo "  Item ID: {$item->id}, Barcode: {$item->barcode}, Bulk ID: {$item->item_bulk_id}" . PHP_EOL;
    
    $bulk = DB::table('item_bulk')->where('id', $item->item_bulk_id)->first();
    echo "  -> ItemBulk Category: {$bulk->category} (SHOULD BE: single_item)" . PHP_EOL;
}

echo PHP_EOL . "=== ANOTHER CASE ===" . PHP_EOL;

// Check Temp ID 92 (single_item)
echo "Temp ID 92 analysis:" . PHP_EOL;
$temp92 = DB::table('temporary_uploads')->where('id', 92)->first();
echo "  Category: {$temp92->category}" . PHP_EOL;

$assoc92 = DB::table('temporary_upload_associates')->where('temporary_id', 92)->first();
echo "  Associate ID: {$assoc92->id}" . PHP_EOL;
echo "  Associate Status: {$assoc92->status}" . PHP_EOL;

// Find which ItemBulk was created for this
$items92 = DB::table('items')
    ->join('temporary_upload_associates', 'items.barcode', '=', 'temporary_upload_associates.barcode')
    ->where('temporary_upload_associates.temporary_id', 92)
    ->select('items.*')
    ->get();

echo PHP_EOL . "Items created for Temp ID 92:" . PHP_EOL;
foreach ($items92 as $item) {
    echo "  Item ID: {$item->id}, Barcode: {$item->barcode}, Bulk ID: {$item->item_bulk_id}" . PHP_EOL;
    
    $bulk = DB::table('item_bulk')->where('id', $item->item_bulk_id)->first();
    if ($bulk) {
        echo "  -> ItemBulk Category: {$bulk->category} (SHOULD BE: single_item)" . PHP_EOL;
    } else {
        echo "  -> No ItemBulk found!" . PHP_EOL;
    }
}

echo PHP_EOL . "=== CHECKING A BULK UPLOAD CASE ===" . PHP_EOL;

// Check a temporary_list case to see if it's working correctly
$tempBulk = DB::table('temporary_uploads')->where('category', 'temporary_list')->where('id', 94)->first();
if ($tempBulk) {
    echo "Temp ID {$tempBulk->id} (bulk upload):" . PHP_EOL;
    echo "  Category: {$tempBulk->category}" . PHP_EOL;
    
    $assocsBulk = DB::table('temporary_upload_associates')->where('temporary_id', $tempBulk->id)->get();
    echo "  Associates count: " . $assocsBulk->count() . PHP_EOL;
    
    foreach ($assocsBulk as $assoc) {
        $itemsBulk = DB::table('items')
            ->where('barcode', $assoc->barcode)
            ->get();
        
        foreach ($itemsBulk as $item) {
            $bulk = DB::table('item_bulk')->where('id', $item->item_bulk_id)->first();
            if ($bulk) {
                echo "  -> Item {$item->id}, Bulk {$bulk->id}, Category: {$bulk->category} (SHOULD BE: temporary_list)" . PHP_EOL;
            }
        }
    }
}

?>