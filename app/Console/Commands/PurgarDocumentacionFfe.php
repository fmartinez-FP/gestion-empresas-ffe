<?php

namespace App\Console\Commands;

use App\Services\GeneradorPdfFfeService;
use Illuminate\Console\Command;

class PurgarDocumentacionFfe extends Command
{
    protected $signature   = 'ffe:purgar-documentacion';
    protected $description = 'Purga documentos FFE caducados (firmados con purgar_after <= hoy) y limpia temporales de más de 24h';

    public function __construct(
        private readonly GeneradorPdfFfeService $generador
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('=== Purga de Documentación FFE ===');
        $this->newLine();

        // --- Temporales (generados, >24h) ---
        $this->line('Buscando documentos generados con más de 24h de antigüedad...');
        $temporales = $this->generador->limpiarTemporales();
        $this->info("  → {$temporales} documento(s) temporal(es) eliminado(s).");
        $this->newLine();

        // --- Firmados caducados ---
        $this->line('Buscando documentos firmados con purgar_after <= hoy...');

        // Previsualizar qué se va a eliminar
        $candidatos = \App\Models\DocumentoFct::where('tipo', \App\Models\DocumentoFct::TIPO_FIRMADO)
            ->whereNotNull('purgar_after')
            ->whereDate('purgar_after', '<=', today())
            ->with('asignacion.alumno')
            ->get();

        if ($candidatos->isEmpty()) {
            $this->info('  → No hay documentos firmados que purgar.');
            return self::SUCCESS;
        }

        $this->warn("  Se eliminarán {$candidatos->count()} documento(s) firmado(s):");
        foreach ($candidatos as $doc) {
            $alumno = optional(optional($doc->asignacion)->alumno);
            $this->line("    - [{$doc->id}] {$doc->nombre_archivo} (purgar_after: {$doc->purgar_after})");
        }

        $this->newLine();

        if (! $this->confirm('¿Confirmar la eliminación de estos documentos firmados? Esta acción es irreversible.')) {
            $this->warn('Operación cancelada.');
            return self::SUCCESS;
        }

        $firmados = $this->generador->purgarFirmados();
        $this->info("  → {$firmados} documento(s) firmado(s) purgado(s).");

        return self::SUCCESS;
    }
}
