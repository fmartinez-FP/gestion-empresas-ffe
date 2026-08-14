<?php

namespace Tests\Feature;

use App\Mail\BienvenidaAlumnoMail;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResetearPasswordAlumnoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_puede_resetear_password_de_alumno_con_cuenta(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['rol' => 'admin']);
        $alumnoUser = User::factory()->create(['rol' => 'alumno', 'password_change_required' => false]);
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);

        $response = $this->actingAs($admin)
            ->post(route('alumnos.resetear-password', $alumno));

        $response->assertRedirect(route('alumnos.show', $alumno));
        $response->assertSessionHas('success');

        $this->assertTrue($alumnoUser->fresh()->password_change_required);
        Mail::assertSent(BienvenidaAlumnoMail::class, function ($mail) use ($alumno) {
            return $mail->alumno->is($alumno);
        });
    }

    #[Test]
    public function tutor_empresa_no_puede_resetear_password(): void
    {
        $tutorEmpresa = User::factory()->create(['rol' => 'tutor_empresa']);
        $alumnoUser = User::factory()->create(['rol' => 'alumno']);
        $alumno = Alumno::factory()->create(['user_id' => $alumnoUser->id]);

        $response = $this->actingAs($tutorEmpresa)
            ->post(route('alumnos.resetear-password', $alumno));

        $response->assertForbidden();
    }

    #[Test]
    public function fallo_controlado_si_alumno_no_tiene_cuenta_de_portal(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $alumno = Alumno::factory()->create(['user_id' => null]);

        $response = $this->actingAs($admin)
            ->post(route('alumnos.resetear-password', $alumno));

        $response->assertRedirect(route('alumnos.show', $alumno));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function profesor_puede_resetear_password_de_alumno_de_su_grupo_en_curso_activo(): void
    {
        Mail::fake();

        $profesor = User::factory()->create(['rol' => 'profesor']);
        $grupo = Grupo::factory()->create();
        $profesor->sincronizarGruposTutor([$grupo->id]);

        $alumnoUser = User::factory()->create(['rol' => 'alumno', 'password_change_required' => false]);
        $alumno = Alumno::factory()->create([
            'user_id'         => $alumnoUser->id,
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2025-2026',
        ]);

        $response = $this->actingAs($profesor)
            ->post(route('alumnos.resetear-password', $alumno));

        $response->assertRedirect(route('alumnos.show', $alumno));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function profesor_no_puede_resetear_password_de_alumno_de_grupo_ajeno(): void
    {
        $profesor = User::factory()->create(['rol' => 'profesor']);
        $grupoAsignado = Grupo::factory()->create();
        $grupoAjeno = Grupo::factory()->create();
        $profesor->sincronizarGruposTutor([$grupoAsignado->id]);

        $alumnoUser = User::factory()->create(['rol' => 'alumno']);
        $alumno = Alumno::factory()->create([
            'user_id'         => $alumnoUser->id,
            'grupo_id'        => $grupoAjeno->id,
            'curso_academico' => '2025-2026',
        ]);

        $response = $this->actingAs($profesor)
            ->post(route('alumnos.resetear-password', $alumno));

        $response->assertForbidden();
    }

    #[Test]
    public function profesor_no_puede_resetear_password_de_alumno_de_curso_academico_cerrado(): void
    {
        $profesor = User::factory()->create(['rol' => 'profesor']);
        $grupo = Grupo::factory()->create();
        $profesor->sincronizarGruposTutor([$grupo->id]);

        $alumnoUser = User::factory()->create(['rol' => 'alumno']);
        $alumno = Alumno::factory()->create([
            'user_id'         => $alumnoUser->id,
            'grupo_id'        => $grupo->id,
            'curso_academico' => '2023-2024',
        ]);

        $response = $this->actingAs($profesor)
            ->post(route('alumnos.resetear-password', $alumno));

        $response->assertForbidden();
    }
}
