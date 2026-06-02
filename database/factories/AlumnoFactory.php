<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\CicloFormativo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    public function definition(): array
    {
        return [
            'user_id'         => null,
            'nombre'          => $this->faker->firstName(),
            'apellidos'       => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'email'           => $this->faker->unique()->safeEmail(),
            'telefono'        => $this->faker->optional()->numerify('6########'),
            'ciclo_id'        => CicloFormativo::factory(),
            'curso_academico' => '2025-2026',
            'numero_curso'    => $this->faker->randomElement([1, 2]),
            'importado_via'   => 'manual',
        ];
    }
}
