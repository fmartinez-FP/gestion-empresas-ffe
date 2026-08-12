<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use App\Models\HorarioAsignacion;
use App\Models\NoLectivoIes;
use App\Services\CalendarioAsignacionService;
use App\Services\CuadernoCalendarioService;
use App\Services\HorarioAsignacionService;
use App\Services\NoLectivoIesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Verifica la fusion de no_lectivos_ies (festivos de centro) en los 3 puntos
 * de calculo/estado que deben excluir esas fechas: HorarioAsignacionService,
 * CalendarioAsignacionService y CuadernoCalendarioService. Decision 2026-08-12:
 * si una fecha coincide en calendario_asignacion Y no_lectivos_ies, gana centro.
 */
class NoLectivoIesIntegracionTest extends TestCase
{
    use RefreshDatabase;

    private function asignacionConHorarioLV(array $attrs = []): AsignacionFct
    {
        $asignacion = AsignacionFct::factory()->create(array_merge([
            'fecha_inicio' => '2026-09-07', // lunes
            'fecha_fin'    => '2026-09-11', // viernes misma semana
        ], $attrs));

        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
            HorarioAsignacion::factory()->create([
                'asignacion_id'   => $asignacion->id,
                'dia'             => $dia,
                'entrada_manana'  => '09:00',
                'salida_manana'   => '13:00',
            ]);
        }

        return $asignacion->fresh();
    }

    #[Test]
    public function horas_previstas_excluye_no_lectivo_de_centro(): void
    {
        NoLectivoIes::factory()->create(['fecha' => '2026-09-09']); // miercoles

        $asignacion = $this->asignacionConHorarioLV();
        $servicio   = new HorarioAsignacionService(new NoLectivoIesService());

        // 5 dias * 4h = 20h; menos el miercoles no lectivo = 16h
        $this->assertEquals(16.0, $servicio->horasPrevistas($asignacion));
    }

    #[Test]
    public function horas_semana_excluye_no_lectivo_de_centro(): void
    {
        NoLectivoIes::factory()->create(['fecha' => '2026-09-10']); // jueves

        $asignacion = $this->asignacionConHorarioLV();
        $servicio   = new HorarioAsignacionService(new NoLectivoIesService());

        $resultado = $servicio->horasSemana($asignacion, Carbon::parse('2026-09-07'));

        $this->assertEquals(16.0, $resultado['previstas']);
    }

    #[Test]
    public function dias_laborables_excluye_no_lectivo_de_centro(): void
    {
        NoLectivoIes::factory()->create(['fecha' => '2026-09-11']); // viernes

        $asignacion = $this->asignacionConHorarioLV();
        $servicio   = new CalendarioAsignacionService(new NoLectivoIesService());

        $this->assertEquals(4, $servicio->diasLaborables($asignacion));
    }

    #[Test]
    public function resolver_estados_marca_no_lectivo_de_centro_con_motivo_fijo(): void
    {
        NoLectivoIes::factory()->create(['fecha' => '2026-09-08', 'motivo' => 'Motivo interno admin']);

        $asignacion = $this->asignacionConHorarioLV();
        $servicio   = new CuadernoCalendarioService(new NoLectivoIesService());

        $estados = $servicio->resolverEstados($asignacion, collect([Carbon::parse('2026-09-08')]));
        $estado  = $estados->get('2026-09-08');

        $this->assertEquals('festivo', $estado['estado']);
        $this->assertEquals('No lectivo', $estado['motivo']);
    }

    #[Test]
    public function resolver_estados_no_lectivo_centro_gana_sobre_festivo_asignacion(): void
    {
        $asignacion = $this->asignacionConHorarioLV();

        CalendarioAsignacion::factory()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-09',
            'tipo'          => 'festivo',
            'motivo'        => 'Puente local asignacion individual',
        ]);

        NoLectivoIes::factory()->create(['fecha' => '2026-09-09']);

        $servicio = new CuadernoCalendarioService(new NoLectivoIesService());

        $estados = $servicio->resolverEstados($asignacion, collect([Carbon::parse('2026-09-09')]));
        $estado  = $estados->get('2026-09-09');

        $this->assertEquals('festivo', $estado['estado']);
        $this->assertEquals('No lectivo', $estado['motivo']);
    }

    #[Test]
    public function resolver_estados_dia_sin_no_lectivo_mantiene_festivo_de_asignacion(): void
    {
        $asignacion = $this->asignacionConHorarioLV();

        CalendarioAsignacion::factory()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-09',
            'tipo'          => 'festivo',
            'motivo'        => 'Puente local asignacion individual',
        ]);

        $servicio = new CuadernoCalendarioService(new NoLectivoIesService());

        $estados = $servicio->resolverEstados($asignacion, collect([Carbon::parse('2026-09-09')]));
        $estado  = $estados->get('2026-09-09');

        $this->assertEquals('festivo', $estado['estado']);
        $this->assertEquals('Puente local asignacion individual', $estado['motivo']);
    }
}
