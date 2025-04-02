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
            'tiempo' => $this->faker->randomNumber(),
            'velocidad' => $this->faker->randomNumber(),
            'velocidad_promedio' => $this->faker->randomNumber(),
            'velocidad_maxima' => $this->faker->randomNumber(),
            'distancia_recorrida' => $this->faker->randomFloat(2, 0, 100),
            'usuario' => [
                '_id' => $idUsuario,
                'rol_id' => 2,
            ],
            'bicicleta_id' => \App\Models\Bicicleta::where('usuario_id', $idUsuario)->inRandomOrder()->value('id'),
            'temperatura' => $this->faker->randomNumber(),
            'suma_velocidad' => [
                'suma' => $this->faker->randomNumber(),
                'cantidad' => $this->faker->randomNumber(),
            ],
            'duracion_final' => $this->faker->randomFloat(2, 0, 100),
            'acabado' => true,
            'created_at' => $this->faker->dateTimeBetween(\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()),
            'updated_at' => $this->faker->dateTimeBetween(\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()),
        ];
    }
}
