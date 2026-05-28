<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\PersonaContacto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonaContactoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
    }

    private function crearEmpresa(User $user): Empresa
    {
        return Empresa::create([
            'nombre'     => 'Empresa Test S.L.',
            'cif'        => 'B' . rand(10000000, 99999999),
            'creador_id' => $user->id,
        ]);
    }

    /** @test */
    public function creador_puede_ver_formulario_crear_persona_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $this->actingAs($user)
            ->get("/empresas/{$empresa->id}/personas-contacto/create")
            ->assertStatus(200)
            ->assertSee('Persona de Contacto');
    }

    /** @test */
    public function profesor_ajeno_no_puede_crear_persona_contacto()
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otro = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($propietario);

        $this->actingAs($otro)
            ->get("/empresas/{$empresa->id}/personas-contacto/create")
            ->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('error');
    }

    /** @test */
    public function creador_puede_crear_persona_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $this->actingAs($user)
            ->post("/empresas/{$empresa->id}/personas-contacto", [
                'nombre'   => 'Ana López',
                'cargo'    => 'Directora RRHH',
                'telefono' => '912345678',
                'email'    => 'ana@empresa.es',
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('personas_contacto', [
            'empresa_id' => $empresa->id,
            'nombre'     => 'Ana López',
            'cargo'      => 'Directora RRHH',
        ]);
    }

    /** @test */
    public function primera_persona_se_marca_como_principal_automaticamente()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $this->actingAs($user)->post("/empresas/{$empresa->id}/personas-contacto", [
            'nombre' => 'Primera Persona',
        ]);

        $this->assertDatabaseHas('personas_contacto', [
            'empresa_id' => $empresa->id,
            'nombre'     => 'Primera Persona',
            'principal'  => true,
        ]);
    }

    /** @test */
    public function marcar_como_principal_desmarca_la_anterior()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $primera = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Primera', 'principal' => true]);
        $segunda = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Segunda', 'principal' => false]);

        $this->actingAs($user)->put(
            "/empresas/{$empresa->id}/personas-contacto/{$segunda->id}",
            ['nombre' => 'Segunda', 'principal' => true]
        );

        $this->assertDatabaseHas('personas_contacto', ['id' => $primera->id, 'principal' => false]);
        $this->assertDatabaseHas('personas_contacto', ['id' => $segunda->id, 'principal' => true]);
    }

    /** @test */
    public function creador_puede_editar_persona_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);
        $persona = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Original', 'principal' => true]);

        $this->actingAs($user)
            ->put("/empresas/{$empresa->id}/personas-contacto/{$persona->id}", [
                'nombre' => 'Modificado',
                'cargo'  => 'CEO',
            ])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('personas_contacto', ['id' => $persona->id, 'nombre' => 'Modificado', 'cargo' => 'CEO']);
    }

    /** @test */
    public function profesor_ajeno_no_puede_editar_persona_contacto()
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otro = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($propietario);
        $persona = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Contacto', 'principal' => true]);

        $this->actingAs($otro)
            ->put("/empresas/{$empresa->id}/personas-contacto/{$persona->id}", ['nombre' => 'Intento'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('personas_contacto', ['id' => $persona->id, 'nombre' => 'Contacto']);
    }

    /** @test */
    public function admin_puede_gestionar_personas_de_cualquier_empresa()
    {
        $profesor = User::factory()->create(['rol' => 'profesor']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $empresa = $this->crearEmpresa($profesor);

        $this->actingAs($admin)
            ->post("/empresas/{$empresa->id}/personas-contacto", ['nombre' => 'Desde Admin'])
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('personas_contacto', ['empresa_id' => $empresa->id, 'nombre' => 'Desde Admin']);
    }

    /** @test */
    public function creador_puede_eliminar_persona_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);
        $persona = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'A Eliminar', 'principal' => true]);

        $this->actingAs($user)
            ->delete("/empresas/{$empresa->id}/personas-contacto/{$persona->id}")
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseMissing('personas_contacto', ['id' => $persona->id]);
    }

    /** @test */
    public function al_eliminar_principal_la_siguiente_asume_el_rol()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);
        $principal = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Principal', 'principal' => true]);
        $segunda = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Segunda', 'principal' => false]);

        $this->actingAs($user)->delete("/empresas/{$empresa->id}/personas-contacto/{$principal->id}");

        $this->assertDatabaseHas('personas_contacto', ['id' => $segunda->id, 'principal' => true]);
    }

    /** @test */
    public function nombre_es_obligatorio()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $this->actingAs($user)
            ->post("/empresas/{$empresa->id}/personas-contacto", ['nombre' => ''])
            ->assertSessionHasErrors('nombre');
    }

    /** @test */
    public function persona_de_otra_empresa_devuelve_404()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa1 = $this->crearEmpresa($user);
        $empresa2 = Empresa::create(['nombre' => 'Otra', 'cif' => 'B00000001', 'creador_id' => $user->id]);
        $persona = PersonaContacto::create(['empresa_id' => $empresa2->id, 'nombre' => 'Contacto', 'principal' => true]);

        $this->actingAs($user)
            ->put("/empresas/{$empresa1->id}/personas-contacto/{$persona->id}", ['nombre' => 'Hack'])
            ->assertStatus(404);
    }
}
