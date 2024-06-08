<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'Admin',
            'description' => 'Tiene todos los permisos en la aplicación'],
            ['name' => 'Admin-1',
            'description' => 'Tiene todos la mayoría de permisos, excepto crear administradores'],
            ['name' => 'Admin-2',
            'description' => 'Tiene permisos para crear reportes y visualizar las estadisticas en la aplicación'],
        ]);
    }
}
