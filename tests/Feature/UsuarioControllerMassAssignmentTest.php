<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioControllerMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin', 'activo' => true]);
    }

    #[Test]
    public function actualizar_usuario_ignora_campos_no_permitidos_en_el_payload(): void
    {
        $admin  = $this->admin();
        $target = User::factory()->create([
            'rol'    => 'profesor',
            'activo' => true,
            'email'  => 'original@educa.madrid.org',
        ]);
        Auth::loginUsingId($admin->id);

        $response = $this->put(route('admin.usuarios.update', $target), [
            'rol'      => 'profesor',
            'activo'   => false,
            'email'    => 'hackeado@evil.com',
            'username' => 'hackeado',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $target->refresh();
        $this->assertTrue($target->activo);
        $this->assertEquals('original@educa.madrid.org', $target->email);
        $this->assertNotEquals('hackeado', $target->username);
    }
}
