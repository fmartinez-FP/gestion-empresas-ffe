<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AsignacionFct;
use App\Models\CicloFormativo;
use App\Models\CriterioEvaluacion;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\ModuloProfesional;
use App\Models\ResultadoAprendizaje;
use App\Models\User;
use App\Services\OnboardingAlumnoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignacionFctTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $responsable;
    private User $profesor;
    private Alumno $alumno;
    private Empresa $empresa;
    private CicloFormativo $ciclo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ciclo      = CicloFormativo::factory()->create();
        $this->admin      = User::factory()->create(['rol' => 'admin', 'activo' => true]);
        $this->responsable = User::factory()->create(['rol' => 'responsable_ffe', 'activo' => true]);
        $this->profesor   = User::factory()->create(['rol' => 'profesor', 'activo' => true]);
        $this->empresa    = Empresa::factory()->create();
        $this->alumno     = Alumno::factory()->create([
            'grupo_id'        => Grupo::factory()->create(['ciclo_id' => $this->ciclo->id, 'numero_curso' => 2])->id,
            'curso_academico' => '2025-2026',
        ]);

        // El profesor de este fixture tutoriza el grupo del alumno de este fixture,
        // para que los tests del "camino feliz" (crear/ver formulario) no colisionen
        // con el scope de AlumnoPolicy::verAlumno() aplicado en crearAsignacion().
        $this->profesor->sincronizarGruposTutor([$this->alumno->grupo_id]);
    }

    /**
     * Payload de horario mínimo válido para Fase D: 2026-10-05 es lunes y
     * 2026-10-09 es viernes. Un único día configurado (lunes, 6h) permite
     * verificar num_horas calculado sin depender de un rango largo.
     */
    private function horarioMinimoValido(): array
    {
        return [
            'fecha_inicio' => '2026-10-05',
            'fecha_fin'    => '2026-10-09',
            'horarios'     => [
                ['dia' => 'lunes', 'entrada_manana' => '09:00', 'salida_manana' => '15:00'],
            ],
        ];
    }

    // =========================================================================
    // AUTORIZACIÓN — create
    // =========================================================================

    #[Test]
    public function invitado_no_puede_ver_formulario_crear_asignacion(): void
    {
        $resp = $this->get(route('asignaciones.create', $this->alumno));
        $resp->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_puede_ver_formulario_crear_asignacion(): void
    {
        $resp = $this->actingAs($this->admin)->get(route('asignaciones.create', $this->alumno));
        $resp->assertOk()->assertViewIs('asignaciones.create');
    }

    #[Test]
    public function profesor_puede_ver_formulario_crear_asignacion(): void
    {
        $resp = $this->actingAs($this->profesor)->get(route('asignaciones.create', $this->alumno));
        $resp->assertOk()->assertViewIs('asignaciones.create');
    }

    #[Test]
    public function profesor_no_puede_ver_formulario_crear_asignacion_de_alumno_de_grupo_ajeno(): void
    {
        $grupoAjeno = Grupo::factory()->create(['ciclo_id' => $this->ciclo->id, 'numero_curso' => 1]);
        $alumnoAjeno = Alumno::factory()->create([
            'grupo_id'        => $grupoAjeno->id,
            'curso_academico' => '2025-2026',
        ]);

        $resp = $this->actingAs($this->profesor)->get(route('asignaciones.create', $alumnoAjeno));
        $resp->assertForbidden();
    }

    #[Test]
    public function profesor_no_puede_crear_asignacion_de_alumno_de_grupo_ajeno(): void
    {
        $grupoAjeno = Grupo::factory()->create(['ciclo_id' => $this->ciclo->id, 'numero_curso' => 1]);
        $alumnoAjeno = Alumno::factory()->create([
            'grupo_id'        => $grupoAjeno->id,
            'curso_academico' => '2025-2026',
        ]);

        $resp = $this->actingAs($this->profesor)->post(route('asignaciones.store', $alumnoAjeno), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->profesor->id,
        ], $this->horarioMinimoValido()));

        $resp->assertForbidden();
        $this->assertDatabaseMissing('asignaciones_fct', ['alumno_id' => $alumnoAjeno->id]);
    }

    #[Test]
    public function profesor_no_puede_crear_asignacion_de_alumno_de_curso_academico_cerrado(): void
    {
        $alumnoCursoCerrado = Alumno::factory()->create([
            'grupo_id'        => $this->alumno->grupo_id,
            'curso_academico' => '2023-2024',
        ]);

        $resp = $this->actingAs($this->profesor)->post(route('asignaciones.store', $alumnoCursoCerrado), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->profesor->id,
        ], $this->horarioMinimoValido()));

        $resp->assertForbidden();
        $this->assertDatabaseMissing('asignaciones_fct', ['alumno_id' => $alumnoCursoCerrado->id]);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    #[Test]
    public function admin_puede_crear_asignacion(): void
    {
        $this->instance(OnboardingAlumnoService::class, Mockery::mock(OnboardingAlumnoService::class, function ($mock) {
            $mock->shouldReceive('crearCuentaAlumno')->once()->andReturn(User::factory()->create(['rol' => 'alumno']));
        }));

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->admin->id,
        ], [
            'fecha_inicio' => '2026-10-05',
            'fecha_fin'    => '2026-10-09',
            'horarios'     => [
                ['dia' => 'lunes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
                ['dia' => 'martes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
                ['dia' => 'miercoles', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
                ['dia' => 'jueves', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
                ['dia' => 'viernes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
            ],
        ]));

        $resp->assertRedirect();
        $this->assertDatabaseHas('asignaciones_fct', [
            'alumno_id'  => $this->alumno->id,
            'empresa_id' => $this->empresa->id,
            'estado'     => 'activa',
            'num_horas'  => 35, // 5 días x 7h
            'horario'    => 'Lunes a Viernes 08:00-15:00',
        ]);
        $this->assertDatabaseCount('horario_asignacion', 5);
    }

    #[Test]
    public function profesor_crear_asignacion_fuerza_tutor_ies_a_si_mismo(): void
    {
        $otroProfesor = User::factory()->create(['rol' => 'profesor', 'activo' => true]);

        $this->instance(OnboardingAlumnoService::class, Mockery::mock(OnboardingAlumnoService::class, function ($mock) {
            $mock->shouldReceive('crearCuentaAlumno')->once()->andReturn(User::factory()->create(['rol' => 'alumno']));
        }));

        $resp = $this->actingAs($this->profesor)->post(route('asignaciones.store', $this->alumno), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $otroProfesor->id, // intenta poner a otro profesor
        ], $this->horarioMinimoValido()));

        $resp->assertRedirect();
        $this->assertDatabaseHas('asignaciones_fct', [
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->profesor->id, // forzado al propio profesor, no al otro
        ]);
    }

    #[Test]
    public function crear_asignacion_sin_empresa_falla_validacion(): void
    {
        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), [
            'tutor_ies_id' => $this->admin->id,
        ]);
        $resp->assertSessionHasErrors('empresa_id');
    }

    #[Test]
    public function crear_asignacion_sin_tutor_ies_falla_validacion(): void
    {
        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), [
            'empresa_id' => $this->empresa->id,
        ]);
        $resp->assertSessionHasErrors('tutor_ies_id');
    }

    #[Test]
    public function crear_asignacion_sin_horarios_falla_validacion(): void
    {
        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), [
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->admin->id,
            'fecha_inicio' => '2026-10-05',
            'fecha_fin'    => '2026-10-09',
        ]);
        $resp->assertSessionHasErrors('horarios');
    }

    #[Test]
    public function fecha_fin_anterior_a_inicio_falla_validacion(): void
    {
        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), [
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->admin->id,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin'    => '2025-10-01',
        ]);
        $resp->assertSessionHasErrors('fecha_fin');
    }

    #[Test]
    public function onboarding_se_llama_solo_en_primera_asignacion(): void
    {
        // Primera asignación: debe llamar al servicio
        $mock = Mockery::mock(OnboardingAlumnoService::class);
        $mock->shouldReceive('crearCuentaAlumno')->once()->andReturn(User::factory()->create(['rol' => 'alumno']));
        $this->instance(OnboardingAlumnoService::class, $mock);

        $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->admin->id,
        ], $this->horarioMinimoValido()));

        // Segunda asignación: NO debe llamar al servicio
        $mock2 = Mockery::mock(OnboardingAlumnoService::class);
        $mock2->shouldReceive('crearCuentaAlumno')->never();
        $this->instance(OnboardingAlumnoService::class, $mock2);

        $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->admin->id,
        ], $this->horarioMinimoValido()));
    }

    #[Test]
    public function crear_asignacion_con_ra_y_ce_los_sincroniza(): void
    {
        $this->instance(OnboardingAlumnoService::class, Mockery::mock(OnboardingAlumnoService::class, function ($m) {
            $m->shouldReceive('crearCuentaAlumno')->once()->andReturn(User::factory()->create(['rol' => 'alumno']));
        }));

        $modulo = ModuloProfesional::factory()->create(['ciclo_id' => $this->ciclo->id]);
        $ra     = ResultadoAprendizaje::factory()->create(['modulo_id' => $modulo->id]);
        $ce     = CriterioEvaluacion::factory()->create(['resultado_aprendizaje_id' => $ra->id]);

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.store', $this->alumno), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->admin->id,
            'ra_ids'       => [$ra->id],
            'ce_ids'       => [$ce->id],
        ], $this->horarioMinimoValido()));

        $resp->assertRedirect();
        $asignacion = AsignacionFct::where('alumno_id', $this->alumno->id)->first();
        $this->assertNotNull($asignacion);
        $this->assertDatabaseHas('asignacion_ra', ['asignacion_id' => $asignacion->id, 'resultado_aprendizaje_id' => $ra->id]);
        $this->assertDatabaseHas('asignacion_ce', ['asignacion_id' => $asignacion->id, 'criterio_evaluacion_id' => $ce->id]);
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    #[Test]
    public function admin_puede_ver_detalle_asignacion(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'   => $this->alumno->id,
            'empresa_id'  => $this->empresa->id,
            'ciclo_id'    => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id,
        ]);

        $resp = $this->actingAs($this->admin)->get(route('asignaciones.show', $asignacion));
        $resp->assertOk()->assertViewIs('asignaciones.show');
    }

    #[Test]
    public function profesor_solo_ve_sus_asignaciones(): void
    {
        $otraAsignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id, // tutor es admin, no el profesor
        ]);

        $resp = $this->actingAs($this->profesor)->get(route('asignaciones.show', $otraAsignacion));
        $resp->assertForbidden();
    }

    #[Test]
    public function profesor_ve_asignacion_donde_es_tutor(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->profesor->id,
        ]);

        $resp = $this->actingAs($this->profesor)->get(route('asignaciones.show', $asignacion));
        $resp->assertOk();
    }

    // =========================================================================
    // EDIT / UPDATE
    // =========================================================================

    #[Test]
    public function admin_puede_ver_formulario_editar_asignacion(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id,
        ]);

        $resp = $this->actingAs($this->admin)->get(route('asignaciones.edit', $asignacion));
        $resp->assertOk()->assertViewIs('asignaciones.edit');
    }

    #[Test]
    public function profesor_no_puede_editar_asignacion_ajena(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id,
        ]);

        $resp = $this->actingAs($this->profesor)->get(route('asignaciones.edit', $asignacion));
        $resp->assertForbidden();
    }

    #[Test]
    public function admin_puede_actualizar_asignacion(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id,
        ]);

        $otraEmpresa = Empresa::factory()->create();

        $resp = $this->actingAs($this->admin)->put(route('asignaciones.update', $asignacion), array_merge([
            'empresa_id'   => $otraEmpresa->id,
            'tutor_ies_id' => $this->admin->id,
            'estado'       => 'finalizada',
        ], $this->horarioMinimoValido()));

        $resp->assertRedirect(route('asignaciones.show', $asignacion));
        $this->assertDatabaseHas('asignaciones_fct', [
            'id'         => $asignacion->id,
            'empresa_id' => $otraEmpresa->id,
            'num_horas'  => 6, // lunes 09:00-15:00
            'estado'     => 'finalizada',
        ]);
    }

    #[Test]
    public function profesor_no_puede_reasignar_tutor_ies_al_actualizar(): void
    {
        $otroProfesor = User::factory()->create(['rol' => 'profesor', 'activo' => true]);

        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->profesor->id,
        ]);

        $resp = $this->actingAs($this->profesor)->put(route('asignaciones.update', $asignacion), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $otroProfesor->id, // intenta transferirsela a otro profesor
        ], $this->horarioMinimoValido()));

        $resp->assertRedirect(route('asignaciones.show', $asignacion));
        $this->assertDatabaseHas('asignaciones_fct', [
            'id'           => $asignacion->id,
            'tutor_ies_id' => $this->profesor->id, // sigue siendo el mismo, no se transfirio
        ]);
    }

    #[Test]
    public function profesor_no_puede_cambiar_estado_en_update(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->profesor->id,
            'estado'       => 'activa',
        ]);

        $this->actingAs($this->profesor)->put(route('asignaciones.update', $asignacion), array_merge([
            'empresa_id'   => $this->empresa->id,
            'tutor_ies_id' => $this->profesor->id,
            'estado'       => 'finalizada',
        ], $this->horarioMinimoValido()));

        $this->assertDatabaseHas('asignaciones_fct', ['id' => $asignacion->id, 'estado' => 'activa']);
    }

    // =========================================================================
    // CANCELAR
    // =========================================================================

    #[Test]
    public function admin_puede_cancelar_asignacion_activa(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id,
            'estado'       => 'activa',
        ]);

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.cancelar', $asignacion), [
            'motivo_baja' => 'El alumno abandonó el programa por motivos personales.',
        ]);

        $resp->assertRedirect(route('asignaciones.show', $asignacion));
        $this->assertDatabaseHas('asignaciones_fct', [
            'id'     => $asignacion->id,
            'estado' => 'cancelada',
        ]);
    }

    #[Test]
    public function profesor_no_puede_cancelar_asignacion(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->profesor->id,
            'estado'       => 'activa',
        ]);

        $resp = $this->actingAs($this->profesor)->post(route('asignaciones.cancelar', $asignacion), [
            'motivo_baja' => 'Intento de cancelación no autorizado.',
        ]);

        $resp->assertForbidden();
        $this->assertDatabaseHas('asignaciones_fct', ['id' => $asignacion->id, 'estado' => 'activa']);
    }

    #[Test]
    public function no_se_puede_cancelar_asignacion_ya_cancelada(): void
    {
        $asignacion = AsignacionFct::factory()->cancelada()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id,
        ]);

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.cancelar', $asignacion), [
            'motivo_baja' => 'Segundo intento de cancelación.',
        ]);

        $resp->assertForbidden();
    }

    #[Test]
    public function cancelar_sin_motivo_falla_validacion(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'alumno_id'    => $this->alumno->id,
            'empresa_id'   => $this->empresa->id,
            'ciclo_id'     => $this->ciclo->id,
            'tutor_ies_id' => $this->admin->id,
            'estado'       => 'activa',
        ]);

        $resp = $this->actingAs($this->admin)->post(route('asignaciones.cancelar', $asignacion), [
            'motivo_baja' => '',
        ]);

        $resp->assertSessionHasErrors('motivo_baja');
    }

    // =========================================================================
    // ENDPOINTS JSON AUXILIARES
    // =========================================================================

    #[Test]
    public function endpoint_sedes_devuelve_json_de_direcciones(): void
    {
        $direccion = \App\Models\Direccion::factory()->create(['empresa_id' => $this->empresa->id]);

        $resp = $this->actingAs($this->admin)->getJson(route('interna.empresa.sedes', $this->empresa));
        $resp->assertOk()->assertJsonStructure([['id', 'label']]);
    }

    #[Test]
    public function endpoint_contactos_devuelve_json_de_personas_contacto(): void
    {
        \App\Models\PersonaContacto::factory()->create(['empresa_id' => $this->empresa->id]);

        $resp = $this->actingAs($this->admin)->getJson(route('interna.empresa.contactos', $this->empresa));
        $resp->assertOk()->assertJsonStructure([['id', 'label']]);
    }

    #[Test]
    public function profesor_ajeno_a_la_empresa_no_puede_ver_sedes(): void
    {
        // $this->empresa tiene creador_id distinto (factory por defecto); $this->profesor
        // no tiene relacion alguna con ella.
        \App\Models\Direccion::factory()->create(['empresa_id' => $this->empresa->id]);

        $resp = $this->actingAs($this->profesor)->getJson(route('interna.empresa.sedes', $this->empresa));
        $resp->assertForbidden();
    }

    #[Test]
    public function profesor_ajeno_a_la_empresa_no_puede_ver_contactos(): void
    {
        \App\Models\PersonaContacto::factory()->create(['empresa_id' => $this->empresa->id]);

        $resp = $this->actingAs($this->profesor)->getJson(route('interna.empresa.contactos', $this->empresa));
        $resp->assertForbidden();
    }

    #[Test]
    public function profesor_creador_de_la_empresa_puede_ver_sedes(): void
    {
        $empresaPropia = \App\Models\Empresa::factory()->create(['creador_id' => $this->profesor->id]);
        \App\Models\Direccion::factory()->create(['empresa_id' => $empresaPropia->id]);

        $resp = $this->actingAs($this->profesor)->getJson(route('interna.empresa.sedes', $empresaPropia));
        $resp->assertOk()->assertJsonStructure([['id', 'label']]);
    }
}
