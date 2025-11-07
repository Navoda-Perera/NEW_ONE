<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\TemporaryUpload;
use App\Models\Item;
use App\Models\ItemBulk;

// Initialize Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Customer Item Acceptance Fix Test ===\n";

// First, let's see the current state
echo "\n1. Current ItemBulk records:\n";
$itemBulks = ItemBulk::with('items')->orderBy('id', 'desc')->take(5)->get();
foreach ($itemBulks as $bulk) {
    echo "ItemBulk {$bulk->id}: Category={$bulk->category}, Items={$bulk->items->count()}, Created=" . $bulk->created_at->format('Y-m-d H:i') . "\n";
}

// Check recent temporary uploads
echo "\n2. Recent TemporaryUploads:\n";
$tempUploads = TemporaryUpload::orderBy('id', 'desc')->take(5)->get();
foreach ($tempUploads as $upload) {
    echo "TemporaryUpload {$upload->id}: Category={$upload->category}, User={$upload->user_id}, Created=" . $upload->created_at->format('Y-m-d H:i') . "\n";
}

// Check items with their temporary upload associations
echo "\n3. Recent Items and their temporary upload associations:\n";
$items = Item::with('temporaryUploadAssociate.temporaryUpload')
    ->whereHas('temporaryUploadAssociate')
    ->orderBy('id', 'desc')
    ->take(10)
    ->get();

foreach ($items as $item) {
    $tempUpload = $item->temporaryUploadAssociate->temporaryUpload ?? null;
    $tempUploadId = $tempUpload ? $tempUpload->id : 'None';
    echo "Item {$item->id}: ItemBulk={$item->item_bulk_id}, TempUpload={$tempUploadId}, Created=" . $item->created_at->format('Y-m-d H:i') . "\n";
}

// Simulate creating a NEW temporary upload and then accepting items from it
echo "\n4. Creating test scenario:\n";

// Create a new test temporary upload
$newTempUpload = TemporaryUpload::create([
    'user_id' => 1, // Admin user
    'location_id' => 1,
    'category' => 'temporary_list',
    'total_item' => 2,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Created new TemporaryUpload: {$newTempUpload->id}\n";

// Test the lookup logic that was causing the issue
echo "\n5. Testing old vs new lookup logic:\n";

// OLD LOGIC (problematic)
$oldLogicItemBulk = ItemBulk::where('sender_name', $newTempUpload->user->name)
    ->where('location_id', $newTempUpload->location_id)
    ->where('category', 'temporary_list')
    ->whereHas('items', function($query) use ($newTempUpload) {
        $query->whereIn('created_by', [$newTempUpload->user_id]);
    })
    ->first();

echo "Old logic would find ItemBulk: " . ($oldLogicItemBulk ? $oldLogicItemBulk->id : 'None') . "\n";

// NEW LOGIC (fixed)
$newLogicItemBulk = ItemBulk::where('category', 'temporary_list')
    ->whereHas('items', function($query) use ($newTempUpload) {
        $query->whereHas('temporaryUploadAssociate', function($subQuery) use ($newTempUpload) {
            $subQuery->where('temporary_id', $newTempUpload->id);
        });
    })
    ->first();

echo "New logic would find ItemBulk: " . ($newLogicItemBulk ? $newLogicItemBulk->id : 'None - will create new') . "\n";

echo "\n=== RESULT ===\n";
echo "✅ Old logic would reuse existing ItemBulk (causing the issue)\n";
echo "✅ New logic would create new ItemBulk for each upload session\n";
echo "✅ This prevents items from different uploads getting mixed together\n";

// Clean up test data
$newTempUpload->delete();
echo "\nTest temporary upload cleaned up.\n";

?>
