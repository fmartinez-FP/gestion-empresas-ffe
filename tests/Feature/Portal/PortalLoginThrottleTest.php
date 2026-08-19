<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('portal-login|' . request()->ip());
        parent::tearDown();
    }

    #[Test]
    public function sexto_intento_fallido_en_un_minuto_es_bloqueado_por_throttle(): void
    {
        User::factory()->create([
            'email'    => 'alumno.throttle@example.com',
            'password' => Hash::make('password-correcto'),
            'rol'      => 'alumno',
            'activo'   => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('portal.login.submit'), [
                'email'    => 'alumno.throttle@example.com',
                'password' => 'password-incorrecto',
            ])->assertSessionHasErrors(['email']);
        }

        $sextoIntento = $this->post(route('portal.login.submit'), [
            'email'    => 'alumno.throttle@example.com',
            'password' => 'password-incorrecto',
        ]);

        $sextoIntento->assertSessionHasErrors(['email']);
        $sextoIntento->assertSessionHas('errors', function ($errors) {
            return str_contains($errors->first('email'), 'Demasiados intentos');
        });
    }

    #[Test]
    public function login_correcto_no_se_ve_afectado_por_throttle_dentro_del_limite(): void
    {
        User::factory()->create([
            'email'    => 'alumno.ok@example.com',
            'password' => Hash::make('password-correcto'),
            'rol'      => 'alumno',
            'activo'   => true,
        ]);

        $response = $this->post(route('portal.login.submit'), [
            'email'    => 'alumno.ok@example.com',
            'password' => 'password-correcto',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
    }
}
