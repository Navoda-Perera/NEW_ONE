<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $items = [
            [
                'barcode' => 'LK001234567890',
                'receiver_name' => 'Saman Kumara',
                'receiver_address' => 'No. 10, Temple Road, Kandy',
                'status' => 'accept',
                'weight' => 0.5,
                'amount' => 150.00,
                'updated_by' => $users->first()->id,
            ],
            [
                'barcode' => 'LK001234567891',
                'receiver_name' => 'Priya Jayasinghe',
                'receiver_address' => 'No. 25, Lake View, Galle',
                'status' => 'dispatched',
                'weight' => 1.2,
                'amount' => 250.00,
                'updated_by' => $users->first()->id,
            ],
            [
                'barcode' => 'LK001234567892',
                'receiver_name' => 'Rohitha Fernando',
                'receiver_address' => 'No. 78, Main Street, Matara',
                'status' => 'delivered',
                'weight' => 0.8,
                'amount' => 200.00,
                'updated_by' => $users->first()->id,
            ],
            [
                'barcode' => 'LK001234567893',
                'receiver_name' => 'Malini Perera',
                'receiver_address' => 'No. 12, Hospital Road, Jaffna',
                'status' => 'accept',
                'weight' => 2.0,
                'amount' => 350.00,
                'updated_by' => $users->first()->id,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
