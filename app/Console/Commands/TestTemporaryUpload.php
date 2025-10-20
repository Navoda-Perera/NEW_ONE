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

        // Test creating TemporaryUpload records with different categories
        $categories = ['single_item', 'temporary_list'];

        foreach ($categories as $category) {
            $temp = \App\Models\TemporaryUpload::create([
                'category' => $category,
                'location_id' => 1,
                'user_id' => 1
            ]);
            $this->info("Created TemporaryUpload ID: {$temp->id} with category: {$temp->category}");
        }

        $totalCount = \App\Models\TemporaryUpload::count();
        $this->info("Total TemporaryUpload records: {$totalCount}");

        $this->info('TemporaryUpload test completed successfully!');
    }
}
