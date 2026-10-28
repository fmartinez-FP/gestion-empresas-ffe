<?php

namespace Tests\Feature;

use App\Livewire\GestorDirecciones;
use App\Models\Alumno;
use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use App\Models\CicloFormativo;
use App\Models\Configuracion;
use App\Models\Direccion;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\PersonaContacto;
use App\Models\SeguimientoDiario;
use App\Models\User;
use App\Services\TokenTutorEmpresaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regresión del Bloque B (flujo FFE): coherencia de asignaciones, onboarding
 * seguro, cuaderno de seguimiento, histórico y sedes en uso.
 */
class BloqueBLogicaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $profesor;
    private Alumno $alumno;
    private Empresa $empresa;
    private CicloFormativo $ciclo;

    protected function setUp(): void
    {
        parent::setUp();
        Configuracion::setCursoActivo('2025-2026');

        $this->ciclo    = CicloFormativo::factory()->create();
        $this->admin    = User::factory()->create(['rol' => 'admin', 'activo' => true]);
        $this->profesor = User::factory()->create(['rol' => 'profesor', 'activo' => true]);
        $this->empresa  = Empresa::factory()->create();
        $this->alumno   = Alumno::factory()->create([
            'grupo_id'        => Grupo::factory()->create(['ciclo_id' => $this->ciclo->id, 'numero_curso' => 2])->id,
            'curso_academico' => '2025-2026',
        ]);
        $this->profesor->sincronizarGruposTutor([$this->alumno->grupo_id]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->admin->id,
            'fecha_inicio' => '2026-10-05',
            'fecha_fin'    => '2026-10-09',
            'horarios'     => [['dia' => 'lunes', 'entrada_manana' => '09:00', 'salida_manana' => '15:00']],
        ], $extra);
    }

    private function asignacionActiva(array $attrs = []): AsignacionFct
    {
        return AsignacionFct::factory()->create(array_merge([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->profesor->id,
            'estado'       => 'activa',
            'fecha_inicio' => today()->subDays(3)->toDateString(),
            'fecha_fin'    => today()->addDays(30)->toDateString(),
        ], $attrs));
    }

    // =========================================================================
    // Asignaciones: coherencia
    // =========================================================================

    #[Test]
    public function no_acepta_sede_ni_tutor_de_empresa_de_otra_empresa(): void
    {
        Mail::fake();
        $otra = Empresa::factory()->create();
        $sedeAjena = Direccion::factory()->create(['empresa_id' => $otra->id]);
        $tutorAjeno = PersonaContacto::factory()->create(['empresa_id' => $otra->id]);

        $this->actingAs($this->admin)
            ->post(route('asignaciones.store', $this->alumno), $this->payload([
                'sede_id' => $sedeAjena->id,
                'tutor_empresa_id' => $tutorAjeno->id,
            ]))
            ->assertSessionHasErrors(['sede_id', 'tutor_empresa_id']);

        $this->assertDatabaseCount('asignaciones_fct', 0);
    }

    #[Test]
    public function acepta_sede_y_tutor_de_la_propia_empresa(): void
    {
        Mail::fake();
        $sede = Direccion::factory()->create(['empresa_id' => $this->empresa->id]);
        $tutor = PersonaContacto::factory()->create(['empresa_id' => $this->empresa->id]);

        $this->actingAs($this->admin)
            ->post(route('asignaciones.store', $this->alumno), $this->payload([
                'sede_id' => $sede->id,
                'tutor_empresa_id' => $tutor->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('asignaciones_fct', ['alumno_id' => $this->alumno->id, 'sede_id' => $sede->id]);
    }

    #[Test]
    public function no_permite_dos_asignaciones_activas_solapadas_del_mismo_alumno(): void
    {
        Mail::fake();
        $this->asignacionActiva(['fecha_inicio' => '2026-10-07', 'fecha_fin' => '2026-10-20']);

        $this->actingAs($this->admin)
            ->post(route('asignaciones.store', $this->alumno), $this->payload())
            ->assertSessionHasErrors('fecha_inicio');

        $this->assertDatabaseCount('asignaciones_fct', 1);

        // Fuera de rango: sí se permite.
        $this->actingAs($this->admin)
            ->post(route('asignaciones.store', $this->alumno), $this->payload(['fecha_inicio' => '2026-11-02', 'fecha_fin' => '2026-11-06']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('asignaciones_fct', 2);
    }

    #[Test]
    public function una_asignacion_cancelada_no_bloquea_fechas(): void
    {
        Mail::fake();
        $this->asignacionActiva(['fecha_inicio' => '2026-10-01', 'fecha_fin' => '2026-10-30', 'estado' => 'cancelada']);

        $this->actingAs($this->admin)
            ->post(route('asignaciones.store', $this->alumno), $this->payload())
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function profesor_puede_listar_sedes_y_contactos_de_una_empresa_que_no_gestiona(): void
    {
        Direccion::factory()->create(['empresa_id' => $this->empresa->id]);
        PersonaContacto::factory()->create(['empresa_id' => $this->empresa->id]);

        $this->actingAs($this->profesor)->getJson(route('interna.empresa.sedes', $this->empresa))->assertOk()->assertJsonCount(1);
        $this->actingAs($this->profesor)->getJson(route('interna.empresa.contactos', $this->empresa))->assertOk()->assertJsonCount(1);
        $this->actingAs(User::factory()->create(['rol' => 'alumno']))->getJson(route('interna.empresa.sedes', $this->empresa))->assertForbidden();
    }

    // =========================================================================
    // Onboarding
    // =========================================================================

    #[Test]
    public function alumno_sin_email_no_rompe_la_asignacion_y_avisa(): void
    {
        Mail::fake();
        $this->alumno->update(['email' => null]);

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), $this->payload());

        $resp->assertRedirect();
        $resp->assertSessionHas('warning');
        $this->assertDatabaseHas('asignaciones_fct', ['alumno_id' => $this->alumno->id]);
        $this->assertNull($this->alumno->fresh()->user_id);
    }

    #[Test]
    public function no_vincula_al_alumno_con_un_usuario_del_personal_con_su_mismo_email(): void
    {
        Mail::fake();
        $personal = User::factory()->create(['rol' => 'profesor', 'email' => $this->alumno->email]);

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), $this->payload());

        $resp->assertSessionHas('warning');
        $this->assertNull($this->alumno->fresh()->user_id);
        $this->assertSame('profesor', $personal->fresh()->rol);
    }

    #[Test]
    public function un_fallo_de_smtp_no_deshace_la_asignacion_ni_la_cuenta(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP caído'));

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), $this->payload());

        $resp->assertRedirect();
        $resp->assertSessionHas('warning');
        $this->assertDatabaseCount('asignaciones_fct', 1);
        $this->assertNotNull($this->alumno->fresh()->user_id);
    }

    #[Test]
    public function resetear_password_de_alumno_sin_cuenta_la_crea(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)
            ->post(route('alumnos.resetear-password', $this->alumno))
            ->assertSessionHas('success');

        $this->assertNotNull($this->alumno->fresh()->user_id);
    }

    // =========================================================================
    // Cuaderno
    // =========================================================================

    #[Test]
    public function confirmar_no_borra_el_comentario_del_tutor_de_empresa(): void
    {
        $asignacion = $this->asignacionActiva();
        $seg = SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion->id, 'fecha' => today(), 'comentario_tutor' => 'Buen trabajo (empresa)', 'confirmado_tutor' => false,
        ]);

        $this->actingAs($this->profesor)
            ->post(route('asignaciones.seguimientos.confirmar', [$asignacion, $seg]), ['comentario_ies' => 'Visto'])
            ->assertSessionHasNoErrors();

        $seg->refresh();
        $this->assertTrue($seg->confirmado_tutor);
        $this->assertSame('Buen trabajo (empresa)', $seg->comentario_tutor);
        $this->assertSame('Visto', $seg->comentario_ies);
    }

    #[Test]
    public function el_tutor_de_empresa_no_puede_cambiar_su_comentario_tras_la_confirmacion(): void
    {
        $asignacion = $this->asignacionActiva();
        $seg = SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion->id, 'fecha' => today(), 'comentario_tutor' => 'Original', 'confirmado_tutor' => true,
        ]);
        $token = app(TokenTutorEmpresaService::class)->generar($asignacion)['tokenPlano'];

        $this->post(route('tutor.comentar', ['token' => $token, 'seguimiento' => $seg]), ['comentario_tutor' => 'Cambiado'])
            ->assertSessionHas('error');

        $this->assertSame('Original', $seg->fresh()->comentario_tutor);
    }

    #[Test]
    public function el_tutor_ies_puede_retirar_una_confirmacion_y_otro_profesor_no(): void
    {
        $asignacion = $this->asignacionActiva();
        $seg = SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion->id, 'fecha' => today(), 'confirmado_tutor' => true, 'confirmado_at' => now(),
        ]);
        $otro = User::factory()->create(['rol' => 'profesor', 'activo' => true]);

        $this->actingAs($otro)
            ->post(route('asignaciones.seguimientos.desconfirmar', [$asignacion, $seg]))
            ->assertForbidden();
        $this->assertTrue($seg->fresh()->confirmado_tutor);

        $this->actingAs($this->profesor)
            ->post(route('asignaciones.seguimientos.desconfirmar', [$asignacion, $seg]))
            ->assertSessionHas('success');
        $this->assertFalse($seg->fresh()->confirmado_tutor);
    }

    #[Test]
    public function el_tutor_ies_puede_ver_la_evidencia_del_alumno_y_otros_no(): void
    {
        Storage::fake('private');
        $asignacion = $this->asignacionActiva();
        Storage::disk('private')->put('seguimientos/x/foto.png', 'contenido-evidencia');
        $seg = SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion->id, 'fecha' => today(), 'evidencia_path' => 'seguimientos/x/foto.png',
        ]);
        $otro = User::factory()->create(['rol' => 'profesor', 'activo' => true]);

        $this->actingAs($this->profesor)
            ->get(route('asignaciones.seguimientos.evidencia', [$asignacion, $seg]))
            ->assertOk();
        $this->actingAs($otro)
            ->get(route('asignaciones.seguimientos.evidencia', [$asignacion, $seg]))
            ->assertForbidden();
    }

    private function alumnoPortal(AsignacionFct $asignacion): User
    {
        $user = User::factory()->create(['rol' => 'alumno']);
        $asignacion->alumno->update(['user_id' => $user->id]);

        return $user;
    }

    private function entradaDia(): array
    {
        return ['descripcion_tareas' => 'Trabajo', 'hora_entrada' => '09:00', 'hora_salida' => '14:00'];
    }

    #[Test]
    public function el_alumno_no_puede_registrar_entrada_en_fin_de_semana(): void
    {
        $this->travelTo(now()->next('Saturday')->setTime(10, 0));
        $asignacion = $this->asignacionActiva();
        $user = $this->alumnoPortal($asignacion);

        $this->actingAs($user, 'web_externo')
            ->post(route('portal.cuaderno.store'), $this->entradaDia())
            ->assertRedirect(route('portal.cuaderno.index'))
            ->assertSessionHas('info');

        $this->assertDatabaseCount('seguimiento_diario', 0);
    }

    #[Test]
    public function el_alumno_no_puede_registrar_entrada_en_un_dia_festivo_de_su_calendario(): void
    {
        $this->travelTo(now()->next('Wednesday')->setTime(10, 0));
        $asignacion = $this->asignacionActiva();
        $user = $this->alumnoPortal($asignacion);
        CalendarioAsignacion::create(['asignacion_id' => $asignacion->id, 'fecha' => today()->toDateString(), 'tipo' => 'festivo', 'motivo' => 'Local']);

        $this->actingAs($user, 'web_externo')
            ->post(route('portal.cuaderno.store'), $this->entradaDia())
            ->assertSessionHas('info');

        $this->assertDatabaseCount('seguimiento_diario', 0);
    }

    #[Test]
    public function el_alumno_si_puede_registrar_entrada_en_un_dia_laborable(): void
    {
        $this->travelTo(now()->next('Wednesday')->setTime(10, 0));
        $asignacion = $this->asignacionActiva();
        $user = $this->alumnoPortal($asignacion);

        $this->actingAs($user, 'web_externo')
            ->post(route('portal.cuaderno.store'), $this->entradaDia())
            ->assertRedirect(route('portal.cuaderno.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('seguimiento_diario', 1);
    }

    // =========================================================================
    // Histórico y sedes
    // =========================================================================

    #[Test]
    public function finalizar_desde_el_formulario_registra_el_historico_de_colocaciones(): void
    {
        $asignacion = $this->asignacionActiva(['numero_curso' => 2]);
        $asignacion->horarios()->create([
            'dia' => 'lunes', 'entrada_manana' => '09:00', 'salida_manana' => '15:00',
        ]);

        $this->actingAs($this->admin)
            ->put(route('asignaciones.update', $asignacion), [
                'empresa_id'   => $asignacion->empresa_id,
                'tutor_ies_id' => $asignacion->tutor_ies_id,
                'fecha_inicio' => $asignacion->fecha_inicio->toDateString(),
                'fecha_fin'    => $asignacion->fecha_fin->toDateString(),
                'estado'       => 'finalizada',
                'horarios'     => [['dia' => 'lunes', 'entrada_manana' => '09:00', 'salida_manana' => '15:00']],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('finalizada', $asignacion->fresh()->estado);
        $this->assertDatabaseHas('colocacion_asignacion_fct', ['asignacion_id' => $asignacion->id]);
    }

    #[Test]
    public function no_se_puede_eliminar_una_sede_usada_por_una_asignacion(): void
    {
        $sede = Direccion::factory()->create(['empresa_id' => $this->empresa->id]);
        $this->asignacionActiva(['sede_id' => $sede->id]);

        Livewire::actingAs($this->admin)
            ->test(GestorDirecciones::class, ['empresa' => $this->empresa])
            ->call('eliminar', $sede->id);

        $this->assertDatabaseHas('direcciones', ['id' => $sede->id]);

        // Sin asignaciones que la usen, sí se borra.
        $libre = Direccion::factory()->create(['empresa_id' => $this->empresa->id]);
        Livewire::actingAs($this->admin)
            ->test(GestorDirecciones::class, ['empresa' => $this->empresa])
            ->call('eliminar', $libre->id);
        $this->assertDatabaseMissing('direcciones', ['id' => $libre->id]);
    }
}
