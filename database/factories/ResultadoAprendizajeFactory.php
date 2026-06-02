<?php

namespace Database\Factories;

use App\Models\ModuloProfesional;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultadoAprendizajeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'modulo_id'              => ModuloProfesional::factory(),
            'codigo'                 => 'RA' . $this->faker->unique()->numberBetween(1, 999),
            'descripcion'            => $this->faker->sentence(10),
            'activo_ffe'             => false,
            'curso_academico_activo' => null,
        ];
    }
}
