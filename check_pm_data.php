<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;

echo "=== PM Dashboard Data Check ===\n\n";

// Check what PM dashboard should show (location_id = 1, pending items)
$pmQuery = TemporaryUpload::with(['user', 'location', 'associates'])
    ->where('location_id', 1)
    ->whereHas('associates', function($q) {
        $q->where('status', 'pending');
    })
    ->withCount([
        'associates as total_items',
        'associates as pending_items' => function($query) {
            $query->where('status', 'pending');
        }
    ])
    ->orderBy('created_at', 'desc')
    ->get();

echo "PM Dashboard should show " . $pmQuery->count() . " uploads with pending items:\n\n";

foreach ($pmQuery as $upload) {
    echo "Upload ID: {$upload->id}\n";
    echo "Category: {$upload->category}\n";
    echo "User: {$upload->user->name}\n";
    echo "Location: {$upload->location_id}\n";
    echo "Total Items: {$upload->total_items}\n";
    echo "Pending Items: {$upload->pending_items}\n";
    echo "Associates:\n";

    foreach ($upload->associates as $associate) {
        echo "  - Associate ID: {$associate->id}\n";
        echo "    Temporary ID: {$associate->temporary_id}\n";
        echo "    Status: {$associate->status}\n";
        echo "    Service Type: {$associate->service_type}\n";
        echo "    Receiver: {$associate->receiver_name}\n";
    }
    echo "---\n";
}

// Check if there are associates without proper temporary_upload relationship
echo "\nChecking all associates for proper relationships:\n";
$allAssociates = TemporaryUploadAssociate::with('temporaryUpload')->get();

foreach ($allAssociates as $associate) {
    if (!$associate->temporaryUpload) {
        echo "BROKEN: Associate ID {$associate->id} has temporary_id {$associate->temporary_id} but no upload found!\n";
    }
}

echo "\nTotal associates checked: " . $allAssociates->count() . "\n";
