<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use Illuminate\Support\Facades\DB;

echo "=== Data Fix Script for Temporary Upload Associates ===\n\n";

// Step 1: Check current state
echo "1. Current Data Analysis:\n";
$totalUploads = TemporaryUpload::count();
$totalAssociates = TemporaryUploadAssociate::count();
echo "   Total uploads: $totalUploads\n";
echo "   Total associates: $totalAssociates\n";

// Step 2: Find orphaned associates (pointing to non-existent uploads)
$orphanedAssociates = TemporaryUploadAssociate::whereNotExists(function($query) {
    $query->select(DB::raw(1))
          ->from('temporary_uploads')
          ->whereColumn('temporary_uploads.id', 'temporary_upload_associates.temporary_id');
})->get();

echo "   Orphaned associates: " . $orphanedAssociates->count() . "\n";

// Step 3: Find uploads without associates
$uploadsWithoutAssociates = TemporaryUpload::whereDoesntHave('associates')->get();
echo "   Uploads without associates: " . $uploadsWithoutAssociates->count() . "\n";

// Step 4: Show specific problematic data
echo "\n2. Problematic Data:\n";
foreach ($orphanedAssociates as $associate) {
    echo "   Associate ID {$associate->id} points to non-existent upload ID {$associate->temporary_id}\n";
}

foreach ($uploadsWithoutAssociates as $upload) {
    echo "   Upload ID {$upload->id} ({$upload->category}) has no associates\n";
}

// Step 5: Fix the data by creating proper relationships
echo "\n3. Attempting to fix data...\n";

DB::beginTransaction();
try {
    // For single_item uploads without associates, we need to create them
    $singleItemsWithoutAssociates = TemporaryUpload::where('category', 'single_item')
        ->whereDoesntHave('associates')
        ->get();
    
    echo "   Found " . $singleItemsWithoutAssociates->count() . " single items without associates\n";
    
    // For now, let's just report what needs to be fixed
    foreach ($singleItemsWithoutAssociates as $upload) {
        echo "   Upload ID {$upload->id} needs associate creation\n";
    }
    
    // Check for associates with wrong temporary_id that might need remapping
    $associatesWithWrongId = TemporaryUploadAssociate::whereNotExists(function($query) {
        $query->select(DB::raw(1))
              ->from('temporary_uploads')
              ->whereColumn('temporary_uploads.id', 'temporary_upload_associates.temporary_id');
    })->get();
    
    echo "   Associates with wrong temporary_id: " . $associatesWithWrongId->count() . "\n";
    
    DB::rollback(); // Don't actually fix yet, just analyze
    
} catch (\Exception $e) {
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n4. Recommendations:\n";
echo "   - Delete orphaned associates with non-existent temporary_id\n";
echo "   - Recreate missing associates for single_item uploads\n";
echo "   - Ensure proper data integrity going forward\n";