<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestItemBulk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-item-bulk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing ItemBulk model with new service_type structure...');

        // Test creating a record
        $bulk = \App\Models\ItemBulk::create([
            'sender_name' => 'Test Sender',
            'service_type' => 'register_post',
            'location_id' => 1,
            'created_by' => 1,
            'category' => 'single_item',
            'item_quantity' => 1
        ]);

        $this->info("Created ItemBulk ID: {$bulk->id}");
        $this->info("Service Type: {$bulk->service_type}");

        // Test the scope
        $registerPostCount = \App\Models\ItemBulk::registerPost()->count();
        $this->info("Register Post items count: {$registerPostCount}");

        // Test all available service types
        $this->info('Testing all service types...');
        $serviceTypes = ['register_post', 'slp_courier', 'cod', 'remittance'];
        foreach ($serviceTypes as $type) {
            $testBulk = \App\Models\ItemBulk::create([
                'sender_name' => "Test {$type}",
                'service_type' => $type,
                'location_id' => 1,
                'created_by' => 1,
                'category' => 'single_item',
                'item_quantity' => 1
            ]);
            $this->info("Created {$type} ItemBulk ID: {$testBulk->id}");
        }

        $totalCount = \App\Models\ItemBulk::count();
        $this->info("Total ItemBulk records: {$totalCount}");

        // Test all scopes
        $this->info('Testing service type scopes...');
        $this->info('Register Post count: ' . \App\Models\ItemBulk::registerPost()->count());
        $this->info('SLP Courier count: ' . \App\Models\ItemBulk::slpCourier()->count());
        $this->info('COD count: ' . \App\Models\ItemBulk::cod()->count());
        $this->info('Remittance count: ' . \App\Models\ItemBulk::remittance()->count());

        $this->info('ItemBulk test completed successfully!');
    }
}
