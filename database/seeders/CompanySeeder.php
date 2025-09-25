<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();
        $locations = Location::all();

        $companies = [
            [
                'name' => 'Lanka Express Couriers',
                'mobile' => '+94771234567',
                'address' => 'No. 123, Galle Road, Colombo 03',
                'email' => 'info@lankaexpress.lk',
                'type' => 'cash',
                'status' => 'ACTIVE',
                'assign_postoffice' => $locations->first()->id,
                'balance' => 50000.00,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Quick Post Services',
                'mobile' => '+94772345678',
                'address' => 'No. 456, Kandy Road, Kurunegala',
                'email' => 'contact@quickpost.lk',
                'type' => 'credit',
                'status' => 'ACTIVE',
                'assign_postoffice' => $locations->skip(1)->first()->id,
                'balance' => 75000.00,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Government Postal Bureau',
                'mobile' => '+94773456789',
                'address' => 'Ministry of Communications, Colombo 01',
                'email' => 'admin@postal.gov.lk',
                'type' => 'franking',
                'status' => 'ACTIVE',
                'assign_postoffice' => $locations->first()->id,
                'balance' => 100000.00,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
