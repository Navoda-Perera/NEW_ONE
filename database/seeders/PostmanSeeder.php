<?php

namespace Database\Seeders;

use App\Models\Postman;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostmanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $postmasterUser = User::where('role', 'pm')->first();
        $locations = Location::all();

        $postmen = [
            [
                'name' => 'Kamal Perera',
                'nic' => '199012345678',
                'mobile' => '+94771111111',
                'paysheet_id' => 'PS001',
                'location_id' => $locations->first()->id,
                'created_by' => $postmasterUser->id,
                'status' => 'active',
                'postman_type' => 'permanent',
                'updated_by' => $postmasterUser->id,
            ],
            [
                'name' => 'Sunil Fernando',
                'nic' => '198834567890',
                'mobile' => '+94772222222',
                'paysheet_id' => 'PS002',
                'location_id' => $locations->skip(1)->first()->id,
                'created_by' => $postmasterUser->id,
                'status' => 'active',
                'postman_type' => 'temporary',
                'updated_by' => $postmasterUser->id,
            ],
            [
                'name' => 'Nimal Silva',
                'nic' => '199234567891',
                'mobile' => '+94773333333',
                'paysheet_id' => 'PS003',
                'location_id' => $locations->first()->id,
                'created_by' => $postmasterUser->id,
                'status' => 'active',
                'postman_type' => 'substitute',
                'updated_by' => $postmasterUser->id,
            ],
        ];

        foreach ($postmen as $postman) {
            Postman::create($postman);
        }
    }
}
