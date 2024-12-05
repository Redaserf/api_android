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
            'nombre' => 'pepe',
            'apellido' => 'josejuan',
            'email' => 'dev@gmail.com',
            'peso' => '60.32',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);
    }
}
