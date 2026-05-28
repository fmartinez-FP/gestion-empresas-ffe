<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\User;
use App\Models\CicloFormativo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class EmpresaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
    }

    /** @test */
    public function puede_crear_una_empresa()
    {
        $user = User::factory()->create();
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Test S.L.',
            'cif' => 'B12345678',
            'creador_id' => $user->id,
        ]);

        $this->assertDatabaseHas('empresas', [
            'nombre' => 'Empresa Test S.L.',
            'cif' => 'B12345678',
        ]);
    }

    /** @test */
    public function estado_convenio_es_activo_cuando_fecha_firma_reciente()
    {
        $user = User::factory()->create();
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Activa',
            'cif' => 'B11111111',
            'creador_id' => $user->id,
            'fecha_firma' => Carbon::now()->subYear(),
        ]);

        $this->assertEquals('activo', $empresa->estado_convenio);
    }

    /** @test */
    public function estado_convenio_es_por_caducar_cuando_faltan_menos_de_6_meses()
    {
        $user = User::factory()->create();
        
        // Convenio firmado hace 3 años y 8 meses (faltan 4 meses para caducar)
        $empresa = Empresa::create([
            'nombre' => 'Empresa Por Caducar',
            'cif' => 'B22222222',
            'creador_id' => $user->id,
            'fecha_firma' => Carbon::now()->subYears(3)->subMonths(8),
        ]);

        $this->assertEquals('por_caducar', $empresa->estado_convenio);
    }

    /** @test */
    public function estado_convenio_es_caducado_cuando_pasan_4_anos()
    {
        $user = User::factory()->create();
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Caducada',
            'cif' => 'B33333333',
            'creador_id' => $user->id,
            'fecha_firma' => Carbon::now()->subYears(5),
        ]);

        $this->assertEquals('caducado', $empresa->estado_convenio);
    }

    /** @test */
    public function estado_convenio_es_sin_convenio_cuando_no_hay_fecha_firma()
    {
        $user = User::factory()->create();
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Sin Convenio',
            'cif' => 'B44444444',
            'creador_id' => $user->id,
            'fecha_firma' => null,
        ]);

        $this->assertEquals('sin_convenio', $empresa->estado_convenio);
    }

    /** @test */
    public function calcula_fecha_vencimiento_correctamente()
    {
        $user = User::factory()->create();
        $fechaFirma = Carbon::create(2020, 6, 15);
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Vencimiento',
            'cif' => 'B55555555',
            'creador_id' => $user->id,
            'fecha_firma' => $fechaFirma,
        ]);

        $this->assertEquals('2024-06-15', $empresa->fecha_vencimiento->format('Y-m-d'));
    }

    /** @test */
    public function scope_estado_convenio_filtra_correctamente()
    {
        $user = User::factory()->create();
        
        // Crear empresas con diferentes estados
        Empresa::create([
            'nombre' => 'Activa',
            'cif' => 'B66666666',
            'creador_id' => $user->id,
            'fecha_firma' => Carbon::now()->subYear(),
        ]);
        
        Empresa::create([
            'nombre' => 'Caducada',
            'cif' => 'B77777777',
            'creador_id' => $user->id,
            'fecha_firma' => Carbon::now()->subYears(5),
        ]);

        $activas = Empresa::estadoConvenio('activo')->get();
        $caducadas = Empresa::estadoConvenio('caducado')->get();

        $this->assertEquals(1, $activas->count());
        $this->assertEquals(1, $caducadas->count());
        $this->assertEquals('Activa', $activas->first()->nombre);
        $this->assertEquals('Caducada', $caducadas->first()->nombre);
    }

    /** @test */
    public function scope_buscar_encuentra_por_nombre_cif_o_contacto()
    {
        $user = User::factory()->create();
        
        $empresa = Empresa::create([
            'nombre' => 'Telefónica España',
            'cif' => 'A28015865',
            'creador_id' => $user->id,
        ]);

        \App\Models\PersonaContacto::create([
            'empresa_id' => $empresa->id,
            'nombre'     => 'Juan García',
            'principal'  => true,
        ]);

        $this->assertEquals(1, Empresa::buscar('Telefónica')->count());
        $this->assertEquals(1, Empresa::buscar('A28015865')->count());
        $this->assertEquals(1, Empresa::buscar('García')->count());
        $this->assertEquals(0, Empresa::buscar('NoExiste')->count());
    }

    /** @test */
    public function puede_sincronizar_ciclos_con_pivot()
    {
        $user = User::factory()->create();
        $ciclos = CicloFormativo::take(2)->get();
        
        $empresa = Empresa::create([
            'nombre' => 'Empresa Ciclos',
            'cif' => 'B88888888',
            'creador_id' => $user->id,
        ]);

        $empresa->sincronizarCiclos([
            $ciclos[0]->id => ['acepta_primero' => true, 'acepta_segundo' => true],
            $ciclos[1]->id => ['acepta_primero' => false, 'acepta_segundo' => true],
        ]);

        $this->assertEquals(2, $empresa->ciclos()->count());
        // MySQL devuelve tinyint; cast explícito a bool para la aserción
        $this->assertTrue((bool) $empresa->ciclos->first()->pivot->acepta_primero);
    }
}
