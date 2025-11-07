<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Postman User Creation and Location Assignment\n";
echo "====================================================\n\n";

try {
    // Check if we have any locations for testing
    $locations = DB::table('locations')->get();
    echo "Available Locations: " . $locations->count() . "\n";

    if ($locations->count() > 0) {
        $firstLocation = $locations->first();
        echo "First Location: {$firstLocation->name} ({$firstLocation->code}) - {$firstLocation->city}\n\n";
    }

    // Check current postman users
    $postmanUsers = DB::table('users')
        ->where('role', 'postman')
        ->get();

    echo "Current Postman Users:\n";
    foreach ($postmanUsers as $user) {
        $locationName = 'No Location';
        if ($user->location_id) {
            $location = DB::table('locations')->where('id', $user->location_id)->first();
            if ($location) {
                $locationName = "{$location->name} ({$location->code})";
            }
        }
        echo "- {$user->name} (ID: {$user->id}) - Location: {$locationName}\n";
    }

    if ($postmanUsers->count() === 0) {
        echo "- No postman users found\n";
    }

    echo "\nPostman Role Validation Test:\n";
    echo "Expected: Postman users should have location_id assigned\n";
    echo "Actual: ";

    $postmanWithoutLocation = DB::table('users')
        ->where('role', 'postman')
        ->whereNull('location_id')
        ->count();

    if ($postmanWithoutLocation > 0) {
        echo "❌ Found {$postmanWithoutLocation} postman user(s) without location assignment\n";
    } else {
        echo "✅ All postman users have location assignments\n";
    }

    echo "\nUser Role Statistics:\n";
    $roleStats = DB::table('users')
        ->select('role', DB::raw('count(*) as count'))
        ->groupBy('role')
        ->get();

    foreach ($roleStats as $stat) {
        echo "- {$stat->role}: {$stat->count} users\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
