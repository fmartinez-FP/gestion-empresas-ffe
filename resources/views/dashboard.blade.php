@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    
    <!-- Cabecera -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">
                @if($dashboardTipo === 'admin')
                    Resumen general del sistema · Curso {{ $cursoActual }}
                @elseif($dashboardTipo === 'responsable_ffe')
                    Gestión global de empresas · Curso {{ $cursoActual }}
                @elseif($dashboardTipo === 'responsable_ciclo')
                    Empresas de tus ciclos · Curso {{ $cursoActual }}
                @else
                    Tus empresas y asignaciones · Curso {{ $cursoActual }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if(isset($todosLosCiclos) && $todosLosCiclos->count() > 0 && $dashboardTipo !== 'profesor')
            <form method="GET" class="flex items-center gap-2">
                <select name="ciclo" onchange="this.form.submit()" 
                        class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 text-sm">
                    <option value="">
                        @if($dashboardTipo === 'responsable_ciclo')
                            Todos mis ciclos
                        @else
                            Todos los ciclos
                        @endif
                    </option>
                    @foreach($todosLosCiclos as $ciclo)
                    <option value="{{ $ciclo->id }}" {{ $cicloFiltro == $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->codigo }} - {{ $ciclo->nombre }}
                    </option>
                    @endforeach
                </select>
            </form>
            @endif
            <a href="{{ route('empresas.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Empresa
            </a>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total empresas -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        {{ $dashboardTipo === 'profesor' ? 'Mis Empresas' : 'Total Empresas' }}
                    </p>
                    <p class="text-3xl font-bold text-slate-800 dark:text-white mt-1">{{ $stats['total_empresas'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Convenios activos -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Convenios Activos</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['convenios_activos'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Por caducar -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Próximos a Caducar</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['convenios_por_caducar'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Caducados -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Convenios Caducados</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['convenios_caducados'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget seguimientos pendientes -->
    @if(isset($seguimientosPendientes) && $seguimientosPendientes->count() > 0)
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Seguimientos Pendientes
            </h2>
            <a href="{{ route('seguimientos.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                Ver todos →
            </a>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($seguimientosPendientes as $contacto)
            @php
                $dias = now()->startOfDay()->diffInDays($contacto->fecha_seguimiento->startOfDay(), false);
                $colorDias = match(true) {
                    $dias <= 0 => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                    $dias <= 2 => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                    $dias <= 7 => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                    default    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                };
                $labelDias = match(true) {
                    $dias < 0  => abs($dias) . 'd vencido',
                    $dias === 0 => 'Hoy',
                    $dias === 1 => 'Mañana',
                    default    => $dias . ' días',
                };
            @endphp
            <a href="{{ route('empresas.show', $contacto->empresa) }}" class="flex items-center justify-between p-4 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <div>
                    <p class="font-medium text-slate-800 dark:text-white">{{ $contacto->empresa->nombre }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $contacto->tipo_etiqueta }}</p>
                </div>
                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold {{ $colorDias }}">
                    {{ $labelDias }}
                </span>
            </a>
            @endforeach
        </div>
    </div>
    @endif


    <!-- Resumen del curso actual -->
    @if($colocacionesCurso && $colocacionesCurso->num_envios > 0)
    <div class="bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl p-6 text-white shadow-lg shadow-primary-500/20">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            @if($dashboardTipo === 'profesor')
                Mis Asignaciones · Curso {{ $cursoActual }}
            @elseif($dashboardTipo === 'responsable_ciclo')
                Asignaciones de mis Ciclos · Curso {{ $cursoActual }}
            @else
                Resumen Curso {{ $cursoActual }}
            @endif
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <p class="text-primary-200 text-sm">Alumnos enviados a empresas</p>
                <p class="text-3xl font-bold">{{ number_format($colocacionesCurso->total_alumnos ?? 0) }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-sm">Horas de prácticas</p>
                <p class="text-3xl font-bold">{{ number_format($colocacionesCurso->total_horas ?? 0) }}</p>
            </div>
            <div>
                <p class="text-primary-200 text-sm">Envíos registrados</p>
                <p class="text-3xl font-bold">{{ number_format($colocacionesCurso->num_envios ?? 0) }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Convenios urgentes / Mis empresas urgentes -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    @if($dashboardTipo === 'profesor')
                        Mis Convenios Próximos a Caducar
                    @else
                        Convenios Próximos a Caducar
                    @endif
                </h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($empresasUrgentes as $empresa)
                <a href="{{ route('empresas.show', $empresa) }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white">{{ $empresa->nombre }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $empresa->creador->nombre ?? 'Sin asignar' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="status-badge status-yellow bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                {{ $empresa->dias_hasta_vencimiento }} días
                            </span>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                Vence: {{ $empresa->fecha_vencimiento->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400">No hay convenios próximos a caducar</p>
                </div>
                @endforelse
            </div>
            @if($stats['convenios_por_caducar'] > 5)
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('empresas.index', ['estado' => 'por_caducar']) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Ver todos ({{ $stats['convenios_por_caducar'] }}) →
                </a>
            </div>
            @endif
        </div>

        <!-- Últimas empresas / Mis asignaciones recientes -->
        @if(isset($misAsignaciones) && ($dashboardTipo === 'profesor' || $dashboardTipo === 'responsable_ciclo'))
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Mis Últimas Asignaciones
                </h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($misAsignaciones as $asignacion)
                <a href="{{ route('empresas.show', $asignacion->empresa) }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white">{{ $asignacion->empresa->nombre }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    {{ $asignacion->ciclo->codigo }}
                                </span>
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $asignacion->numero_curso }}º curso</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold text-primary-600">{{ $asignacion->num_alumnos }}</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400"> alum.</span>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                {{ $asignacion->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400">No has registrado asignaciones aún</p>
                </div>
                @endforelse
            </div>
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('colocaciones.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Ver histórico completo →
                </a>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Últimas Empresas Añadidas
                </h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($ultimasEmpresas as $empresa)
                <a href="{{ route('empresas.show', $empresa) }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-white">{{ $empresa->nombre }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                @foreach($empresa->ciclos->take(3) as $ciclo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    {{ $ciclo->codigo }}
                                </span>
                                @endforeach
                                @if($empresa->ciclos->count() > 3)
                                <span class="text-xs text-slate-400">+{{ $empresa->ciclos->count() - 3 }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            @php
                                $colorClass = match($empresa->estado_convenio) {
                                    'activo' => 'status-green bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                    'por_caducar' => 'status-yellow bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                                    'caducado' => 'status-red bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                    default => 'status-gray bg-slate-50 dark:bg-slate-600 text-slate-600 dark:text-slate-300',
                                };
                            @endphp
                            <span class="status-badge {{ $colorClass }}">
                                {{ $empresa->estado_etiqueta }}
                            </span>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                {{ $empresa->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400">No hay empresas registradas</p>
                    <a href="{{ route('empresas.create') }}" class="inline-block mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Añadir primera empresa →
                    </a>
                </div>
                @endforelse
            </div>
            @if($stats['total_empresas'] > 5)
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('empresas.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Ver todas las empresas →
                </a>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Ciclos formativos (solo para admin, responsable_ffe y responsable_ciclo) -->
    @if($ciclos->count() > 0)
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">
            @if($dashboardTipo === 'responsable_ciclo')
                Mis Ciclos Formativos
            @else
                Ciclos Formativos del Centro
            @endif
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($ciclos as $ciclo)
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm
                        @if($ciclo->nivel === 'basica') bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400
                        @elseif($ciclo->nivel === 'media') bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400
                        @else bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400
                        @endif">
                        {{ $ciclo->codigo }}
                    </div>
                    <div>
                        <p class="font-medium text-slate-800 dark:text-white text-sm">{{ $ciclo->nombre }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $ciclo->nombre_nivel }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
