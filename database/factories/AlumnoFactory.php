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
            'nombre'          => fake()->firstName(),
            'apellidos'       => fake()->lastName() . ' ' . fake()->lastName(),
            'email'           => fake()->unique()->safeEmail(),
            'telefono'        => fake()->optional()->numerify('6########'),
            'ciclo_id'        => CicloFormativo::factory(),
            'curso_academico' => '2025-2026',
            'numero_curso'    => fake()->randomElement([1, 2]),
            'importado_via'   => 'manual',
        ];
    }

    public function primero(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero_curso' => 1,
        ]);
    }

    public function segundo(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero_curso' => 2,
        ]);
    }
}
