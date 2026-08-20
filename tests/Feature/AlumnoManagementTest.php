<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CicloFormativo;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlumnoManagementTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'activo' => true]);
    }

    private function responsableFFE(): User
    {
        return User::factory()->create(['rol' => 'responsable_ffe', 'activo' => true]);
    }

    private function profesor(): User
    {
        return User::factory()->create(['rol' => 'profesor', 'activo' => true]);
    }

    private function ciclo(): CicloFormativo
    {
        return CicloFormativo::factory()->create(['codigo' => 'DAM', 'nombre' => 'Desarrollo Aplicaciones Multiplataforma']);
    }

    private function alumno(array $attrs = []): Alumno
    {
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        return Alumno::factory()->create(array_merge([
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025-2026',
        ], $attrs));
    }

    // =========================================================================
    // ACCESO — LISTADO
    // =========================================================================

    #[Test]
    public function usuario_autenticado_puede_ver_listado_alumnos()
    {
        $user = $this->profesor();
        Auth::loginUsingId($user->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertSee('Alumnos en FFE');
    }

    #[Test]
    public function usuario_no_autenticado_es_redirigido()
    {
        $response = $this->get(route('alumnos.index'));
        $response->assertRedirect();
    }

    // =========================================================================
    // FILTROS
    // =========================================================================

    #[Test]
    public function listado_filtra_por_ciclo()
    {
        $admin  = $this->admin();
        $ciclo1 = CicloFormativo::factory()->create(['codigo' => 'DAM']);
        $ciclo2 = CicloFormativo::factory()->create(['codigo' => 'DAW']);

        $grupo1 = Grupo::factory()->create(['ciclo_id' => $ciclo1->id, 'numero_curso' => 2]);
        $grupo2 = Grupo::factory()->create(['ciclo_id' => $ciclo2->id, 'numero_curso' => 2]);
        Alumno::factory()->create(['grupo_id' => $grupo1->id, 'apellidos' => 'García', 'curso_academico' => '2025-2026']);
        Alumno::factory()->create(['grupo_id' => $grupo2->id, 'apellidos' => 'López',  'curso_academico' => '2025-2026']);

        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.index', ['ciclo_id' => $ciclo1->id]));
        $response->assertStatus(200);
        $response->assertSee('García');
        $response->assertDontSee('López');
    }

    #[Test]
    public function listado_filtra_por_busqueda_texto()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();

        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        Alumno::factory()->create(['grupo_id' => $grupo->id, 'nombre' => 'María',  'apellidos' => 'Fernández', 'curso_academico' => '2025-2026']);
        Alumno::factory()->create(['grupo_id' => $grupo->id, 'nombre' => 'Carlos', 'apellidos' => 'Ruiz',      'curso_academico' => '2025-2026']);

        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.index', ['q' => 'Fernández']));
        $response->assertSee('Fernández');
        $response->assertDontSee('Carlos');
    }

    // =========================================================================
    // CREAR
    // =========================================================================

    #[Test]
    public function crear_alumno_ignora_user_id_inyectado_en_el_payload()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        $otroUsuario = User::factory()->create();
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('alumnos.store'), [
            'nombre'          => 'Ana',
            'apellidos'       => 'Martinez Lopez',
            'email'           => 'ana.massassign@educa.madrid.org',
            'telefono'        => '600111222',
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025-2026',
            'user_id'         => $otroUsuario->id,
        ]);

        $response->assertRedirect();
        $alumno = Alumno::where('email', 'ana.massassign@educa.madrid.org')->firstOrFail();
        $this->assertNull($alumno->user_id);
    }

    #[Test]
    public function profesor_no_puede_reasignar_grupo_de_alumno_via_mass_assignment()
    {
        $profesorSinPermisoGrupo = User::factory()->create(['rol' => 'profesor', 'activo' => true]);
        $alumno = $this->alumno();
        $grupoOriginal = $alumno->grupo_id;
        $otroGrupo = Grupo::factory()->create([
            'ciclo_id'     => $alumno->grupo->ciclo_id,
            'numero_curso' => 1,
        ]);
        Auth::loginUsingId($profesorSinPermisoGrupo->id);

        $response = $this->put(route('alumnos.update', $alumno), [
            'email'    => 'nuevo.massassign@educa.madrid.org',
            'telefono' => '600999888',
            'grupo_id' => $otroGrupo->id,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $alumno->refresh();
        $this->assertEquals($grupoOriginal, $alumno->grupo_id);
        $this->assertEquals('nuevo.massassign@educa.madrid.org', $alumno->email);
    }

    #[Test]
    public function admin_puede_crear_alumno()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('alumnos.store'), [
            'nombre'          => 'Ana',
            'apellidos'       => 'Martínez López',
            'email'           => 'ana@educa.madrid.org',
            'telefono'        => '600111222',
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025-2026',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('alumnos', [
            'nombre'    => 'Ana',
            'apellidos' => 'Martínez López',
            'grupo_id'  => $grupo->id,
        ]);
    }

    #[Test]
    public function crear_alumno_requiere_nombre_y_apellidos()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('alumnos.store'), [
            'nombre'          => '',
            'apellidos'       => '',
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025-2026',
        ]);

        $response->assertSessionHasErrors(['nombre', 'apellidos']);
        $this->assertDatabaseCount('alumnos', 0);
    }

    #[Test]
    public function crear_alumno_valida_formato_curso_academico()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('alumnos.store'), [
            'nombre'          => 'Ana',
            'apellidos'       => 'García',
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025/2026',  // formato incorrecto
        ]);

        $response->assertSessionHasErrors(['curso_academico']);
    }

    // =========================================================================
    // EDITAR
    // =========================================================================

    #[Test]
    public function admin_puede_editar_alumno()
    {
        $admin  = $this->admin();
        $alumno = $this->alumno(['nombre' => 'Pedro', 'apellidos' => 'Sánchez']);
        Auth::loginUsingId($admin->id);

        $response = $this->put(route('alumnos.update', $alumno), [
            'nombre'          => 'Pedro',
            'apellidos'       => 'Sánchez Ruiz',
            'grupo_id'        => $alumno->grupo_id,
            'curso_academico' => $alumno->curso_academico,
        ]);

        $response->assertRedirect(route('alumnos.show', $alumno));
        $this->assertDatabaseHas('alumnos', ['id' => $alumno->id, 'apellidos' => 'Sánchez Ruiz']);
    }

    // =========================================================================
    // SOFT DELETE
    // =========================================================================

    #[Test]
    public function admin_puede_dar_de_baja_alumno()
    {
        $admin  = $this->admin();
        $alumno = $this->alumno();
        Auth::loginUsingId($admin->id);

        $response = $this->delete(route('alumnos.destroy', $alumno));

        $response->assertRedirect(route('alumnos.index'));
        $this->assertSoftDeleted('alumnos', ['id' => $alumno->id]);
    }

    #[Test]
    public function profesor_no_puede_dar_de_baja_alumno()
    {
        $profesor = $this->profesor();
        $alumno   = $this->alumno();
        Auth::loginUsingId($profesor->id);

        $response = $this->delete(route('alumnos.destroy', $alumno));

        $response->assertStatus(403);
        $this->assertDatabaseHas('alumnos', ['id' => $alumno->id, 'deleted_at' => null]);
    }

    #[Test]
    public function alumno_dado_de_baja_no_aparece_en_listado_activos()
    {
        $admin  = $this->admin();
        $alumno = $this->alumno(['nombre' => 'Laura', 'apellidos' => 'Pérez']);
        $alumno->delete();
        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertDontSee('Laura');
    }

    // =========================================================================
    // ARCHIVO
    // =========================================================================

    #[Test]
    public function admin_puede_acceder_al_archivo()
    {
        $admin  = $this->admin();
        $alumno = $this->alumno(['nombre' => 'Juan', 'apellidos' => 'Inactivo']);
        $alumno->delete();
        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.archivo'));
        $response->assertStatus(200);
        $response->assertSee('Juan');
    }

    #[Test]
    public function profesor_no_puede_acceder_al_archivo()
    {
        $profesor = $this->profesor();
        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.archivo'));
        $response->assertStatus(403);
    }

    #[Test]
    public function responsable_ffe_puede_acceder_al_archivo()
    {
        $rffe   = $this->responsableFFE();
        $alumno = $this->alumno();
        $alumno->delete();
        Auth::loginUsingId($rffe->id);

        $response = $this->get(route('alumnos.archivo'));
        $response->assertStatus(200);
    }

    // =========================================================================
    // IMPORTACIÓN
    // =========================================================================

    #[Test]
    public function admin_puede_ver_formulario_importacion()
    {
        $admin = $this->admin();
        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.import'));
        $response->assertStatus(200);
        $response->assertSee('Importar alumnos');
    }

    #[Test]
    public function profesor_no_puede_ver_formulario_importacion()
    {
        $profesor = $this->profesor();
        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.import'));
        $response->assertStatus(403);
    }

    // =========================================================================
    // IMPORT ALUMNOS SERVICE — DEDUPLICACIÓN
    // =========================================================================

    #[Test]
    public function importar_deduplica_por_nombre_apellidos_ciclo_curso()
    {
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);

        // Alumno ya existente
        Alumno::factory()->create([
            'nombre'          => 'María',
            'apellidos'       => 'García López',
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025-2026',
        ]);

        $service = new \App\Services\ImportAlumnosService();

        // Crear CSV temporal con el mismo alumno
        $csv = "Nombre,Apellidos,Email\nMaría,García López,garcia@educa.madrid.org\nCarlos,Ruiz Martín,ruiz@educa.madrid.org";
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.csv';
        file_put_contents($tmpFile, $csv);

        $resultado = $service->importar($tmpFile, $grupo->id, '2025-2026');
        @unlink($tmpFile);

        $this->assertTrue($resultado['success']);
        $this->assertEquals(1, $resultado['importados']);  // solo Carlos
        $this->assertEquals(1, $resultado['omitidos']);    // María ya existe
        $this->assertDatabaseCount('alumnos', 2);          // original + Carlos
    }

    #[Test]
    public function importar_falla_si_faltan_columnas_obligatorias()
    {
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        $service = new \App\Services\ImportAlumnosService();

        $csv = "Email,Teléfono\nana@educa.madrid.org,600111222";
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.csv';
        file_put_contents($tmpFile, $csv);

        $resultado = $service->importar($tmpFile, $grupo->id, '2025-2026');
        @unlink($tmpFile);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Nombre', $resultado['mensaje']);
    }

    // =========================================================================
    // FICHA — SHOW
    // =========================================================================

    #[Test]
    public function admin_puede_ver_ficha_de_cualquier_alumno()
    {
        $admin  = $this->admin();
        $alumno = $this->alumno(['nombre' => 'Eva', 'apellidos' => 'Torres']);
        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertStatus(200);
        $response->assertSee('Torres');
        $response->assertSee('Eva');
    }

    #[Test]
    public function profesor_puede_ver_ficha_de_alumno_de_su_grupo_en_curso_activo()
    {
        $profesor = $this->profesor();
        $grupo    = Grupo::factory()->create(['ciclo_id' => $this->ciclo()->id]);
        $profesor->sincronizarGruposTutor([$grupo->id]);
        $alumno = Alumno::factory()->create([
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025-2026',
            'apellidos'       => 'DeSuGrupo',
        ]);
        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertStatus(200);
        $response->assertSee('DeSuGrupo');
    }

    #[Test]
    public function profesor_no_puede_ver_ficha_de_alumno_de_grupo_ajeno()
    {
        $profesor      = $this->profesor();
        $ciclo         = $this->ciclo();
        $grupoAsignado = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 1]);
        $grupoAjeno    = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        $profesor->sincronizarGruposTutor([$grupoAsignado->id]);
        $alumno = Alumno::factory()->create([
            'grupo_id'        => $grupoAjeno->id,
            'curso_academico' => '2025-2026',
        ]);
        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertStatus(403);
    }

    #[Test]
    public function profesor_no_puede_ver_ficha_de_alumno_de_curso_academico_cerrado()
    {
        $profesor = $this->profesor();
        $grupo    = Grupo::factory()->create(['ciclo_id' => $this->ciclo()->id]);
        $profesor->sincronizarGruposTutor([$grupo->id]);
        $alumno = Alumno::factory()->create([
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2023-2024',
        ]);
        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertStatus(403);
    }

    // =========================================================================
    // SCOPE POR PROFESOR (Fase L)
    // =========================================================================

    #[Test]
    public function profesor_con_grupo_tutor_asignado_solo_ve_alumnos_de_su_grupo_y_curso_activo()
    {
        $profesor = $this->profesor();
        $cicloAsignado = CicloFormativo::factory()->create(['codigo' => 'DAM']);
        $cicloAjeno    = CicloFormativo::factory()->create(['codigo' => 'DAW']);
        $grupoAsignado = Grupo::factory()->create(['ciclo_id' => $cicloAsignado->id]);
        $grupoAjeno    = Grupo::factory()->create(['ciclo_id' => $cicloAjeno->id]);

        $profesor->sincronizarGruposTutor([$grupoAsignado->id]);

        Alumno::factory()->create(['grupo_id' => $grupoAsignado->id, 'apellidos' => 'DeSuGrupo', 'curso_academico' => '2025-2026']);
        Alumno::factory()->create(['grupo_id' => $grupoAjeno->id, 'apellidos' => 'DeOtroGrupo', 'curso_academico' => '2025-2026']);
        Alumno::factory()->create(['grupo_id' => $grupoAsignado->id, 'apellidos' => 'CursoAnterior', 'curso_academico' => '2023-2024']);

        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertSee('DeSuGrupo');
        $response->assertDontSee('DeOtroGrupo');
        $response->assertDontSee('CursoAnterior');
    }

    #[Test]
    public function profesor_sin_grupos_tutor_asignados_ve_listado_vacio_y_aviso()
    {
        $profesor = $this->profesor();
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);

        Alumno::factory()->create(['grupo_id' => $grupo->id, 'apellidos' => 'NoDeberiaVerse', 'curso_academico' => '2025-2026']);

        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertDontSee('NoDeberiaVerse');
        $response->assertSee('Todavía no tienes grupos asignados');
    }

    #[Test]
    public function admin_ve_alumnos_de_todos_los_ciclos_sin_depender_de_ciclos_tutor()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);

        Alumno::factory()->create(['grupo_id' => $grupo->id, 'apellidos' => 'VisibleParaAdmin', 'curso_academico' => '2025-2026']);

        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertSee('VisibleParaAdmin');
    }
}
