<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'General Post Office',
                'code' => 'GPO',
                'address' => 'D.R. Wijewardena Mawatha',
                'city' => 'Colombo',
                'province' => 'Western',
                'postal_code' => '10010',
                'phone' => '+94112326203',
                'is_active' => true,
            ],
            [
                'name' => 'Colombo Central Post Office',
                'code' => 'CO001',
                'address' => 'York Street',
                'city' => 'Colombo',
                'province' => 'Western',
                'postal_code' => '10001',
                'phone' => '+94112421171',
                'is_active' => true,
            ],
            [
                'name' => 'Kandy Post Office',
                'code' => 'KY001',
                'address' => 'Dalada Veediya',
                'city' => 'Kandy',
                'province' => 'Central',
                'postal_code' => '20000',
                'phone' => '+94812222334',
                'is_active' => true,
            ],
            [
                'name' => 'Galle Post Office',
                'code' => 'GL001',
                'address' => 'Main Street',
                'city' => 'Galle',
                'province' => 'Southern',
                'postal_code' => '80000',
                'phone' => '+94912234567',
                'is_active' => true,
            ],
            [
                'name' => 'Jaffna Post Office',
                'code' => 'JF001',
                'address' => 'Hospital Road',
                'city' => 'Jaffna',
                'province' => 'Northern',
                'postal_code' => '40000',
                'phone' => '+94212222345',
                'is_active' => true,
            ],
            [
                'name' => 'Anuradhapura Post Office',
                'code' => 'AN001',
                'address' => 'Maithripala Senanayake Mawatha',
                'city' => 'Anuradhapura',
                'province' => 'North Central',
                'postal_code' => '50000',
                'phone' => '+94252222456',
                'is_active' => true,
            ],
            [
                'name' => 'Kurunegala Post Office',
                'code' => 'KU001',
                'address' => 'Colombo Road',
                'city' => 'Kurunegala',
                'province' => 'North Western',
                'postal_code' => '60000',
                'phone' => '+94372222567',
                'is_active' => true,
            ],
            [
                'name' => 'Ratnapura Post Office',
                'code' => 'RT001',
                'address' => 'Main Street',
                'city' => 'Ratnapura',
                'province' => 'Sabaragamuwa',
                'postal_code' => '70000',
                'phone' => '+94452222678',
                'is_active' => true,
            ],
            [
                'name' => 'Badulla Post Office',
                'code' => 'BD001',
                'address' => 'Lower Street',
                'city' => 'Badulla',
                'province' => 'Uva',
                'postal_code' => '90000',
                'phone' => '+94552222789',
                'is_active' => true,
            ],
            [
                'name' => 'Batticaloa Post Office',
                'code' => 'BT001',
                'address' => 'Main Road',
                'city' => 'Batticaloa',
                'province' => 'Eastern',
                'postal_code' => '30000',
                'phone' => '+94652222890',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
