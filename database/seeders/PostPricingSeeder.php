<?php

namespace Database\Seeders;

use App\Models\PostPricing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing records
        PostPricing::truncate();

        // Register Post pricing data
        $pricingData = [
            // Weight ranges with Register prices only
            ['min' => 0, 'max' => 20, 'register' => 110],
            ['min' => 21, 'max' => 30, 'register' => 120],
            ['min' => 31, 'max' => 40, 'register' => 130],
            ['min' => 41, 'max' => 50, 'register' => 140],
            ['min' => 51, 'max' => 60, 'register' => 150],
            ['min' => 61, 'max' => 70, 'register' => 160],
            ['min' => 71, 'max' => 80, 'register' => 170],
            ['min' => 81, 'max' => 90, 'register' => 180],
            ['min' => 91, 'max' => 100, 'register' => 190],
            ['min' => 101, 'max' => 150, 'register' => 210],
            ['min' => 151, 'max' => 200, 'register' => 230],
            ['min' => 201, 'max' => 250, 'register' => 250],
            ['min' => 251, 'max' => 300, 'register' => 270],
            ['min' => 301, 'max' => 350, 'register' => 290],
            ['min' => 351, 'max' => 400, 'register' => 310],
            ['min' => 401, 'max' => 450, 'register' => 330],
            ['min' => 451, 'max' => 500, 'register' => 350],
            ['min' => 501, 'max' => 550, 'register' => 370],
            ['min' => 551, 'max' => 600, 'register' => 390],
            ['min' => 601, 'max' => 650, 'register' => 410],
            ['min' => 651, 'max' => 700, 'register' => 430],
            ['min' => 701, 'max' => 750, 'register' => 450],
            ['min' => 751, 'max' => 800, 'register' => 470],
            ['min' => 801, 'max' => 850, 'register' => 490],
            ['min' => 851, 'max' => 900, 'register' => 510],
            ['min' => 901, 'max' => 950, 'register' => 530],
            ['min' => 951, 'max' => 1000, 'register' => 550],
            ['min' => 1001, 'max' => 1250, 'register' => 580],
            ['min' => 1251, 'max' => 1500, 'register' => 610],
            ['min' => 1501, 'max' => 1750, 'register' => 640],
            ['min' => 1751, 'max' => 2000, 'register' => 670],
            // For weights above 2kg, use the 2kg price
            ['min' => 2001, 'max' => 10000, 'register' => 700],
        ];

        $records = [];
        foreach ($pricingData as $data) {
            // Register Post pricing only
            $records[] = [
                'service_type' => PostPricing::TYPE_REGISTER,
                'min_weight' => $data['min'],
                'max_weight' => $data['max'],
                'price' => $data['register'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all records
        PostPricing::insert($records);

        $this->command->info('Register Post pricing data seeded successfully.');
        $this->command->info('Total records created: ' . count($records));
        $this->command->info('Register Post tiers: ' . count($pricingData));
    }
}
