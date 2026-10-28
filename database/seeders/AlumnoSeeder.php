<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\CicloFormativo;
use App\Models\Configuracion;
use App\Models\Grupo;
use Illuminate\Database\Seeder;

class AlumnoSeeder extends Seeder
{
    /**
     * Seeder de alumnos de ejemplo para pruebas.
     * Crea un grupo (1º y 2º, sin paralelos) por cada ciclo formativo y
     * entre 10 y 15 alumnos por grupo, matriculados en el curso académico
     * activo. No crea asignaciones FCT.
     * Ejecutar con: php artisan db:seed --class=AlumnoSeeder
     */
    public function run(): void
    {
        $ciclos = CicloFormativo::all();

        if ($ciclos->isEmpty()) {
            $this->command->error('Primero ejecuta: php artisan db:seed --class=CicloFormativoSeeder');
            return;
        }

        $cursoActivo = Configuracion::cursoActivo();

        $this->command->info('Creando grupos y alumnos de ejemplo...');

        $totalAlumnos = 0;

        foreach ($ciclos as $ciclo) {
            foreach ([1, 2] as $numeroCurso) {
                $grupo = Grupo::firstOrCreate(
                    ['ciclo_id' => $ciclo->id, 'numero_curso' => $numeroCurso, 'etiqueta' => ''],
                    ['activo' => true]
                );

                $numAlumnos = rand(10, 15);

                for ($i = 0; $i < $numAlumnos; $i++) {
                    Alumno::create([
                        'nombre'          => fake()->firstName(),
                        'apellidos'       => fake()->lastName() . ' ' . fake()->lastName(),
                        'email'           => fake()->unique()->safeEmail(),
                        'telefono'        => fake()->numerify('6########'),
                        'grupo_id'        => $grupo->id,
                        'curso_academico' => $cursoActivo,
                        'importado_via'   => 'manual',
                    ]);
                    $totalAlumnos++;
                }
            }
        }

        $this->command->info("✓ Creados {$totalAlumnos} alumnos en " . ($ciclos->count() * 2) . ' grupos.');
    }
}
