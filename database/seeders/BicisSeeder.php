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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 1,
                'nombre' => 'Bicicleta Aarón',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'nombre' => 'Bicicleta Max',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'usuario_id' => 2,
                'nombre' => 'Bicicleta Aarón',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
