{{-- ============================================================
     Sección de Documentos FCT — partial para asignaciones.show
     ============================================================ --}}
<div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Documentos</h3>

    {{-- GENERAR DOCUMENTOS --}}
    @can('gestionarDocumento')
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <p class="text-sm font-medium text-gray-600 mb-3">Generar documento:</p>
        <div class="flex flex-wrap gap-2">
            @foreach([
                'plan_formativo'    => 'Plan de Formación (Anexo 6)',
                'ficha_seguimiento' => 'Ficha de Seguimiento (Anexo 8)',
                'informe_final'     => 'Informe de Valoración Final (Anexo 9)',
            ] as $tipo => $etiqueta)
            @if($tipo === 'plan_formativo' && Route::has('documentos.plan-formativo.form'))
            <a href="{{ route('documentos.plan-formativo.form', $asignacion) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white rounded-lg transition-colors bg-primary-600 hover:bg-primary-700">
                {{ $etiqueta }}
            </a>
            @elseif(Route::has('documentos.generar'))
            <form method="POST" action="{{ route('documentos.generar', [$asignacion, $tipo]) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded
                               bg-indigo-600 text-white hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    {{ $etiqueta }}
                </button>
            </form>
            @endif
            @endforeach
        </div>
    </div>
    @endcan

    {{-- LISTA DE DOCUMENTOS EXISTENTES --}}
    @php
        $documentos = $asignacion->documentos()->orderByDesc('created_at')->get();
    @endphp

    @if($documentos->isNotEmpty())
    <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre de archivo</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Purgar</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($documentos as $doc)
                <tr class="{{ $doc->esFirmado() ? 'bg-green-50' : '' }}">
                    <td class="px-4 py-2 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $doc->esFirmado() ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $doc->etiqueta() }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-gray-700 font-mono text-xs">{{ $doc->nombre_archivo }}</td>
                    <td class="px-4 py-2 text-gray-500 whitespace-nowrap">
                        {{ $doc->esFirmado()
                            ? optional($doc->subido_at)->format('d/m/Y H:i')
                            : optional($doc->generado_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-2 text-gray-500 whitespace-nowrap text-xs">
                        {{ $doc->purgar_after ? $doc->purgar_after->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap flex gap-2">
                        @can('gestionarDocumento')
                        @if(Route::has('documentos.descargar'))
                        <a href="{{ route('documentos.descargar', $doc) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                            Descargar
                        </a>
                        @endif
                        @endcan

                        @can('eliminarDocumento')
                        @if(Route::has('documentos.destroy'))
                        <form method="POST" action="{{ route('documentos.destroy', $doc) }}"
                              onsubmit="return confirm('¿Eliminar este documento? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 hover:text-red-800 text-xs font-medium">
                                Eliminar
                            </button>
                        </form>
                        @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-sm text-gray-400 italic mb-4">No hay documentos generados para esta asignación.</p>
    @endif

    {{-- SUBIR PDF FIRMADO --}}
    @can('gestionarDocumento')
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm font-medium text-gray-600 mb-3">Subir documento firmado (PDF):</p>
        @if(Route::has('documentos.subir-firmado'))
        <form method="POST"
              action="{{ route('documentos.subir-firmado', $asignacion) }}"
              enctype="multipart/form-data"
              class="flex items-center gap-3">
            @csrf
            <input type="file"
                   name="pdf_firmado"
                   accept="application/pdf"
                   required
                   class="block text-sm text-gray-700 file:mr-3 file:py-1.5 file:px-3
                          file:rounded file:border-0 file:text-sm file:font-medium
                          file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
            <button type="submit"
                    class="px-3 py-1.5 text-sm font-medium rounded bg-green-600 text-white hover:bg-green-700 transition">
                Subir firmado
            </button>
        </form>
        @if($errors->has('pdf_firmado'))
            <p class="text-red-600 text-xs mt-1">{{ $errors->first('pdf_firmado') }}</p>
        @endif
        @endif
    </div>
    @endcan
</div>
