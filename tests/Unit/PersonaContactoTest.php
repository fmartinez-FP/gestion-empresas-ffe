<?php

namespace Tests\Unit;

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

    private function crearEmpresa(): Empresa
    {
        $user = User::factory()->create();
        return Empresa::create([
            'nombre'     => 'Empresa Test',
            'cif'        => 'B' . rand(10000000, 99999999),
            'creador_id' => $user->id,
        ]);
    }

    /** @test */
    public function puede_crear_persona_contacto()
    {
        $empresa = $this->crearEmpresa();
        $persona = PersonaContacto::create([
            'empresa_id' => $empresa->id,
            'nombre'     => 'María García',
            'cargo'      => 'Responsable RRHH',
            'principal'  => true,
        ]);

        $this->assertDatabaseHas('personas_contacto', ['nombre' => 'María García']);
        $this->assertTrue((bool) $persona->principal);
    }

    /** @test */
    public function empresa_tiene_relacion_has_many_personas_contacto()
    {
        $empresa = $this->crearEmpresa();
        PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Uno', 'principal' => true]);
        PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Dos', 'principal' => false]);

        $this->assertEquals(2, $empresa->personasContacto()->count());
    }

    /** @test */
    public function accessor_persona_contacto_devuelve_nombre_del_principal()
    {
        $empresa = $this->crearEmpresa();
        PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Secundario', 'principal' => false]);
        PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Principal', 'principal' => true]);

        $empresa->refresh();
        $this->assertEquals('Principal', $empresa->persona_contacto);
    }

    /** @test */
    public function accessor_persona_contacto_devuelve_null_sin_personas()
    {
        $empresa = $this->crearEmpresa();
        $this->assertNull($empresa->persona_contacto);
    }

    /** @test */
    public function persona_contacto_pertenece_a_empresa()
    {
        $empresa = $this->crearEmpresa();
        $persona = PersonaContacto::create(['empresa_id' => $empresa->id, 'nombre' => 'Test', 'principal' => true]);

        $this->assertEquals($empresa->id, $persona->empresa->id);
    }
}
