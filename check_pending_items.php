<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\TemporaryUploadAssociate;
use App\Models\TemporaryUpload;

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

echo "=== Pending Items Analysis ===\n";

// Get pending items
$pendingItems = TemporaryUploadAssociate::where('status', 'pending')
    ->whereNotNull('barcode')
    ->where('barcode', '!=', '')
    ->get();

echo "Pending items with barcodes: " . count($pendingItems) . "\n\n";

foreach ($pendingItems->take(5) as $item) {
    echo "ID: {$item->id}\n";
    echo "Service Type: {$item->service_type}\n";
    echo "Amount: {$item->amount}\n";
    echo "Postage: {$item->postage}\n";
    echo "Weight: {$item->weight}\n";
    echo "Barcode: {$item->barcode}\n";
    echo "---\n";
}

// Let's also check if we have any recent temporary uploads with pending items
echo "\n=== Recent Temporary Uploads ===\n";
$uploads = TemporaryUpload::latest()->take(3)->get();
foreach ($uploads as $upload) {
    $pendingCount = TemporaryUploadAssociate::where('temporary_id', $upload->id)
        ->where('status', 'pending')
        ->count();
    
    $totalPostage = TemporaryUploadAssociate::where('temporary_id', $upload->id)
        ->where('status', 'pending')
        ->sum('postage');
    
    echo "Upload ID: {$upload->id}, User: {$upload->user->name}, Pending Items: {$pendingCount}, Total Postage: {$totalPostage}\n";
}
?>