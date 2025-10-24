<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TemporaryUploadAssociate;
use App\Models\SmsSent;

echo "=== Database Status Check ===\n";

try {
    // Check recent TemporaryUploadAssociate records
    echo "\n1. Recent TemporaryUploadAssociate Records:\n";
    $associates = TemporaryUploadAssociate::latest()->take(5)->get();

    if ($associates->count() > 0) {
        foreach ($associates as $assoc) {
            echo "ID: {$assoc->id}, Name: {$assoc->receiver_name}, Contact: '{$assoc->contact_number}', Status: {$assoc->status}\n";
        }
    } else {
        echo "No TemporaryUploadAssociate records found.\n";
    }

    // Check recent SMS records
    echo "\n2. Recent SMS Records:\n";
    $smsRecords = SmsSent::latest()->take(5)->get();

    if ($smsRecords->count() > 0) {
        foreach ($smsRecords as $sms) {
            echo "SMS ID: {$sms->id}, Item ID: {$sms->item_id}, Receiver Mobile: '{$sms->receiver_mobile}', Status: {$sms->status}\n";
        }
    } else {
        echo "No SMS records found.\n";
    }

    // Check if there are any NULL receiver_mobile entries
    echo "\n3. SMS Records with empty receiver_mobile:\n";
    $nullSmsRecords = SmsSent::where(function($query) {
        $query->whereNull('receiver_mobile')
              ->orWhere('receiver_mobile', '');
    })->latest()->take(5)->get();

    if ($nullSmsRecords->count() > 0) {
        foreach ($nullSmsRecords as $sms) {
            echo "SMS ID: {$sms->id}, Item ID: {$sms->item_id}, Receiver Mobile: '{$sms->receiver_mobile}', Status: {$sms->status}\n";
        }
    } else {
        echo "No SMS records with empty receiver_mobile found.\n";
    }

    echo "\n=== Status Check Complete ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
