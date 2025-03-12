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
            'estatura' => '1.64',
            'codigo' => '000000',
            'rol_id' => 3,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);

        DB::table('usuarios')->insert([
            'nombre' => 'pepe',
            'apellido' => 'josejuan',
            'email' => 'jas4@gmail.com',
            'peso' => '60.32',
            'estatura' => '1.64',
            'codigo' => '000000',
            'rol_id' => 2,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);

        DB::table('usuarios')->insert([
            'nombre' => 'pepe',
            'apellido' => 'josejuan',
            'email' => 'jas3@gmail.com',
            'peso' => '60.32',
            'estatura' => '1.64',
            'codigo' => '000000',
            'rol_id' => 2,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);
        DB::table('usuarios')->insert([
            'nombre' => 'pepe',
            'apellido' => 'josejuan',
            'email' => 'jas2@gmail.com',
            'peso' => '60.32',
            'estatura' => '1.64',
            'codigo' => '000000',
            'rol_id' => 2,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);
        DB::table('usuarios')->insert([
            'nombre' => 'pepe',
            'apellido' => 'josejuan',
            'email' => 'jas@gmail.com',
            'peso' => '60.32',
            'estatura' => '1.64',
            'codigo' => '000000',
            'rol_id' => 2,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),

        ]);
    }
}
