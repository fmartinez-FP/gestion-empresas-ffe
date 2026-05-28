@extends('layouts.app')

@section('title', 'Histórico de Asignaciones')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Histórico de Asignaciones</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Registro de alumnos enviados a prácticas · Últimos 5 años</p>
    </div>

    <!-- Resumen del curso seleccionado -->
    @php
        $totalAlumnos = 0;
        $totalHoras = 0;
        $totalEnvios = 0;
        if(!empty($estadisticas) && isset($estadisticas[$cursoSeleccionado])) {
            foreach($estadisticas[$cursoSeleccionado] as $stat) {
                $totalAlumnos += $stat['total_alumnos'] ?? 0;
                $totalHoras += $stat['total_horas'] ?? 0;
                $totalEnvios += $stat['num_envios'] ?? 0;
            }
        }
    @endphp
    
    @if($totalEnvios > 0)
    <div class="bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl p-6 text-white">
        <h2 class="text-lg font-semibold mb-4">Resumen {{ $cursoSeleccionado }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-primary-200 text-sm">Total alumnos</p>
                <p class="text-3xl font-bold">{{ number_format($totalAlumnos) }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-sm">Total horas</p>
                <p class="text-3xl font-bold">{{ number_format($totalHoras) }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-sm">Envíos</p>
                <p class="text-3xl font-bold">{{ number_format($totalEnvios) }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-sm">Media h/alumno</p>
                <p class="text-3xl font-bold">{{ $totalAlumnos > 0 ? number_format($totalHoras / $totalAlumnos, 0) : 0 }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <form method="GET" class="flex flex-col lg:flex-row gap-4">
            <select name="curso" class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Todos los cursos</option>
                @foreach($cursosDisponibles as $curso)
                <option value="{{ $curso }}" {{ request('curso', $cursoActivo) == $curso ? 'selected' : '' }}>{{ $curso }}</option>
                @endforeach
            </select>
            <select name="ciclo" class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                <option value="">Todos los ciclos</option>
                @foreach($ciclos as $ciclo)
                <option value="{{ $ciclo->id }}" {{ request('ciclo') == $ciclo->id ? 'selected' : '' }}>{{ $ciclo->codigo }} - {{ $ciclo->nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600">Filtrar</button>
            @if(request()->hasAny(['curso', 'ciclo']))
            <a href="{{ route('colocaciones.index') }}" class="px-4 py-2.5 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white text-center">Limpiar</a>
            @endif
        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        @if($colocaciones->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Curso Acad.</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Empresa</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Ciclo</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Nº</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Alumnos</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Horas</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Registrado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($colocaciones as $colocacion)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700">
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-800 dark:text-white">{{ $colocacion->curso_academico }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('empresas.show', $colocacion->empresa) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                {{ $colocacion->empresa->nombre }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $cicloColor = match($colocacion->ciclo->nivel) {
                                    'basica' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
                                    'media' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                    'superior' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                                    default => 'bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-300',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $cicloColor }}">
                                {{ $colocacion->ciclo->codigo }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-300 text-xs font-medium">
                                {{ $colocacion->numero_curso }}º
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-white">{{ $colocacion->num_alumnos }}</td>
                        <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">{{ number_format($colocacion->num_horas) }}h</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-sm hidden lg:table-cell">
                            {{ $colocacion->registradoPor->nombre ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($colocaciones->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $colocaciones->links() }}
        </div>
        @endif
        
        @else
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-white mb-1">No hay asignaciones registradas</h3>
            <p class="text-slate-500 dark:text-slate-400">
                @if(request()->hasAny(['curso', 'ciclo']))
                    No se encontraron resultados con los filtros aplicados
                @else
                    Las colocaciones se registran desde el detalle de cada empresa
                @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
