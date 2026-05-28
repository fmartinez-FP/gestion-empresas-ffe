<?php

namespace Tests\Unit;

use App\Models\Colocacion;
use App\Models\Empresa;
use App\Models\User;
use App\Models\CicloFormativo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ColocacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
    }

    /** @test */
    public function puede_crear_una_colocacion()
    {
        $user = User::factory()->create();
        $ciclo = CicloFormativo::first();
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Test',
            'cif' => 'B12345678',
            'creador_id' => $user->id,
        ]);

        $colocacion = Colocacion::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'registrado_por_id' => $user->id,
            'curso_academico' => '2024-2025',
            'numero_curso' => 1,
            'num_alumnos' => 3,
            'num_horas' => 300,
        ]);

        $this->assertDatabaseHas('colocaciones', [
            'empresa_id' => $empresa->id,
            'num_alumnos' => 3,
        ]);
    }

    /** @test */
    public function obtener_curso_actual_calcula_correctamente()
    {
        // El curso académico depende de la fecha actual
        $cursoActual = Colocacion::obtenerCursoActual();
        
        $now = Carbon::now();
        $year = $now->month >= 9 ? $now->year : $now->year - 1;
        $expectedCurso = $year . '-' . ($year + 1);

        $this->assertEquals($expectedCurso, $cursoActual);
    }

    /** @test */
    public function generar_lista_cursos_devuelve_array_correcto()
    {
        $cursos = Colocacion::generarListaCursos(3, 1);
        
        $this->assertCount(4, $cursos); // 3 atrás + actual
        
        // Verificar formato
        foreach ($cursos as $curso) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{4}$/', $curso);
        }
    }

    /** @test */
    public function curso_etiqueta_devuelve_formato_correcto()
    {
        $user = User::factory()->create();
        $ciclo = CicloFormativo::first();
        $empresa = Empresa::create([
            'nombre' => 'Empresa',
            'cif' => 'B11111111',
            'creador_id' => $user->id,
        ]);

        $colocacion1 = Colocacion::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'registrado_por_id' => $user->id,
            'curso_academico' => '2024-2025',
            'numero_curso' => 1,
            'num_alumnos' => 2,
            'num_horas' => 200,
        ]);

        $colocacion2 = Colocacion::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'registrado_por_id' => $user->id,
            'curso_academico' => '2024-2025',
            'numero_curso' => 2,
            'num_alumnos' => 2,
            'num_horas' => 200,
        ]);

        $this->assertEquals('1º', $colocacion1->curso_etiqueta);
        $this->assertEquals('2º', $colocacion2->curso_etiqueta);
    }

    /** @test */
    public function scope_curso_academico_filtra_correctamente()
    {
        $user = User::factory()->create();
        $ciclo = CicloFormativo::first();
        $empresa = Empresa::create([
            'nombre' => 'Empresa',
            'cif' => 'B22222222',
            'creador_id' => $user->id,
        ]);

        Colocacion::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'registrado_por_id' => $user->id,
            'curso_academico' => '2023-2024',
            'numero_curso' => 1,
            'num_alumnos' => 2,
            'num_horas' => 200,
        ]);

        Colocacion::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'registrado_por_id' => $user->id,
            'curso_academico' => '2024-2025',
            'numero_curso' => 1,
            'num_alumnos' => 3,
            'num_horas' => 300,
        ]);

        $this->assertEquals(1, Colocacion::cursoAcademico('2023-2024')->count());
        $this->assertEquals(1, Colocacion::cursoAcademico('2024-2025')->count());
    }

    /** @test */
    public function estadisticas_por_ciclo_calcula_totales()
    {
        $user = User::factory()->create();
        $ciclo = CicloFormativo::first();
        $empresa = Empresa::create([
            'nombre' => 'Empresa Stats',
            'cif' => 'B33333333',
            'creador_id' => $user->id,
        ]);

        // Crear varias colocaciones
        Colocacion::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'registrado_por_id' => $user->id,
            'curso_academico' => '2024-2025',
            'numero_curso' => 1,
            'num_alumnos' => 5,
            'num_horas' => 400,
        ]);

        Colocacion::create([
            'empresa_id' => $empresa->id,
            'ciclo_id' => $ciclo->id,
            'registrado_por_id' => $user->id,
            'curso_academico' => '2024-2025',
            'numero_curso' => 2,
            'num_alumnos' => 3,
            'num_horas' => 300,
        ]);

        $stats = Colocacion::estadisticasPorCiclo('2024-2025');

        $this->assertArrayHasKey('2024-2025', $stats);
        
        // El método agrupa por ciclo+numero_curso, hay que sumar las filas del ciclo
        $cicloRows = collect($stats['2024-2025'])->where('ciclo_id', $ciclo->id);
        $this->assertEquals(8, $cicloRows->sum('total_alumnos'));
        $this->assertEquals(700, $cicloRows->sum('total_horas'));
    }
}
