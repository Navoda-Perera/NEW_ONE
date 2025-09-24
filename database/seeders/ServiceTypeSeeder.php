<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            [
                'name' => 'Register Post',
                'code' => ServiceType::REGISTER_POST,
                'description' => 'Standard registered postal service with tracking',
                'is_active' => true,
                'has_weight_pricing' => false,
                'base_price' => 50.00,
            ],
            [
                'name' => 'SLP Courier',
                'code' => ServiceType::SLP_COURIER,
                'description' => 'Sri Lanka Post Courier service with weight-based pricing',
                'is_active' => true,
                'has_weight_pricing' => true, // SLP Courier uses weight-based pricing
                'base_price' => null,
            ],
            [
                'name' => 'COD (Cash on Delivery)',
                'code' => ServiceType::COD,
                'description' => 'Cash on Delivery service for payment collection',
                'is_active' => true,
                'has_weight_pricing' => false,
                'base_price' => 150.00,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::updateOrCreate(
                ['code' => $serviceType['code']],
                $serviceType
            );
        }
    }
}
