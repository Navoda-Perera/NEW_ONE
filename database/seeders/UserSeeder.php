<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create additional sample users for development/testing

        // Additional admin user
        User::create([
            'name' => 'Secondary Admin',
            'nic' => '199512345678',
            'email' => 'admin2@system.com',
            'mobile' => '0771234567',
            'password' => Hash::make('admin123'),
            'user_type' => 'internal',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Additional postmaster users
        $kandyLocation = \App\Models\Location::where('code', 'KY001')->first();

        User::create([
            'name' => 'Sarah Postmaster',
            'nic' => '199156789012',
            'email' => 'sarah.postmaster@post.gov',
            'mobile' => '0772345678',
            'password' => Hash::make('pm123'),
            'user_type' => 'internal',
            'role' => 'pm',
            'location_id' => $kandyLocation ? $kandyLocation->id : null,
            'is_active' => true,
        ]);

        // Additional customers with diverse company types
        $additionalCustomers = [
            [
                'name' => 'Global Trading Manager',
                'nic' => '199312345678',
                'email' => 'manager@globaltrading.com',
                'mobile' => '0773456789',
                'company_name' => 'Global Trading Company',
                'company_br' => 'PV00345678',
                'is_active' => true,
            ],
            [
                'name' => 'Logistics Hub Contact',
                'nic' => '198912345678',
                'email' => 'contact@logisticshub.com',
                'mobile' => '0774567890',
                'company_name' => 'Logistics Hub (Pvt) Ltd',
                'company_br' => 'PV00567890',
                'is_active' => true,
            ],
            [
                'name' => 'Manufacturing Corp Rep',
                'nic' => '199412345678',
                'email' => null,
                'mobile' => '0775678901',
                'company_name' => 'Manufacturing Corporation',
                'company_br' => 'PV00111222',
                'is_active' => true,
            ],
        ];

        foreach ($additionalCustomers as $customer) {
            User::create([
                'name' => $customer['name'],
                'nic' => $customer['nic'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile'],
                'company_name' => $customer['company_name'],
                'company_br' => $customer['company_br'],
                'password' => Hash::make('customer123'),
                'user_type' => 'external',
                'role' => 'customer',
                'is_active' => $customer['is_active'],
            ]);
        }
    }
}
