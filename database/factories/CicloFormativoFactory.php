<?php

namespace Database\Factories;

use App\Models\CicloFormativo;
use Illuminate\Database\Eloquent\Factories\Factory;

class CicloFormativoFactory extends Factory
{
    protected $model = CicloFormativo::class;

    public function definition(): array
    {
        static $i = 0;
        $codigos = ['DAM', 'DAW', 'SMR', 'ASIR', 'STI', 'IFCT', 'ADG', 'COM'];
        $codigo  = $codigos[$i % count($codigos)] . $i;
        $i++;
        return [
            'codigo' => $codigo,
            'nombre' => 'Ciclo ' . $codigo,
        ];
    }
}
