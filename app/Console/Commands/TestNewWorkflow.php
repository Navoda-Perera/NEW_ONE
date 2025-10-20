<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;

class TestNewWorkflow extends Command
{
    protected $signature = 'app:test-new-workflow';
    protected $description = 'Test the new customer item workflow';

    public function handle()
    {
        $this->info('Testing new customer item workflow...');

        // Get a customer user
        $user = User::where('role', 'customer')->first();
        if (!$user) {
            $this->error('No customer users found!');
            return;
        }

        $this->info("Using customer: {$user->name} (ID: {$user->id})");

        // Create a test temporary upload
        $temporaryUpload = TemporaryUpload::create([
            'category' => 'temporary_list',
            'location_id' => $user->location_id ?? 1,
            'user_id' => $user->id,
        ]);

        $this->info("Created TemporaryUpload ID: {$temporaryUpload->id}");

        // Create associate record
        $associate = TemporaryUploadAssociate::create([
            'temporary_id' => $temporaryUpload->id,
            'sender_name' => $user->name,
            'receiver_name' => 'Test Receiver',
            'receiver_address' => 'Test Address, Colombo',
            'weight' => 250,
            'amount' => 100.00,
            'postage' => 25.00, // Calculated postage
            'barcode' => null, // PM will assign
            'status' => 'pending',
        ]);

        $this->info("Created TemporaryUploadAssociate ID: {$associate->id}");

        // Test the query used in customer items view
        $query = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('temporaryUpload');

        $items = $query->orderBy('created_at', 'desc')->get();

        $this->info("Found {$items->count()} items for customer");

        foreach ($items as $item) {
            $serviceType = $item->service_type ?? 'register_post';
            $this->info("- Item ID: {$item->id}, Receiver: {$item->receiver_name}, Status: {$item->status}, Service: {$serviceType}");
        }

        $this->info('New workflow test completed successfully!');
    }
}
