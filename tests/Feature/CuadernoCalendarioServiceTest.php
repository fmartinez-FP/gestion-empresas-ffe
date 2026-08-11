<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use App\Models\SeguimientoDiario;
use App\Services\CuadernoCalendarioService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuadernoCalendarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private CuadernoCalendarioService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = app(CuadernoCalendarioService::class);
    }

    /** @test */
    public function dias_laborables_excluye_fines_de_semana(): void
    {
        // Lunes 2026-01-05 a domingo 2026-01-11 -> deben quedar 5 dias (L-V)
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => '2026-01-05',
            'fecha_fin'    => '2026-01-11',
        ]);

        $dias = $this->servicio->diasLaborables($asignacion);

        $this->assertCount(5, $dias);
        $this->assertTrue($dias->every(fn (Carbon $d) => ! $d->isWeekend()));
    }

    /** @test */
    public function dias_laborables_devuelve_vacio_si_faltan_fechas(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => null,
            'fecha_fin'    => null,
        ]);

        $this->assertTrue($this->servicio->diasLaborables($asignacion)->isEmpty());
    }

    /** @test */
    public function agrupar_por_semana_junta_los_dias_bajo_el_lunes_correspondiente(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => '2026-01-05', // lunes
            'fecha_fin'    => '2026-01-16', // viernes de la semana siguiente
        ]);

        $dias    = $this->servicio->diasLaborables($asignacion);
        $semanas = $this->servicio->agruparPorSemana($dias);

        $this->assertCount(2, $semanas);
        $this->assertTrue($semanas->has('2026-01-05'));
        $this->assertTrue($semanas->has('2026-01-12'));
        $this->assertCount(5, $semanas->get('2026-01-05'));
        $this->assertCount(5, $semanas->get('2026-01-12'));
    }

    /** @test */
    public function estado_dia_devuelve_festivo_si_esta_marcado_en_calendario_asignacion(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        CalendarioAsignacion::create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-03-19',
            'tipo'          => 'festivo',
            'motivo'        => 'San Jose',
        ]);

        $estado = $this->servicio->estadoDia(
            Carbon::parse('2026-03-19'),
            collect(),
            collect(['2026-03-19' => CalendarioAsignacion::first()])
        );

        $this->assertSame('festivo', $estado['estado']);
        $this->assertSame('San Jose', $estado['motivo']);
    }

    /** @test */
    public function estado_dia_devuelve_confirmado_si_hay_seguimiento_confirmado(): void
    {
        $asignacion  = AsignacionFct::factory()->create();
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => '2026-02-10',
            'confirmado_tutor' => true,
        ]);

        $estado = $this->servicio->estadoDia(
            Carbon::parse('2026-02-10'),
            collect(['2026-02-10' => $seguimiento]),
            collect()
        );

        $this->assertSame('confirmado', $estado['estado']);
    }

    /** @test */
    public function estado_dia_devuelve_pendiente_si_hay_seguimiento_sin_confirmar(): void
    {
        $asignacion  = AsignacionFct::factory()->create();
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => '2026-02-10',
            'confirmado_tutor' => false,
        ]);

        $estado = $this->servicio->estadoDia(
            Carbon::parse('2026-02-10'),
            collect(['2026-02-10' => $seguimiento]),
            collect()
        );

        $this->assertSame('pendiente', $estado['estado']);
    }

    /** @test */
    public function estado_dia_devuelve_no_trabajado_si_es_dia_pasado_sin_entrada(): void
    {
        $estado = $this->servicio->estadoDia(
            Carbon::today()->subDays(3),
            collect(),
            collect()
        );

        $this->assertSame('no_trabajado', $estado['estado']);
    }

    /** @test */
    public function estado_dia_devuelve_pendiente_si_es_hoy_sin_entrada(): void
    {
        $estado = $this->servicio->estadoDia(
            Carbon::today(),
            collect(),
            collect()
        );

        $this->assertSame('pendiente', $estado['estado']);
    }

    /** @test */
    public function estado_dia_devuelve_pendiente_si_es_dia_futuro_sin_entrada(): void
    {
        $estado = $this->servicio->estadoDia(
            Carbon::today()->addDays(5),
            collect(),
            collect()
        );

        $this->assertSame('pendiente', $estado['estado']);
    }

    /** @test */
    public function resolver_estados_devuelve_el_estado_correcto_para_varias_fechas_en_una_pasada(): void
    {
        $asignacion  = AsignacionFct::factory()->create();
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => '2026-02-10',
            'confirmado_tutor' => true,
        ]);
        CalendarioAsignacion::create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-02-11',
            'tipo'          => 'festivo',
            'motivo'        => 'Prueba',
        ]);

        $fechas   = collect([Carbon::parse('2026-02-10'), Carbon::parse('2026-02-11')]);
        $estados  = $this->servicio->resolverEstados($asignacion, $fechas);

        $this->assertSame('confirmado', $estados->get('2026-02-10')['estado']);
        $this->assertSame('festivo', $estados->get('2026-02-11')['estado']);
    }

    /** @test */
    public function dias_del_mes_solo_incluye_dias_laborables_dentro_del_periodo_de_la_asignacion(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => '2026-03-10', // martes
            'fecha_fin'    => '2026-03-20', // viernes
        ]);

        $dias = $this->servicio->diasDelMes($asignacion, Carbon::parse('2026-03-01'));

        $this->assertTrue($dias->every(fn (Carbon $d) => $d->between('2026-03-10', '2026-03-20')));
        $this->assertTrue($dias->every(fn (Carbon $d) => ! $d->isWeekend()));
        $this->assertFalse($dias->contains(fn (Carbon $d) => $d->toDateString() === '2026-03-09'));
    }

    /** @test */
    public function dias_del_mes_devuelve_vacio_si_faltan_fechas_de_la_asignacion(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => null,
            'fecha_fin'    => null,
        ]);

        $this->assertTrue($this->servicio->diasDelMes($asignacion, Carbon::parse('2026-03-01'))->isEmpty());
    }

    /** @test */
    public function estado_dia_devuelve_ausencia_si_esta_marcado_como_ausencia_no_justificada(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        CalendarioAsignacion::create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-04-14',
            'tipo'          => 'ausencia_no_justificada',
            'motivo'        => 'No avisó',
        ]);

        $estado = $this->servicio->estadoDia(
            Carbon::parse('2026-04-14'),
            collect(),
            collect(['2026-04-14' => CalendarioAsignacion::first()])
        );

        $this->assertSame('ausencia', $estado['estado']);
        $this->assertSame('ausencia_no_justificada', $estado['tipo']);
        $this->assertSame('No avisó', $estado['motivo']);
    }
}
