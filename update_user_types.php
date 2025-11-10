<?php

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "Updating user types for users with IDs 11 and 12...\n\n";

try {
    // Find users with IDs 11 and 12
    $users = User::whereIn('id', [11, 12])->get();

    if ($users->isEmpty()) {
        echo "❌ No users found with IDs 11 and 12\n";
        exit(1);
    }

    echo "Found users:\n";
    foreach ($users as $user) {
        echo "- ID: {$user->id}, Name: {$user->name}, Current Type: {$user->user_type}, Role: {$user->role}\n";
    }

    echo "\nUpdating user types to 'internal'...\n";

    $updated = User::whereIn('id', [11, 12])->update(['user_type' => 'internal']);

    echo "✅ Successfully updated {$updated} user(s)\n\n";

    // Verify the update
    echo "Verification - Updated users:\n";
    $updatedUsers = User::whereIn('id', [11, 12])->get();
    foreach ($updatedUsers as $user) {
        echo "- ID: {$user->id}, Name: {$user->name}, Type: {$user->user_type}, Role: {$user->role}\n";
    }

} catch (Exception $e) {
    echo "❌ Error updating users: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Update completed successfully!\n";
