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
        return [
            //
            'calorias' => $this->faker->randomNumber(),
            'tiempo' => $this->faker->randomNumber(),
            'velocidad' => $this->faker->randomNumber(),
            'velocidad_promedio' => $this->faker->randomNumber(),
            'velocidad_maxima' => $this->faker->randomNumber(),
            'distancia_recorrida' => $this->faker->randomNumber(),
            'usuario' => $this->faker->randomNumber(),
            'bicicleta_id' => $this->faker->randomNumber(),
            'temperatura' => $this->faker->randomNumber(),
            'suma_velocidad' => [
                'suma' => $this->faker->randomNumber(),
                'cantidad' => $this->faker->randomNumber(),
            ],
            'duracion_final' => $this->faker->randomNumber(),
            'acabado' => true,
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
