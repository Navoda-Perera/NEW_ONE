<?php

// Check PM user location
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Location;

echo "=== PM Users and Locations Check ===\n";

// Check all PM users
$pmUsers = User::where('role', 'pm')->get(['id', 'name', 'location_id']);
echo "PM Users:\n";
foreach ($pmUsers as $user) {
    $location = $user->location_id ? Location::find($user->location_id) : null;
    $locationName = $location ? $location->name : 'No location';
    echo "  - ID: {$user->id}, Name: {$user->name}, Location ID: {$user->location_id}, Location: {$locationName}\n";
}

echo "\nAll Locations:\n";
$locations = Location::all(['id', 'name']);
foreach ($locations as $location) {
    echo "  - ID: {$location->id}, Name: {$location->name}\n";
}

// Check the specific item's location
echo "\nItem 'bn675111' is in location ID: 9\n";
$location9 = Location::find(9);
if ($location9) {
    echo "Location ID 9 name: {$location9->name}\n";
} else {
    echo "Location ID 9 not found!\n";
}

?>
