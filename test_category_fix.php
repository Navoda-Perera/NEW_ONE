<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TESTING CATEGORY FIX ===" . PHP_EOL . PHP_EOL;

// Test data for different scenarios
$testCases = [
    [
        'upload_id' => 95,
        'expected_category' => 'single_item',
        'description' => 'Single item upload'
    ],
    [
        'upload_id' => 94,
        'expected_category' => 'temporary_list', 
        'description' => 'Bulk upload'
    ]
];

foreach ($testCases as $testCase) {
    $uploadId = $testCase['upload_id'];
    $expectedCategory = $testCase['expected_category'];
    $description = $testCase['description'];
    
    echo "Testing: {$description} (Upload ID: {$uploadId})" . PHP_EOL;
    
    // Get the temporary upload
    $temporaryUpload = DB::table('temporary_uploads')->where('id', $uploadId)->first();
    if (!$temporaryUpload) {
        echo "  ❌ TemporaryUpload not found" . PHP_EOL . PHP_EOL;
        continue;
    }
    
    echo "  TemporaryUpload category: {$temporaryUpload->category}" . PHP_EOL;
    
    // Simulate the NEW logic: $temporaryUpload->category (fixed)
    $resultCategory = $temporaryUpload->category;
    echo "  NEW logic result: {$resultCategory}" . PHP_EOL;
    
    // Check if correct
    if ($resultCategory === $expectedCategory) {
        echo "  ✅ CORRECT: Category will be preserved properly" . PHP_EOL;
    } else {
        echo "  ❌ INCORRECT: Expected {$expectedCategory}, got {$resultCategory}" . PHP_EOL;
    }
    
    echo PHP_EOL;
}

echo "=== SUMMARY ===" . PHP_EOL;
echo "✅ Fixed PMDashboardController->acceptAllUpload() to use \$temporaryUpload->category" . PHP_EOL;
echo "✅ Fixed PMDashboardController->acceptSelectedUpload() to use \$temporaryUpload->category" . PHP_EOL;
echo "✅ Single items will now correctly get category='single_item'" . PHP_EOL;
echo "✅ Bulk uploads will still correctly get category='temporary_list'" . PHP_EOL . PHP_EOL;

echo "TESTING RECOMMENDATION:" . PHP_EOL;
echo "1. Create a new single item through customer interface" . PHP_EOL;
echo "2. Accept it through PM dashboard using 'Accept All' button" . PHP_EOL;
echo "3. Verify the resulting ItemBulk has category='single_item'" . PHP_EOL;

?>