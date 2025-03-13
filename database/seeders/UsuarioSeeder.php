<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('usuarios')->insert([
            'nombre' => 'Admin',
            'apellido' => 'xd',
            'email' => 'admin@gmail.com',
            'peso' => '100',
            'estatura' => '2.20',
            'codigo' => '000000',
            'rol_id' => 3,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);

        DB::table('usuarios')->insert([
            'nombre' => 'User',
            'apellido' => 'xd',
            'email' => 'user@gmail.com',
            'peso' => '60.32',
            'estatura' => '1.64',
            'codigo' => '000000',
            'rol_id' => 2,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);
    }
}
