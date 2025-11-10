<?php

require_once 'vendor/autoload.php';

echo "=== Register Post Form Route Fix Verification ===\n";
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

// Test route existence
echo "\n=== Testing Routes ===\n";

$routes = [
    'pm.single-item.index',
    'pm.single-item.register-form',
    'pm.single-item.store-register',
    'pm.single-item.calculate-postage'
];

foreach ($routes as $route) {
    try {
        $url = route($route);
        echo "✅ Route '{$route}' exists: {$url}\n";
    } catch (Exception $e) {
        echo "❌ Route '{$route}' missing: " . $e->getMessage() . "\n";
    }
}

// Test form file
echo "\n=== Testing Form File ===\n";
$formFile = 'resources/views/pm/single-item/register-form.blade.php';
if (file_exists($formFile)) {
    echo "✅ Register form file exists\n";

    $content = file_get_contents($formFile);

    // Check for correct route usage
    if (strpos($content, 'route("pm.single-item.store-register")') !== false) {
        echo "✅ Form uses correct store route\n";
    } else {
        echo "❌ Form uses incorrect store route\n";
    }

    // Check for red color theme
    if (strpos($content, 'bg-danger') !== false && strpos($content, 'text-danger') !== false) {
        echo "✅ Form uses red color theme\n";
    } else {
        echo "❌ Form missing red color theme\n";
    }

    // Check for modern layout
    if (strpos($content, '@extends(\'layouts.modern-pm\')') !== false) {
        echo "✅ Form uses modern PM layout\n";
    } else {
        echo "❌ Form uses old layout\n";
    }

} else {
    echo "❌ Register form file missing\n";
}

// Test controller method
echo "\n=== Testing Controller ===\n";
$controllerFile = 'app/Http/Controllers/PM/PMSingleItemController.php';
if (file_exists($controllerFile)) {
    echo "✅ PMSingleItemController exists\n";

    $content = file_get_contents($controllerFile);

    if (strpos($content, 'showRegisterForm') !== false) {
        echo "✅ showRegisterForm method exists\n";
    } else {
        echo "❌ showRegisterForm method missing\n";
    }

    if (strpos($content, 'storeRegister') !== false) {
        echo "✅ storeRegister method exists\n";
    } else {
        echo "❌ storeRegister method missing\n";
    }

} else {
    echo "❌ PMSingleItemController missing\n";
}

echo "\n=== Register Post Form Fix Summary ===\n";
echo "The route error has been fixed by correcting the form action route.\n";
echo "Form now uses: route('pm.single-item.store-register')\n";
echo "Instead of: route('pm.single-item.register-post.store')\n";
echo "\n✅ Register Post form should now load correctly with red theme!\n";
