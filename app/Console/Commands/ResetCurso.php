<?php

namespace App\Console\Commands;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Models\TokenTutorEmpresa;
use App\Models\DocumentoFct;
use App\Services\HistorialAsignacionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResetCurso extends Command
{
    protected $signature   = 'ffe:reset-curso {curso_academico : Curso a resetear, formato YYYY-YYYY}';
    protected $description = 'Purga datos FFE de un curso: finaliza asignaciones, elimina seguimientos, tokens y documentos generados. Conserva firmados, alumnos y empresas.';

    public function handle(HistorialAsignacionService $historialService): int
    {
        $curso = $this->argument('curso_academico');

        if (!preg_match('/^\d{4}-\d{4}$/', $curso)) {
            $this->error("Formato incorrecto. Usa YYYY-YYYY (ej: 2024-2025).");
            return self::FAILURE;
        }

        // --- Recuento previo para resumen ---
        $asignaciones = AsignacionFct::with('ciclo')
            ->where('curso_academico', $curso)
            ->where('estado', 'activa')
            ->get();

        $numAsignaciones = $asignaciones->count();

        if ($numAsignaciones === 0) {
            $this->info("No hay asignaciones activas para el curso {$curso}. Nada que hacer.");
            return self::SUCCESS;
        }

        $asignacionIds = $asignaciones->pluck('id');

        $numSeguimientos = SeguimientoDiario::whereIn('asignacion_id', $asignacionIds)->count();
        $numTokens       = TokenTutorEmpresa::whereIn('asignacion_id', $asignacionIds)->count();

        $docsGenerados = DocumentoFct::whereIn('asignacion_id', $asignacionIds)
            ->whereIn('tipo', ['plan_formativo', 'ficha_seguimiento', 'informe_final'])
            ->get();
        $docsFirmados = DocumentoFct::whereIn('asignacion_id', $asignacionIds)
            ->where('tipo', 'firmado')
            ->count();

        // --- Resumen y confirmación ---
        $this->newLine();
        $this->line('<fg=yellow>RESUMEN DEL RESET — Curso ' . $curso . '</>');
        $this->line('─────────────────────────────────────────────');
        $this->line("  Asignaciones activas a finalizar : {$numAsignaciones}");
        $this->line("  Seguimientos diarios a eliminar  : {$numSeguimientos}");
        $this->line("  Tokens tutor empresa a eliminar  : {$numTokens}");
        $this->line("  Documentos generados a eliminar  : {$docsGenerados->count()}");
        $this->line("  Documentos firmados (conservados): {$docsFirmados}");
        $this->line('─────────────────────────────────────────────');
        $this->newLine();

        if (!$this->confirm("¿Confirmas el reset del curso {$curso}? Esta acción NO se puede deshacer.")) {
            $this->info('Operación cancelada.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Iniciando reset...');

        // Rutas de fichero a borrar del disco: se borran DESPUÉS de confirmar la
        // transacción, para que un fallo a mitad no deje la BD apuntando a ficheros
        // ya eliminados (ni a medio reset).
        $evidencias = SeguimientoDiario::whereIn('asignacion_id', $asignacionIds)
            ->whereNotNull('evidencia_path')
            ->pluck('evidencia_path');
        $ficherosDocs = $docsGenerados->map(fn ($d) => [$d->disco ?? 'private', $d->ruta_disco]);

        $updated = 0;

        DB::transaction(function () use ($curso, $asignacionIds, $docsGenerados, $asignaciones, $historialService, &$updated) {
            // 1. Seguimientos diarios
            $this->line('  [1/4] Eliminando seguimientos diarios...');
            $deleted = SeguimientoDiario::whereIn('asignacion_id', $asignacionIds)->delete();
            Log::info("ffe:reset-curso [{$curso}] Seguimientos eliminados: {$deleted}");

            // 2. Tokens tutor empresa
            $this->line('  [2/4] Eliminando tokens tutor empresa y documentos generados...');
            $deleted = TokenTutorEmpresa::whereIn('asignacion_id', $asignacionIds)->delete();
            Log::info("ffe:reset-curso [{$curso}] Tokens eliminados: {$deleted}");

            // 3. Registros de documentos generados (no firmados)
            foreach ($docsGenerados as $doc) {
                $doc->delete();
            }
            Log::info("ffe:reset-curso [{$curso}] Documentos generados eliminados: {$docsGenerados->count()}");

            // 4. Finalizar asignaciones y generar el histórico de colocaciones
            $this->line('  [3/4] Finalizando asignaciones...');
            $updated = AsignacionFct::where('curso_academico', $curso)
                ->where('estado', 'activa')
                ->update(['estado' => 'finalizada']);
            Log::info("ffe:reset-curso [{$curso}] Asignaciones finalizadas: {$updated}");

            $this->line('  [4/4] Generando histórico de colocaciones...');
            foreach ($asignaciones as $asignacion) {
                $asignacion->estado = 'finalizada';
            }
            $historialService->registrarLote($asignaciones);

            // Las finalizadas desde el formulario antes de este reset también deben constar (idempotente).
            $historialService->registrarLote(
                AsignacionFct::with('ciclo')->where('curso_academico', $curso)->where('estado', 'finalizada')->get()
            );
            Log::info("ffe:reset-curso [{$curso}] Histórico de colocaciones generado.");
        });

        // Ficheros del disco, ya con la BD consistente.
        foreach ($evidencias as $ruta) {
            Storage::disk('private')->delete($ruta);
        }
        foreach ($ficherosDocs as [$disco, $ruta]) {
            Storage::disk($disco)->delete($ruta);
        }
        Log::info("ffe:reset-curso [{$curso}] Ficheros eliminados: evidencias {$evidencias->count()}, documentos {$ficherosDocs->count()}");

        $this->newLine();
        $this->line('<fg=green>Reset completado correctamente.</>');
        $this->table(
            ['Acción', 'Resultado'],
            [
                ['Asignaciones finalizadas',    $updated],
                ['Seguimientos eliminados',      $numSeguimientos],
                ['Tokens eliminados',            $numTokens],
                ['Documentos generados borrados',$docsGenerados->count()],
                ['Documentos firmados (intactos)',$docsFirmados],
            ]
        );

        Log::info("ffe:reset-curso [{$curso}] Reset completado.", [
            'asignaciones_finalizadas' => $updated,
            'seguimientos_eliminados'  => $numSeguimientos,
            'tokens_eliminados'        => $numTokens,
            'docs_generados_borrados'  => $docsGenerados->count(),
            'docs_firmados_conservados'=> $docsFirmados,
        ]);

        return self::SUCCESS;
    }
}
