<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed locations first
        $this->call(LocationSeeder::class);

        // Seed service types and pricing
        $this->call(ServiceTypeSeeder::class);
        $this->call(SlpPricingSeeder::class);
        $this->call(PostPricingSeeder::class);

        // Create sample admin user
        User::create([
            'name' => 'Admin User',
            'nic' => '199012345678',
            'email' => 'admin@example.com',
            'mobile' => '0701234567',
            'password' => Hash::make('admin123'),
            'user_type' => 'internal',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Get locations for assignment
        $colomboLocation = \App\Models\Location::where('code', 'GPO')->first();
        $kandyLocation = \App\Models\Location::where('code', 'KY001')->first();

        // Create sample PM user with location assignment
        User::create([
            'name' => 'John Postmaster',
            'nic' => '199087654321',
            'email' => 'postmaster@example.com',
            'mobile' => '0702345678',
            'password' => Hash::make('pm123'),
            'user_type' => 'internal',
            'role' => 'pm',
            'location_id' => $colomboLocation ? $colomboLocation->id : null,
            'is_active' => true,
        ]);

        // Create sample customer user
        User::create([
            'name' => 'ABC Company Manager',
            'nic' => '199112345678',
            'email' => 'manager@abccompany.com',
            'mobile' => '0703456789',
            'company_name' => 'ABC Private Limited',
            'company_br' => 'PV00123456',
            'password' => Hash::make('customer123'),
            'user_type' => 'external',
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Create additional sample customers
        User::create([
            'name' => 'XYZ Corporation Contact',
            'nic' => '198812345678',
            'email' => 'contact@xyzcorp.com',
            'mobile' => '0704567890',
            'company_name' => 'XYZ Corporation',
            'company_br' => 'PV00654321',
            'password' => Hash::make('customer123'),
            'user_type' => 'external',
            'role' => 'customer',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Tech Solutions Rep',
            'nic' => '199212345678',
            'email' => null, // Optional email
            'mobile' => '0705678901',
            'company_name' => 'Tech Solutions (Pvt) Ltd',
            'company_br' => 'PV00789012',
            'password' => Hash::make('customer123'),
            'user_type' => 'external',
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Create an inactive customer for testing
        User::create([
            'name' => 'Inactive Company User',
            'nic' => '198712345678',
            'email' => 'inactive@company.com',
            'mobile' => '0706789012',
            'company_name' => 'Inactive Company Ltd',
            'company_br' => 'PV00999999',
            'password' => Hash::make('customer123'),
            'user_type' => 'external',
            'role' => 'customer',
            'is_active' => false, // Inactive user for testing
        ]);

        // Seed companies, postmen, and sample items
        $this->call([
            CompanySeeder::class,
            PostmanSeeder::class,
            ItemSeeder::class,
        ]);
    }
}
