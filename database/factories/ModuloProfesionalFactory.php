<?php

namespace Database\Factories;

use App\Models\CicloFormativo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuloProfesionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ciclo_id'      => CicloFormativo::factory(),
            'codigo'        => 'MP' . $this->faker->unique()->numberBetween(1, 999),
            'nombre'        => $this->faker->words(4, true),
            'horas_totales' => $this->faker->randomElement([64, 96, 128, 160, 192, 256]),
            'curso'         => $this->faker->randomElement([1, 2]),
        ];
    }
}
