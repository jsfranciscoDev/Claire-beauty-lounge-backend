<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $items = [
            [
                'id' => '1',
                'role' => 'Inventory',
                'role_description' => 'Manage Inventory'
            ],
            [
                'id' => '2',
                'role' => 'Services',
                'role_description' => 'Services'
            ],
        ];

        DB::table('staff_roles')->insert($items);
    }
}
