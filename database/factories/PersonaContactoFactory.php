<?php

namespace Database\Factories;

use App\Models\PersonaContacto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonaContactoFactory extends Factory
{
    protected $model = PersonaContacto::class;

    public function definition(): array
    {
        return [
            'empresa_id' => null, // proporcionar en el test
            'nombre'     => fake()->name(),
            'cargo'      => fake()->jobTitle(),
            'telefono'   => fake()->phoneNumber(),
            'email'      => fake()->safeEmail(),
            'notas'      => null,
            'principal'  => false,
        ];
    }

    public function principal(): static
    {
        return $this->state(fn (array $attributes) => ['principal' => true]);
    }
}
