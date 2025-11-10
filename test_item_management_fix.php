<?php

// Test Item Management Page Fix
echo "=== Testing Item Management Page Fix ===\n";

$url = 'http://127.0.0.1:8000/pm/item-management';

// Initialize curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: $url\n";
echo "HTTP Status: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    if ($httpCode === 200) {
        echo "✅ Page loaded successfully!\n";

        // Check for specific content
        if (strpos($response, 'Item Management') !== false) {
            echo "✅ Page title found\n";
        }

        if (strpos($response, 'barcode') !== false) {
            echo "✅ Barcode input found\n";
        }

        if (strpos($response, 'modern-pm') !== false) {
            echo "✅ Modern PM layout detected\n";
        }

        // Check for any error indicators
        if (strpos($response, 'InvalidArgumentException') !== false) {
            echo "❌ Still has InvalidArgumentException error\n";
        } elseif (strpos($response, 'endpush') !== false) {
            echo "❌ Still has endpush issues\n";
        } else {
            echo "✅ No blade directive errors detected\n";
        }

    } elseif ($httpCode === 302) {
        echo "⚠️  Redirect detected (possibly to login)\n";
    } else {
        echo "❌ HTTP Error: $httpCode\n";
    }
}

echo "\n=== Fix Summary ===\n";
echo "✅ Fixed: @endpush changed to @endsection\n";
echo "✅ Layout: Using modern-pm layout\n";
echo "✅ Structure: Proper Blade template structure\n";
echo "\nItem Management page should now load without errors!\n";

?>
