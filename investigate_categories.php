<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CATEGORY INVESTIGATION ===" . PHP_EOL . PHP_EOL;

// Check recent item_bulk records to see the category patterns
echo "Recent ItemBulk records:" . PHP_EOL;
$bulks = DB::table('item_bulk')
    ->where('created_at', '>', '2025-11-04')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get(['id', 'sender_name', 'category', 'item_quantity', 'created_at']);

foreach ($bulks as $bulk) {
    echo "Bulk ID: {$bulk->id}, Sender: {$bulk->sender_name}, Category: {$bulk->category}, Quantity: {$bulk->item_quantity}, Created: {$bulk->created_at}" . PHP_EOL;
}

echo PHP_EOL . "=== CHECKING TEMPORARY UPLOADS ===" . PHP_EOL;

// Check the temporary uploads to see their categories
$tempUploads = DB::table('temporary_uploads')
    ->where('created_at', '>', '2025-11-04')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get(['id', 'user_id', 'category', 'created_at']);

foreach ($tempUploads as $temp) {
    $user = DB::table('users')->where('id', $temp->user_id)->first();
    $userName = $user ? $user->name : 'Unknown';

    echo "Temp ID: {$temp->id}, User: {$userName}, Category: {$temp->category}, Created: {$temp->created_at}" . PHP_EOL;

    // Check how many associates this temp upload has
    $associateCount = DB::table('temporary_upload_associates')->where('temporary_id', $temp->id)->count();
    echo "  -> Associates: {$associateCount}" . PHP_EOL;
}

echo PHP_EOL . "=== CHECKING SPECIFIC USER 'kasun perera' ===" . PHP_EOL;

// Find the kasun perera user
$kasunUser = DB::table('users')->where('name', 'like', '%kasun%')->first();
if ($kasunUser) {
    echo "Found user: {$kasunUser->name} (ID: {$kasunUser->id})" . PHP_EOL;

    // Check his temporary uploads
    $kasunTemps = DB::table('temporary_uploads')
        ->where('user_id', $kasunUser->id)
        ->where('created_at', '>', '2025-11-04')
        ->get(['id', 'category', 'created_at']);

    echo "Kasun's temporary uploads:" . PHP_EOL;
    foreach ($kasunTemps as $temp) {
        echo "  Temp ID: {$temp->id}, Category: {$temp->category}, Created: {$temp->created_at}" . PHP_EOL;

        // Check associates for this temp upload
        $associates = DB::table('temporary_upload_associates')
            ->where('temporary_id', $temp->id)
            ->get(['id', 'sender_name', 'status']);

        foreach ($associates as $assoc) {
            echo "    Associate ID: {$assoc->id}, Sender: {$assoc->sender_name}, Status: {$assoc->status}" . PHP_EOL;
        }
    }
}

?>
