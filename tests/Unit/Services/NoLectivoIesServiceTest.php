<?php

namespace Tests\Unit\Services;

use App\Models\NoLectivoIes;
use App\Services\NoLectivoIesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoLectivoIesServiceTest extends TestCase
{
    use RefreshDatabase;

    private NoLectivoIesService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = new NoLectivoIesService();
    }

    #[Test]
    public function marcar_rango_crea_solo_dias_laborables_lv(): void
    {
        // Lunes 2026-08-24 a Domingo 2026-08-30 (semana completa)
        $inicio = Carbon::parse('2026-08-24');
        $fin    = Carbon::parse('2026-08-30');

        $creados = $this->servicio->marcarRango($inicio, $fin, 'Vacaciones de prueba');

        $this->assertCount(5, $creados);
        $this->assertDatabaseCount('no_lectivos_ies', 5);
        $this->assertDatabaseHas('no_lectivos_ies', ['fecha' => '2026-08-24', 'motivo' => 'Vacaciones de prueba']);
        $this->assertDatabaseMissing('no_lectivos_ies', ['fecha' => '2026-08-29']); // sabado
        $this->assertDatabaseMissing('no_lectivos_ies', ['fecha' => '2026-08-30']); // domingo
    }

    #[Test]
    public function marcar_rango_hace_upsert_sobre_fecha_existente(): void
    {
        $fecha = Carbon::parse('2026-09-07'); // lunes

        $this->servicio->marcarRango($fecha, $fecha, 'Motivo original');
        $this->assertDatabaseCount('no_lectivos_ies', 1);

        $this->servicio->marcarRango($fecha, $fecha, 'Motivo corregido');

        $this->assertDatabaseCount('no_lectivos_ies', 1);
        $this->assertDatabaseHas('no_lectivos_ies', ['fecha' => '2026-09-07', 'motivo' => 'Motivo corregido']);
    }

    #[Test]
    public function eliminar_hace_soft_delete(): void
    {
        $dia = NoLectivoIes::factory()->create(['fecha' => '2026-09-08']);

        $this->servicio->eliminar($dia);

        $this->assertSoftDeleted('no_lectivos_ies', ['id' => $dia->id]);
    }

    #[Test]
    public function fechas_excluidas_sin_rango_devuelve_todas(): void
    {
        NoLectivoIes::factory()->create(['fecha' => '2026-09-01']);
        NoLectivoIes::factory()->create(['fecha' => '2026-12-25']);

        $fechas = $this->servicio->fechasExcluidas();

        $this->assertCount(2, $fechas);
        $this->assertTrue($fechas->contains('2026-09-01'));
        $this->assertTrue($fechas->contains('2026-12-25'));
    }

    #[Test]
    public function fechas_excluidas_con_rango_filtra_correctamente(): void
    {
        NoLectivoIes::factory()->create(['fecha' => '2026-09-01']);
        NoLectivoIes::factory()->create(['fecha' => '2026-12-25']);

        $fechas = $this->servicio->fechasExcluidas(
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-09-30')
        );

        $this->assertCount(1, $fechas);
        $this->assertTrue($fechas->contains('2026-09-01'));
    }

    #[Test]
    public function fechas_excluidas_no_incluye_soft_deleted(): void
    {
        $dia = NoLectivoIes::factory()->create(['fecha' => '2026-09-15']);
        $this->servicio->eliminar($dia);

        $fechas = $this->servicio->fechasExcluidas();

        $this->assertFalse($fechas->contains('2026-09-15'));
    }
}
