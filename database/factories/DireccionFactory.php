<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class DireccionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'empresa_id'  => Empresa::factory(),
            'tipo_via'    => fake()->randomElement(['Calle', 'Avenida', 'Plaza']),
            'nombre_via'  => fake()->streetName(),
            'numero'      => (string) fake()->buildingNumber(),
            'codigo_postal' => fake()->numerify('#####'),
            'municipio'   => fake()->city(),
            'principal'   => false,
        ];
    }
}
