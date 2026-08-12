<?php

namespace Database\Factories;

use App\Models\NoLectivoIes;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoLectivoIesFactory extends Factory
{
    protected $model = NoLectivoIes::class;

    public function definition(): array
    {
        return [
            'fecha'  => $this->faker->unique()->dateTimeBetween('-1 month', '+3 months')->format('Y-m-d'),
            'motivo' => $this->faker->optional()->sentence(3),
        ];
    }
}
