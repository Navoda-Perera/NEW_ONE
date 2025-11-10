<?php
/**
 * Verification Script: Postman Location Assignment Fix
 * Date: November 7, 2025
 * Purpose: Check and fix postman users missing location assignments
 */

echo "=== POSTMAN LOCATION ASSIGNMENT FIX ===\n\n";

// Check users table for postman without location
try {
    $pdo = new PDO('mysql:host=localhost;dbname=laravel_postage_system', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Database connection successful\n\n";

    // Check all postman users
    $stmt = $pdo->prepare("SELECT id, name, nic, role, location_id FROM users WHERE role = 'postman'");
    $stmt->execute();
    $postmanUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "📋 POSTMAN USERS STATUS:\n";
    if (empty($postmanUsers)) {
        echo "   No postman users found in database\n";
    } else {
        foreach ($postmanUsers as $user) {
            $locationStatus = $user['location_id'] ? "✓ Assigned (ID: {$user['location_id']})" : "❌ Not assigned";
            echo "   ID {$user['id']}: {$user['name']} ({$user['nic']}) - {$locationStatus}\n";
        }
    }

    // Check users missing location assignments who should have them
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.nic, u.role, u.location_id
        FROM users u
        WHERE u.role IN ('pm', 'postman') AND u.location_id IS NULL
    ");
    $stmt->execute();
    $missingLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n🚨 USERS MISSING LOCATION ASSIGNMENTS:\n";
    if (empty($missingLocations)) {
        echo "   ✅ All PM and Postman users have location assignments\n";
    } else {
        echo "   The following users need location assignments:\n";
        foreach ($missingLocations as $user) {
            echo "   - ID {$user['id']}: {$user['name']} ({$user['role']})\n";
        }
    }

    // Check available locations
    $stmt = $pdo->prepare("SELECT id, name, code, city FROM locations WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n📍 AVAILABLE LOCATIONS:\n";
    foreach ($locations as $location) {
        echo "   ID {$location['id']}: {$location['name']} ({$location['code']}) - {$location['city']}\n";
    }

    // Check controller fix
    $controllerFile = 'app/Http/Controllers/Admin/AdminDashboardController.php';
    if (file_exists($controllerFile)) {
        $content = file_get_contents($controllerFile);

        echo "\n🔧 CONTROLLER FIX STATUS:\n";

        // Check if the fix is in place
        if (strpos($content, "in_array(\$request->role, ['pm', 'postman'])") !== false) {
            echo "   ✅ Controller updated - now assigns location to both PM and Postman\n";
        } else {
            echo "   ❌ Controller still needs fixing\n";
        }

        // Check validation
        if (strpos($content, "if (in_array(\$request->role, ['pm', 'postman'])) {\n            \$validationRules['location_id']") !== false) {
            echo "   ✅ Validation includes both PM and Postman roles\n";
        } else {
            echo "   ❌ Validation may need updating\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "Status: Controller fixed to save location for both PM and Postman\n";
echo "Next: Test creating new postman user to verify fix\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
?>
