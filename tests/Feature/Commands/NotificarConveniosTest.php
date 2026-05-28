<?php

namespace Tests\Feature\Commands;

use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificarConveniosTest extends TestCase
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
    public function suspende_notificaciones_en_julio()
    {
        Carbon::setTestNow(Carbon::create(2025, 7, 15));

        $this->artisan('convenios:notificar')
            ->expectsOutput('Notificaciones suspendidas en julio y agosto.')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function suspende_notificaciones_en_agosto()
    {
        Carbon::setTestNow(Carbon::create(2025, 8, 1));

        $this->artisan('convenios:notificar')
            ->expectsOutput('Notificaciones suspendidas en julio y agosto.')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_envia_si_no_hay_empresas_por_caducar()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 10, 15));

        $admin = User::factory()->create(['rol' => 'admin', 'email' => 'admin@test.es']);

        // Empresa activa, no por caducar
        Empresa::create([
            'nombre'      => 'Empresa Activa',
            'cif'         => 'B12345678',
            'creador_id'  => $admin->id,
            'fecha_firma' => Carbon::now()->subYear(),
        ]);

        $this->artisan('convenios:notificar')
            ->expectsOutputToContain('Total notificaciones enviadas: 0')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_recibe_notificacion_con_empresas_por_caducar()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 10, 15));

        $admin = User::factory()->create(['rol' => 'admin', 'email' => 'admin@test.es']);

        // Empresa bajo el propio admin: solo él tiene email y recibe notificación
        Empresa::create([
            'nombre'      => 'Empresa Por Caducar',
            'cif'         => 'B12345678',
            'creador_id'  => $admin->id,
            'fecha_firma' => Carbon::now()->subYears(3)->subMonths(8),
        ]);

        $this->artisan('convenios:notificar')
            ->expectsOutputToContain('Notificado:')
            ->expectsOutputToContain('Total notificaciones enviadas: 1')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profesor_solo_recibe_sus_propias_empresas_por_caducar()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 10, 15));

        $profesor1 = User::factory()->create(['rol' => 'profesor', 'email' => 'p1@test.es']);
        $profesor2 = User::factory()->create(['rol' => 'profesor', 'email' => 'p2@test.es']);

        // Solo profesor1 tiene empresa por caducar (profesor2 con empresa activa no recibe notificacion)
        Empresa::create([
            'nombre'      => 'Empresa P1 Por Caducar',
            'cif'         => 'B11111111',
            'creador_id'  => $profesor1->id,
            'fecha_firma' => Carbon::now()->subYears(3)->subMonths(8),
        ]);

        // Empresa de profesor2 está activa
        Empresa::create([
            'nombre'      => 'Empresa P2 Activa',
            'cif'         => 'B22222222',
            'creador_id'  => $profesor2->id,
            'fecha_firma' => Carbon::now()->subYear(),
        ]);

        $this->artisan('convenios:notificar')
            ->expectsOutputToContain('Total notificaciones enviadas: 1')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function responsable_ciclo_solo_recibe_empresas_de_sus_ciclos()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 10, 15));

        $ciclo = \App\Models\CicloFormativo::first();

        $responsable = User::factory()->create([
            'rol'      => 'responsable_ciclo',
            'email'    => 'resp@test.es',
            'ciclo_id' => $ciclo->id,
        ]);
        $responsable->ciclos()->attach($ciclo->id);

        // Empresa bajo el responsable: es el único con email, recibe 1 notificación
        $empresa = Empresa::create([
            'nombre'      => 'Empresa Ciclo Por Caducar',
            'cif'         => 'B33333333',
            'creador_id'  => $responsable->id,
            'fecha_firma' => Carbon::now()->subYears(3)->subMonths(8),
        ]);
        $empresa->ciclos()->attach($ciclo->id, [
            'acepta_primero'  => true,
            'acepta_segundo'  => true,
        ]);

        $this->artisan('convenios:notificar')
            ->expectsOutputToContain('Total notificaciones enviadas: 1')
            ->assertExitCode(0);
    }
}
