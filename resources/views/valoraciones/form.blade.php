@extends('layouts.app')

@section('title', $valoracion ? 'Editar Valoración' : 'Valorar Empresa')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Cabecera -->
    <div class="flex items-center gap-4">
        <a href="{{ route('empresas.show', $empresa) }}" 
           class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                {{ $valoracion ? 'Editar Valoración' : 'Valorar Empresa' }}
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $empresa->nombre }} · Curso {{ $cursoActivo }}</p>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('valoraciones.store', $empresa) }}" class="space-y-6" x-data="valoracionForm()">
        @csrf

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Criterios de Valoración
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Puntúa cada criterio del 1 al 5</p>

            <div class="space-y-6">
                @foreach($criterios as $campo => $criterio)
                <div class="border-b border-slate-100 dark:border-slate-700 pb-6 last:border-0 last:pb-0">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $criterio['icono'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <label class="block font-medium text-slate-800 dark:text-white">
                                {{ $criterio['nombre'] }} <span class="text-red-500">*</span>
                            </label>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">{{ $criterio['descripcion'] }}</p>
                            
                            <!-- Estrellas -->
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                <button type="button" 
                                        @click="ratings['{{ $campo }}'] = {{ $i }}"
                                        class="p-1 transition-transform hover:scale-110 focus:outline-none">
                                    <svg class="w-8 h-8 transition-colors" 
                                         :class="ratings['{{ $campo }}'] >= {{ $i }} ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300 dark:text-slate-600'"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                </button>
                                @endfor
                                <input type="hidden" name="{{ $campo }}" :value="ratings['{{ $campo }}']">
                            </div>
                            @error($campo)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Observaciones -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Observaciones
            </h2>
            <textarea name="observaciones" rows="4"
                      placeholder="Comentarios adicionales sobre la empresa, recomendaciones, aspectos a destacar..."
                      class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('observaciones', $valoracion->observaciones ?? '') }}</textarea>
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ route('empresas.show', $empresa) }}" 
               class="px-6 py-2.5 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-center">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20">
                Guardar Valoración
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function valoracionForm() {
    return {
        ratings: {
            trato_alumno: {{ old('trato_alumno', $valoracion->trato_alumno ?? 0) }},
            calidad_formacion: {{ old('calidad_formacion', $valoracion->calidad_formacion ?? 0) }},
            seguimiento_tutor: {{ old('seguimiento_tutor', $valoracion->seguimiento_tutor ?? 0) }},
            comunicacion_ies: {{ old('comunicacion_ies', $valoracion->comunicacion_ies ?? 0) }},
            posibilidad_contratacion: {{ old('posibilidad_contratacion', $valoracion->posibilidad_contratacion ?? 0) }},
        }
    }
}
</script>
@endpush
@endsection
