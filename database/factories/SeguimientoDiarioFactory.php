<?php

namespace Database\Factories;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeguimientoDiarioFactory extends Factory
{
    protected $model = SeguimientoDiario::class;

    public function definition(): array
    {
        return [
            'asignacion_id'      => AsignacionFct::factory(),
            'fecha'              => fake()->dateTimeBetween('2025-09-01', '2026-06-30'),
            'descripcion_tareas' => fake()->paragraph(),
            'evidencia_path'     => null,
            'hora_entrada'       => '09:00:00',
            'hora_salida'        => '14:00:00',
            'confirmado_tutor'   => false,
            'confirmado_at'      => null,
            'comentario_tutor'   => null,
        ];
    }

    public function confirmado(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmado_tutor' => true,
            'confirmado_at'    => now(),
            'comentario_tutor' => fake()->optional()->sentence(),
        ]);
    }
}
