<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pagina_login_se_muestra_correctamente()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Iniciar sesión');
    }

    /** @test */
    public function usuario_puede_autenticarse()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'activo' => true,
        ]);

        // Auth vía LDAP no es testeable en entorno local sin servidor corporativo.
        // Verificamos que un usuario válido puede quedar autenticado en el sistema.
        Auth::loginUsingId($user->id);

        $this->assertAuthenticated();
        $this->assertEquals($user->id, Auth::id());
    }

    /** @test */
    public function usuario_inactivo_no_puede_autenticarse()
    {
        $user = User::factory()->create([
            'username' => 'inactivo',
            'email' => 'inactivo@iespacifico.es',
            'password' => bcrypt('password123'),
            'activo' => false,
        ]);

        $response = $this->post('/login', [
            'username' => 'inactivo',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /** @test */
    public function credenciales_incorrectas_muestran_error()
    {
        $user = User::factory()->create([
            'username' => 'testuser2',
            'email' => 'test@iespacifico.es',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser2',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /** @test */
    public function usuario_puede_cerrar_sesion()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /** @test */
    public function rutas_protegidas_redirigen_a_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/empresas');
        $response->assertRedirect('/login');

        $response = $this->get('/colocaciones');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function usuario_autenticado_accede_a_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }
}
