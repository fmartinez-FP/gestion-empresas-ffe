<?php

namespace Tests\Feature;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificacionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function get_a_la_ruta_leer_devuelve_405(): void
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $notificacion = Notificacion::create([
            'user_id' => $user->id,
            'tipo'    => 'empresa_editada',
            'titulo'  => 'Notificación de prueba',
            'url'     => null,
        ]);

        $response = $this->actingAs($user)->get(
            route('notificaciones.leer', $notificacion)
        );

        $response->assertStatus(405);
        $this->assertDatabaseHas('notificaciones', ['id' => $notificacion->id]);
    }

    #[Test]
    public function post_borra_notificacion_propia_y_redirige(): void
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $notificacion = Notificacion::create([
            'user_id' => $user->id,
            'tipo'    => 'empresa_editada',
            'titulo'  => 'Notificación de prueba',
            'url'     => '/dashboard',
        ]);

        $response = $this->actingAs($user)->post(
            route('notificaciones.leer', $notificacion)
        );

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('notificaciones', ['id' => $notificacion->id]);
    }

    #[Test]
    public function post_sobre_notificacion_ajena_devuelve_403(): void
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otroUsuario = User::factory()->create(['rol' => 'profesor']);
        $notificacion = Notificacion::create([
            'user_id' => $propietario->id,
            'tipo'    => 'empresa_editada',
            'titulo'  => 'Notificación de otro usuario',
            'url'     => null,
        ]);

        $response = $this->actingAs($otroUsuario)->post(
            route('notificaciones.leer', $notificacion)
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('notificaciones', ['id' => $notificacion->id]);
    }
}
