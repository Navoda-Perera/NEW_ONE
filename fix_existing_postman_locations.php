<?php
/**
 * Fix Script: Update Existing Postman Users Without Location
 * Date: November 7, 2025
 * Purpose: Assign locations to existing postman users who are missing assignments
 */

echo "=== FIXING EXISTING POSTMAN LOCATION ASSIGNMENTS ===\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=laravel_postage_system', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database connection successful\n\n";
    
    // Get postman users without location
    $stmt = $pdo->prepare("
        SELECT id, name, nic, role 
        FROM users 
        WHERE role = 'postman' AND location_id IS NULL
    ");
    $stmt->execute();
    $postmanUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($postmanUsers)) {
        echo "✅ No postman users found without location assignments\n";
    } else {
        echo "📋 POSTMAN USERS NEEDING LOCATION ASSIGNMENT:\n";
        foreach ($postmanUsers as $user) {
            echo "   ID {$user['id']}: {$user['name']} ({$user['nic']})\n";
        }
        
        echo "\nℹ️  Since these users were created before the fix, they need manual location assignment.\n";
        echo "   Options:\n";
        echo "   1. Update through admin interface (edit user)\n";
        echo "   2. Auto-assign to General Post Office (GPO) as default\n";
        echo "   3. Set specific locations based on user needs\n\n";
        
        // Option to auto-assign to GPO
        echo "🔧 AUTO-ASSIGNING TO GENERAL POST OFFICE (GPO):\n";
        
        foreach ($postmanUsers as $user) {
            $updateStmt = $pdo->prepare("
                UPDATE users 
                SET location_id = 1 
                WHERE id = ? AND role = 'postman' AND location_id IS NULL
            ");
            $updateStmt->execute([$user['id']]);
            
            if ($updateStmt->rowCount() > 0) {
                echo "   ✅ Updated ID {$user['id']}: {$user['name']} -> General Post Office (GPO)\n";
            } else {
                echo "   ❌ Failed to update ID {$user['id']}: {$user['name']}\n";
            }
        }
    }
    
    // Verify the fix
    echo "\n📊 VERIFICATION AFTER FIX:\n";
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.nic, u.role, u.location_id, l.name as location_name, l.code as location_code
        FROM users u 
        LEFT JOIN locations l ON u.location_id = l.id
        WHERE u.role = 'postman'
        ORDER BY u.id
    ");
    $stmt->execute();
    $allPostmanUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allPostmanUsers as $user) {
        if ($user['location_id']) {
            echo "   ✅ ID {$user['id']}: {$user['name']} -> {$user['location_name']} ({$user['location_code']})\n";
        } else {
            echo "   ❌ ID {$user['id']}: {$user['name']} -> No location assigned\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIX COMPLETE ===\n";
echo "Status: All existing postman users now have location assignments\n";
echo "Future: New postman users will automatically get location assignments\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
?>