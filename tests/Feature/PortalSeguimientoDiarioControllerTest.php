<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalSeguimientoDiarioControllerTest extends TestCase
{
    use RefreshDatabase;

    private function alumnoConAsignacionActiva(): array
    {
        $user = User::factory()->create(['rol' => 'alumno']);
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => today()->subDays(5)->toDateString(),
            'fecha_fin'    => today()->addDays(30)->toDateString(),
            'estado'       => 'activa',
        ]);
        $asignacion->alumno->update(['user_id' => $user->id]);

        return [$user, $asignacion];
    }

    // =========================================================================
    // index() -- ya scoped por whereHas('alumno', user_id), sin parametro de
    // ruta manipulable. Confirmacion: cada alumno solo ve su propia asignacion.
    // =========================================================================

    #[Test]
    public function alumno_solo_ve_su_propio_cuaderno_en_el_index(): void
    {
        [$alumnoA, $asignacionA] = $this->alumnoConAsignacionActiva();
        [$alumnoB, $asignacionB] = $this->alumnoConAsignacionActiva();

        SeguimientoDiario::factory()->create([
            'asignacion_id'      => $asignacionA->id,
            'fecha'              => today()->toDateString(),
            'descripcion_tareas' => 'Tarea exclusiva de A',
        ]);
        SeguimientoDiario::factory()->create([
            'asignacion_id'      => $asignacionB->id,
            'fecha'              => today()->toDateString(),
            'descripcion_tareas' => 'Tarea exclusiva de B',
        ]);

        $this->actingAs($alumnoA, 'web_externo')
            ->get(route('portal.cuaderno.index'))
            ->assertOk()
            ->assertSee('Tarea exclusiva de A')
            ->assertDontSee('Tarea exclusiva de B');
    }

    // =========================================================================
    // edit() / update() -- IDOR real: {seguimiento} es un parametro de ruta
    // directo. Regresion contra el hallazgo pendiente de la v29.
    // =========================================================================

    #[Test]
    public function alumno_ajeno_no_puede_ver_formulario_editar_seguimiento_de_otro_alumno(): void
    {
        [$alumnoA, $asignacionA] = $this->alumnoConAsignacionActiva();
        [$alumnoB, $asignacionB] = $this->alumnoConAsignacionActiva();

        $seguimientoDeA = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacionA->id,
            'fecha'            => today()->toDateString(),
            'confirmado_tutor' => false,
        ]);

        $this->actingAs($alumnoB, 'web_externo')
            ->get(route('portal.cuaderno.edit', $seguimientoDeA))
            ->assertForbidden();
    }

    #[Test]
    public function alumno_ajeno_no_puede_actualizar_seguimiento_de_otro_alumno(): void
    {
        [$alumnoA, $asignacionA] = $this->alumnoConAsignacionActiva();
        [$alumnoB, $asignacionB] = $this->alumnoConAsignacionActiva();

        $seguimientoDeA = SeguimientoDiario::factory()->create([
            'asignacion_id'      => $asignacionA->id,
            'fecha'              => today()->toDateString(),
            'confirmado_tutor'   => false,
            'descripcion_tareas' => 'Original de A',
        ]);

        $this->actingAs($alumnoB, 'web_externo')
            ->put(route('portal.cuaderno.update', $seguimientoDeA), [
                'descripcion_tareas' => 'Intento de secuestro desde B',
                'hora_entrada'       => '09:00',
                'hora_salida'        => '14:00',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('seguimiento_diario', [
            'id'                 => $seguimientoDeA->id,
            'descripcion_tareas' => 'Original de A',
        ]);
    }

    // =========================================================================
    // create() / store() -- la asignacion se resuelve siempre por
    // whereHas('alumno', user_id), sin parametro de ruta; no hay superficie
    // de IDOR ahi, pero se confirma el aislamiento con dos alumnos.
    // =========================================================================

    #[Test]
    public function alumno_no_puede_registrar_entrada_para_la_asignacion_de_otro_alumno(): void
    {
        [$alumnoA, $asignacionA] = $this->alumnoConAsignacionActiva();
        [$alumnoB, $asignacionB] = $this->alumnoConAsignacionActiva();

        $this->actingAs($alumnoB, 'web_externo')
            ->post(route('portal.cuaderno.store'), [
                'descripcion_tareas' => 'Entrada de B',
                'hora_entrada'       => '09:00',
                'hora_salida'        => '14:00',
            ])
            ->assertRedirect(route('portal.cuaderno.index'));

        $this->assertDatabaseHas('seguimiento_diario', [
            'asignacion_id'      => $asignacionB->id,
            'descripcion_tareas' => 'Entrada de B',
        ]);
        $this->assertDatabaseMissing('seguimiento_diario', [
            'asignacion_id'      => $asignacionA->id,
            'descripcion_tareas' => 'Entrada de B',
        ]);
    }
}
