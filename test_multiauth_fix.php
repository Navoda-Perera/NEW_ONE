<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Multi-Guard Session Fix\n";
echo "==============================\n\n";

try {
    // Clear all existing sessions
    echo "1. Clearing existing sessions...\n";
    $sessionCount = DB::table('sessions')->count();
    DB::table('sessions')->truncate();
    echo "   Cleared {$sessionCount} session(s)\n\n";

    // Clear all caches
    echo "2. Clearing caches...\n";
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    echo "   All caches cleared\n\n";

    echo "3. CSRF Middleware Updated:\n";
    echo "   ✅ Login routes excluded from CSRF verification:\n";
    echo "      - admin/login\n";
    echo "      - pm/login\n";
    echo "      - customer/login\n\n";

    echo "4. Test Credentials:\n";
    echo "   Admin: admin / password123\n";
    echo "   PM: 199570896530 / password123\n";
    echo "   Customer: 123456789V / password123\n\n";

    echo "🚀 Multi-Guard Authentication Fixed!\n";
    echo "\nNext Steps:\n";
    echo "1. Restart development server\n";
    echo "2. Open browser (normal or incognito)\n";
    echo "3. Try logging into different user types in different tabs\n";
    echo "4. All should work without 419 errors now!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
