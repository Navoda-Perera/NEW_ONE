<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use App\Models\TemporaryUploadAssociate;
use App\Models\TemporaryUpload;
use App\Models\User;

// Initialize Laravel application
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== Finding PM for Pending Items ===\n";

// Get pending item details
$pendingItem = TemporaryUploadAssociate::with('temporaryUpload')
    ->where('status', 'pending')
    ->whereNotNull('barcode')
    ->where('barcode', '!=', '')
    ->first();

if ($pendingItem) {
    $upload = $pendingItem->temporaryUpload;
    echo "Upload ID: {$upload->id}\n";
    echo "Upload Location ID: {$upload->location_id}\n";
    
    // Find PM for this location
    $pm = User::where('role', 'pm')
        ->where('location_id', $upload->location_id)
        ->first();
    
    if ($pm) {
        echo "PM Found: {$pm->name} (ID: {$pm->id})\n";
        echo "PM Location ID: {$pm->location_id}\n";
        
        echo "\nPending Item Details:\n";
        echo "Barcode: {$pendingItem->barcode}\n";
        echo "Service Type: {$pendingItem->service_type}\n";
        echo "Amount: {$pendingItem->amount}\n";
        echo "Postage: {$pendingItem->postage}\n";
        echo "Weight: {$pendingItem->weight}\n";
        
        echo "\nThis PM can accept this item through the dashboard.\n";
    } else {
        echo "No PM found for location ID: {$upload->location_id}\n";
    }
} else {
    echo "No pending items found.\n";
}
?>