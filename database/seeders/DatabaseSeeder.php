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
        // Skip locations if they already exist to avoid conflicts
        try {
            $this->call(LocationSeeder::class);
        } catch (\Exception $e) {
            echo "Locations already exist, skipping LocationSeeder\n";
        }

        // Seed pricing data
        $this->call(SlpPricingSeeder::class);
        $this->call(PostPricingSeeder::class);

        // Create sample admin user
        try {
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
            echo "Admin user created successfully\n";
        } catch (\Exception $e) {
            echo "Admin user already exists or error: " . $e->getMessage() . "\n";
        }

        // Get locations for assignment
        $colomboLocation = \App\Models\Location::where('code', 'GPO')->first();
        $kandyLocation = \App\Models\Location::where('code', 'KY001')->first();

        // Create sample PM user with location assignment
        try {
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
            echo "PM user created successfully\n";
        } catch (\Exception $e) {
            echo "PM user already exists or error: " . $e->getMessage() . "\n";
        }

        // Create sample customer user
        try {
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
            echo "Customer user created successfully\n";
        } catch (\Exception $e) {
            echo "Customer user already exists or error: " . $e->getMessage() . "\n";
        }

        // Create additional sample customers
        try {
            User::create([
                'name' => 'XYZ Corporation Contact',
                'nic' => '198812345679', // Changed to make unique
                'email' => 'contact@xyzcorp.com',
                'mobile' => '0704567890',
                'company_name' => 'XYZ Corporation',
                'company_br' => 'PV00654321',
                'password' => Hash::make('customer123'),
                'user_type' => 'external',
                'role' => 'customer',
                'is_active' => true,
            ]);
            echo "XYZ Corporation user created successfully\n";
        } catch (\Exception $e) {
            echo "XYZ Corporation user already exists or error: " . $e->getMessage() . "\n";
        }

        try {
            User::create([
                'name' => 'Tech Solutions Rep',
                'nic' => '199212345680', // Changed to make unique
                'email' => null, // Optional email
                'mobile' => '0705678901',
                'company_name' => 'Tech Solutions (Pvt) Ltd',
                'company_br' => 'PV00789012',
                'password' => Hash::make('customer123'),
                'user_type' => 'external',
                'role' => 'customer',
                'is_active' => true,
            ]);
            echo "Tech Solutions user created successfully\n";
        } catch (\Exception $e) {
            echo "Tech Solutions user already exists or error: " . $e->getMessage() . "\n";
        }

        // Create an inactive customer for testing
        try {
            User::create([
                'name' => 'Inactive Company User',
                'nic' => '198712345681', // Changed to make unique
                'email' => 'inactive@company.com',
                'mobile' => '0706789012',
                'company_name' => 'Inactive Company Ltd',
                'company_br' => 'PV00999999',
                'password' => Hash::make('customer123'),
                'user_type' => 'external',
                'role' => 'customer',
                'is_active' => false, // Inactive user for testing
            ]);
            echo "Inactive user created successfully\n";
        } catch (\Exception $e) {
            echo "Inactive user already exists or error: " . $e->getMessage() . "\n";
        }

        // Seed companies, postmen, and sample items
        try {
            $this->call([
                CompanySeeder::class,
                PostmanSeeder::class,
                ItemSeeder::class,
            ]);
            echo "Additional seeders completed successfully\n";
        } catch (\Exception $e) {
            echo "Some seeders may have conflicts: " . $e->getMessage() . "\n";
        }
    }
}
