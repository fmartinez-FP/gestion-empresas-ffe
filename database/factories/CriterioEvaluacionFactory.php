<?php

namespace Database\Factories;

use App\Models\ResultadoAprendizaje;
use Illuminate\Database\Eloquent\Factories\Factory;

class CriterioEvaluacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'resultado_aprendizaje_id' => ResultadoAprendizaje::factory(),
            'codigo'                   => 'CE' . $this->faker->unique()->numberBetween(1, 999),
            'descripcion'              => $this->faker->sentence(8),
        ];
    }
}
