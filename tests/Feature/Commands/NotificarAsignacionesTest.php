<?php

namespace Tests\Feature\Commands;

use App\Models\CicloFormativo;
use App\Models\Colocacion;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificarAsignacionesTest extends TestCase
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

        $this->artisan('asignaciones:notificar')
            ->expectsOutput('Notificaciones suspendidas en julio y agosto.')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function suspende_notificaciones_en_agosto()
    {
        Carbon::setTestNow(Carbon::create(2025, 8, 1));

        $this->artisan('asignaciones:notificar')
            ->expectsOutput('Notificaciones suspendidas en julio y agosto.')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_notifica_si_no_hay_asignaciones_recientes()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 10, 15));

        $profesor = User::factory()->create(['rol' => 'profesor', 'email' => 'p@test.es']);
        Empresa::create([
            'nombre'     => 'Empresa',
            'cif'        => 'B12345678',
            'creador_id' => $profesor->id,
        ]);

        // Sin colocaciones recientes
        $this->artisan('asignaciones:notificar')
            ->expectsOutputToContain('Total notificaciones enviadas: 0')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notifica_profesor_con_asignaciones_recientes_de_otro_usuario()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 10, 15));

        $ciclo    = CicloFormativo::first();
        $profesor = User::factory()->create(['rol' => 'profesor', 'email' => 'p@test.es']);
        $otro     = User::factory()->create(['rol' => 'profesor']);

        $empresa = Empresa::create([
            'nombre'     => 'Empresa Profesor',
            'cif'        => 'B12345678',
            'creador_id' => $profesor->id,
        ]);

        // Colocación reciente registrada por OTRO usuario
        Colocacion::create([
            'empresa_id'        => $empresa->id,
            'ciclo_id'          => $ciclo->id,
            'registrado_por_id' => $otro->id,
            'curso_academico'   => '2025-2026',
            'numero_curso'      => 1,
            'num_alumnos'       => 2,
            'num_horas'         => 200,
            'created_at'        => Carbon::now()->subDays(2),
        ]);

        $this->artisan('asignaciones:notificar')
            ->expectsOutputToContain('Notificado:')
            ->expectsOutputToContain('Total notificaciones enviadas: 1')
            ->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_notifica_asignaciones_del_propio_profesor()
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2025, 10, 15));

        $ciclo    = CicloFormativo::first();
        $profesor = User::factory()->create(['rol' => 'profesor', 'email' => 'p@test.es']);

        $empresa = Empresa::create([
            'nombre'     => 'Mi Empresa',
            'cif'        => 'B12345678',
            'creador_id' => $profesor->id,
        ]);

        // Colocación registrada por el mismo profesor → no debe notificar
        Colocacion::create([
            'empresa_id'        => $empresa->id,
            'ciclo_id'          => $ciclo->id,
            'registrado_por_id' => $profesor->id,
            'curso_academico'   => '2025-2026',
            'numero_curso'      => 1,
            'num_alumnos'       => 2,
            'num_horas'         => 200,
            'created_at'        => Carbon::now()->subDays(2),
        ]);

        $this->artisan('asignaciones:notificar')
            ->expectsOutputToContain('Total notificaciones enviadas: 0')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
