<?php

namespace Tests\Feature;

use App\Models\CicloFormativo;
use App\Models\Colocacion;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ColocacionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin']);
    }

    private function responsableFfe(): User
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

    private function empresaConCiclo(CicloFormativo $ciclo, ?User $creador = null): Empresa
    {
        $empresa = Empresa::factory()->create(
            $creador ? ['creador_id' => $creador->id] : []
        );

        $empresa->ciclos()->attach($ciclo->id, [
            'acepta_primero' => true,
            'acepta_segundo' => true,
        ]);

        return $empresa;
    }

    private function tutorizaCiclo(User $user, CicloFormativo $ciclo): void
    {
        $user->ciclos()->attach($ciclo->id);
    }

    private function colocacion(Empresa $empresa, CicloFormativo $ciclo, User $registradoPor): Colocacion
    {
        return Colocacion::create([
            'empresa_id'        => $empresa->id,
            'ciclo_id'          => $ciclo->id,
            'registrado_por_id' => $registradoPor->id,
            'curso_academico'   => '2025-2026',
            'numero_curso'      => 1,
            'num_alumnos'       => 2,
            'num_horas'         => 200,
        ]);
    }

    // =========================================================================
    // create() / store() -- EmpresaPolicy::colocar()
    // =========================================================================

    #[Test]
    public function admin_puede_ver_formulario_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->admin())
            ->get(route('colocaciones.create', $empresa))
            ->assertOk();
    }

    #[Test]
    public function creador_puede_ver_formulario_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        $this->actingAs($creador)
            ->get(route('colocaciones.create', $empresa))
            ->assertOk();
    }

    #[Test]
    public function responsable_ciclo_que_tutoriza_puede_ver_formulario_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);
        $responsable = $this->responsableCiclo();
        $this->tutorizaCiclo($responsable, $ciclo);

        $this->actingAs($responsable)
            ->get(route('colocaciones.create', $empresa))
            ->assertOk();
    }

    #[Test]
    public function profesor_ajeno_no_puede_ver_formulario_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->profesor())
            ->get(route('colocaciones.create', $empresa))
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');
    }

    #[Test]
    public function responsable_ciclo_ajeno_no_puede_ver_formulario_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->responsableCiclo())
            ->get(route('colocaciones.create', $empresa))
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');
    }

    #[Test]
    public function admin_puede_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->admin())
            ->post(route('colocaciones.store'), [
                'empresa_id'   => $empresa->id,
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 1,
                'num_alumnos'  => 4,
                'num_horas'    => 400,
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('colocaciones', [
            'empresa_id'  => $empresa->id,
            'num_alumnos' => 4,
        ]);
    }

    #[Test]
    public function profesor_ajeno_no_puede_crear_colocacion_en_empresa_sin_relacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->profesor())
            ->post(route('colocaciones.store'), [
                'empresa_id'   => $empresa->id,
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 1,
                'num_alumnos'  => 4,
                'num_horas'    => 400,
            ])
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('colocaciones', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function creador_no_puede_inyectar_registrado_por_id_ni_origen_al_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $otroUsuario = User::factory()->create();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        $this->actingAs($creador)
            ->post(route('colocaciones.store'), [
                'empresa_id'        => $empresa->id,
                'ciclo_id'          => $ciclo->id,
                'numero_curso'      => 1,
                'num_alumnos'       => 2,
                'num_horas'         => 200,
                'registrado_por_id' => $otroUsuario->id,
                'origen'            => 'automatica',
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('colocaciones', [
            'empresa_id'        => $empresa->id,
            'registrado_por_id' => $creador->id,
            'origen'            => 'manual',
        ]);
    }

    #[Test]
    public function creador_puede_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        $this->actingAs($creador)
            ->post(route('colocaciones.store'), [
                'empresa_id'   => $empresa->id,
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 1,
                'num_alumnos'  => 2,
                'num_horas'    => 200,
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('colocaciones', [
            'empresa_id'        => $empresa->id,
            'registrado_por_id' => $creador->id,
        ]);
    }

    #[Test]
    public function responsable_ciclo_que_tutoriza_puede_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);
        $responsable = $this->responsableCiclo();
        $this->tutorizaCiclo($responsable, $ciclo);

        $this->actingAs($responsable)
            ->post(route('colocaciones.store'), [
                'empresa_id'   => $empresa->id,
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 1,
                'num_alumnos'  => 2,
                'num_horas'    => 200,
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('colocaciones', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function responsable_ciclo_ajeno_no_puede_crear_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->responsableCiclo())
            ->post(route('colocaciones.store'), [
                'empresa_id'   => $empresa->id,
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 1,
                'num_alumnos'  => 2,
                'num_horas'    => 200,
            ])
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('colocaciones', ['empresa_id' => $empresa->id]);
    }

    // =========================================================================
    // edit() / update() / destroy() -- registrado_por_id (sin gate, chequeo manual)
    // =========================================================================

    #[Test]
    public function admin_puede_ver_formulario_editar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($this->admin())
            ->get(route('colocaciones.edit', $colocacion))
            ->assertOk();
    }

    #[Test]
    public function creador_puede_ver_formulario_editar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($creador)
            ->get(route('colocaciones.edit', $colocacion))
            ->assertOk();
    }

    #[Test]
    public function otro_profesor_no_puede_ver_formulario_editar_colocacion_ajena(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($this->profesor())
            ->get(route('colocaciones.edit', $colocacion))
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');
    }

    #[Test]
    public function responsable_ciclo_tutor_no_puede_editar_colocacion_si_no_la_registro(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $responsable = $this->responsableCiclo();
        $this->tutorizaCiclo($responsable, $ciclo);

        $this->actingAs($responsable)
            ->get(route('colocaciones.edit', $colocacion))
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');

        $this->actingAs($responsable)
            ->put(route('colocaciones.update', $colocacion), [
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 2,
                'num_alumnos'  => 9,
                'num_horas'    => 900,
            ])
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');

        $this->actingAs($responsable)
            ->delete(route('colocaciones.destroy', $colocacion))
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('colocaciones', [
            'id'           => $colocacion->id,
            'numero_curso' => 1,
        ]);
    }

    #[Test]
    public function admin_puede_actualizar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($this->admin())
            ->put(route('colocaciones.update', $colocacion), [
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 2,
                'num_alumnos'  => 5,
                'num_horas'    => 500,
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('colocaciones', ['id' => $colocacion->id, 'num_alumnos' => 5]);
    }

    #[Test]
    public function creador_puede_actualizar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($creador)
            ->put(route('colocaciones.update', $colocacion), [
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 2,
                'num_alumnos'  => 5,
                'num_horas'    => 500,
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('colocaciones', ['id' => $colocacion->id, 'num_alumnos' => 5]);
    }

    #[Test]
    public function profesor_ajeno_no_puede_actualizar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($this->profesor())
            ->put(route('colocaciones.update', $colocacion), [
                'ciclo_id'     => $ciclo->id,
                'numero_curso' => 2,
                'num_alumnos'  => 5,
                'num_horas'    => 500,
            ])
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('colocaciones', ['id' => $colocacion->id, 'num_alumnos' => 2]);
    }

    #[Test]
    public function admin_puede_eliminar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($this->admin())
            ->delete(route('colocaciones.destroy', $colocacion))
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseMissing('colocaciones', ['id' => $colocacion->id]);
    }

    #[Test]
    public function creador_puede_eliminar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($creador)
            ->delete(route('colocaciones.destroy', $colocacion))
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseMissing('colocaciones', ['id' => $colocacion->id]);
    }

    #[Test]
    public function profesor_ajeno_no_puede_eliminar_colocacion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);
        $colocacion = $this->colocacion($empresa, $ciclo, $creador);

        $this->actingAs($this->profesor())
            ->delete(route('colocaciones.destroy', $colocacion))
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('colocaciones', ['id' => $colocacion->id]);
    }
}
