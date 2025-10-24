<?php

use App\Models\TemporaryUploadAssociate;
use App\Models\SmsSent;

echo "=== Testing Database Records ===\n";

// Check recent TemporaryUploadAssociate records
echo "\n1. Recent TemporaryUploadAssociate Records:\n";
$associates = TemporaryUploadAssociate::latest()->take(5)->get();
foreach ($associates as $assoc) {
    echo "ID: {$assoc->id}, Name: {$assoc->receiver_name}, Contact: '{$assoc->contact_number}', Status: {$assoc->status}\n";
}

// Check recent SMS records
echo "\n2. Recent SMS Records:\n";
$smsRecords = SmsSent::latest()->take(5)->get();
foreach ($smsRecords as $sms) {
    echo "SMS ID: {$sms->id}, Item ID: {$sms->item_id}, Receiver Mobile: '{$sms->receiver_mobile}', Status: {$sms->status}\n";
}

// Check if there are any NULL receiver_mobile entries
echo "\n3. SMS Records with NULL receiver_mobile:\n";
$nullSmsRecords = SmsSent::whereNull('receiver_mobile')->orWhere('receiver_mobile', '')->latest()->take(5)->get();
foreach ($nullSmsRecords as $sms) {
    echo "SMS ID: {$sms->id}, Item ID: {$sms->item_id}, Receiver Mobile: '{$sms->receiver_mobile}', Status: {$sms->status}\n";
}

echo "\n=== Test Complete ===\n";