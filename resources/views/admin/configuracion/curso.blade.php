@extends('layouts.app')

@section('title', 'Curso Activo')

@section('content')
<div class="space-y-6">

    @include('admin.partials.nav')

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Gestión del Curso Académico</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Controla en qué curso se registran las colocaciones</p>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        
        <!-- Curso Activo -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-xl">
                    <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Curso Activo Actual</p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white">{{ $cursoActivo }}</p>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium">Todas las colocaciones nuevas se registran en este curso.</p>
                        <p class="mt-1 text-blue-600 dark:text-blue-300">Los profesores solo pueden registrar colocaciones en el curso activo.</p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del curso activo -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $estadisticas['colocaciones'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Registros</p>
                </div>
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $estadisticas['alumnos'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Alumnos</p>
                </div>
                <div class="text-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($estadisticas['horas']) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Horas</p>
                </div>
            </div>
        </div>

        <!-- Avanzar Curso -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Avanzar al Siguiente Curso</h3>
            
            <div class="flex items-center gap-4 mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                <div class="flex items-center gap-2 text-green-700 dark:text-green-300">
                    <span class="font-semibold">{{ $cursoActivo }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <span class="font-bold text-green-800 dark:text-green-200">{{ $cursoSiguiente }}</span>
                </div>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                Al avanzar de curso, las nuevas colocaciones se registrarán en <strong>{{ $cursoSiguiente }}</strong>. 
                Los datos del curso anterior permanecerán en el histórico.
            </p>

            <form method="POST" action="{{ route('admin.configuracion.avanzar-curso') }}" 
                  onsubmit="return confirm('¿Estás seguro de avanzar al curso {{ $cursoSiguiente }}?\n\nEsta acción cambiará el curso activo para todos los usuarios.')">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Avanzar a {{ $cursoSiguiente }}
                </button>
            </form>
        </div>
    </div>

    <!-- Selección manual de curso -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Seleccionar Curso Manualmente</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
            Usa esta opción solo si necesitas corregir el curso activo o volver a un curso anterior.
        </p>
        
        <form method="POST" action="{{ route('admin.configuracion.cambiar-curso') }}" class="flex items-end gap-4">
            @csrf
            <div class="flex-1">
                <label for="curso" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Curso académico</label>
                <select name="curso" id="curso" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                    @foreach($cursosDisponibles as $valor => $etiqueta)
                        <option value="{{ $valor }}" {{ $valor === $cursoActivo ? 'selected' : '' }}>
                            {{ $etiqueta }} {{ $valor === $cursoActivo ? '(actual)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-slate-600 text-white rounded-xl font-medium hover:bg-slate-700 transition-colors">
                Cambiar
            </button>
        </form>
    </div>

</div>
@endsection
