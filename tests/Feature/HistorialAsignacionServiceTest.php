<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\CicloFormativo;
use App\Models\Colocacion;
use App\Services\HistorialAsignacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HistorialAsignacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private HistorialAsignacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HistorialAsignacionService::class);
    }

    #[Test]
    public function primer_curso_siempre_son_150_horas(): void
    {
        $ciclo = CicloFormativo::factory()->create(['nivel' => 'media']);
        $asignacion = AsignacionFct::factory()->create([
            'ciclo_id' => $ciclo->id,
            'numero_curso' => 1,
            'num_horas' => 999,
            'estado' => 'finalizada',
        ]);

        $this->service->registrar($asignacion);

        $this->assertDatabaseHas('colocaciones', [
            'ciclo_id' => $ciclo->id,
            'numero_curso' => 1,
            'num_horas' => 150,
            'num_alumnos' => 1,
            'origen' => 'automatica',
        ]);
    }

    #[Test]
    public function segundo_basica_redondea_al_tramo_mas_cercano(): void
    {
        $ciclo = CicloFormativo::factory()->create(['nivel' => 'basica']);

        $cerca250 = AsignacionFct::factory()->create([
            'ciclo_id' => $ciclo->id, 'numero_curso' => 2, 'num_horas' => 270, 'estado' => 'finalizada',
        ]);
        $cerca400 = AsignacionFct::factory()->create([
            'ciclo_id' => $ciclo->id, 'numero_curso' => 2, 'num_horas' => 380, 'estado' => 'finalizada',
        ]);

        $this->service->registrar($cerca250);
        $this->service->registrar($cerca400);

        $this->assertDatabaseHas('colocaciones', ['ciclo_id' => $ciclo->id, 'num_horas' => 250]);
        $this->assertDatabaseHas('colocaciones', ['ciclo_id' => $ciclo->id, 'num_horas' => 400]);
    }

    #[Test]
    public function segundo_medio_y_superior_usan_tramos_350_500(): void
    {
        foreach (['media', 'superior'] as $nivel) {
            $ciclo = CicloFormativo::factory()->create(['nivel' => $nivel]);
            $asignacion = AsignacionFct::factory()->create([
                'ciclo_id' => $ciclo->id, 'numero_curso' => 2, 'num_horas' => 480, 'estado' => 'finalizada',
            ]);

            $this->service->registrar($asignacion);

            $this->assertDatabaseHas('colocaciones', ['ciclo_id' => $ciclo->id, 'num_horas' => 500]);
        }
    }

    #[Test]
    public function agrupa_varios_alumnos_con_la_misma_combinacion_en_una_sola_fila(): void
    {
        $ciclo = CicloFormativo::factory()->create(['nivel' => 'media']);

        $a1 = AsignacionFct::factory()->create([
            'ciclo_id' => $ciclo->id, 'curso_academico' => '2025-2026',
            'numero_curso' => 1, 'num_horas' => 150, 'estado' => 'finalizada',
        ]);
        $a2 = AsignacionFct::factory()->create([
            'empresa_id' => $a1->empresa_id, 'ciclo_id' => $ciclo->id, 'curso_academico' => '2025-2026',
            'numero_curso' => 1, 'num_horas' => 150, 'estado' => 'finalizada',
        ]);

        $this->service->registrar($a1);
        $this->service->registrar($a2);

        $this->assertSame(1, Colocacion::where('origen', 'automatica')->count());
        $this->assertDatabaseHas('colocaciones', ['num_alumnos' => 2, 'origen' => 'automatica']);
    }

    #[Test]
    public function es_idempotente_no_duplica_si_se_registra_dos_veces(): void
    {
        $ciclo = CicloFormativo::factory()->create(['nivel' => 'basica']);
        $asignacion = AsignacionFct::factory()->create([
            'ciclo_id' => $ciclo->id, 'numero_curso' => 1, 'num_horas' => 150, 'estado' => 'finalizada',
        ]);

        $this->service->registrar($asignacion);
        $this->service->registrar($asignacion);

        $this->assertSame(1, Colocacion::where('origen', 'automatica')->count());
        $this->assertDatabaseHas('colocaciones', ['num_alumnos' => 1]);
    }

    #[Test]
    public function no_hace_nada_si_la_asignacion_no_esta_finalizada(): void
    {
        $asignacion = AsignacionFct::factory()->create(['estado' => 'activa']);

        $this->service->registrar($asignacion);

        $this->assertSame(0, Colocacion::where('origen', 'automatica')->count());
    }
}
