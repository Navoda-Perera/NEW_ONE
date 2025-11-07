<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "CSRF and Session Debug Test\n";
echo "===========================\n\n";

try {
    echo "1. Session Configuration:\n";
    echo "------------------------\n";
    echo "Session Driver: " . config('session.driver') . "\n";
    echo "Session Lifetime: " . config('session.lifetime') . " minutes\n";
    echo "Session Cookie: " . config('session.cookie') . "\n";
    echo "Session Domain: " . (config('session.domain') ?: 'null') . "\n";
    echo "Session Path: " . config('session.path') . "\n";
    echo "Session Secure: " . (config('session.secure') ? 'true' : 'false') . "\n";
    echo "Session HTTP Only: " . (config('session.http_only') ? 'true' : 'false') . "\n";
    echo "Session Same Site: " . (config('session.same_site') ?: 'null') . "\n";

    echo "\n2. Application Configuration:\n";
    echo "----------------------------\n";
    echo "APP_KEY set: " . (config('app.key') ? 'Yes' : 'No') . "\n";
    echo "APP_ENV: " . config('app.env') . "\n";
    echo "APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
    echo "APP_URL: " . config('app.url') . "\n";

    echo "\n3. Database Session Test:\n";
    echo "------------------------\n";

    // Test database connection
    try {
        DB::connection()->getPdo();
        echo "Database Connection: ✅ Working\n";

        // Check sessions table
        $sessionCount = DB::table('sessions')->count();
        echo "Sessions Table: ✅ Accessible (current sessions: {$sessionCount})\n";

        // Test session write
        $testSessionId = 'test_' . time();
        DB::table('sessions')->insert([
            'id' => $testSessionId,
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'payload' => base64_encode('test_data'),
            'last_activity' => time()
        ]);
        echo "Session Write Test: ✅ Working\n";

        // Clean up test session
        DB::table('sessions')->where('id', $testSessionId)->delete();
        echo "Session Delete Test: ✅ Working\n";

    } catch (Exception $e) {
        echo "Database Issue: ❌ " . $e->getMessage() . "\n";
    }

    echo "\n4. Authentication Guards:\n";
    echo "------------------------\n";
    $guards = config('auth.guards');
    foreach ($guards as $guardName => $guardConfig) {
        echo "- {$guardName}: {$guardConfig['driver']} driver\n";
    }

    echo "\n5. Potential Issues & Solutions:\n";
    echo "-------------------------------\n";

    $issues = [];

    // Check APP_KEY
    if (!config('app.key')) {
        $issues[] = "❌ APP_KEY not set - Run: php artisan key:generate";
    } else {
        echo "✅ APP_KEY is set\n";
    }

    // Check session driver
    if (config('session.driver') !== 'database') {
        $issues[] = "⚠️  Session driver is not 'database' - Current: " . config('session.driver');
    } else {
        echo "✅ Session driver is database\n";
    }

    // Check session cookie domain
    if (config('session.domain')) {
        $issues[] = "⚠️  Session domain is set - This might cause issues with localhost";
    } else {
        echo "✅ Session domain is null (good for localhost)\n";
    }

    // Check secure cookie setting
    if (config('session.secure') && config('app.env') === 'local') {
        $issues[] = "⚠️  Secure cookies enabled in local environment";
    } else {
        echo "✅ Secure cookie setting is appropriate\n";
    }

    if (empty($issues)) {
        echo "\n🎉 No obvious configuration issues found!\n";
        echo "\nTry these solutions:\n";
        echo "1. Clear all caches: php artisan cache:clear && php artisan config:clear\n";
        echo "2. Clear sessions: php artisan tinker --execute=\"DB::table('sessions')->truncate();\"\n";
        echo "3. Restart development server\n";
        echo "4. Try accessing admin login in incognito/private mode\n";
    } else {
        echo "\n⚠️  Found potential issues:\n";
        foreach ($issues as $issue) {
            echo "   {$issue}\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
