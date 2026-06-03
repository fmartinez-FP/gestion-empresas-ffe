<?php

namespace Tests\Feature\Commands;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Models\TokenTutorEmpresa;
use App\Models\DocumentoFct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResetCursoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function finaliza_asignaciones_activas_del_curso(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'curso_academico' => '2024-2025',
            'estado'          => 'activa',
        ]);

        $this->artisan('ffe:reset-curso', ['curso_academico' => '2024-2025'])
            ->expectsConfirmation(
                '¿Confirmas el reset del curso 2024-2025? Esta acción NO se puede deshacer.',
                'yes'
            )
            ->assertExitCode(0);

        $this->assertDatabaseHas('asignaciones_fct', [
            'id'     => $asignacion->id,
            'estado' => 'finalizada',
        ]);
    }

    #[Test]
    public function elimina_seguimientos_y_tokens_del_curso(): void
    {
        Storage::fake('private');

        $asignacion = AsignacionFct::factory()->create([
            'curso_academico' => '2024-2025',
            'estado'          => 'activa',
        ]);

        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'evidencia_path' => null,
        ]);

        $token = TokenTutorEmpresa::factory()->for($asignacion, 'asignacion')->create();

        $this->artisan('ffe:reset-curso', ['curso_academico' => '2024-2025'])
            ->expectsConfirmation(
                '¿Confirmas el reset del curso 2024-2025? Esta acción NO se puede deshacer.',
                'yes'
            )
            ->assertExitCode(0);

        $this->assertDatabaseMissing('seguimiento_diario', ['id' => $seguimiento->id]);
        $this->assertDatabaseMissing('tokens_tutor_empresa', ['id' => $token->id]);
    }

    #[Test]
    public function elimina_documentos_generados_y_conserva_firmados(): void
    {
        Storage::fake('private');

        $asignacion = AsignacionFct::factory()->create([
            'curso_academico' => '2024-2025',
            'estado'          => 'activa',
        ]);

        $docGenerado = DocumentoFct::factory()->create([
            'asignacion_id' => $asignacion->id,
            'tipo'          => 'plan_formativo',
            'ruta_disco'    => 'fct/plan.pdf',
            'disco'         => 'private',
        ]);

        $docFirmado = DocumentoFct::factory()->create([
            'asignacion_id' => $asignacion->id,
            'tipo'          => 'firmado',
            'ruta_disco'    => 'fct/firmado.pdf',
            'disco'         => 'private',
        ]);

        $this->artisan('ffe:reset-curso', ['curso_academico' => '2024-2025'])
            ->expectsConfirmation(
                '¿Confirmas el reset del curso 2024-2025? Esta acción NO se puede deshacer.',
                'yes'
            )
            ->assertExitCode(0);

        $this->assertDatabaseMissing('documentos_fct', ['id' => $docGenerado->id]);
        $this->assertDatabaseHas('documentos_fct', ['id' => $docFirmado->id, 'tipo' => 'firmado']);
    }

    #[Test]
    public function no_actua_si_no_hay_asignaciones_activas(): void
    {
        AsignacionFct::factory()->create([
            'curso_academico' => '2024-2025',
            'estado'          => 'finalizada',
        ]);

        $this->artisan('ffe:reset-curso', ['curso_academico' => '2024-2025'])
            ->assertExitCode(0);
    }

    #[Test]
    public function no_toca_asignaciones_de_otro_curso(): void
    {
        $otroCurso = AsignacionFct::factory()->create([
            'curso_academico' => '2023-2024',
            'estado'          => 'activa',
        ]);

        $asignacion = AsignacionFct::factory()->create([
            'curso_academico' => '2024-2025',
            'estado'          => 'activa',
        ]);

        $this->artisan('ffe:reset-curso', ['curso_academico' => '2024-2025'])
            ->expectsConfirmation(
                '¿Confirmas el reset del curso 2024-2025? Esta acción NO se puede deshacer.',
                'yes'
            )
            ->assertExitCode(0);

        $this->assertDatabaseHas('asignaciones_fct', [
            'id'     => $otroCurso->id,
            'estado' => 'activa',
        ]);
    }

    #[Test]
    public function cancela_si_usuario_no_confirma(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'curso_academico' => '2024-2025',
            'estado'          => 'activa',
        ]);

        $this->artisan('ffe:reset-curso', ['curso_academico' => '2024-2025'])
            ->expectsConfirmation(
                '¿Confirmas el reset del curso 2024-2025? Esta acción NO se puede deshacer.',
                'no'
            )
            ->assertExitCode(0);

        $this->assertDatabaseHas('asignaciones_fct', [
            'id'     => $asignacion->id,
            'estado' => 'activa',
        ]);
    }
}
