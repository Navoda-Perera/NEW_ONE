<?php

// Test search API directly
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Simulate a POST request to the search endpoint
$request = \Illuminate\Http\Request::create(
    '/pm/item-management/search-barcode',
    'POST',
    [
        'barcode' => 'bn675111',
        '_token' => 'test-token' // We'll need to handle this
    ]
);

// We need to authenticate as PM user
// Start a session and log in
$app->make('session')->start();

// Find the PM user and authenticate them
$pmUser = \App\Models\User::where('role', 'pm')->where('id', 10)->first(); // F V herath
if ($pmUser) {
    \Illuminate\Support\Facades\Auth::guard('pm')->login($pmUser);
    echo "Authenticated as: {$pmUser->name} (Location: {$pmUser->location_id})\n";
} else {
    echo "Could not find PM user\n";
    exit(1);
}

// Set up CSRF token
$request->session()->put('_token', 'test-token');
$request->headers->set('X-CSRF-TOKEN', 'test-token');

try {
    echo "Making search request for barcode: bn675111\n";

    // Create the controller instance and call the method directly
    $controller = new \App\Http\Controllers\PM\PMItemController();
    $response = $controller->searchByBarcode($request);

    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>
