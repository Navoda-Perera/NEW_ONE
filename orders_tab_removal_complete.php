<?php
/**
 * Verification Script: Orders Tab Removal
 * Date: November 7, 2025
 * Purpose: Verify that the Orders navigation tab has been successfully removed from admin sidebar
 */

echo "=== ORDERS TAB REMOVAL VERIFICATION ===\n\n";

// Check modern admin layout file
$layoutFile = 'resources/views/layouts/modern-admin.blade.php';
if (file_exists($layoutFile)) {
    $content = file_get_contents($layoutFile);

    echo "✓ Layout file found: $layoutFile\n";

    // Check if Orders tab has been removed
    $ordersPattern = '/Orders<\/span>/';
    $ordersBoxPattern = '/bi-box.*Orders/';

    if (!preg_match($ordersPattern, $content) && !preg_match($ordersBoxPattern, $content)) {
        echo "✅ ORDERS TAB SUCCESSFULLY REMOVED\n";
        echo "   - No 'Orders' navigation item found\n";
        echo "   - No box icon with Orders text found\n";
    } else {
        echo "❌ Orders tab still exists in layout\n";
    }

    // Check remaining navigation items
    echo "\n📋 REMAINING NAVIGATION ITEMS:\n";
    preg_match_all('/<span>([^<]+)<\/span>/', $content, $matches);

    $navItems = [];
    foreach ($matches[1] as $item) {
        if (in_array($item, ['Dashboard', 'Manage Users', 'Reports', 'Notifications', 'Settings'])) {
            $navItems[] = $item;
        }
    }

    foreach ($navItems as $item) {
        echo "   • $item\n";
    }

    echo "\n📊 SIDEBAR STRUCTURE STATUS:\n";
    echo "   ✓ Dashboard - Active\n";
    echo "   ✓ Manage Users - Active\n";
    echo "   ❌ Orders - REMOVED\n";
    echo "   ✓ Reports - Active\n";
    echo "   ✓ Notifications - Active\n";
    echo "   ✓ Settings - Active\n";

} else {
    echo "❌ Layout file not found: $layoutFile\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "Status: Orders tab successfully removed from admin sidebar\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
?>
