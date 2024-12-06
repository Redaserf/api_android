<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BicisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear 5 bicicletas para el usuario con id = 1
        DB::table('bicicletas')->insert([
            [
                'usuario_id' => 1,
                'nombre' => 'Bicicleta Max',
                'imagen' => "hola",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 1,
                'nombre' => 'Bicicleta Aarón',
                'imagen' => 'hola',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 1,
                'nombre' => 'Bicicleta Angel',
                'imagen' => 'hola',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 1,
                'nombre' => 'Bicicleta Aldebarán',
                'imagen' => 'hola',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 1,
                'nombre' => 'Bicicleta Hugo',
                'imagen' => 'hola',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
