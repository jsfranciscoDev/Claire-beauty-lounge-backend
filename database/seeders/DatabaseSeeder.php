<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'id' => '1',
                'name' => 'admin',
                'email'=>'admin@gmail.com',
                'password' =>bcrypt('Admin@123'),
                'role_id' =>1,
            ],
        ];

        foreach ($items as $item) {
            \App\Models\User::updateOrCreate([
                'id' => $item['id'],
            ], $item);
        }

        $roles = [
            [
                'id' => '1',
                'role' => 'admin',
                'role_description' => 'admin',
            ],
            [
                'id' => '2',
                'role' => 'staff',
                'role_description' => 'staff',
            ],
            [
                'id' => '3',
                'role' => 'user',
                'role_description' => 'user',
            ],
        ];

        // Use the insert method to insert multiple records
         DB::table('user_roles')->insert($roles);
    }
}
