<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Empresa;
use App\Models\CicloFormativo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
    }

    /** @test */
    public function puede_crear_usuario_admin()
    {
        $user = User::factory()->create([
            'rol' => 'admin',
        ]);

        $this->assertTrue($user->esAdmin());
        $this->assertFalse($user->esResponsableCiclo());
        $this->assertFalse($user->esProfesor());
    }

    /** @test */
    public function puede_crear_usuario_responsable_ciclo()
    {
        $ciclo = CicloFormativo::first();
        
        $user = User::factory()->create([
            'rol' => 'responsable_ciclo',
            'ciclo_id' => $ciclo->id,
        ]);

        $this->assertFalse($user->esAdmin());
        $this->assertTrue($user->esResponsableCiclo());
        $this->assertFalse($user->esProfesor());
        $this->assertEquals($ciclo->id, $user->ciclo_id);
    }

    /** @test */
    public function puede_crear_usuario_profesor()
    {
        $user = User::factory()->create([
            'rol' => 'profesor',
        ]);

        $this->assertFalse($user->esAdmin());
        $this->assertFalse($user->esResponsableCiclo());
        $this->assertTrue($user->esProfesor());
    }

    /** @test */
    public function admin_puede_editar_cualquier_empresa()
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $profesor = User::factory()->create(['rol' => 'profesor']);
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Ajena',
            'cif' => 'B12345678',
            'creador_id' => $profesor->id,
        ]);

        $this->assertTrue($admin->can('update', $empresa));
    }

    /** @test */
    public function profesor_solo_puede_editar_sus_empresas()
    {
        $profesor1 = User::factory()->create(['rol' => 'profesor']);
        $profesor2 = User::factory()->create(['rol' => 'profesor']);
        
        $empresaPropia = Empresa::create([
            'nombre' => 'Mi Empresa',
            'cif' => 'B11111111',
            'creador_id' => $profesor1->id,
        ]);

        $empresaAjena = Empresa::create([
            'nombre' => 'Empresa Ajena',
            'cif' => 'B22222222',
            'creador_id' => $profesor2->id,
        ]);

        $this->assertTrue($profesor1->can('update', $empresaPropia));
        $this->assertFalse($profesor1->can('update', $empresaAjena));
    }

    /** @test */
    public function responsable_puede_editar_empresas_de_su_ciclo()
    {
        $ciclo = CicloFormativo::first();
        
        $responsable = User::factory()->create([
            'rol' => 'responsable_ciclo',
            'ciclo_id' => $ciclo->id,
        ]);
        $responsable->ciclos()->attach($ciclo->id);
        
        $profesor = User::factory()->create(['rol' => 'profesor']);
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa del Ciclo',
            'cif' => 'B33333333',
            'creador_id' => $profesor->id,
        ]);
        
        // Asignar ciclo a la empresa
        $empresa->ciclos()->attach($ciclo->id, ['acepta_primero' => true, 'acepta_segundo' => true]);

        $this->assertTrue($responsable->can('update', $empresa));
    }

    /** @test */
    public function nombre_rol_devuelve_etiqueta_correcta()
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $responsable = User::factory()->create(['rol' => 'responsable_ciclo']);
        $profesor = User::factory()->create(['rol' => 'profesor']);

        $this->assertEquals('Administrador', $admin->nombre_rol);
        $this->assertEquals('Responsable de Ciclo', $responsable->nombre_rol);
        $this->assertEquals('Profesor', $profesor->nombre_rol);
    }

    /** @test */
    public function scope_activos_filtra_usuarios_activos()
    {
        User::factory()->create(['activo' => true]);
        User::factory()->create(['activo' => true]);
        User::factory()->create(['activo' => false]);

        $this->assertEquals(2, User::activos()->count());
    }

    /** @test */
    public function scope_rol_filtra_por_rol()
    {
        User::factory()->create(['rol' => 'admin']);
        User::factory()->create(['rol' => 'profesor']);
        User::factory()->create(['rol' => 'profesor']);

        $this->assertEquals(1, User::rol('admin')->count());
        $this->assertEquals(2, User::rol('profesor')->count());
    }
}
