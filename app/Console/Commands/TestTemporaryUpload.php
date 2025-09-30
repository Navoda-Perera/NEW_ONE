<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestTemporaryUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-temporary-upload';

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
        $this->info('Testing TemporaryUpload with service types...');

        // Test creating TemporaryUpload records with different service types
        $serviceTypes = ['register_post', 'slp_courier', 'cod', 'remittance'];

        foreach ($serviceTypes as $type) {
            $temp = \App\Models\TemporaryUpload::create([
                'service_type' => $type,
                'location_id' => 1,
                'user_id' => 1
            ]);
            $this->info("Created TemporaryUpload ID: {$temp->id} with service_type: {$temp->service_type}");
        }

        $totalCount = \App\Models\TemporaryUpload::count();
        $this->info("Total TemporaryUpload records: {$totalCount}");

        $this->info('TemporaryUpload test completed successfully!');
    }
}
