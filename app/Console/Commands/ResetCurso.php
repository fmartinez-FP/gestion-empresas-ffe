<?php

namespace App\Console\Commands;

use App\Models\AsignacionFct;
use App\Models\SeguimientoDiario;
use App\Models\TokenTutorEmpresa;
use App\Models\DocumentoFct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResetCurso extends Command
{
    protected $signature   = 'ffe:reset-curso {curso_academico : Curso a resetear, formato YYYY-YYYY}';
    protected $description = 'Purga datos FFE de un curso: finaliza asignaciones, elimina seguimientos, tokens y documentos generados. Conserva firmados, alumnos y empresas.';

    public function handle(): int
    {
        $curso = $this->argument('curso_academico');

        if (!preg_match('/^\d{4}-\d{4}$/', $curso)) {
            $this->error("Formato incorrecto. Usa YYYY-YYYY (ej: 2024-2025).");
            return self::FAILURE;
        }

        // --- Recuento previo para resumen ---
        $asignaciones = AsignacionFct::where('curso_academico', $curso)
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

        // 1. Eliminar evidencias de seguimiento del disco
        $this->line('  [1/5] Eliminando evidencias de seguimiento...');
        $seguimientos = SeguimientoDiario::whereIn('asignacion_id', $asignacionIds)
            ->whereNotNull('evidencia_path')
            ->get();
        foreach ($seguimientos as $seg) {
            if (Storage::disk('private')->exists($seg->evidencia_path)) {
                Storage::disk('private')->delete($seg->evidencia_path);
            }
        }
        Log::info("ffe:reset-curso [{$curso}] Evidencias eliminadas: {$seguimientos->count()}");

        // 2. Eliminar seguimientos
        $this->line('  [2/5] Eliminando seguimientos diarios...');
        $deleted = SeguimientoDiario::whereIn('asignacion_id', $asignacionIds)->delete();
        Log::info("ffe:reset-curso [{$curso}] Seguimientos eliminados: {$deleted}");

        // 3. Eliminar tokens tutor empresa
        $this->line('  [3/5] Eliminando tokens tutor empresa...');
        $deleted = TokenTutorEmpresa::whereIn('asignacion_id', $asignacionIds)->delete();
        Log::info("ffe:reset-curso [{$curso}] Tokens eliminados: {$deleted}");

        // 4. Eliminar archivos y registros de documentos generados (no firmados)
        $this->line('  [4/5] Eliminando documentos generados...');
        foreach ($docsGenerados as $doc) {
            if (Storage::disk($doc->disco ?? 'private')->exists($doc->ruta_disco)) {
                Storage::disk($doc->disco ?? 'private')->delete($doc->ruta_disco);
            }
            $doc->delete();
        }
        Log::info("ffe:reset-curso [{$curso}] Documentos generados eliminados: {$docsGenerados->count()}");

        // 5. Finalizar asignaciones
        $this->line('  [5/5] Finalizando asignaciones...');
        $updated = AsignacionFct::where('curso_academico', $curso)
            ->where('estado', 'activa')
            ->update(['estado' => 'finalizada']);
        Log::info("ffe:reset-curso [{$curso}] Asignaciones finalizadas: {$updated}");

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
