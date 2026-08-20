<?php

namespace Tests\Feature;

use App\Models\AjusteHorasSemana;
use App\Models\AsignacionFct;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AjusteHorasSemanaTest extends TestCase
{
    use RefreshDatabase;

    private function asignacion(array $attrs = []): AsignacionFct
    {
        return AsignacionFct::factory()->create(array_merge([
            'fecha_inicio' => '2026-09-07',
            'fecha_fin'    => '2026-09-18',
        ], $attrs));
    }

    #[Test]
    public function se_puede_crear_un_ajuste_asociado_a_una_asignacion()
    {
        $asignacion = $this->asignacion();
        $usuario    = User::factory()->create();

        $ajuste = AjusteHorasSemana::factory()->create([
            'asignacion_id' => $asignacion->id,
            'semana'        => '2026-09-07',
            'ajuste'        => 3.5,
            'motivo'        => 'Recuperación de horas por incidencia técnica en la empresa.',
            'created_by_id' => $usuario->id,
        ]);

        $this->assertDatabaseHas('ajuste_horas_semana', [
            'id'            => $ajuste->id,
            'asignacion_id' => $asignacion->id,
            'created_by_id' => $usuario->id,
        ]);
        $this->assertEquals(3.5, $ajuste->ajuste);
    }

    #[Test]
    public function estado_positivo_genera_un_ajuste_mayor_que_cero()
    {
        $ajuste = AjusteHorasSemana::factory()->positivo()->create();

        $this->assertGreaterThan(0, $ajuste->ajuste);
    }

    #[Test]
    public function estado_negativo_genera_un_ajuste_menor_que_cero()
    {
        $ajuste = AjusteHorasSemana::factory()->negativo()->create();

        $this->assertLessThan(0, $ajuste->ajuste);
    }

    #[Test]
    public function no_se_puede_duplicar_semana_para_la_misma_asignacion()
    {
        $asignacion = $this->asignacion();

        AjusteHorasSemana::factory()->create([
            'asignacion_id' => $asignacion->id,
            'semana'        => '2026-09-07',
        ]);

        $this->expectException(QueryException::class);

        AjusteHorasSemana::factory()->create([
            'asignacion_id' => $asignacion->id,
            'semana'        => '2026-09-07',
        ]);
    }

    #[Test]
    public function ajustes_se_borran_en_cascada_al_borrar_definitivamente_la_asignacion()
    {
        $asignacion = $this->asignacion();

        AjusteHorasSemana::factory()->create(['asignacion_id' => $asignacion->id]);

        $asignacion->forceDelete();

        $this->assertDatabaseCount('ajuste_horas_semana', 0);
    }

    #[Test]
    public function relacion_ajustes_horas_ordena_por_semana_ascendente()
    {
        $asignacion = $this->asignacion();

        AjusteHorasSemana::factory()->create(['asignacion_id' => $asignacion->id, 'semana' => '2026-09-14']);
        AjusteHorasSemana::factory()->create(['asignacion_id' => $asignacion->id, 'semana' => '2026-09-07']);

        $semanas = $asignacion->ajustesHoras()->pluck('semana')->map->toDateString()->all();

        $this->assertEquals(['2026-09-07', '2026-09-14'], $semanas);
    }

    #[Test]
    public function creado_por_apunta_al_usuario_que_registro_el_ajuste()
    {
        $usuario = User::factory()->create();
        $ajuste  = AjusteHorasSemana::factory()->create(['created_by_id' => $usuario->id]);

        $this->assertTrue($ajuste->creadoPor->is($usuario));
    }
}
