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
        return Alumno::factory()->create(array_merge([
            'ciclo_id'        => $ciclo->id,
            'curso_academico' => '2025-2026',
            'numero_curso'    => 2,
        ], $attrs));
    }

    // =========================================================================
    // ACCESO — LISTADO
    // =========================================================================

    /** @test */
    public function usuario_autenticado_puede_ver_listado_alumnos()
    {
        $user = $this->profesor();
        Auth::loginUsingId($user->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertSee('Alumnos en FFE');
    }

    /** @test */
    public function usuario_no_autenticado_es_redirigido()
    {
        $response = $this->get(route('alumnos.index'));
        $response->assertRedirect();
    }

    // =========================================================================
    // FILTROS
    // =========================================================================

    /** @test */
    public function listado_filtra_por_ciclo()
    {
        $admin  = $this->admin();
        $ciclo1 = CicloFormativo::factory()->create(['codigo' => 'DAM']);
        $ciclo2 = CicloFormativo::factory()->create(['codigo' => 'DAW']);

        Alumno::factory()->create(['ciclo_id' => $ciclo1->id, 'apellidos' => 'García', 'curso_academico' => '2025-2026', 'numero_curso' => 2]);
        Alumno::factory()->create(['ciclo_id' => $ciclo2->id, 'apellidos' => 'López',  'curso_academico' => '2025-2026', 'numero_curso' => 2]);

        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.index', ['ciclo_id' => $ciclo1->id]));
        $response->assertStatus(200);
        $response->assertSee('García');
        $response->assertDontSee('López');
    }

    /** @test */
    public function listado_filtra_por_busqueda_texto()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();

        Alumno::factory()->create(['ciclo_id' => $ciclo->id, 'nombre' => 'María',  'apellidos' => 'Fernández', 'curso_academico' => '2025-2026', 'numero_curso' => 2]);
        Alumno::factory()->create(['ciclo_id' => $ciclo->id, 'nombre' => 'Carlos', 'apellidos' => 'Ruiz',      'curso_academico' => '2025-2026', 'numero_curso' => 2]);

        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.index', ['q' => 'Fernández']));
        $response->assertSee('Fernández');
        $response->assertDontSee('Carlos');
    }

    // =========================================================================
    // CREAR
    // =========================================================================

    /** @test */
    public function admin_puede_crear_alumno()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('alumnos.store'), [
            'nombre'          => 'Ana',
            'apellidos'       => 'Martínez López',
            'email'           => 'ana@educa.madrid.org',
            'telefono'        => '600111222',
            'ciclo_id'        => $ciclo->id,
            'curso_academico' => '2025-2026',
            'numero_curso'    => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('alumnos', [
            'nombre'    => 'Ana',
            'apellidos' => 'Martínez López',
            'ciclo_id'  => $ciclo->id,
        ]);
    }

    /** @test */
    public function crear_alumno_requiere_nombre_y_apellidos()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('alumnos.store'), [
            'nombre'          => '',
            'apellidos'       => '',
            'ciclo_id'        => $ciclo->id,
            'curso_academico' => '2025-2026',
            'numero_curso'    => 2,
        ]);

        $response->assertSessionHasErrors(['nombre', 'apellidos']);
        $this->assertDatabaseCount('alumnos', 0);
    }

    /** @test */
    public function crear_alumno_valida_formato_curso_academico()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('alumnos.store'), [
            'nombre'          => 'Ana',
            'apellidos'       => 'García',
            'ciclo_id'        => $ciclo->id,
            'curso_academico' => '2025/2026',  // formato incorrecto
            'numero_curso'    => 2,
        ]);

        $response->assertSessionHasErrors(['curso_academico']);
    }

    // =========================================================================
    // EDITAR
    // =========================================================================

    /** @test */
    public function admin_puede_editar_alumno()
    {
        $admin  = $this->admin();
        $alumno = $this->alumno(['nombre' => 'Pedro', 'apellidos' => 'Sánchez']);
        Auth::loginUsingId($admin->id);

        $response = $this->put(route('alumnos.update', $alumno), [
            'nombre'          => 'Pedro',
            'apellidos'       => 'Sánchez Ruiz',
            'ciclo_id'        => $alumno->ciclo_id,
            'curso_academico' => $alumno->curso_academico,
            'numero_curso'    => $alumno->numero_curso,
        ]);

        $response->assertRedirect(route('alumnos.show', $alumno));
        $this->assertDatabaseHas('alumnos', ['id' => $alumno->id, 'apellidos' => 'Sánchez Ruiz']);
    }

    // =========================================================================
    // SOFT DELETE
    // =========================================================================

    /** @test */
    public function admin_puede_dar_de_baja_alumno()
    {
        $admin  = $this->admin();
        $alumno = $this->alumno();
        Auth::loginUsingId($admin->id);

        $response = $this->delete(route('alumnos.destroy', $alumno));

        $response->assertRedirect(route('alumnos.index'));
        $this->assertSoftDeleted('alumnos', ['id' => $alumno->id]);
    }

    /** @test */
    public function profesor_no_puede_dar_de_baja_alumno()
    {
        $profesor = $this->profesor();
        $alumno   = $this->alumno();
        Auth::loginUsingId($profesor->id);

        $response = $this->delete(route('alumnos.destroy', $alumno));

        $response->assertStatus(403);
        $this->assertDatabaseHas('alumnos', ['id' => $alumno->id, 'deleted_at' => null]);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function profesor_no_puede_acceder_al_archivo()
    {
        $profesor = $this->profesor();
        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.archivo'));
        $response->assertStatus(403);
    }

    /** @test */
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

    /** @test */
    public function admin_puede_ver_formulario_importacion()
    {
        $admin = $this->admin();
        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.import'));
        $response->assertStatus(200);
        $response->assertSee('Importar alumnos');
    }

    /** @test */
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

    /** @test */
    public function importar_deduplica_por_nombre_apellidos_ciclo_curso()
    {
        $ciclo = $this->ciclo();

        // Alumno ya existente
        Alumno::factory()->create([
            'nombre'          => 'María',
            'apellidos'       => 'García López',
            'ciclo_id'        => $ciclo->id,
            'curso_academico' => '2025-2026',
            'numero_curso'    => 2,
        ]);

        $service = new \App\Services\ImportAlumnosService();

        // Crear CSV temporal con el mismo alumno
        $csv = "Nombre,Apellidos,Email\nMaría,García López,garcia@educa.madrid.org\nCarlos,Ruiz Martín,ruiz@educa.madrid.org";
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.csv';
        file_put_contents($tmpFile, $csv);

        $resultado = $service->importar($tmpFile, $ciclo->id, '2025-2026', 2);
        @unlink($tmpFile);

        $this->assertTrue($resultado['success']);
        $this->assertEquals(1, $resultado['importados']);  // solo Carlos
        $this->assertEquals(1, $resultado['omitidos']);    // María ya existe
        $this->assertDatabaseCount('alumnos', 2);          // original + Carlos
    }

    /** @test */
    public function importar_falla_si_faltan_columnas_obligatorias()
    {
        $ciclo = $this->ciclo();
        $service = new \App\Services\ImportAlumnosService();

        $csv = "Email,Teléfono\nana@educa.madrid.org,600111222";
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_') . '.csv';
        file_put_contents($tmpFile, $csv);

        $resultado = $service->importar($tmpFile, $ciclo->id, '2025-2026', 2);
        @unlink($tmpFile);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Nombre', $resultado['mensaje']);
    }

    // =========================================================================
    // FICHA — SHOW
    // =========================================================================

    /** @test */
    public function cualquier_usuario_puede_ver_ficha_alumno()
    {
        $profesor = $this->profesor();
        $alumno   = $this->alumno(['nombre' => 'Eva', 'apellidos' => 'Torres']);
        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.show', $alumno));
        $response->assertStatus(200);
        $response->assertSee('Torres');
        $response->assertSee('Eva');
    }

    // =========================================================================
    // SCOPE POR PROFESOR (Fase L)
    // =========================================================================

    /** @test */
    public function profesor_con_grupo_tutor_asignado_solo_ve_alumnos_de_su_grupo_y_curso_activo()
    {
        $profesor = $this->profesor();
        $cicloAsignado = CicloFormativo::factory()->create(['codigo' => 'DAM']);
        $cicloAjeno    = CicloFormativo::factory()->create(['codigo' => 'DAW']);
        $grupoAsignado = Grupo::factory()->create(['ciclo_id' => $cicloAsignado->id]);
        $grupoAjeno    = Grupo::factory()->create(['ciclo_id' => $cicloAjeno->id]);

        $profesor->sincronizarGruposTutor([$grupoAsignado->id]);

        Alumno::factory()->create(['ciclo_id' => $cicloAsignado->id, 'grupo_id' => $grupoAsignado->id, 'apellidos' => 'DeSuGrupo', 'curso_academico' => '2025-2026', 'numero_curso' => 2]);
        Alumno::factory()->create(['ciclo_id' => $cicloAjeno->id, 'grupo_id' => $grupoAjeno->id, 'apellidos' => 'DeOtroGrupo', 'curso_academico' => '2025-2026', 'numero_curso' => 2]);
        Alumno::factory()->create(['ciclo_id' => $cicloAsignado->id, 'grupo_id' => $grupoAsignado->id, 'apellidos' => 'CursoAnterior', 'curso_academico' => '2023-2024', 'numero_curso' => 2]);

        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertSee('DeSuGrupo');
        $response->assertDontSee('DeOtroGrupo');
        $response->assertDontSee('CursoAnterior');
    }

    /** @test */
    public function profesor_sin_grupos_tutor_asignados_ve_listado_vacio_y_aviso()
    {
        $profesor = $this->profesor();
        $ciclo = $this->ciclo();

        Alumno::factory()->create(['ciclo_id' => $ciclo->id, 'apellidos' => 'NoDeberiaVerse', 'curso_academico' => '2025-2026', 'numero_curso' => 2]);

        Auth::loginUsingId($profesor->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertDontSee('NoDeberiaVerse');
        $response->assertSee('Todavía no tienes grupos asignados');
    }

    /** @test */
    public function admin_ve_alumnos_de_todos_los_ciclos_sin_depender_de_ciclos_tutor()
    {
        $admin = $this->admin();
        $ciclo = $this->ciclo();

        Alumno::factory()->create(['ciclo_id' => $ciclo->id, 'apellidos' => 'VisibleParaAdmin', 'curso_academico' => '2025-2026', 'numero_curso' => 2]);

        Auth::loginUsingId($admin->id);

        $response = $this->get(route('alumnos.index'));
        $response->assertStatus(200);
        $response->assertSee('VisibleParaAdmin');
    }
}
