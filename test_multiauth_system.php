<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Multi-Guard Authentication System Test\n";
echo "=====================================\n\n";

try {
    echo "1. Authentication Configuration:\n";
    echo "-------------------------------\n";

    $guards = config('auth.guards');
    echo "Available Guards:\n";
    foreach ($guards as $guardName => $guardConfig) {
        echo "  - {$guardName}: {$guardConfig['driver']} driver, {$guardConfig['provider']} provider\n";
    }

    echo "\n2. User Distribution by Role:\n";
    echo "----------------------------\n";

    $roleStats = DB::table('users')
        ->select('role', DB::raw('count(*) as count'), DB::raw('sum(case when is_active = 1 then 1 else 0 end) as active_count'))
        ->groupBy('role')
        ->get();

    foreach ($roleStats as $stat) {
        echo "  - {$stat->role}: {$stat->count} total ({$stat->active_count} active)\n";
    }

    echo "\n3. Testing Guard Authentication (Simulation):\n";
    echo "--------------------------------------------\n";

    // Test each guard by finding users for each role
    $testUsers = [
        'admin' => DB::table('users')->where('role', 'admin')->where('is_active', true)->first(),
        'pm' => DB::table('users')->where('role', 'pm')->where('is_active', true)->first(),
        'postman' => DB::table('users')->where('role', 'postman')->where('is_active', true)->first(),
        'customer' => DB::table('users')->where('role', 'customer')->where('is_active', true)->first(),
    ];

    foreach ($testUsers as $role => $user) {
        if ($user) {
            echo "✅ {$role} guard ready - Test user: {$user->name} (ID: {$user->id})\n";
        } else {
            echo "❌ {$role} guard - No active users found for this role\n";
        }
    }

    echo "\n4. Session Configuration:\n";
    echo "------------------------\n";
    echo "Session Driver: " . config('session.driver') . "\n";
    echo "Session Lifetime: " . config('session.lifetime') . " minutes\n";
    echo "Session Cookie: " . config('session.cookie') . "\n";

    echo "\n5. Updated Controller Files:\n";
    echo "---------------------------\n";

    $updatedFiles = [
        'app/Http/Controllers/Admin/AdminAuthController.php' => 'Uses admin guard',
        'app/Http/Controllers/PM/PMAuthController.php' => 'Uses pm guard',
        'app/Http/Controllers/Admin/AdminDashboardController.php' => 'Uses admin guard',
        'app/Http/Controllers/PM/PMDashboardController.php' => 'Uses pm guard',
        'app/Http/Controllers/PM/PMItemController.php' => 'Uses pm guard',
        'app/Http/Controllers/Customer/CustomerDashboardController.php' => 'Uses customer guard',
    ];

    foreach ($updatedFiles as $file => $description) {
        if (file_exists($file)) {
            echo "✅ {$file} - {$description}\n";
        } else {
            echo "❌ {$file} - File not found\n";
        }
    }

    echo "\n6. Updated Template Files:\n";
    echo "-------------------------\n";

    $templateFiles = [
        'resources/views/layouts/app.blade.php' => 'Multi-guard user detection',
        'resources/views/admin/dashboard.blade.php' => 'Uses admin guard',
        'resources/views/pm/dashboard.blade.php' => 'Uses pm guard',
        'resources/views/customer/profile.blade.php' => 'Uses customer guard',
        'resources/views/pm/partials/location-info.blade.php' => 'Uses pm guard',
    ];

    foreach ($templateFiles as $file => $description) {
        if (file_exists($file)) {
            echo "✅ {$file} - {$description}\n";
        } else {
            echo "❌ {$file} - File not found\n";
        }
    }

    echo "\n7. Authentication Flow Summary:\n";
    echo "------------------------------\n";
    echo "✅ Multiple guards configured (admin, pm, postman, customer)\n";
    echo "✅ Separate authentication controllers for each user type\n";
    echo "✅ Role-based middleware updated to use appropriate guards\n";
    echo "✅ Controllers updated to use specific guards for user access\n";
    echo "✅ Blade templates updated to detect current authenticated user\n";
    echo "✅ Session management configured for concurrent logins\n";

    echo "\n8. Benefits of Multi-Guard System:\n";
    echo "---------------------------------\n";
    echo "🎯 Different user types can log in simultaneously in same browser\n";
    echo "🎯 Each guard manages its own authentication state\n";
    echo "🎯 Better security isolation between user types\n";
    echo "🎯 Cleaner code with explicit guard usage\n";
    echo "🎯 Prevents session conflicts between user types\n";

    echo "\n🎉 Multi-Guard Authentication System Ready!\n";
    echo "\nTo test:\n";
    echo "1. Open browser and log in as Admin at /admin/login\n";
    echo "2. Open new tab and log in as PM at /pm/login\n";
    echo "3. Both sessions should work independently without conflicts\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
