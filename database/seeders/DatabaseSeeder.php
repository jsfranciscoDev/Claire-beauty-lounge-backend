<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\UserProfile;

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
            [
                'id' => '2',
                'name' => 'Lexi Lore',
                'email'=>'user@gmail.com',
                'password' =>bcrypt('123456'),
                'role_id' =>3,
            ],
            [
                'id' => '3',
                'name' => 'John Doe',
                'email'=>'staff1@gmail.com',
                'contact'=> '09123456789',
                'password' =>bcrypt('123456'),
                'role_id' =>2,
                'staff_role' =>1,
            ],
            [
                'id' => '4',
                'name' => 'Gojo Saturo',
                'email'=>'staff2@gmail.com',
                'contact'=> '09123456789',
                'password' =>bcrypt('123456'),
                'role_id' =>2,
                'staff_role' =>2,
            ],
        ];



    
        DB::table('user_profile')->insert([
            'path' => 'storage/user/profile.png',
            'user_id' => 1,
            // Add other columns and their values as needed
        ],
        [
            'path' => 'storage/user/profile.png',
            'user_id' => 2,
            // Add other columns and their values as needed
        ]);
        
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

        $status = [
            [
                'detail' => 'Pending',
            ],
            [
                'detail' => 'Cancelled',
            ],
            [
                'detail' => 'Approved',
            ],
            [
                'detail' => 'Reschedule',
            ],
            [
                'detail' => 'Completed',
            ],
        ];

        DB::table('appointment_status')->insert($status);
        // Use the insert method to insert multiple records
         DB::table('user_roles')->insert($roles);
        

        $items = [
            [
                'id' => '1',
                'role' => 'System Administrator',
                'role_description' => 'Manage System'
            ],
            [
                'id' => '2',
                'role' => 'Services',
                'role_description' => 'Provide services to the clients.'
            ],
        ];

        DB::table('staff_roles')->insert($items);
    }
}
