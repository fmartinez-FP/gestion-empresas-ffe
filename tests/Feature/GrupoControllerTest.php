<?php

namespace Tests\Feature;

use App\Models\CicloFormativo;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GrupoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'activo' => true]);
    }

    #[Test]
    public function admin_puede_crear_un_grupo(): void
    {
        $admin = $this->admin();
        $ciclo = CicloFormativo::factory()->create();
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('admin.ciclos.grupos.store', $ciclo), [
            'numero_curso' => 1,
            'etiqueta'     => 'A',
        ]);

        $response->assertRedirect(route('admin.ciclos.edit', $ciclo));
        $this->assertDatabaseHas('grupos', [
            'ciclo_id'     => $ciclo->id,
            'numero_curso' => 1,
            'etiqueta'     => 'A',
            'activo'       => 1,
        ]);
    }

    #[Test]
    public function crear_grupo_sin_etiqueta_la_normaliza_a_cadena_vacia(): void
    {
        $admin = $this->admin();
        $ciclo = CicloFormativo::factory()->create();
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('admin.ciclos.grupos.store', $ciclo), [
            'numero_curso' => 2,
        ]);

        $response->assertRedirect(route('admin.ciclos.edit', $ciclo));
        $this->assertDatabaseHas('grupos', [
            'ciclo_id'     => $ciclo->id,
            'numero_curso' => 2,
            'etiqueta'     => '',
        ]);
    }

    #[Test]
    public function no_permite_crear_un_grupo_duplicado_en_el_mismo_ciclo(): void
    {
        $admin = $this->admin();
        $ciclo = CicloFormativo::factory()->create();
        Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 1, 'etiqueta' => 'A']);
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('admin.ciclos.grupos.store', $ciclo), [
            'numero_curso' => 1,
            'etiqueta'     => 'A',
        ]);

        $response->assertSessionHasErrors(['etiqueta']);
        $this->assertDatabaseCount('grupos', 1);
    }

    #[Test]
    public function el_mismo_numero_curso_y_etiqueta_en_ciclos_distintos_no_colisiona(): void
    {
        $admin  = $this->admin();
        $ciclo1 = CicloFormativo::factory()->create();
        $ciclo2 = CicloFormativo::factory()->create();
        Grupo::factory()->create(['ciclo_id' => $ciclo1->id, 'numero_curso' => 1, 'etiqueta' => 'A']);
        Auth::loginUsingId($admin->id);

        $response = $this->post(route('admin.ciclos.grupos.store', $ciclo2), [
            'numero_curso' => 1,
            'etiqueta'     => 'A',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseCount('grupos', 2);
    }

    #[Test]
    public function admin_puede_activar_y_desactivar_un_grupo(): void
    {
        $admin = $this->admin();
        $ciclo = CicloFormativo::factory()->create();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'activo' => true]);
        Auth::loginUsingId($admin->id);

        $response = $this->patch(route('admin.ciclos.grupos.toggle', [$ciclo, $grupo]));

        $response->assertRedirect(route('admin.ciclos.edit', $ciclo));
        $this->assertDatabaseHas('grupos', ['id' => $grupo->id, 'activo' => 0]);
    }

    #[Test]
    public function toggle_de_grupo_que_no_pertenece_al_ciclo_devuelve_404(): void
    {
        $admin       = $this->admin();
        $cicloReal   = CicloFormativo::factory()->create();
        $cicloAjeno  = CicloFormativo::factory()->create();
        $grupo       = Grupo::factory()->create(['ciclo_id' => $cicloReal->id]);
        Auth::loginUsingId($admin->id);

        $response = $this->patch(route('admin.ciclos.grupos.toggle', [$cicloAjeno, $grupo]));

        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_no_admin_no_puede_crear_grupos(): void
    {
        $profesor = User::factory()->create(['rol' => 'profesor', 'activo' => true]);
        $ciclo = CicloFormativo::factory()->create();
        Auth::loginUsingId($profesor->id);

        $response = $this->post(route('admin.ciclos.grupos.store', $ciclo), [
            'numero_curso' => 1,
            'etiqueta'     => 'A',
        ]);

        $response->assertStatus(403);
    }
}
