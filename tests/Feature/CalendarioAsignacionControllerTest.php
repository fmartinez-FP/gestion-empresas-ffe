<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarioAsignacionControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // index (lectura — verAsignacion)
    // =========================================================================

    #[Test]
    public function tutor_ies_puede_ver_calendario_de_su_asignacion(): void
    {
        $tutor = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($tutor)
            ->get(route('asignaciones.calendario.index', $asignacion))
            ->assertOk();
    }

    #[Test]
    public function profesor_no_tutor_no_puede_ver_calendario(): void
    {
        $tutor = User::factory()->create(['rol' => 'profesor']);
        $otro  = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($otro)
            ->get(route('asignaciones.calendario.index', $asignacion))
            ->assertStatus(403);
    }

    #[Test]
    public function admin_puede_ver_calendario_de_cualquier_asignacion(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $asignacion = AsignacionFct::factory()->create(['estado' => 'activa']);

        $this->actingAs($admin)
            ->get(route('asignaciones.calendario.index', $asignacion))
            ->assertOk();
    }

    // =========================================================================
    // store (escritura — editarAsignacion, IDOR corregido en esta sesión)
    // =========================================================================

    #[Test]
    public function tutor_ies_puede_marcar_dia_en_calendario_de_su_asignacion(): void
    {
        $tutor = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($tutor)
            ->post(route('asignaciones.calendario.store', $asignacion), [
                'fecha'  => '2026-09-08',
                'tipo'   => 'festivo',
                'motivo' => 'Fiesta local',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('calendario_asignacion', [
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-08',
            'tipo'          => 'festivo',
        ]);
    }

    #[Test]
    public function profesor_no_tutor_no_puede_marcar_dia_en_calendario_ajeno(): void
    {
        $tutor = User::factory()->create(['rol' => 'profesor']);
        $otro  = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($otro)
            ->post(route('asignaciones.calendario.store', $asignacion), [
                'fecha' => '2026-09-08',
                'tipo'  => 'festivo',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('calendario_asignacion', ['asignacion_id' => $asignacion->id]);
    }

    /**
     * Regresión del IDOR corregido en esta sesión: antes store()/destroy() usaban
     * el gate de lectura verAsignacion, que da acceso sin restricción a
     * responsable_ciclo para CUALQUIER asignación, no solo las de su ciclo.
     * Confirmado con Fernando: escritura en calendario de asignación debe seguir
     * el mismo criterio que editarAsignacion (admin/responsable_ffe sin
     * restricción, resto solo si es el tutor_ies exacto).
     */
    #[Test]
    public function responsable_ciclo_ajeno_no_puede_marcar_dia_en_calendario(): void
    {
        $responsable = User::factory()->create(['rol' => 'responsable_ciclo']);
        $asignacion  = AsignacionFct::factory()->create(['estado' => 'activa']);

        $this->actingAs($responsable)
            ->post(route('asignaciones.calendario.store', $asignacion), [
                'fecha' => '2026-09-08',
                'tipo'  => 'festivo',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('calendario_asignacion', ['asignacion_id' => $asignacion->id]);
    }

    #[Test]
    public function admin_puede_marcar_dia_en_calendario_de_cualquier_asignacion(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $asignacion = AsignacionFct::factory()->create(['estado' => 'activa']);

        $this->actingAs($admin)
            ->post(route('asignaciones.calendario.store', $asignacion), [
                'fecha' => '2026-09-08',
                'tipo'  => 'no_lectivo',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('calendario_asignacion', [
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-08',
            'tipo'          => 'no_lectivo',
        ]);
    }

    // =========================================================================
    // destroy (escritura — editarAsignacion, IDOR corregido en esta sesión)
    // =========================================================================

    #[Test]
    public function tutor_ies_puede_eliminar_dia_de_su_calendario(): void
    {
        $tutor = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($tutor)->post(route('asignaciones.calendario.store', $asignacion), [
            'fecha' => '2026-09-08',
            'tipo'  => 'festivo',
        ]);
        $calendario = CalendarioAsignacion::where('asignacion_id', $asignacion->id)->firstOrFail();

        $this->actingAs($tutor)
            ->delete(route('asignaciones.calendario.destroy', [$asignacion, $calendario]))
            ->assertRedirect();

        $this->assertDatabaseMissing('calendario_asignacion', ['id' => $calendario->id]);
    }

    #[Test]
    public function profesor_no_tutor_no_puede_eliminar_dia_de_calendario_ajeno(): void
    {
        $tutor = User::factory()->create(['rol' => 'profesor']);
        $otro  = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($tutor)->post(route('asignaciones.calendario.store', $asignacion), [
            'fecha' => '2026-09-08',
            'tipo'  => 'festivo',
        ]);
        $calendario = CalendarioAsignacion::where('asignacion_id', $asignacion->id)->firstOrFail();

        $this->actingAs($otro)
            ->delete(route('asignaciones.calendario.destroy', [$asignacion, $calendario]))
            ->assertStatus(403);

        $this->assertDatabaseHas('calendario_asignacion', ['id' => $calendario->id]);
    }

    #[Test]
    public function responsable_ciclo_ajeno_no_puede_eliminar_dia_de_calendario(): void
    {
        $tutor = User::factory()->create(['rol' => 'profesor']);
        $responsable = User::factory()->create(['rol' => 'responsable_ciclo']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($tutor)->post(route('asignaciones.calendario.store', $asignacion), [
            'fecha' => '2026-09-08',
            'tipo'  => 'festivo',
        ]);
        $calendario = CalendarioAsignacion::where('asignacion_id', $asignacion->id)->firstOrFail();

        $this->actingAs($responsable)
            ->delete(route('asignaciones.calendario.destroy', [$asignacion, $calendario]))
            ->assertStatus(403);

        $this->assertDatabaseHas('calendario_asignacion', ['id' => $calendario->id]);
    }

    #[Test]
    public function admin_puede_eliminar_dia_de_calendario_de_cualquier_asignacion(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $asignacion = AsignacionFct::factory()->create(['estado' => 'activa']);

        $this->actingAs($admin)->post(route('asignaciones.calendario.store', $asignacion), [
            'fecha' => '2026-09-08',
            'tipo'  => 'festivo',
        ]);
        $calendario = CalendarioAsignacion::where('asignacion_id', $asignacion->id)->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('asignaciones.calendario.destroy', [$asignacion, $calendario]))
            ->assertRedirect();

        $this->assertDatabaseMissing('calendario_asignacion', ['id' => $calendario->id]);
    }

    #[Test]
    public function dia_de_otra_asignacion_devuelve_404(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $asignacion1 = AsignacionFct::factory()->create(['estado' => 'activa']);
        $asignacion2 = AsignacionFct::factory()->create(['estado' => 'activa']);

        $this->actingAs($admin)->post(route('asignaciones.calendario.store', $asignacion2), [
            'fecha' => '2026-09-08',
            'tipo'  => 'festivo',
        ]);
        $calendario = CalendarioAsignacion::where('asignacion_id', $asignacion2->id)->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('asignaciones.calendario.destroy', [$asignacion1, $calendario]))
            ->assertStatus(404);

        $this->assertDatabaseHas('calendario_asignacion', ['id' => $calendario->id]);
    }
}
