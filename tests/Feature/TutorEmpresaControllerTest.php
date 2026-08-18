<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\CicloFormativo;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\User;
use App\Services\TokenTutorEmpresaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TutorEmpresaControllerTest extends TestCase
{
    use RefreshDatabase;

    private function asignacion(): AsignacionFct
    {
        $ciclo  = CicloFormativo::factory()->create();
        $grupo  = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 2]);
        $alumno = Alumno::factory()->create(['grupo_id' => $grupo->id, 'curso_academico' => '2025-2026']);
        $empresa = Empresa::factory()->create();
        $tutorIes = User::factory()->create(['rol' => 'profesor', 'activo' => true]);

        return AsignacionFct::factory()->create([
            'alumno_id'    => $alumno->id,
            'empresa_id'   => $empresa->id,
            'ciclo_id'     => $ciclo->id,
            'tutor_ies_id' => $tutorIes->id,
        ]);
    }

    #[Test]
    public function token_generado_no_se_almacena_en_claro_en_bd(): void
    {
        $asignacion = $this->asignacion();

        $resultado = app(TokenTutorEmpresaService::class)->generar($asignacion);

        $this->assertDatabaseMissing('tokens_tutor_empresa', [
            'token' => $resultado['tokenPlano'],
        ]);

        $this->assertDatabaseHas('tokens_tutor_empresa', [
            'token' => hash('sha256', $resultado['tokenPlano']),
        ]);
    }

    #[Test]
    public function acceso_con_token_generado_funciona_pese_al_hash(): void
    {
        $asignacion = $this->asignacion();

        $resultado = app(TokenTutorEmpresaService::class)->generar($asignacion);

        $this->get(route('tutor.acceso', ['token' => $resultado['tokenPlano']]))
            ->assertOk();
    }

    #[Test]
    public function token_invalido_no_da_acceso(): void
    {
        $this->get(route('tutor.acceso', ['token' => 'token-inventado-que-no-existe']))
            ->assertNotFound();
    }
}
