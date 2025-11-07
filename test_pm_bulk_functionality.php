<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ItemBulk;
use App\Models\Item;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Location;

echo "=== TESTING PM BULK UPLOAD FUNCTIONALITY ===\n\n";

// Check if there are any errors in the logs
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $todayLogs = '';
    $lines = explode("\n", $logContent);
    foreach ($lines as $line) {
        if (strpos($line, '2025-11-04') !== false) {
            $todayLogs .= $line . "\n";
        }
    }

    if (!empty($todayLogs)) {
        echo "🔍 TODAY'S LOG ENTRIES:\n";
        echo substr($todayLogs, -2000) . "\n\n"; // Last 2000 chars
    } else {
        echo "✅ No error logs found for today\n\n";
    }
}

// Check if the route is working by looking for any bulk_list records
echo "🔍 CHECKING FOR ANY bulk_list RECORDS:\n";
$allBulkList = ItemBulk::where('category', 'bulk_list')->get();
foreach ($allBulkList as $bulk) {
    echo "ItemBulk {$bulk->id}: {$bulk->sender_name}, Items: {$bulk->item_quantity}, Created: {$bulk->created_at}\n";
}

if ($allBulkList->count() == 0) {
    echo "❌ NO PM bulk uploads found in entire database!\n";
    echo "This suggests PM bulk upload has never been successfully used.\n\n";
} else {
    echo "✅ Found " . $allBulkList->count() . " PM bulk uploads\n\n";
}

// Check if test CSV file exists
$testCsv = __DIR__ . '/test_pm_bulk_upload.csv';
if (file_exists($testCsv)) {
    echo "✅ Test CSV file exists: {$testCsv}\n";
    echo "File size: " . filesize($testCsv) . " bytes\n";
    echo "First few lines:\n";
    $handle = fopen($testCsv, 'r');
    for ($i = 0; $i < 3 && ($line = fgetcsv($handle)) !== false; $i++) {
        echo "  " . implode(',', $line) . "\n";
    }
    fclose($handle);
} else {
    echo "❌ Test CSV file not found\n";
}

echo "\n📋 MANUAL TEST INSTRUCTIONS:\n";
echo "1. Login as PM user\n";
echo "2. Go to: /pm/bulk-upload\n";
echo "3. Select service type: COD\n";
echo "4. Upload: test_pm_bulk_upload.csv\n";
echo "5. Check if ItemBulk with category='bulk_list' is created\n";
echo "6. Check if Items are linked with proper amounts\n";
echo "7. Check if Receipt is created\n\n";

echo "🔧 If PM bulk upload isn't working, possible issues:\n";
echo "- Route not working correctly\n";
echo "- File upload/validation failing\n";
echo "- Database transaction rolling back due to error\n";
echo "- Method not being called at all\n\n";

?>
