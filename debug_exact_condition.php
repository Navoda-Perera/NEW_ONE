<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\TemporaryUploadAssociate;

echo "=== DEBUG THE EXACT CONDITION ===" . PHP_EOL . PHP_EOL;

// Get the exact item that had the problem
$itemId = 126; // TemporaryUploadAssociate ID
echo "Testing TemporaryUploadAssociate ID: {$itemId}" . PHP_EOL;

// Load the item exactly like the controller does
$item = TemporaryUploadAssociate::findOrFail($itemId);
echo "Item loaded successfully" . PHP_EOL;

// Get the temporaryUpload relationship exactly like the controller does
$temporaryUpload = $item->temporaryUpload;
echo "TemporaryUpload loaded via relationship" . PHP_EOL;

// Check the category value and type
echo "Category value: '" . $temporaryUpload->category . "'" . PHP_EOL;
echo "Category type: " . gettype($temporaryUpload->category) . PHP_EOL;
echo "Category length: " . strlen($temporaryUpload->category) . PHP_EOL;

// Test the exact condition from the controller
echo PHP_EOL . "Testing the condition:" . PHP_EOL;
echo "temporaryUpload->category === 'temporary_list': " . ($temporaryUpload->category === 'temporary_list' ? 'TRUE' : 'FALSE') . PHP_EOL;
echo "temporaryUpload->category === 'single_item': " . ($temporaryUpload->category === 'single_item' ? 'TRUE' : 'FALSE') . PHP_EOL;

// Check for any hidden characters
echo PHP_EOL . "Hex dump of category:" . PHP_EOL;
echo bin2hex($temporaryUpload->category) . PHP_EOL;

// Compare with manual strings
echo PHP_EOL . "Manual comparisons:" . PHP_EOL;
echo "Raw category: " . var_export($temporaryUpload->category, true) . PHP_EOL;
echo "Expected 'single_item': " . var_export('single_item', true) . PHP_EOL;
echo "Strict comparison result: " . var_export($temporaryUpload->category === 'single_item', true) . PHP_EOL;

// Check if there's any whitespace
$trimmed = trim($temporaryUpload->category);
echo "Trimmed category: '" . $trimmed . "'" . PHP_EOL;
echo "Trimmed === 'single_item': " . ($trimmed === 'single_item' ? 'TRUE' : 'FALSE') . PHP_EOL;

echo PHP_EOL . "=== SIMULATION ===" . PHP_EOL;
echo "If this condition is used:" . PHP_EOL;
echo "if (\$temporaryUpload->category === 'temporary_list') {" . PHP_EOL;
echo "    // Use temporary_list" . PHP_EOL;
echo "} else {" . PHP_EOL;
echo "    // Use single_item" . PHP_EOL;
echo "}" . PHP_EOL . PHP_EOL;

if ($temporaryUpload->category === 'temporary_list') {
    echo "RESULT: Would use 'temporary_list' ❌" . PHP_EOL;
} else {
    echo "RESULT: Would use 'single_item' ✅" . PHP_EOL;
}

?>
