<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;

echo "=== Testing PM Customer Uploads Query ===\n\n";

// This is the exact query from PMDashboardController->customerUploads()
$uploadsQuery = TemporaryUpload::with(['user', 'location'])
    ->where('location_id', 1)  // PM location
    ->whereHas('associates', function($q) {
        $q->where('status', 'pending');
    })
    ->withCount([
        'associates as total_items',
        'associates as pending_items' => function($query) {
            $query->where('status', 'pending');
        }
    ]);

$uploads = $uploadsQuery->orderBy('created_at', 'desc')->get();

echo "Found " . $uploads->count() . " uploads that should show in PM dashboard:\n\n";

foreach ($uploads as $upload) {
    echo "Upload ID: {$upload->id}\n";
    echo "Category: {$upload->category}\n";
    echo "User: {$upload->user->name}\n";
    echo "Location: {$upload->location_id}\n";
    echo "Total Items: {$upload->total_items}\n";
    echo "Pending Items: {$upload->pending_items}\n";
    echo "Created: {$upload->created_at}\n";

    // Check if this is upload 22
    if ($upload->id == 22) {
        echo "*** THIS IS UPLOAD 22 - IT SHOULD BE VISIBLE! ***\n";
        echo "Associates for upload 22:\n";
        foreach ($upload->associates as $associate) {
            echo "  - Associate ID: {$associate->id}\n";
            echo "    Status: {$associate->status}\n";
            echo "    Service Type: {$associate->service_type}\n";
            echo "    Receiver: {$associate->receiver_name}\n";
        }
    }
    echo "---\n";
}

// Specifically check upload 22
echo "\nSpecific check for Upload ID 22:\n";
$upload22 = TemporaryUpload::with(['user', 'location', 'associates'])->find(22);
if ($upload22) {
    echo "Upload 22 exists:\n";
    echo "- Location ID: {$upload22->location_id}\n";
    echo "- User: {$upload22->user->name}\n";
    echo "- Associates count: " . $upload22->associates->count() . "\n";
    foreach ($upload22->associates as $associate) {
        echo "  - Associate {$associate->id}: status={$associate->status}, service_type={$associate->service_type}\n";
    }
} else {
    echo "Upload 22 NOT found!\n";
}
