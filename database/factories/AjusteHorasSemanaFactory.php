<?php

namespace Database\Factories;

use App\Models\AjusteHorasSemana;
use App\Models\AsignacionFct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AjusteHorasSemanaFactory extends Factory
{
    protected $model = AjusteHorasSemana::class;

    public function definition(): array
    {
        return [
            'asignacion_id' => AsignacionFct::factory(),
            'semana'        => now()->startOfWeek()->toDateString(),
            'ajuste'        => $this->faker->randomFloat(2, -5, 5),
            'motivo'        => $this->faker->optional()->sentence(),
            'created_by_id' => User::factory(),
        ];
    }

    public function positivo(): static
    {
        return $this->state(fn (array $attrs) => ['ajuste' => $this->faker->randomFloat(2, 0.5, 8)]);
    }

    public function negativo(): static
    {
        return $this->state(fn (array $attrs) => ['ajuste' => -1 * $this->faker->randomFloat(2, 0.5, 8)]);
    }
}
