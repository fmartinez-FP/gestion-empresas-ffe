<?php

namespace App\Services;

use App\Contracts\PdfGeneratorInterface;
use App\Models\AsignacionFct;
use App\Models\DocumentoFct;
use Illuminate\Support\Facades\Storage;

class GeneradorPdfFfeService
{
    public function __construct(
        private readonly PdfGeneratorInterface $pdf
    ) {}

    // =========================================================================
    // API PÚBLICA
    // =========================================================================

    public function generarPlanFormativo(AsignacionFct $asignacion, array $datosPlan = []): DocumentoFct
    {
        return $this->generar($asignacion, DocumentoFct::TIPO_PLAN_FORMATIVO, 'pdf.plan_formativo', $datosPlan);
    }

    public function generarFichaSeguimiento(AsignacionFct $asignacion): DocumentoFct
    {
        return $this->generar($asignacion, DocumentoFct::TIPO_FICHA_SEGUIMIENTO, 'pdf.ficha_seguimiento');
    }

    public function generarInformeFinal(AsignacionFct $asignacion): DocumentoFct
    {
        return $this->generar($asignacion, DocumentoFct::TIPO_INFORME_FINAL, 'pdf.informe_final');
    }

    /**
     * Elimina documentos generados (no firmados) con más de 24h de antigüedad.
     * Llamado por el comando PurgarDocumentacionFfe.
     */
    public function limpiarTemporales(): int
    {
        $candidatos = DocumentoFct::whereIn('tipo', DocumentoFct::TIPOS_GENERABLES)
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        $eliminados = 0;
        foreach ($candidatos as $doc) {
            $this->eliminarArchivo($doc);
            $doc->delete();
            $eliminados++;
        }

        return $eliminados;
    }

    /**
     * Elimina documentos firmados con purgar_after <= hoy.
     */
    public function purgarFirmados(): int
    {
        $candidatos = DocumentoFct::where('tipo', DocumentoFct::TIPO_FIRMADO)
            ->whereNotNull('purgar_after')
            ->whereDate('purgar_after', '<=', today())
            ->get();

        $eliminados = 0;
        foreach ($candidatos as $doc) {
            $this->eliminarArchivo($doc);
            $doc->delete();
            $eliminados++;
        }

        return $eliminados;
    }

    public function eliminarArchivo(DocumentoFct $doc): void
    {
        if ($doc->ruta_disco && Storage::disk($doc->disco)->exists($doc->ruta_disco)) {
            Storage::disk($doc->disco)->delete($doc->ruta_disco);
        }
    }

    // =========================================================================
    // PRIVADOS
    // =========================================================================

    private function generar(AsignacionFct $asignacion, string $tipo, string $vista, array $extraData = []): DocumentoFct
    {
        // Cargar relaciones necesarias para las plantillas
        $asignacion->loadMissing([
            'alumno',
            'empresa',
            'sede',
            'tutorIes',
            'tutorEmpresa',
            'ciclo',
            'resultadosAprendizaje.modulo',
            'criteriosEvaluacion.resultadoAprendizaje.modulo',
        ]);

        // Eliminar documento generado previo del mismo tipo (no firmados)
        $previo = $asignacion->documentos()
            ->where('tipo', $tipo)
            ->first();

        if ($previo) {
            $this->eliminarArchivo($previo);
            $previo->delete();
        }

        // Generar nombre y ruta
        $nombreArchivo = DocumentoFct::generarNombreArchivo($tipo, $asignacion);
        $rutaDisco     = "fct/{$asignacion->id}/{$nombreArchivo}";
        $disco         = 'private';

        // Asegurarse de que el directorio existe
        Storage::disk($disco)->makeDirectory("fct/{$asignacion->id}");

        // Generar PDF via interfaz (swappable en tests)
        $this->pdf->generar(
            $vista,
            array_merge(['asignacion' => $asignacion, 'centro' => config('centro')], $extraData),
            $rutaDisco,
            $disco
        );

        // Persistir registro
        return DocumentoFct::create([
            'asignacion_id'  => $asignacion->id,
            'tipo'           => $tipo,
            'nombre_archivo' => $nombreArchivo,
            'ruta_disco'     => $rutaDisco,
            'disco'          => $disco,
            'generado_at'    => now(),
        ]);
    }
}
