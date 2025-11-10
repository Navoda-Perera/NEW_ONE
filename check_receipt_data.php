<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\Receipt;

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

echo "=== Latest Receipt Data ===\n";

$receipt = Receipt::with('itemBulk.items')->latest()->first();
if ($receipt) {
    echo "Receipt ID: {$receipt->id}\n";
    echo "Amount (COD): {$receipt->amount}\n";
    echo "Postage: {$receipt->postage}\n";
    echo "Total Amount: {$receipt->total_amount}\n";
    if ($receipt->itemBulk) {
        echo "Service Type: {$receipt->itemBulk->service_type}\n";
        echo "Item Count: {$receipt->itemBulk->items->count()}\n";
    }
} else {
    echo "No receipts found\n";
}

// Let's also check a few more receipts
echo "\n=== Last 3 Receipts ===\n";
$receipts = Receipt::with('itemBulk')->latest()->take(3)->get();
foreach ($receipts as $r) {
    echo "ID: {$r->id}, Amount: {$r->amount}, Postage: {$r->postage}, Total: {$r->total_amount}, Service: " . ($r->itemBulk->service_type ?? 'N/A') . "\n";
}
?>
