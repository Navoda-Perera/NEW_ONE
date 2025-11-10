<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\TemporaryUploadAssociate;

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

echo "=== Temporary Upload Associates Data ===\n";

// Get recent temporary upload associates
$tempItems = TemporaryUploadAssociate::with('temporaryUpload')
    ->latest()
    ->take(5)
    ->get();

foreach ($tempItems as $item) {
    echo "ID: {$item->id}\n";
    echo "Service Type: {$item->service_type}\n";
    echo "Amount: {$item->amount}\n";
    echo "Postage: {$item->postage}\n";
    echo "Weight: {$item->weight}\n";
    echo "Status: {$item->status}\n";
    echo "Barcode: {$item->barcode}\n";
    echo "---\n";
}

// Let's also check what postage values we have
echo "\n=== Postage Values Summary ===\n";
$postageStats = TemporaryUploadAssociate::selectRaw('service_type, AVG(postage) as avg_postage, COUNT(*) as count')
    ->groupBy('service_type')
    ->get();

foreach ($postageStats as $stat) {
    echo "Service: {$stat->service_type}, Avg Postage: {$stat->avg_postage}, Count: {$stat->count}\n";
}
?>
