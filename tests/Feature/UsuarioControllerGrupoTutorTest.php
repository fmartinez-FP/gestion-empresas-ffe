<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioControllerGrupoTutorTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'activo' => true]);
    }

    #[Test]
    public function admin_asigna_grupos_tutor_a_profesor_correctamente(): void
    {
        Configuracion::setCursoActivo('2025-2026');
        $admin  = $this->admin();
        $target = User::factory()->create(['rol' => 'responsable_ffe']);
        $grupoA = Grupo::factory()->create();
        $grupoB = Grupo::factory()->create();
        Auth::loginUsingId($admin->id);

        $response = $this->put(route('admin.usuarios.update', $target), [
            'rol'    => 'profesor',
            'grupos' => [$grupoA->id, $grupoB->id],
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $target->refresh();
        $this->assertEquals('profesor', $target->rol);
        $this->assertDatabaseHas('profesor_tutor', [
            'user_id'         => $target->id,
            'grupo_id'        => $grupoA->id,
            'curso_academico' => '2025-2026',
        ]);
        $this->assertDatabaseHas('profesor_tutor', [
            'user_id'         => $target->id,
            'grupo_id'        => $grupoB->id,
            'curso_academico' => '2025-2026',
        ]);
        $this->assertCount(2, $target->gruposTutor);
    }

    #[Test]
    public function admin_puede_guardar_profesor_sin_grupos(): void
    {
        Configuracion::setCursoActivo('2025-2026');
        $admin  = $this->admin();
        $target = User::factory()->create(['rol' => 'responsable_ffe']);
        Auth::loginUsingId($admin->id);

        $response = $this->put(route('admin.usuarios.update', $target), [
            'rol' => 'profesor',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $target->refresh();
        $this->assertEquals('profesor', $target->rol);
        $this->assertCount(0, $target->gruposTutor);
    }

    #[Test]
    public function cambiar_de_profesor_a_otro_rol_hace_detach_de_grupos_tutor(): void
    {
        Configuracion::setCursoActivo('2025-2026');
        $admin  = $this->admin();
        $target = User::factory()->create(['rol' => 'profesor']);
        $grupo  = Grupo::factory()->create();
        $target->sincronizarGruposTutor([$grupo->id]);
        Auth::loginUsingId($admin->id);

        $response = $this->put(route('admin.usuarios.update', $target), [
            'rol' => 'responsable_ffe',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $target->refresh();
        $this->assertCount(0, $target->gruposTutor);
    }

    #[Test]
    public function rechaza_id_de_grupo_inexistente(): void
    {
        Configuracion::setCursoActivo('2025-2026');
        $admin  = $this->admin();
        $target = User::factory()->create(['rol' => 'responsable_ffe']);
        Auth::loginUsingId($admin->id);

        $response = $this->put(route('admin.usuarios.update', $target), [
            'rol'    => 'profesor',
            'grupos' => [999999],
        ]);

        $response->assertSessionHasErrors(['grupos.0']);
        $target->refresh();
        $this->assertEquals('responsable_ffe', $target->rol);
    }
}
