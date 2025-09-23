<?php

namespace Database\Seeders;

use App\Models\SlpPricing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlpPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Based on the SL Post Courier pricing chart
        $pricingTiers = [
            ['weight_from' => 0, 'weight_to' => 250, 'price' => 200],
            ['weight_from' => 251, 'weight_to' => 500, 'price' => 250],
            ['weight_from' => 501, 'weight_to' => 1000, 'price' => 350],
            ['weight_from' => 1001, 'weight_to' => 2000, 'price' => 400],
            ['weight_from' => 2001, 'weight_to' => 3000, 'price' => 450],
            ['weight_from' => 3001, 'weight_to' => 4000, 'price' => 500],
            ['weight_from' => 4001, 'weight_to' => 5000, 'price' => 550],
            ['weight_from' => 5001, 'weight_to' => 6000, 'price' => 600],
            ['weight_from' => 6001, 'weight_to' => 7000, 'price' => 650],
            ['weight_from' => 7001, 'weight_to' => 8000, 'price' => 700],
            ['weight_from' => 8001, 'weight_to' => 9000, 'price' => 750],
            ['weight_from' => 9001, 'weight_to' => 10000, 'price' => 800],
            ['weight_from' => 10001, 'weight_to' => 15000, 'price' => 850],
            ['weight_from' => 15001, 'weight_to' => 20000, 'price' => 1100],
            ['weight_from' => 20001, 'weight_to' => 25000, 'price' => 1600],
            ['weight_from' => 25001, 'weight_to' => 30000, 'price' => 2100],
            ['weight_from' => 30001, 'weight_to' => 35000, 'price' => 2600],
            ['weight_from' => 35001, 'weight_to' => 40000, 'price' => 3100],
        ];

        foreach ($pricingTiers as $tier) {
            SlpPricing::create([
                'weight_from' => $tier['weight_from'],
                'weight_to' => $tier['weight_to'],
                'price' => $tier['price'],
                'is_active' => true,
            ]);
        }
    }
}
