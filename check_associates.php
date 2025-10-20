<?php
require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TemporaryUploadAssociate;

echo "All TemporaryUploadAssociate records:\n";
echo "======================================\n";

$records = TemporaryUploadAssociate::all();

foreach ($records as $record) {
    echo "ID: {$record->id}\n";
    echo "Receiver Name: {$record->receiver_name}\n";
    echo "Upload ID: {$record->temporary_upload_id}\n";
    echo "Created: {$record->created_at}\n";
    echo "--------------------------------------\n";
}

echo "\nTotal records: " . $records->count() . "\n";

// Check for any soft deleted records
echo "\nChecking for soft deleted records:\n";
$withTrashed = TemporaryUploadAssociate::withTrashed()->get();
echo "Total with trashed: " . $withTrashed->count() . "\n";

if ($withTrashed->count() > $records->count()) {
    echo "Found soft deleted records:\n";
    $trashedRecords = TemporaryUploadAssociate::onlyTrashed()->get();
    foreach ($trashedRecords as $record) {
        echo "Deleted ID: {$record->id} - {$record->receiver_name}\n";
    }
}
