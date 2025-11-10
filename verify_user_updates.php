<?php

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== VERIFICATION: User Type Updates ===\n\n";

// Check users 11 and 12
$users = User::whereIn('id', [11, 12])->get();

if ($users->isEmpty()) {
    echo "❌ No users found with IDs 11 and 12\n";
    exit(1);
}

echo "Current status of updated users:\n";
echo "================================\n";

foreach ($users as $user) {
    echo "📋 User ID: {$user->id}\n";
    echo "   Name: {$user->name}\n";
    echo "   NIC: {$user->nic}\n";
    echo "   Email: " . ($user->email ?: 'N/A') . "\n";
    echo "   User Type: {$user->user_type} 🔄\n";
    echo "   Role: {$user->role}\n";
    echo "   Status: " . ($user->is_active ? 'Active ✅' : 'Inactive ❌') . "\n";
    echo "   Location: " . ($user->location ? $user->location->name : 'Not assigned') . "\n";
    echo "   Created: {$user->created_at->format('M d, Y')}\n";
    echo "---\n";
}

// Check all user type counts for context
echo "\n=== SYSTEM OVERVIEW ===\n";
$internalCount = User::where('user_type', 'internal')->count();
$externalCount = User::where('user_type', 'external')->count();
$totalCount = User::count();

echo "📊 User Type Distribution:\n";
echo "   Internal Users: {$internalCount}\n";
echo "   External Users: {$externalCount}\n";
echo "   Total Users: {$totalCount}\n";

echo "\n✅ Verification complete!\n";

// Check if admin panel access should work
echo "\n=== ACCESS VERIFICATION ===\n";
foreach ($users as $user) {
    echo "👤 {$user->name} (ID: {$user->id}):\n";
    echo "   - Can access internal systems: " . ($user->isInternal() ? 'Yes ✅' : 'No ❌') . "\n";
    echo "   - Is external customer: " . ($user->isExternalCustomer() ? 'Yes' : 'No ✅') . "\n";
    echo "   - Role permissions: {$user->role}\n\n";
}
