<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Items table columns:\n";
$columns = DB::select('DESCRIBE items');
foreach ($columns as $column) {
    echo $column->Field . ' - ' . $column->Type . "\n";
}

echo "\nTemporaryUploadAssociate table columns:\n";
$columns = DB::select('DESCRIBE temporary_upload_associates');
foreach ($columns as $column) {
    echo $column->Field . ' - ' . $column->Type . "\n";
}

// Check if there's a link between them
echo "\nChecking for relationships:\n";
$sampleItem = DB::select('SELECT * FROM items WHERE id IN (122, 123, 124, 125) LIMIT 1');
if (!empty($sampleItem)) {
    $item = $sampleItem[0];
    echo "Sample item {$item->id}: barcode={$item->barcode}, item_bulk_id={$item->item_bulk_id}\n";
    
    // Check if there's a temporary upload associate with the same barcode
    $tempAssoc = DB::select("SELECT * FROM temporary_upload_associates WHERE barcode = ? LIMIT 1", [$item->barcode]);
    if (!empty($tempAssoc)) {
        $assoc = $tempAssoc[0];
        echo "Found matching temporary_upload_associate: temporary_id={$assoc->temporary_id}\n";
    } else {
        echo "No matching temporary_upload_associate found\n";
    }
}

?>