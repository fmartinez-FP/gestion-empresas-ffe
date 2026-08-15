<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeguimientoIesControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // ajustarHorasSemana
    // =========================================================================

    #[Test]
    public function tutor_ies_puede_ajustar_horas_de_su_asignacion(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $response = $this->actingAs($tutor)->post(
            route('asignaciones.ajustes-horas-semana.store', $asignacion),
            ['semana' => '2026-09-07', 'ajuste' => 2.5, 'motivo' => 'Recuperación']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('ajuste_horas_semana', [
            'asignacion_id' => $asignacion->id,
            'ajuste'        => 2.5,
        ]);
    }

    #[Test]
    public function profesor_no_tutor_no_puede_ajustar_horas(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $otro       = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $response = $this->actingAs($otro)->post(
            route('asignaciones.ajustes-horas-semana.store', $asignacion),
            ['semana' => '2026-09-07', 'ajuste' => 2.5]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ajuste_horas_semana', ['asignacion_id' => $asignacion->id]);
    }

    #[Test]
    public function admin_puede_ajustar_horas_de_cualquier_asignacion(): void
    {
        $admin      = User::factory()->create(['rol' => 'admin']);
        $asignacion = AsignacionFct::factory()->create(['estado' => 'activa']);

        $response = $this->actingAs($admin)->post(
            route('asignaciones.ajustes-horas-semana.store', $asignacion),
            ['semana' => '2026-09-07', 'ajuste' => -1.5]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('ajuste_horas_semana', [
            'asignacion_id' => $asignacion->id,
            'ajuste'        => -1.5,
        ]);
    }

    // =========================================================================
    // marcarNoTrabajado
    // =========================================================================

    #[Test]
    public function tutor_ies_puede_marcar_dia_no_trabajado_de_su_asignacion(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
            'fecha_inicio' => '2026-09-07',
            'fecha_fin'    => '2026-09-18',
        ]);

        $response = $this->actingAs($tutor)->post(
            route('asignaciones.marcar-no-trabajado.store', $asignacion),
            ['fecha' => '2026-09-08', 'motivo' => 'Baja médica']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('calendario_asignacion', [
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-08',
            'tipo'          => 'ausencia_no_justificada',
        ]);
    }

    #[Test]
    public function profesor_no_tutor_no_puede_marcar_dia_no_trabajado(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $otro       = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
            'fecha_inicio' => '2026-09-07',
            'fecha_fin'    => '2026-09-18',
        ]);

        $response = $this->actingAs($otro)->post(
            route('asignaciones.marcar-no-trabajado.store', $asignacion),
            ['fecha' => '2026-09-08', 'motivo' => 'Baja médica']
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('calendario_asignacion', ['asignacion_id' => $asignacion->id]);
    }

    #[Test]
    public function admin_puede_marcar_dia_no_trabajado_de_cualquier_asignacion(): void
    {
        $admin      = User::factory()->create(['rol' => 'admin']);
        $asignacion = AsignacionFct::factory()->create([
            'estado'       => 'activa',
            'fecha_inicio' => '2026-09-07',
            'fecha_fin'    => '2026-09-18',
        ]);

        $response = $this->actingAs($admin)->post(
            route('asignaciones.marcar-no-trabajado.store', $asignacion),
            ['fecha' => '2026-09-08']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('calendario_asignacion', [
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-08',
        ]);
    }
}
