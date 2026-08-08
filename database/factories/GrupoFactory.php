<?php

namespace Database\Factories;

use App\Models\CicloFormativo;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrupoFactory extends Factory
{
    protected $model = Grupo::class;

    public function definition(): array
    {
        return [
            'ciclo_id'     => CicloFormativo::factory(),
            'numero_curso' => $this->faker->randomElement([1, 2]),
            'etiqueta'     => '',
            'activo'       => true,
        ];
    }

    public function conEtiqueta(string $etiqueta = 'A'): static
    {
        return $this->state(fn (array $attributes) => [
            'etiqueta' => $etiqueta,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
