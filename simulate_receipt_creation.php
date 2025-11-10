<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
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

echo "=== Simulating Receipt Creation with Pending Items ===\n";

// Get a temporary upload with pending items
$pendingItems = TemporaryUploadAssociate::where('status', 'pending')
    ->whereNotNull('barcode')
    ->where('barcode', '!=', '')
    ->get();

if ($pendingItems->count() > 0) {
    $temporaryUploadId = $pendingItems->first()->temporary_id;
    $pendingItemsForUpload = TemporaryUploadAssociate::where('temporary_id', $temporaryUploadId)
        ->where('status', 'pending')
        ->whereNotNull('barcode')
        ->where('barcode', '!=', '')
        ->get();

    echo "Temporary Upload ID: {$temporaryUploadId}\n";
    echo "Pending Items Count: " . $pendingItemsForUpload->count() . "\n";

    // Calculate what the receipt would show
    $totalCodAmount = 0;
    $totalPostage = 0;

    foreach ($pendingItemsForUpload as $item) {
        echo "\nItem ID: {$item->id}\n";
        echo "Service Type: {$item->service_type}\n";
        echo "Amount: {$item->amount}\n";
        echo "Postage: {$item->postage}\n";

        if ($item->service_type === 'cod') {
            $totalCodAmount += $item->amount;
        }
        $totalPostage += $item->postage;
    }

    echo "\n=== Receipt Calculation ===\n";
    echo "Total COD Amount: {$totalCodAmount}\n";
    echo "Total Postage: {$totalPostage}\n";
    echo "Combined Total: " . ($totalCodAmount + $totalPostage) . "\n";

    // Show what would be stored in receipt
    echo "\n=== Receipt Fields ===\n";
    echo "amount (COD): {$totalCodAmount}\n";
    echo "postage: {$totalPostage}\n";
    echo "total_amount: " . ($totalCodAmount + $totalPostage) . "\n";

} else {
    echo "No pending items found to test with.\n";
}
?>
