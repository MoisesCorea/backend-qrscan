<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->insert(
            ['name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'alias' => '@admin',
            'password' => Hash::make('admin100'),
            'rol_id' => 1]
        );
    }
}
