<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recorrido>
 */
class RecorridoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $idUsuario = \App\Models\Usuario::inRandomOrder()->value('id');
        return [
            //
            'calorias' => $this->faker->randomFloat(2, 0, 100),
            'tiempo' => $this->faker->time('H:i:s'),
            'velocidad' => $this->faker->randomNumber(2),
            'velocidad_promedio' => $this->faker->randomNumber(2),
            'velocidad_maxima' => $this->faker->randomNumber(2),
            'distancia_recorrida' => $this->faker->randomFloat(2, 0, 100),
            'usuario' => [
                '_id' => $idUsuario,
                'rol_id' => 2,
            ],
            'bicicleta_id' => \App\Models\Bicicleta::where('usuario_id', $idUsuario)->inRandomOrder()->value('id'),
            'temperatura' => $this->faker->randomNumber(2),
            'suma_velocidad' => [
                'suma' => $this->faker->randomNumber(3),
                'cantidad' => $this->faker->randomNumber(3),
            ],
            'duracion_final' => $this->faker->randomFloat(2, 0, 100),
            'acabado' => true,
            'created_at' => $this->faker->dateTimeBetween(\Carbon\Carbon::now()->startOfYear(), \Carbon\Carbon::now()->endOfYear()),
            'updated_at' => $this->faker->dateTimeBetween(\Carbon\Carbon::now()->startOfYear(), \Carbon\Carbon::now()->startOfYear()),
        ];
    }
}
