<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\TemporaryUpload;

echo "Testing Customer Dashboard Data...\n\n";

// Get a customer user
$customer = User::where('user_type', 'external')->first();

if ($customer) {
    echo "Customer: {$customer->name} (ID: {$customer->id})\n\n";

    // Test statistics using TemporaryUpload
    $temporaryUploads = TemporaryUpload::where('user_id', $customer->id)->get();

    $totalItems = 0;
    $pendingItems = 0;
    $acceptedItems = 0;
    $rejectedItems = 0;

    foreach ($temporaryUploads as $upload) {
        $itemCount = $upload->associates->count();
        $totalItems += $itemCount;

        if ($upload->status === 'pending') {
            $pendingItems += $itemCount;
        } elseif ($upload->status === 'accept') {
            $acceptedItems += $itemCount;
        } elseif ($upload->status === 'reject') {
            $rejectedItems += $itemCount;
        }
    }

    echo "📊 Statistics:\n";
    echo "✅ Total Items: {$totalItems}\n";
    echo "⏳ Pending: {$pendingItems}\n";
    echo "✅ Accepted: {$acceptedItems}\n";
    echo "❌ Rejected: {$rejectedItems}\n\n";

    // Test service breakdown from associates
    $serviceBreakdown = [];
    foreach ($temporaryUploads as $upload) {
        foreach ($upload->associates as $associate) {
            $serviceType = $associate->service_type;
            if (!isset($serviceBreakdown[$serviceType])) {
                $serviceBreakdown[$serviceType] = 0;
            }
            $serviceBreakdown[$serviceType]++;
        }
    }

    echo "🎯 Service Breakdown:\n";
    foreach ($serviceBreakdown as $service => $count) {
        $serviceName = match($service) {
            'cod' => 'COD',
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'remittance' => 'Remittance',
            default => ucfirst($service)
        };
        echo "   {$serviceName}: {$count}\n";
    }

    // Test recent uploads
    $recentUploads = TemporaryUpload::where('user_id', $customer->id)
        ->with('associates')
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();

    echo "\n📅 Recent Uploads:\n";
    foreach ($recentUploads as $upload) {
        $serviceType = $upload->associates->count() > 0 ? $upload->associates->first()->service_type : 'unknown';
        echo "   {$serviceType}: {$upload->associates->count()} items ({$upload->status}) - {$upload->created_at->diffForHumans()}\n";
    }

} else {
    echo "❌ No customer found.\n";
}

echo "\n✅ Dashboard data test completed!\n";
