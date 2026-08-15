<?php

namespace App\Http\Controllers;

use App\Models\AsignacionFct;
use App\Models\DocumentoFct;
use App\Services\GeneradorPdfFfeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoFctController extends Controller
{
    public function __construct(
        private readonly GeneradorPdfFfeService $generador
    ) {}

    /**
     * Genera un PDF de tipo generado, lo persiste y redirige a show de la asignación.
     */
    public function generar(Request $request, AsignacionFct $asignacion, string $tipo): \Illuminate\Http\RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarDocumento', $asignacion), 403);
        abort_unless(in_array($tipo, DocumentoFct::TIPOS_GENERABLES), 422);

        $documento = match ($tipo) {
            DocumentoFct::TIPO_PLAN_FORMATIVO    => $this->generador->generarPlanFormativo($asignacion),
            DocumentoFct::TIPO_FICHA_SEGUIMIENTO => $this->generador->generarFichaSeguimiento($asignacion),
            DocumentoFct::TIPO_INFORME_FINAL     => $this->generador->generarInformeFinal($asignacion),
        };

        return redirect()
            ->route('asignaciones.show', $asignacion)
            ->with('success', $documento->etiqueta() . ' generado correctamente.');
    }

    /**
     * Descarga un documento desde el disco private vía stream.
     */
    public function descargar(DocumentoFct $documento): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(auth()->user()->can('gestionarDocumento', $documento->asignacion), 403);
        abort_unless(Storage::disk($documento->disco)->exists($documento->ruta_disco), 404);

        return Storage::disk($documento->disco)->download(
            $documento->ruta_disco,
            $documento->nombre_archivo
        );
    }

    /**
     * Sube un PDF firmado externo. purgar_after = fecha_fin + 5 años.
     */
    public function subirFirmado(Request $request, AsignacionFct $asignacion): \Illuminate\Http\RedirectResponse
    {
        abort_unless(auth()->user()->can('gestionarDocumento', $asignacion), 403);

        $request->validate([
            'pdf_firmado' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $asignacion->loadMissing(['alumno', 'empresa']);

        $nombreArchivo = DocumentoFct::generarNombreArchivo('firmado', $asignacion);
        $rutaDisco     = "fct/{$asignacion->id}/{$nombreArchivo}";
        $disco         = 'private';

        Storage::disk($disco)->makeDirectory("fct/{$asignacion->id}");
        Storage::disk($disco)->putFileAs(
            "fct/{$asignacion->id}",
            $request->file('pdf_firmado'),
            $nombreArchivo
        );

        // purgar_after: fecha_fin de la asignación + 5 años (o today + 5 años si no hay fecha_fin)
        $base         = $asignacion->fecha_fin ?? today();
        $purgarAfter  = $base->copy()->addYears(5);

        DocumentoFct::create([
            'asignacion_id'  => $asignacion->id,
            'tipo'           => DocumentoFct::TIPO_FIRMADO,
            'nombre_archivo' => $nombreArchivo,
            'ruta_disco'     => $rutaDisco,
            'disco'          => $disco,
            'subido_at'      => now(),
            'purgar_after'   => $purgarAfter,
        ]);

        return redirect()
            ->route('asignaciones.show', $asignacion)
            ->with('success', 'Documento firmado subido correctamente.');
    }

    /**
     * Elimina un documento (registro + archivo en disco).
     */
    public function destroy(DocumentoFct $documento): \Illuminate\Http\RedirectResponse
    {
        abort_unless(auth()->user()->can('eliminarDocumento'), 403);

        $asignacionId = $documento->asignacion_id;
        $this->generador->eliminarArchivo($documento);
        $documento->delete();

        return redirect()
            ->route('asignaciones.show', $asignacionId)
            ->with('success', 'Documento eliminado correctamente.');
    }
}
