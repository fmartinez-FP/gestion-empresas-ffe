<?php

namespace Database\Factories;

use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarioAsignacionFactory extends Factory
{
    protected $model = CalendarioAsignacion::class;

    public function definition(): array
    {
        return [
            'asignacion_id' => AsignacionFct::factory(),
            'fecha'         => fake()->dateTimeBetween('2025-09-01', '2026-06-30'),
            'tipo'          => 'laborable',
            'motivo'        => null,
        ];
    }

    public function laborable(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo'   => 'laborable',
            'motivo' => null,
        ]);
    }

    public function festivo(string $motivo = 'Festivo local'): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo'   => 'festivo',
            'motivo' => $motivo,
        ]);
    }

    public function baja(string $motivo = 'Baja médica'): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo'   => 'baja',
            'motivo' => $motivo,
        ]);
    }
}
