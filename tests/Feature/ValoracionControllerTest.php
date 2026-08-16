<?php

namespace Tests\Feature;

use App\Models\CicloFormativo;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Valoracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValoracionControllerTest extends TestCase
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

    private function payloadValido(): array
    {
        return [
            'trato_alumno'             => 4,
            'calidad_formacion'        => 5,
            'seguimiento_tutor'        => 3,
            'comunicacion_ies'         => 4,
            'posibilidad_contratacion' => 5,
            'observaciones'            => 'Buena colaboracion.',
        ];
    }

    // =========================================================================
    // createOrEdit()
    // =========================================================================

    #[Test]
    public function admin_puede_ver_formulario_de_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->admin())
            ->get(route('valoraciones.form', $empresa))
            ->assertOk();
    }

    #[Test]
    public function creador_puede_ver_formulario_de_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        $this->actingAs($creador)
            ->get(route('valoraciones.form', $empresa))
            ->assertOk();
    }

    #[Test]
    public function responsable_ciclo_que_tutoriza_puede_ver_formulario_de_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);
        $responsable = $this->responsableCiclo();
        $this->tutorizaCiclo($responsable, $ciclo);

        $this->actingAs($responsable)
            ->get(route('valoraciones.form', $empresa))
            ->assertOk();
    }

    #[Test]
    public function profesor_ajeno_no_puede_ver_formulario_de_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->profesor())
            ->get(route('valoraciones.form', $empresa))
            ->assertForbidden();
    }

    #[Test]
    public function responsable_ciclo_ajeno_no_puede_ver_formulario_de_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->responsableCiclo())
            ->get(route('valoraciones.form', $empresa))
            ->assertForbidden();
    }

    // =========================================================================
    // store()
    // =========================================================================

    #[Test]
    public function admin_puede_guardar_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->admin())
            ->post(route('valoraciones.store', $empresa), $this->payloadValido())
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('valoraciones', [
            'empresa_id'   => $empresa->id,
            'trato_alumno' => 4,
        ]);
    }

    #[Test]
    public function creador_puede_guardar_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        $this->actingAs($creador)
            ->post(route('valoraciones.store', $empresa), $this->payloadValido())
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('valoraciones', [
            'empresa_id'      => $empresa->id,
            'valorado_por_id' => $creador->id,
        ]);
    }

    #[Test]
    public function profesor_ajeno_no_puede_guardar_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->profesor())
            ->post(route('valoraciones.store', $empresa), $this->payloadValido())
            ->assertForbidden();

        $this->assertDatabaseMissing('valoraciones', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function responsable_ciclo_ajeno_no_puede_guardar_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $empresa = $this->empresaConCiclo($ciclo);

        $this->actingAs($this->responsableCiclo())
            ->post(route('valoraciones.store', $empresa), $this->payloadValido())
            ->assertForbidden();

        $this->assertDatabaseMissing('valoraciones', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function guardar_dos_veces_en_el_mismo_curso_actualiza_en_vez_de_duplicar(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        $this->actingAs($creador)
            ->post(route('valoraciones.store', $empresa), $this->payloadValido());

        $this->actingAs($creador)
            ->post(route('valoraciones.store', $empresa), array_merge(
                $this->payloadValido(),
                ['trato_alumno' => 1]
            ));

        $this->assertDatabaseCount('valoraciones', 1);
        $this->assertDatabaseHas('valoraciones', [
            'empresa_id'   => $empresa->id,
            'trato_alumno' => 1,
        ]);
    }

    // =========================================================================
    // destroy()
    // =========================================================================

    #[Test]
    public function admin_puede_eliminar_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        Valoracion::create(array_merge($this->payloadValido(), [
            'empresa_id'       => $empresa->id,
            'curso_academico'  => \App\Models\Configuracion::cursoActivo(),
            'valorado_por_id'  => $creador->id,
        ]));

        $this->actingAs($this->admin())
            ->delete(route('valoraciones.destroy', $empresa))
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseMissing('valoraciones', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function creador_puede_eliminar_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        Valoracion::create(array_merge($this->payloadValido(), [
            'empresa_id'       => $empresa->id,
            'curso_academico'  => \App\Models\Configuracion::cursoActivo(),
            'valorado_por_id'  => $creador->id,
        ]));

        $this->actingAs($creador)
            ->delete(route('valoraciones.destroy', $empresa))
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseMissing('valoraciones', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function profesor_ajeno_no_puede_eliminar_valoracion(): void
    {
        $ciclo = $this->ciclo();
        $creador = $this->profesor();
        $empresa = $this->empresaConCiclo($ciclo, $creador);

        Valoracion::create(array_merge($this->payloadValido(), [
            'empresa_id'       => $empresa->id,
            'curso_academico'  => \App\Models\Configuracion::cursoActivo(),
            'valorado_por_id'  => $creador->id,
        ]));

        $this->actingAs($this->profesor())
            ->delete(route('valoraciones.destroy', $empresa))
            ->assertForbidden();

        $this->assertDatabaseHas('valoraciones', ['empresa_id' => $empresa->id]);
    }
}
