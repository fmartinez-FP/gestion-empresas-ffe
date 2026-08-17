<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\CicloFormativo;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TokenTutorEmpresaControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'activo' => true]);
    }

    private function responsableCiclo(): User
    {
        return User::factory()->create(['rol' => 'responsable_ciclo', 'activo' => true]);
    }

    private function profesor(): User
    {
        return User::factory()->create(['rol' => 'profesor', 'activo' => true]);
    }

    private function asignacionConTutor(User $tutorIes): AsignacionFct
    {
        $ciclo  = CicloFormativo::factory()->create();
        $grupo  = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        $alumno = Alumno::factory()->create(['grupo_id' => $grupo->id, 'curso_academico' => '2025-2026']);
        $empresa = Empresa::factory()->create();

        return AsignacionFct::factory()->create([
            'alumno_id'    => $alumno->id,
            'empresa_id'   => $empresa->id,
            'ciclo_id'     => $ciclo->id,
            'tutor_ies_id' => $tutorIes->id,
        ]);
    }

    #[Test]
    public function admin_puede_generar_token_de_cualquier_asignacion(): void
    {
        $admin = $this->admin();
        $tutorIes = $this->profesor();
        $asignacion = $this->asignacionConTutor($tutorIes);

        $this->actingAs($admin)
            ->post(route('asignaciones.token-tutor.generar', $asignacion))
            ->assertRedirect();

        $this->assertDatabaseHas('tokens_tutor_empresa', [
            'asignacion_id' => $asignacion->id,
        ]);
    }

    #[Test]
    public function tutor_ies_puede_generar_token_de_su_asignacion(): void
    {
        $tutorIes = $this->profesor();
        $asignacion = $this->asignacionConTutor($tutorIes);

        $this->actingAs($tutorIes)
            ->post(route('asignaciones.token-tutor.generar', $asignacion))
            ->assertRedirect();

        $this->assertDatabaseHas('tokens_tutor_empresa', [
            'asignacion_id' => $asignacion->id,
        ]);
    }

    #[Test]
    public function responsable_ciclo_ajeno_no_puede_generar_token(): void
    {
        $responsableAjeno = $this->responsableCiclo();
        $tutorIes = $this->profesor();
        $asignacion = $this->asignacionConTutor($tutorIes);

        $this->actingAs($responsableAjeno)
            ->post(route('asignaciones.token-tutor.generar', $asignacion))
            ->assertForbidden();

        $this->assertDatabaseMissing('tokens_tutor_empresa', [
            'asignacion_id' => $asignacion->id,
        ]);
    }

    #[Test]
    public function profesor_no_tutor_no_puede_generar_token(): void
    {
        $profesorAjeno = $this->profesor();
        $tutorIes = $this->profesor();
        $asignacion = $this->asignacionConTutor($tutorIes);

        $this->actingAs($profesorAjeno)
            ->post(route('asignaciones.token-tutor.generar', $asignacion))
            ->assertForbidden();

        $this->assertDatabaseMissing('tokens_tutor_empresa', [
            'asignacion_id' => $asignacion->id,
        ]);
    }
}
