<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Updating auth()->user() references in Blade templates\n";
echo "=================================================\n\n";

// List of Blade files and their corresponding guards
$templateGuards = [
    // Admin templates
    'resources/views/admin/' => 'admin',

    // PM templates
    'resources/views/pm/' => 'pm',

    // Customer templates
    'resources/views/customer/' => 'customer',

    // Layout - needs special handling
    'resources/views/layouts/app.blade.php' => 'detect',
];

// Get all blade files
$bladeFiles = glob('resources/views/**/*.blade.php', GLOB_BRACE);

foreach ($bladeFiles as $file) {
    $guard = null;

    // Determine guard based on path
    if (strpos($file, 'resources/views/admin/') !== false) {
        $guard = 'admin';
    } elseif (strpos($file, 'resources/views/pm/') !== false) {
        $guard = 'pm';
    } elseif (strpos($file, 'resources/views/customer/') !== false) {
        $guard = 'customer';
    } elseif (strpos($file, 'resources/views/layouts/') !== false) {
        // Layout files need special handling - use auth() without guard
        $guard = null;
    }

    if ($guard) {
        $fullPath = "c:\\Users\\User\\Desktop\\NEW_ONE\\{$file}";

        if (file_exists($fullPath)) {
            echo "Processing: {$file} (guard: {$guard})\n";

            $content = file_get_contents($fullPath);

            // Count occurrences before replacement
            $beforeCount = substr_count($content, 'auth()->user()');

            // Replace auth()->user() with auth('guard')->user()
            $newContent = str_replace('auth()->user()', "auth('{$guard}')->user()", $content);

            // Count occurrences after replacement
            $afterCount = substr_count($newContent, 'auth()->user()');
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
}

echo "\n✅ Blade template auth() guard updates completed!\n";
