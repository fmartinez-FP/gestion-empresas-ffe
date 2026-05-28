<?php

namespace Database\Seeders;

use App\Models\CicloFormativo;
use Illuminate\Database\Seeder;

class CicloFormativoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ciclos = [
            // FP Básica
            [
                'codigo' => 'IC',
                'nombre' => 'Informática y Comunicaciones',
                'nivel' => 'basica',
            ],
            [
                'codigo' => 'EE',
                'nombre' => 'Electricidad y Electrónica',
                'nivel' => 'basica',
            ],
            
            // Grado Medio
            [
                'codigo' => 'IT',
                'nombre' => 'Instalaciones de Telecomunicaciones',
                'nivel' => 'media',
            ],
            [
                'codigo' => 'IEA',
                'nombre' => 'Instalaciones Eléctricas y Automáticas',
                'nivel' => 'media',
            ],
            
            // Grado Superior
            [
                'codigo' => 'ME',
                'nombre' => 'Mantenimiento Electrónico',
                'nivel' => 'superior',
            ],
            [
                'codigo' => 'STI',
                'nombre' => 'Sistemas de Telecomunicaciones e Informáticos',
                'nivel' => 'superior',
            ],
        ];

        foreach ($ciclos as $ciclo) {
            CicloFormativo::updateOrCreate(
                ['codigo' => $ciclo['codigo']],
                $ciclo
            );
        }

        $this->command->info('✓ Ciclos formativos creados: ' . count($ciclos));
    }
}
