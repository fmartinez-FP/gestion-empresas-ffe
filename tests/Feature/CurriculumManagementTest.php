<?php

namespace Tests\Feature;

use App\Models\CicloFormativo;
use App\Models\CriterioEvaluacion;
use App\Models\ElegibleFfe;
use App\Models\ModuloProfesional;
use App\Models\ResultadoAprendizaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurriculumManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin']);
    }

    private function responsable(): User
    {
        return User::factory()->create(['rol' => 'responsable_ffe']);
    }

    private function profesor(): User
    {
        return User::factory()->create(['rol' => 'profesor']);
    }

    private function responsableCiclo(): User
    {
        return User::factory()->create(['rol' => 'responsable_ciclo']);
    }

    private function ciclo(): CicloFormativo
    {
        return CicloFormativo::factory()->create();
    }

    private function modulo(CicloFormativo $ciclo): ModuloProfesional
    {
        return ModuloProfesional::factory()->create(['ciclo_id' => $ciclo->id]);
    }

    private function ra(ModuloProfesional $modulo): ResultadoAprendizaje
    {
        return ResultadoAprendizaje::factory()->create(['modulo_id' => $modulo->id]);
    }

    private function ce(ResultadoAprendizaje $ra): CriterioEvaluacion
    {
        return CriterioEvaluacion::factory()->create(['resultado_aprendizaje_id' => $ra->id]);
    }

    #[Test]
    public function admin_puede_ver_indice_curriculum(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.curriculum.index'))
            ->assertOk();
    }

    #[Test]
    public function profesor_puede_ver_indice_curriculum(): void
    {
        $this->actingAs($this->profesor())
            ->get(route('admin.curriculum.index'))
            ->assertOk();
    }

    #[Test]
    public function admin_puede_ver_modulos_de_ciclo(): void
    {
        $ciclo = $this->ciclo();
        $this->actingAs($this->admin())
            ->get(route('admin.curriculum.modulos.index', $ciclo))
            ->assertOk()
            ->assertSee($ciclo->nombre);
    }

    #[Test]
    public function admin_puede_crear_modulo(): void
    {
        $ciclo = $this->ciclo();
        $this->actingAs($this->admin())
            ->post(route('admin.curriculum.modulos.store', $ciclo), [
                'codigo'        => 'MP01',
                'nombre'        => 'Programacion',
                'horas_totales' => 256,
                'curso'         => 1,
            ])
            ->assertRedirect(route('admin.curriculum.modulos.index', $ciclo));

        $this->assertDatabaseHas('modulos_profesionales', [
            'ciclo_id' => $ciclo->id,
            'codigo'   => 'MP01',
            'nombre'   => 'Programacion',
        ]);
    }

    #[Test]
    public function profesor_no_puede_crear_modulo(): void
    {
        $ciclo = $this->ciclo();
        $this->actingAs($this->profesor())
            ->post(route('admin.curriculum.modulos.store', $ciclo), [
                'codigo' => 'MP01',
                'nombre' => 'Programacion',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function admin_puede_actualizar_modulo(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);

        $this->actingAs($this->admin())
            ->put(route('admin.curriculum.modulos.update', [$ciclo, $modulo]), [
                'codigo' => 'MP99',
                'nombre' => 'Nombre actualizado',
            ])
            ->assertRedirect(route('admin.curriculum.modulos.index', $ciclo));

        $this->assertDatabaseHas('modulos_profesionales', ['id' => $modulo->id, 'codigo' => 'MP99']);
    }

    #[Test]
    public function admin_puede_eliminar_modulo_sin_ra(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);

        $this->actingAs($this->admin())
            ->delete(route('admin.curriculum.modulos.destroy', [$ciclo, $modulo]))
            ->assertRedirect(route('admin.curriculum.modulos.index', $ciclo));

        $this->assertDatabaseMissing('modulos_profesionales', ['id' => $modulo->id]);
    }

    #[Test]
    public function no_se_puede_eliminar_modulo_con_ra(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $this->ra($modulo);

        $this->actingAs($this->admin())
            ->delete(route('admin.curriculum.modulos.destroy', [$ciclo, $modulo]))
            ->assertRedirect();

        $this->assertDatabaseHas('modulos_profesionales', ['id' => $modulo->id]);
    }

    #[Test]
    public function admin_puede_ver_formulario_editar_modulo(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);

        $this->actingAs($this->admin())
            ->get(route('admin.curriculum.modulos.edit', [$ciclo, $modulo]))
            ->assertOk();
    }

    #[Test]
    public function editar_modulo_de_otro_ciclo_devuelve_404(): void
    {
        $ciclo1 = $this->ciclo();
        $ciclo2 = $this->ciclo();
        $modulo = $this->modulo($ciclo2);

        $this->actingAs($this->admin())
            ->get(route('admin.curriculum.modulos.edit', [$ciclo1, $modulo]))
            ->assertNotFound();
    }

    #[Test]
    public function actualizar_modulo_de_otro_ciclo_devuelve_404(): void
    {
        $ciclo1 = $this->ciclo();
        $ciclo2 = $this->ciclo();
        $modulo = $this->modulo($ciclo2);

        $this->actingAs($this->admin())
            ->put(route('admin.curriculum.modulos.update', [$ciclo1, $modulo]), [
                'codigo' => 'MP99',
                'nombre' => 'Intento cruzado',
            ])
            ->assertNotFound();
    }

    #[Test]
    public function eliminar_modulo_de_otro_ciclo_devuelve_404(): void
    {
        $ciclo1 = $this->ciclo();
        $ciclo2 = $this->ciclo();
        $modulo = $this->modulo($ciclo2);

        $this->actingAs($this->admin())
            ->delete(route('admin.curriculum.modulos.destroy', [$ciclo1, $modulo]))
            ->assertNotFound();

        $this->assertDatabaseHas('modulos_profesionales', ['id' => $modulo->id]);
    }

    #[Test]
    public function admin_puede_ver_ra_de_modulo(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->admin())
            ->get(route('admin.curriculum.ra.index', $modulo))
            ->assertOk()
            ->assertSee($ra->codigo);
    }

    #[Test]
    public function admin_puede_crear_ra(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);

        $this->actingAs($this->admin())
            ->post(route('admin.curriculum.ra.store', $modulo), [
                'codigo'      => 'RA1',
                'descripcion' => 'Aplica tecnicas de programacion',
            ])
            ->assertRedirect(route('admin.curriculum.ra.index', $modulo));

        $this->assertDatabaseHas('resultados_aprendizaje', [
            'modulo_id' => $modulo->id,
            'codigo'    => 'RA1',
        ]);
    }

    #[Test]
    public function admin_puede_eliminar_ra_sin_asignaciones(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->admin())
            ->delete(route('admin.curriculum.ra.destroy', $ra))
            ->assertRedirect();

        $this->assertDatabaseMissing('resultados_aprendizaje', ['id' => $ra->id]);
    }

    #[Test]
    public function admin_puede_crear_ce(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->admin())
            ->post(route('admin.curriculum.ce.store', $ra), [
                'codigo'      => 'CE1.1',
                'descripcion' => 'Utiliza correctamente los tipos de datos primitivos',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('criterios_evaluacion', [
            'resultado_aprendizaje_id' => $ra->id,
            'codigo'                   => 'CE1.1',
        ]);
    }

    #[Test]
    public function admin_puede_eliminar_ce(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);
        $ce = $this->ce($ra);

        $this->actingAs($this->admin())
            ->delete(route('admin.curriculum.ce.destroy', $ce))
            ->assertRedirect();

        $this->assertDatabaseMissing('criterios_evaluacion', ['id' => $ce->id]);
    }

    #[Test]
    public function responsable_puede_ver_pagina_elegibles(): void
    {
        $this->actingAs($this->responsable())
            ->get(route('admin.curriculum.elegibles.index'))
            ->assertOk();
    }

    #[Test]
    public function profesor_no_puede_ver_pagina_elegibles(): void
    {
        $this->actingAs($this->profesor())
            ->get(route('admin.curriculum.elegibles.index'))
            ->assertForbidden();
    }

    #[Test]
    public function responsable_puede_marcar_ra_como_elegible(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->responsable())
            ->postJson(route('admin.curriculum.elegibles.toggle'), [
                'resultado_aprendizaje_id' => $ra->id,
                'curso_academico'          => '2025-2026',
            ])
            ->assertOk()
            ->assertJson(['elegible' => true]);

        $this->assertDatabaseHas('elegibles_ffe', [
            'resultado_aprendizaje_id' => $ra->id,
            'curso_academico'          => '2025-2026',
        ]);
    }

    #[Test]
    public function toggle_elimina_elegible_si_ya_existia(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);
        $user = $this->responsable();

        ElegibleFfe::create([
            'resultado_aprendizaje_id' => $ra->id,
            'curso_academico'          => '2025-2026',
            'created_by_id'            => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.curriculum.elegibles.toggle'), [
                'resultado_aprendizaje_id' => $ra->id,
                'curso_academico'          => '2025-2026',
            ])
            ->assertOk()
            ->assertJson(['elegible' => false]);

        $this->assertDatabaseMissing('elegibles_ffe', [
            'resultado_aprendizaje_id' => $ra->id,
            'curso_academico'          => '2025-2026',
        ]);
    }

    #[Test]
    public function profesor_no_puede_marcar_elegibles(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->profesor())
            ->postJson(route('admin.curriculum.elegibles.toggle'), [
                'resultado_aprendizaje_id' => $ra->id,
                'curso_academico'          => '2025-2026',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function mismo_ra_puede_ser_elegible_en_cursos_distintos(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);
        $user = $this->responsable();

        ElegibleFfe::create([
            'resultado_aprendizaje_id' => $ra->id,
            'curso_academico'          => '2024-2025',
            'created_by_id'            => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.curriculum.elegibles.toggle'), [
                'resultado_aprendizaje_id' => $ra->id,
                'curso_academico'          => '2025-2026',
            ])
            ->assertOk()
            ->assertJson(['elegible' => true]);

        $this->assertDatabaseCount('elegibles_ffe', 2);
    }

    #[Test]
    public function profesor_no_puede_crear_ra(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);

        $this->actingAs($this->profesor())
            ->post(route('admin.curriculum.ra.store', $modulo), [
                'codigo'      => 'RA1',
                'descripcion' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('resultados_aprendizaje', ['modulo_id' => $modulo->id]);
    }

    #[Test]
    public function profesor_no_puede_actualizar_ra(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->profesor())
            ->put(route('admin.curriculum.ra.update', $ra), [
                'codigo'      => 'RA9',
                'descripcion' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('resultados_aprendizaje', ['id' => $ra->id, 'codigo' => $ra->codigo]);
    }

    #[Test]
    public function profesor_no_puede_eliminar_ra(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->profesor())
            ->delete(route('admin.curriculum.ra.destroy', $ra))
            ->assertForbidden();

        $this->assertDatabaseHas('resultados_aprendizaje', ['id' => $ra->id]);
    }

    #[Test]
    public function responsable_ciclo_no_puede_gestionar_ra(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);

        $this->actingAs($this->responsableCiclo())
            ->post(route('admin.curriculum.ra.store', $modulo), [
                'codigo'      => 'RA1',
                'descripcion' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('resultados_aprendizaje', ['modulo_id' => $modulo->id]);
    }

    #[Test]
    public function profesor_no_puede_crear_ce(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->profesor())
            ->post(route('admin.curriculum.ce.store', $ra), [
                'codigo'      => 'CE1.1',
                'descripcion' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('criterios_evaluacion', ['resultado_aprendizaje_id' => $ra->id]);
    }

    #[Test]
    public function profesor_no_puede_actualizar_ce(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);
        $ce = $this->ce($ra);

        $this->actingAs($this->profesor())
            ->put(route('admin.curriculum.ce.update', $ce), [
                'codigo'      => 'CE9.9',
                'descripcion' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('criterios_evaluacion', ['id' => $ce->id, 'codigo' => $ce->codigo]);
    }

    #[Test]
    public function profesor_no_puede_eliminar_ce(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);
        $ce = $this->ce($ra);

        $this->actingAs($this->profesor())
            ->delete(route('admin.curriculum.ce.destroy', $ce))
            ->assertForbidden();

        $this->assertDatabaseHas('criterios_evaluacion', ['id' => $ce->id]);
    }

    #[Test]
    public function responsable_ciclo_no_puede_gestionar_ce(): void
    {
        $ciclo = $this->ciclo();
        $modulo = $this->modulo($ciclo);
        $ra = $this->ra($modulo);

        $this->actingAs($this->responsableCiclo())
            ->post(route('admin.curriculum.ce.store', $ra), [
                'codigo'      => 'CE1.1',
                'descripcion' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('criterios_evaluacion', ['resultado_aprendizaje_id' => $ra->id]);
    }
}
