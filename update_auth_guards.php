<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Updating Auth::user() references to use appropriate guards\n";
echo "======================================================\n\n";

// List of files to update and their corresponding guards
$files = [
    // PM Controllers
    'app/Http/Controllers/PM/PMDashboardController.php' => 'pm',
    'app/Http/Controllers/PM/PMItemController.php' => 'pm',
    'app/Http/Controllers/PM/PMSingleItemController.php' => 'pm',

    // Admin Controllers
    'app/Http/Controllers/Admin/AdminDashboardController.php' => 'admin',

    // Customer Controllers
    'app/Http/Controllers/Customer/CustomerDashboardController.php' => 'customer',
    'app/Http/Controllers/Customer/CustomerReceiptController.php' => 'customer',
];

foreach ($files as $file => $guard) {
    $fullPath = "c:\\Users\\User\\Desktop\\NEW_ONE\\{$file}";

    if (file_exists($fullPath)) {
        echo "Processing: {$file} (guard: {$guard})\n";

        $content = file_get_contents($fullPath);

        // Count occurrences before replacement
        $beforeCount = substr_count($content, 'Auth::user()');

        // Replace Auth::user() with Auth::guard('guard')->user()
        $newContent = str_replace('Auth::user()', "Auth::guard('{$guard}')->user()", $content);

        // Count occurrences after replacement
        $afterCount = substr_count($newContent, 'Auth::user()');
        $replacedCount = $beforeCount - $afterCount;

        if ($replacedCount > 0) {
            file_put_contents($fullPath, $newContent);
            echo "  → Replaced {$replacedCount} occurrence(s)\n";
        } else {
            echo "  → No changes needed\n";
        }
    } else {
        echo "File not found: {$fullPath}\n";
    }
}

echo "\n✅ Auth::user() guard updates completed!\n";
