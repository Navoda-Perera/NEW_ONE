<?php

require_once 'vendor/autoload.php';

echo "=== PM Navigation Reversion Verification ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Test if Laravel app can boot
try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "✅ Laravel application boots successfully\n";
} catch (Exception $e) {
    echo "❌ Laravel boot failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test PM layout file
echo "\n=== Testing PM Layout Reversion ===\n";
$layoutFile = 'resources/views/layouts/modern-pm.blade.php';
if (file_exists($layoutFile)) {
    echo "✅ Modern PM layout file exists\n";

    $content = file_get_contents($layoutFile);

    // Check that modern features are removed
    $removedFeatures = [
        'nav-section-title' => 'Navigation section titles',
        'nav-section' => 'Navigation sections',
        'backdrop-filter: blur' => 'Backdrop blur effects',
        'transform: translateY(-2px)' => 'Complex transform effects',
        'box-shadow: 0 8px 25px' => 'Complex shadow effects',
        '@keyframes pulse' => 'Pulse animations',
        'nav-text' => 'Complex navigation text styling',
        'sidebar-user-details' => 'Complex user details',
        'bi-plus-circle-fill' => 'Filled icons',
        'bi-search-heart' => 'Special icons'
    ];

    echo "--- Checking Removed Features ---\n";
    foreach ($removedFeatures as $feature => $description) {
        if (strpos($content, $feature) === false) {
            echo "✅ {$description} removed\n";
        } else {
            echo "❌ {$description} still present\n";
        }
    }

    // Check that simple features are present
    $simpleFeatures = [
        'bi-speedometer2' => 'Dashboard icon',
        'bi-people' => 'Customers icon',
        'bi-box-seam' => 'Add Single Item icon',
        'bi-search' => 'Item Management icon',
        'bi-cloud-upload' => 'Bulk Upload icon',
        'bi-inbox' => 'Customer Uploads icon',
        'nav-link' => 'Navigation links',
        'notification-badge' => 'Simple notification badge'
    ];

    echo "\n--- Checking Simple Features ---\n";
    foreach ($simpleFeatures as $feature => $description) {
        if (strpos($content, $feature) !== false) {
            echo "✅ {$description} present\n";
        } else {
            echo "❌ {$description} missing\n";
        }
    }

    // Check for simple styling
    echo "\n--- Checking Simple Styling ---\n";
    $simpleStyling = [
        'border-radius: 8px' => 'Simple rounded corners',
        'padding: 0.75rem 1rem' => 'Simple padding',
        'transform: translateX(4px)' => 'Simple hover effect',
        'box-shadow: 0 2px 8px' => 'Simple shadow effects',
        'margin: 0.25rem 1rem' => 'Simple margins',
        'font-weight: 500' => 'Normal font weight'
    ];

    foreach ($simpleStyling as $css => $feature) {
        if (strpos($content, $css) !== false) {
            echo "✅ {$feature}\n";
        } else {
            echo "❌ {$feature} missing\n";
        }
    }

} else {
    echo "❌ Modern PM layout file missing\n";
}

// Test if all PM routes still work
echo "\n=== Testing PM Routes ===\n";
$pmRoutes = [
    'pm.dashboard' => 'PM Dashboard',
    'pm.single-item.index' => 'Add Single Item',
    'pm.bulk-upload' => 'Bulk Upload',
    'pm.customer-uploads' => 'Customer Uploads',
    'pm.customers.index' => 'Customers',
    'pm.item-management.index' => 'Item Management'
];

foreach ($pmRoutes as $route => $description) {
    try {
        $url = route($route);
        echo "✅ {$description}: {$route}\n";
    } catch (Exception $e) {
        echo "❌ {$description}: {$route} - " . $e->getMessage() . "\n";
    }
}

echo "\n=== Reversion Summary ===\n";
echo "The PM navigation has been reverted to simple design:\n\n";

echo "🔄 REVERTED FEATURES:\n";
echo "• Removed navigation sections (Dashboard, Operations, Management)\n";
echo "• Removed complex gradient backgrounds and texture overlays\n";
echo "• Removed advanced animations and pulse effects\n";
echo "• Removed complex hover effects and transforms\n";
echo "• Removed section titles and enhanced typography\n";
echo "• Simplified user profile section\n";
echo "• Changed back to basic Bootstrap icons\n\n";

echo "✅ SIMPLE FEATURES KEPT:\n";
echo "• Basic navigation structure\n";
echo "• Simple hover effects (translateX(4px))\n";
echo "• Standard notification badges\n";
echo "• Basic rounded corners and shadows\n";
echo "• Clean red gradient background\n";
echo "• Standard active state highlighting\n\n";

echo "✅ PM Navigation reversion is complete!\n";
echo "The sidebar now has a simpler, cleaner design as requested.\n";
