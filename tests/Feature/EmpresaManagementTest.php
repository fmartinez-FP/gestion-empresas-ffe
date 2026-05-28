<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Empresa;
use App\Models\CicloFormativo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
    }

    /** @test */
    public function usuario_puede_ver_listado_empresas()
    {
        $user = User::factory()->create();
        
        Empresa::create([
            'nombre' => 'Empresa Test',
            'cif' => 'B12345678',
            'creador_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/empresas');

        $response->assertStatus(200);
        $response->assertSee('Empresas');
    }

    /** @test */
    public function usuario_puede_crear_empresa()
    {
        $user = User::factory()->create();
        $ciclo = CicloFormativo::first();

        $response = $this->actingAs($user)->post('/empresas', [
            'nombre' => 'Nueva Empresa S.L.',
            'cif' => 'B98765432',
            'telefono' => '912345678',
            'email' => 'contacto@nuevaempresa.es',
            'direccion' => 'Calle Test 123',
            'num_convenio' => 'CONV-2024-001',
            'fecha_firma' => '2024-01-15',
            'ciclos' => [
                $ciclo->id => [
                    'acepta_primero' => true,
                    'acepta_segundo' => true,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('empresas', [
            'nombre' => 'Nueva Empresa S.L.',
            'cif' => 'B98765432',
        ]);
    }

    /** @test */
    public function validacion_cif_unico()
    {
        $user = User::factory()->create();
        
        Empresa::create([
            'nombre' => 'Primera Empresa',
            'cif' => 'B11111111',
            'creador_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/empresas', [
            'nombre' => 'Segunda Empresa',
            'cif' => 'B11111111', // CIF duplicado
        ]);

        $response->assertSessionHasErrors('cif');
    }

    /** @test */
    public function usuario_puede_ver_detalle_empresa()
    {
        $user = User::factory()->create();
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Detalle',
            'cif' => 'B22222222',
            'creador_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/empresas/{$empresa->id}");

        $response->assertStatus(200);
        $response->assertSee('Empresa Detalle');
        $response->assertSee('B22222222');
    }

    /** @test */
    public function creador_puede_editar_su_empresa()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        
        $empresa = Empresa::create([
            'nombre' => 'Mi Empresa',
            'cif' => 'B33333333',
            'creador_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put("/empresas/{$empresa->id}", [
            'nombre' => 'Mi Empresa Actualizada',
            'cif' => 'B33333333',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'nombre' => 'Mi Empresa Actualizada',
        ]);
    }

    /** @test */
    public function profesor_no_puede_editar_empresa_ajena()
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otroProfesor = User::factory()->create(['rol' => 'profesor']);
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Ajena',
            'cif' => 'B44444444',
            'creador_id' => $propietario->id,
        ]);

        $response = $this->actingAs($otroProfesor)->put("/empresas/{$empresa->id}", [
            'nombre' => 'Intento de Modificación',
            'cif' => 'B44444444',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_puede_editar_cualquier_empresa()
    {
        $profesor = User::factory()->create(['rol' => 'profesor']);
        $admin = User::factory()->create(['rol' => 'admin']);
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa de Profesor',
            'cif' => 'B55555555',
            'creador_id' => $profesor->id,
        ]);

        $response = $this->actingAs($admin)->put("/empresas/{$empresa->id}", [
            'nombre' => 'Empresa Modificada por Admin',
            'cif' => 'B55555555',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('empresas', [
            'id' => $empresa->id,
            'nombre' => 'Empresa Modificada por Admin',
        ]);
    }

    /** @test */
    public function admin_puede_eliminar_empresa()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $admin = User::factory()->create(['rol' => 'admin']);
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa a Eliminar',
            'cif' => 'B66666666',
            'creador_id' => $user->id,
        ]);

        $response = $this->actingAs($admin)->delete("/empresas/{$empresa->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('empresas', ['id' => $empresa->id]);
    }

    /** @test */
    public function profesor_no_puede_eliminar_empresa()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        
        $empresa = Empresa::create([
            'nombre' => 'Mi Empresa',
            'cif' => 'B77777777',
            'creador_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/empresas/{$empresa->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('empresas', ['id' => $empresa->id]);
    }
}
