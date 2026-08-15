<?php

namespace Tests\Feature;

use App\Console\Commands\PurgarDocumentacionFfe;
use App\Contracts\PdfGeneratorInterface;
use App\Models\AsignacionFct;
use App\Models\DocumentoFct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentoFctTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // SETUP: mock de PdfGeneratorInterface para no invocar DomPDF
    // =========================================================================

    protected function setUp(): void
    {
        parent::setUp();

        // Sustituye el adaptador real por un fake que escribe un PDF mínimo en disco
        $this->instance(PdfGeneratorInterface::class, new class implements PdfGeneratorInterface {
            public function generar(string $vista, array $datos, string $rutaDisco, string $disco = 'private'): string
            {
                Storage::disk($disco)->put($rutaDisco, '%PDF-1.4 fake');
                return $rutaDisco;
            }
        });

        Storage::fake('private');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function usuarioConRol(string $rol): User
    {
        return User::factory()->create(['rol' => $rol]);
    }

    private function asignacionCompleta(array $attrs = []): AsignacionFct
    {
        return AsignacionFct::factory()->create(array_merge([
            'fecha_inicio'  => now()->subDays(30),
            'fecha_fin'     => now()->addDays(30),
            'num_horas'     => 400,
            'curso_academico' => '2025-2026',
        ], $attrs));
    }

    // =========================================================================
    // GENERACIÓN
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function genera_plan_formativo_y_crea_registro(): void
    {
        $user       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('documentos.generar', [$asignacion, 'plan_formativo']));

        $response->assertRedirect(route('asignaciones.show', $asignacion));
        $this->assertDatabaseHas('documentos_fct', [
            'asignacion_id' => $asignacion->id,
            'tipo'          => 'plan_formativo',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function genera_ficha_seguimiento_y_crea_registro(): void
    {
        $user       = $this->usuarioConRol('responsable_ffe');
        $asignacion = $this->asignacionCompleta();

        $this->actingAs($user)
            ->post(route('documentos.generar', [$asignacion, 'ficha_seguimiento']))
            ->assertRedirect(route('asignaciones.show', $asignacion));

        $this->assertDatabaseHas('documentos_fct', [
            'asignacion_id' => $asignacion->id,
            'tipo'          => 'ficha_seguimiento',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function genera_informe_final_y_crea_registro(): void
    {
        $user       = $this->usuarioConRol('admin');
        $asignacion = $this->asignacionCompleta();

        $this->actingAs($user)
            ->post(route('documentos.generar', [$asignacion, 'informe_final']))
            ->assertRedirect(route('asignaciones.show', $asignacion));

        $this->assertDatabaseCount('documentos_fct', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function regenerar_mismo_tipo_elimina_el_previo(): void
    {
        $user       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $user->id]);

        $this->actingAs($user)->post(route('documentos.generar', [$asignacion, 'plan_formativo']));
        $this->actingAs($user)->post(route('documentos.generar', [$asignacion, 'plan_formativo']));

        // Solo debe haber un registro (el previo fue reemplazado)
        $this->assertDatabaseCount('documentos_fct', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tipo_invalido_devuelve_422(): void
    {
        $user       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('documentos.generar', [$asignacion, 'tipo_inexistente']))
            ->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profesor_ajeno_no_puede_generar_documento(): void
    {
        $tutor      = $this->usuarioConRol('profesor');
        $otro       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $tutor->id]);

        $this->actingAs($otro)
            ->post(route('documentos.generar', [$asignacion, 'plan_formativo']))
            ->assertForbidden();

        $this->assertDatabaseMissing('documentos_fct', ['asignacion_id' => $asignacion->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function responsable_ciclo_ajeno_no_puede_generar_documento(): void
    {
        $tutor      = $this->usuarioConRol('profesor');
        $ciclo      = $this->usuarioConRol('responsable_ciclo');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $tutor->id]);

        $this->actingAs($ciclo)
            ->post(route('documentos.generar', [$asignacion, 'plan_formativo']))
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profesor_ajeno_no_puede_descargar_documento(): void
    {
        $tutor      = $this->usuarioConRol('profesor');
        $otro       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $tutor->id]);

        $this->actingAs($tutor)->post(route('documentos.generar', [$asignacion, 'plan_formativo']));
        $doc = DocumentoFct::first();

        $this->actingAs($otro)
            ->get(route('documentos.descargar', $doc))
            ->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profesor_ajeno_no_puede_subir_firmado(): void
    {
        $tutor      = $this->usuarioConRol('profesor');
        $otro       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $tutor->id]);

        $pdf = UploadedFile::fake()->create('firmado.pdf', 100, 'application/pdf');

        $this->actingAs($otro)
            ->post(route('documentos.subir-firmado', $asignacion), ['pdf_firmado' => $pdf])
            ->assertForbidden();

        $this->assertDatabaseMissing('documentos_fct', ['asignacion_id' => $asignacion->id]);
    }

    // =========================================================================
    // DESCARGA
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function descarga_documento_existente(): void
    {
        $user       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $user->id]);

        // Generar primero
        $this->actingAs($user)->post(route('documentos.generar', [$asignacion, 'plan_formativo']));
        $doc = DocumentoFct::first();

        $response = $this->actingAs($user)->get(route('documentos.descargar', $doc));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function descarga_falla_si_archivo_no_existe_en_disco(): void
    {
        $user       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $user->id]);
        $doc        = DocumentoFct::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'ruta_disco'     => 'fct/99/inexistente.pdf',
            'disco'          => 'private',
            'nombre_archivo' => 'inexistente.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('documentos.descargar', $doc))
            ->assertNotFound();
    }

    // =========================================================================
    // SUBIDA FIRMADO
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function sube_pdf_firmado_y_calcula_purgar_after(): void
    {
        $user       = $this->usuarioConRol('responsable_ffe');
        $fechaFin   = now()->addDays(60)->startOfDay();
        $asignacion = AsignacionFct::factory()->create(['fecha_fin' => $fechaFin]);

        $pdf = UploadedFile::fake()->create('firmado.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('documentos.subir-firmado', $asignacion), ['pdf_firmado' => $pdf])
            ->assertRedirect(route('asignaciones.show', $asignacion));

        $doc = DocumentoFct::where('tipo', 'firmado')->first();
        $this->assertNotNull($doc);
        $this->assertEquals(
            $fechaFin->copy()->addYears(5)->toDateString(),
            $doc->purgar_after->toDateString()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sube_firmado_multiples_veces_acumula_registros(): void
    {
        $user       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $user->id]);

        $pdf = UploadedFile::fake()->create('firmado.pdf', 100, 'application/pdf');
        $this->actingAs($user)->post(route('documentos.subir-firmado', $asignacion), ['pdf_firmado' => $pdf]);
        $this->actingAs($user)->post(route('documentos.subir-firmado', $asignacion), ['pdf_firmado' => $pdf]);

        $this->assertDatabaseCount('documentos_fct', 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function subida_requiere_archivo_pdf(): void
    {
        $user       = $this->usuarioConRol('profesor');
        $asignacion = $this->asignacionCompleta(['tutor_ies_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('documentos.subir-firmado', $asignacion), [])
            ->assertSessionHasErrors('pdf_firmado');
    }

    // =========================================================================
    // ELIMINACIÓN
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_puede_eliminar_documento(): void
    {
        $user       = $this->usuarioConRol('admin');
        $asignacion = $this->asignacionCompleta();

        $this->actingAs($user)->post(route('documentos.generar', [$asignacion, 'plan_formativo']));
        $doc = DocumentoFct::first();

        $this->actingAs($user)
            ->delete(route('documentos.destroy', $doc))
            ->assertRedirect(route('asignaciones.show', $asignacion->id));

        $this->assertDatabaseMissing('documentos_fct', ['id' => $doc->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function profesor_no_puede_eliminar_documento(): void
    {
        $profesor   = $this->usuarioConRol('profesor');
        $admin      = $this->usuarioConRol('admin');
        $asignacion = $this->asignacionCompleta();

        $this->actingAs($admin)->post(route('documentos.generar', [$asignacion, 'plan_formativo']));
        $doc = DocumentoFct::first();

        $this->actingAs($profesor)
            ->delete(route('documentos.destroy', $doc))
            ->assertForbidden();
    }

    // =========================================================================
    // COMANDO PURGA
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function comando_purga_documentos_firmados_caducados(): void
    {
        $asignacion = $this->asignacionCompleta();

        // Documento firmado caducado
        DocumentoFct::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'tipo'           => 'firmado',
            'ruta_disco'     => 'fct/1/viejo.pdf',
            'disco'          => 'private',
            'nombre_archivo' => 'viejo.pdf',
            'purgar_after'   => now()->subDay(),
        ]);

        // Documento firmado vigente
        DocumentoFct::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'tipo'           => 'firmado',
            'ruta_disco'     => 'fct/1/vigente.pdf',
            'disco'          => 'private',
            'nombre_archivo' => 'vigente.pdf',
            'purgar_after'   => now()->addYears(5),
        ]);

        $this->artisan('ffe:purgar-documentacion')
            ->expectsConfirmation('¿Confirmar la eliminación de estos documentos firmados? Esta acción es irreversible.', 'yes')
            ->assertSuccessful();

        $this->assertDatabaseCount('documentos_fct', 1);
        $this->assertDatabaseHas('documentos_fct', ['nombre_archivo' => 'vigente.pdf']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function comando_purga_temporales_mayores_de_24h_sin_confirmacion(): void
    {
        $asignacion = $this->asignacionCompleta();

        $doc = DocumentoFct::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'tipo'           => 'plan_formativo',
            'ruta_disco'     => 'fct/1/viejo.pdf',
            'disco'          => 'private',
            'nombre_archivo' => 'viejo.pdf',
            'created_at'     => now()->subHours(25),
        ]);

        $this->artisan('ffe:purgar-documentacion')
            ->assertSuccessful();

        $this->assertDatabaseMissing('documentos_fct', ['id' => $doc->id]);
    }
}
