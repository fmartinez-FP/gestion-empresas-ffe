<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre'     => fake()->company(),
            'cif'        => strtoupper(fake()->bothify('?########')),
            'creador_id' => User::factory(),
            'notas'      => null,
        ];
    }
}
