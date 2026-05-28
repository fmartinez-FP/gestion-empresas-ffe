<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Colocacion;
use App\Models\CicloFormativo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatosEjemploSeeder extends Seeder
{
    /**
     * Seeder de datos de ejemplo para pruebas.
     * Ejecutar con: php artisan db:seed --class=DatosEjemploSeeder
     */
    public function run(): void
    {
        $admin = User::where('rol', 'admin')->first();
        $ciclos = CicloFormativo::all();

        if (!$admin || $ciclos->isEmpty()) {
            $this->command->error('Primero ejecuta los seeders base: php artisan db:seed');
            return;
        }

        // Crear empresas de ejemplo
        $empresasData = [
            ['nombre' => 'Telefónica España S.A.', 'cif' => 'A28015865', 'telefono' => '912345678', 'email' => 'fct@telefonica.es', 'persona_contacto' => 'María García', 'direccion' => 'Gran Vía 28, Madrid', 'num_convenio' => 'CONV-2023-001', 'fecha_firma' => Carbon::now()->subYears(2)],
            ['nombre' => 'Indra Sistemas S.A.', 'cif' => 'A28599033', 'telefono' => '913345566', 'email' => 'practicas@indra.es', 'persona_contacto' => 'Carlos López', 'direccion' => 'Avda. de Bruselas 35, Alcobendas', 'num_convenio' => 'CONV-2023-002', 'fecha_firma' => Carbon::now()->subMonths(8)],
            ['nombre' => 'Vodafone España S.A.U.', 'cif' => 'A80907397', 'telefono' => '914567890', 'email' => 'formacion@vodafone.es', 'persona_contacto' => 'Ana Martínez', 'direccion' => 'Avda. de América 115, Madrid', 'num_convenio' => 'CONV-2022-015', 'fecha_firma' => Carbon::now()->subYears(3)->subMonths(8)],
            ['nombre' => 'Accenture S.L.', 'cif' => 'B78001877', 'telefono' => '915678901', 'email' => 'rrhh@accenture.com', 'persona_contacto' => 'Pedro Sánchez', 'direccion' => 'Paseo de la Castellana 85, Madrid', 'num_convenio' => 'CONV-2024-001', 'fecha_firma' => Carbon::now()->subMonths(3)],
            ['nombre' => 'Capgemini España S.L.', 'cif' => 'B79224464', 'telefono' => '916789012', 'email' => 'fct@capgemini.com', 'persona_contacto' => 'Laura Fernández', 'direccion' => 'C/ Anabel Segura 14, Alcobendas', 'num_convenio' => 'CONV-2023-008', 'fecha_firma' => Carbon::now()->subYears(1)->subMonths(6)],
            ['nombre' => 'Everis Spain S.L.', 'cif' => 'B82387770', 'telefono' => '917890123', 'email' => 'practicas@everis.com', 'persona_contacto' => 'Roberto Díaz', 'direccion' => 'C/ Manoteras 32, Madrid', 'num_convenio' => 'CONV-2022-003', 'fecha_firma' => Carbon::now()->subYears(4)->addMonths(2)],
            ['nombre' => 'Electrónica Pacífico S.L.', 'cif' => 'B12345678', 'telefono' => '918901234', 'email' => 'info@electronicapacifico.es', 'persona_contacto' => 'Juan Pérez', 'direccion' => 'Polígono Industrial Norte 15, Madrid', 'num_convenio' => 'CONV-2024-002', 'fecha_firma' => Carbon::now()->subMonths(1)],
            ['nombre' => 'Telecomunicaciones Sur S.A.', 'cif' => 'A87654321', 'telefono' => '919012345', 'email' => 'fct@telecomsur.es', 'persona_contacto' => 'Elena Ruiz', 'direccion' => 'C/ Industria 45, Getafe', 'num_convenio' => 'CONV-2023-012', 'fecha_firma' => Carbon::now()->subYears(1)],
            ['nombre' => 'Instalaciones Eléctricas Madrid', 'cif' => 'B11223344', 'telefono' => '910123456', 'email' => 'contacto@instelectmadrid.com', 'persona_contacto' => 'Miguel Ángel Torres', 'direccion' => 'Avda. de la Industria 78, Fuenlabrada', 'num_convenio' => 'CONV-2021-005', 'fecha_firma' => Carbon::now()->subYears(4)->subMonths(3)],
            ['nombre' => 'Sistemas Informáticos Centro', 'cif' => 'B55667788', 'telefono' => '911234567', 'email' => 'formacion@sicentro.es', 'persona_contacto' => 'Sofía Navarro', 'direccion' => 'C/ Tecnología 12, Leganés', 'num_convenio' => 'CONV-2023-020', 'fecha_firma' => Carbon::now()->subMonths(10)],
        ];

        $this->command->info('Creando empresas de ejemplo...');
        
        foreach ($empresasData as $data) {
            $empresa = Empresa::create([
                ...$data,
                'creador_id' => $admin->id,
            ]);

            // Asignar ciclos aleatorios (2-4 ciclos por empresa)
            $ciclosAleatorios = $ciclos->random(rand(2, min(4, $ciclos->count())));
            $syncData = [];
            foreach ($ciclosAleatorios as $ciclo) {
                $syncData[$ciclo->id] = [
                    'acepta_primero' => (bool) rand(0, 1),
                    'acepta_segundo' => true,
                ];
            }
            $empresa->ciclos()->sync($syncData);
        }

        $this->command->info('Creando colocaciones de ejemplo...');

        // Crear colocaciones para los últimos 3 cursos
        $cursos = Colocacion::generarListaCursos(3, 0);
        $empresas = Empresa::with('ciclos')->get();

        foreach ($cursos as $curso) {
            foreach ($empresas as $empresa) {
                // No todas las empresas tienen colocaciones cada curso
                if (rand(0, 100) < 70) {
                    foreach ($empresa->ciclos->random(rand(1, min(2, $empresa->ciclos->count()))) as $ciclo) {
                        // Crear colocación para 1º y/o 2º
                        $numeroCursos = rand(1, 2) == 1 ? [1] : [1, 2];
                        
                        foreach ($numeroCursos as $numCurso) {
                            Colocacion::create([
                                'empresa_id' => $empresa->id,
                                'ciclo_id' => $ciclo->id,
                                'registrado_por_id' => $admin->id,
                                'curso_academico' => $curso,
                                'numero_curso' => $numCurso,
                                'num_alumnos' => rand(1, 4),
                                'num_horas' => rand(200, 400),
                            ]);
                        }
                    }
                }
            }
        }

        $totalEmpresas = Empresa::count();
        $totalColocaciones = Colocacion::count();
        
        $this->command->info("✓ Creadas {$totalEmpresas} empresas y {$totalColocaciones} colocaciones de ejemplo.");
    }
}
