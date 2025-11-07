<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Complete Session and Cache Reset\n";
echo "===============================\n\n";

try {
    // 1. Clear all sessions from database
    echo "1. Clearing all sessions from database...\n";
    $deletedSessions = DB::table('sessions')->count();
    DB::table('sessions')->truncate();
    echo "   Deleted {$deletedSessions} session(s)\n";

    // 2. Clear cache
    echo "\n2. Clearing application cache...\n";
    Artisan::call('cache:clear');
    echo "   " . Artisan::output();

    // 3. Clear config cache
    echo "3. Clearing config cache...\n";
    Artisan::call('config:clear');
    echo "   " . Artisan::output();

    // 4. Clear route cache
    echo "4. Clearing route cache...\n";
    Artisan::call('route:clear');
    echo "   " . Artisan::output();

    // 5. Clear view cache
    echo "5. Clearing view cache...\n";
    Artisan::call('view:clear');
    echo "   " . Artisan::output();

    echo "\n✅ Complete reset finished!\n";
    echo "\nNext steps:\n";
    echo "1. Restart the development server\n";
    echo "2. Try logging in with incognito/private mode\n";
    echo "3. Use these credentials:\n";
    echo "   Admin: admin / password123\n";
    echo "   PM: 199570896530 / password123\n";
    echo "   Customer: 123456789V / password123\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
