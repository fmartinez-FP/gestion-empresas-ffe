<?php

namespace Database\Factories;

use App\Models\AsignacionFct;
use App\Models\HorarioAsignacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class HorarioAsignacionFactory extends Factory
{
    protected $model = HorarioAsignacion::class;

    public function definition(): array
    {
        return [
            'asignacion_id'  => AsignacionFct::factory(),
            'dia'            => $this->faker->randomElement(['lunes', 'martes', 'miercoles', 'jueves', 'viernes']),
            'entrada_manana' => '08:00',
            'salida_manana'  => '15:00',
            'entrada_tarde'  => null,
            'salida_tarde'   => null,
        ];
    }

    public function partida(): static
    {
        return $this->state(fn (array $attrs) => [
            'salida_manana' => '14:00',
            'entrada_tarde' => '16:00',
            'salida_tarde'  => '19:00',
        ]);
    }

    public function jornadaLarga(): static
    {
        return $this->state(fn (array $attrs) => [
            'entrada_manana' => '08:00',
            'salida_manana'  => '13:00',
            'entrada_tarde'  => '14:00',
            'salida_tarde'   => '19:30',
        ]);
    }
}
