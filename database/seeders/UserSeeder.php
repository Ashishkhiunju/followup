<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "DhanaDaya",
            'email' => 'dhanadaya@gmail.com',
            'password' => Hash::make('dhanadaya@12321'),
            'role_id'=>1
        ]);
    }
}
