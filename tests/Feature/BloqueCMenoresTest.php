<?php

namespace Tests\Feature;

use App\Livewire\BuscadorEmpresas;
use App\Mail\SeguimientosPendientesMail;
use App\Models\Alumno;
use App\Models\AsignacionFct;
use App\Models\CicloFormativo;
use App\Models\Colocacion;
use App\Models\Configuracion;
use App\Models\Direccion;
use App\Models\Empresa;
use App\Models\SeguimientoDiario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regresión del Bloque C: errores 500 por parámetros, cifras incoherentes,
 * configuración del curso, avisos, archivo de alumnos y permisos menores.
 */
class BloqueCMenoresTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Configuracion::setCursoActivo('2025-2026');
        $this->admin = User::factory()->create(['rol' => 'admin', 'activo' => true]);
    }

    // =========================================================================
    // Errores 500 por parámetros
    // =========================================================================

    #[Test]
    public function el_mapa_filtra_por_curso_sin_error(): void
    {
        $ciclo = CicloFormativo::factory()->create();
        $empresa = Empresa::factory()->create();
        $empresa->sincronizarCiclos([$ciclo->id => ['acepta_primero' => true, 'acepta_segundo' => false]]);
        Direccion::factory()->create(['empresa_id' => $empresa->id, 'latitud' => 40.4, 'longitud' => -3.7]);

        $this->actingAs($this->admin)->getJson('/empresas-mapa/datos?curso=primero')->assertOk()->assertJsonCount(1);
        $this->actingAs($this->admin)->getJson('/empresas-mapa/datos?curso=segundo')->assertOk()->assertJsonCount(0);
    }

    #[Test]
    public function el_listado_de_colocaciones_ignora_filtros_manipulados(): void
    {
        $this->actingAs($this->admin)
            ->get('/colocaciones?ciclo=abc&empresa=xyz&curso=no-es-un-curso')
            ->assertOk();
    }

    #[Test]
    public function el_buscador_de_empresas_ignora_columnas_de_ordenacion_invalidas(): void
    {
        Empresa::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(BuscadorEmpresas::class)
            ->set('ordenarPor', 'nombre; DROP TABLE empresas')
            ->set('ordenDir', 'sideways')
            ->assertOk();

        $this->assertDatabaseCount('empresas', 1);
    }

    // =========================================================================
    // Cifras
    // =========================================================================

    #[Test]
    public function las_horas_de_las_estadisticas_son_alumnos_por_horas_por_alumno(): void
    {
        $ciclo = CicloFormativo::factory()->create();
        Colocacion::create([
            'empresa_id' => Empresa::factory()->create()->id, 'ciclo_id' => $ciclo->id, 'registrado_por_id' => $this->admin->id,
            'curso_academico' => '2025-2026', 'numero_curso' => 2, 'num_alumnos' => 3, 'num_horas' => 350,
        ]);

        $stats = Colocacion::estadisticasPorCiclo('2025-2026')['2025-2026'];

        $this->assertSame(3, (int) $stats[0]['total_alumnos']);
        $this->assertSame(1050, (int) $stats[0]['total_horas']);
    }

    // =========================================================================
    // Configuración del curso
    // =========================================================================

    #[Test]
    public function el_curso_activo_exige_anios_consecutivos(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.configuracion.cambiar-curso'), ['curso' => '2025-2031'])
            ->assertSessionHasErrors('curso');
        $this->assertSame('2025-2026', Configuracion::cursoActivo());

        $this->actingAs($this->admin)
            ->post(route('admin.configuracion.cambiar-curso'), ['curso' => '2026-2027'])
            ->assertSessionHasNoErrors();
        $this->assertSame('2026-2027', Configuracion::cursoActivo());
    }

    #[Test]
    public function no_se_avanza_de_curso_con_asignaciones_activas(): void
    {
        AsignacionFct::factory()->create(['curso_academico' => '2025-2026', 'estado' => 'activa']);

        $this->actingAs($this->admin)
            ->post(route('admin.configuracion.avanzar-curso'))
            ->assertSessionHas('error');
        $this->assertSame('2025-2026', Configuracion::cursoActivo());

        AsignacionFct::query()->update(['estado' => 'finalizada']);

        $this->actingAs($this->admin)
            ->post(route('admin.configuracion.avanzar-curso'))
            ->assertSessionHas('success');
        $this->assertSame('2026-2027', Configuracion::cursoActivo());
    }

    // =========================================================================
    // Permisos de colocaciones
    // =========================================================================

    #[Test]
    public function el_responsable_ffe_puede_editar_colocaciones_de_otros(): void
    {
        $ffe = User::factory()->create(['rol' => 'responsable_ffe', 'activo' => true]);
        $colocacion = Colocacion::create([
            'empresa_id' => Empresa::factory()->create()->id, 'ciclo_id' => CicloFormativo::factory()->create()->id,
            'registrado_por_id' => $this->admin->id, 'curso_academico' => '2025-2026',
            'numero_curso' => 2, 'num_alumnos' => 1, 'num_horas' => 350,
        ]);

        $this->actingAs($ffe)->get(route('colocaciones.edit', $colocacion))->assertOk();

        $profesor = User::factory()->create(['rol' => 'profesor', 'activo' => true]);
        $this->actingAs($profesor)->get(route('colocaciones.edit', $colocacion))->assertRedirect();
    }

    // =========================================================================
    // Avisos a tutores
    // =========================================================================

    #[Test]
    public function los_avisos_a_tutores_respetan_el_intervalo_entre_envios(): void
    {
        Mail::fake();
        $tutor = User::factory()->create(['rol' => 'profesor', 'activo' => true, 'email' => 'tutor@example.com']);
        $asignacion = AsignacionFct::factory()->create([
            'estado' => 'activa', 'tutor_ies_id' => $tutor->id, 'intervalo_email_tutor' => 14, 'ultimo_email_tutor_at' => null,
        ]);
        SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion->id, 'fecha' => today()->subDays(20), 'confirmado_tutor' => false,
        ]);

        Artisan::call('ffe:notificar-tutores');
        Artisan::call('ffe:notificar-tutores');
        Mail::assertSent(SeguimientosPendientesMail::class, 1);

        // Pasado el intervalo vuelve a avisar.
        $asignacion->update(['ultimo_email_tutor_at' => now()->subDays(15)]);
        Artisan::call('ffe:notificar-tutores');
        Mail::assertSent(SeguimientosPendientesMail::class, 2);
    }

    // =========================================================================
    // Archivo de alumnos
    // =========================================================================

    #[Test]
    public function un_alumno_dado_de_baja_se_puede_ver_y_restaurar_reactivando_su_cuenta(): void
    {
        $user = User::factory()->create(['rol' => 'alumno', 'activo' => true]);
        $alumno = Alumno::factory()->create(['user_id' => $user->id]);

        $this->actingAs($this->admin)->delete(route('alumnos.destroy', $alumno));
        $this->assertSoftDeleted($alumno);
        $this->assertFalse($user->fresh()->activo);

        $this->actingAs($this->admin)->get(route('alumnos.show', $alumno->id))->assertOk();

        $this->actingAs($this->admin)->post(route('alumnos.restaurar', $alumno->id))->assertSessionHas('success');
        $this->assertNotSoftDeleted($alumno);
        $this->assertTrue($user->fresh()->activo);
    }

    #[Test]
    public function solo_admin_y_ffe_ven_o_restauran_alumnos_archivados(): void
    {
        $alumno = Alumno::factory()->create();
        $alumno->delete();
        $profesor = User::factory()->create(['rol' => 'profesor', 'activo' => true]);
        $profesor->sincronizarGruposTutor([$alumno->grupo_id]);

        $this->actingAs($profesor)->get(route('alumnos.show', $alumno->id))->assertForbidden();
        $this->actingAs($profesor)->post(route('alumnos.restaurar', $alumno->id))->assertForbidden();
        $this->assertSoftDeleted($alumno);
    }

    // =========================================================================
    // Mensajes flash
    // =========================================================================

    #[Test]
    public function los_mensajes_warning_e_info_se_muestran(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['warning' => 'Aviso de prueba', 'info' => 'Información de prueba'])
            ->get('/dashboard')
            ->assertSee('Aviso de prueba')
            ->assertSee('Información de prueba');
    }
}
