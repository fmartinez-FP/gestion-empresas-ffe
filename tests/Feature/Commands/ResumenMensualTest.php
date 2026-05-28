<?php

namespace Tests\Feature\Commands;

use App\Models\Colocacion;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResumenMensualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_envia_en_enero()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 1, 15));

        $this->artisan('resumen:mensual')
            ->expectsOutput('El resumen mensual solo se envía de febrero a mayo.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_envia_en_junio()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 6, 1));

        $this->artisan('resumen:mensual')
            ->expectsOutput('El resumen mensual solo se envía de febrero a mayo.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_envia_en_septiembre()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 9, 1));

        $this->artisan('resumen:mensual')
            ->expectsOutput('El resumen mensual solo se envía de febrero a mayo.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sin_usuarios_elegibles_no_envia_nada_en_febrero()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 2, 15));

        // Solo profesores — no son destinatarios del resumen mensual
        User::factory()->create(['rol' => 'profesor', 'email' => 'prof@test.es']);

        $this->artisan('resumen:mensual')
            ->expectsOutputToContain('Total notificaciones enviadas: 0')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function envia_resumen_a_admin_en_marzo()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 3, 15));

        User::factory()->create(['rol' => 'admin', 'email' => 'admin@test.es']);

        $this->artisan('resumen:mensual')
            ->expectsOutputToContain('Notificado:')
            ->expectsOutputToContain('Total notificaciones enviadas: 1')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function envia_a_multiples_roles_en_abril()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 4, 10));

        $ciclo = \App\Models\CicloFormativo::first();

        User::factory()->create(['rol' => 'admin',           'email' => 'admin@test.es']);
        User::factory()->create([
            'rol'      => 'responsable_ciclo',
            'email'    => 'ciclo@test.es',
            'ciclo_id' => $ciclo->id,
        ]);

        // Profesor sin rol elegible → no debe recibir resumen mensual
        User::factory()->create(['rol' => 'profesor']);

        $this->artisan('resumen:mensual')
            ->expectsOutputToContain('Total notificaciones enviadas: 2')
            ->assertExitCode(0);
    }
}
