@extends('layouts.app')

@section('title', 'Informes Estadísticos')

@section('content')
<div class="space-y-6">
    
    <!-- Cabecera -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Informes Estadísticos</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Análisis de asignaciones por ciclo y curso académico</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <select name="curso" onchange="this.form.submit()" class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                    @foreach($cursosDisponibles as $curso)
                    <option value="{{ $curso }}" {{ $cursoSeleccionado == $curso ? 'selected' : '' }}>{{ $curso }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('export.colocaciones.excel', ['curso' => $cursoSeleccionado]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel
            </a>
            <a href="{{ route('export.colocaciones.pdf', ['curso' => $cursoSeleccionado]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                PDF
            </a>
        </div>
    </div>

    <!-- Resumen general -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-primary-600 to-primary-700 rounded-2xl p-6 text-white">
            <p class="text-primary-200 text-sm">Total Alumnos</p>
            <p class="text-4xl font-bold mt-1">{{ number_format($totales['alumnos']) }}</p>
            <p class="text-primary-200 text-sm mt-2">Curso {{ $cursoSeleccionado }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6 text-white">
            <p class="text-green-200 text-sm">Total Horas</p>
            <p class="text-4xl font-bold mt-1">{{ number_format($totales['horas']) }}</p>
            <p class="text-green-200 text-sm mt-2">Horas de prácticas</p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white">
            <p class="text-amber-100 text-sm">Envíos Registrados</p>
            <p class="text-4xl font-bold mt-1">{{ number_format($totales['envios']) }}</p>
            <p class="text-amber-100 text-sm mt-2">Colocaciones</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Tabla por ciclo -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Desglose por Ciclo Formativo</h2>
            </div>
            <div class="overflow-x-auto">
		<table class="w-full">
    <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Ciclo</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">1º Alum.</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">2º Alum.</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Total</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        @foreach($estadisticasCiclo as $stat)
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700">
            <td class="px-6 py-3">
                @php
                    $cicloColor = match($stat['ciclo']->nivel) {
                        'basica' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
                        'media' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                        'superior' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                        default => 'bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-300',
                    };
                @endphp
                <span class="inline-flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $cicloColor }}">{{ $stat['ciclo']->codigo }}</span>
                    <span class="text-sm text-slate-600 dark:text-slate-400 hidden sm:inline">{{ $stat['ciclo']->nombre }}</span>
                </span>
            </td>
            <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">{{ $stat['primero']->alumnos }}</td>
            <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-200">{{ $stat['segundo']->alumnos }}</td>
            <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">{{ $stat['total_alumnos'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="bg-slate-100 dark:bg-slate-700 border-t-2 border-slate-200 dark:border-slate-600">
        <tr>
            <td class="px-6 py-3 font-semibold text-slate-800 dark:text-white">TOTAL</td>
            <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">{{ array_sum(array_map(fn($s) => $s['primero']->alumnos, $estadisticasCiclo)) }}</td>
            <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">{{ array_sum(array_map(fn($s) => $s['segundo']->alumnos, $estadisticasCiclo)) }}</td>
            <td class="px-4 py-3 text-right font-bold text-primary-600">{{ $totales['alumnos'] }}</td>
        </tr>
    </tfoot>
</table>
            </div>
        </div>

        <!-- Empresas más activas -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Empresas Más Activas</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Top 10 por alumnos este curso</p>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($empresasActivas as $index => $colocacion)
                <a href="{{ route('empresas.show', $colocacion->empresa) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-white truncate" style="max-width: 150px;">{{ $colocacion->empresa->nombre }}</span>
                    </div>
                    <span class="text-sm font-semibold text-primary-600">{{ $colocacion->total_alumnos }} alum.</span>
                </a>
                @empty
                <div class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No hay datos para este curso</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Evolución histórica -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-6">Evolución Últimos 5 Años</h2>
        <div class="overflow-x-auto">
	     <table class="w-full">
    <thead>
        <tr class="border-b border-slate-200 dark:border-slate-700">
            <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-400">Curso</th>
            @foreach($evolucion as $curso => $datos)
	    <th class="px-4 py-3 text-center text-sm font-semibold {{ $curso === $cursoSeleccionado ? 'text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30' : 'text-slate-600 dark:text-slate-400' }}">{{ $curso }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr class="border-b border-slate-100 dark:border-slate-700">
            <td class="px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">Alumnos 1º</td>
            @foreach($evolucion as $curso => $datos)
	    <td class="px-4 py-3 text-center {{ $curso === $cursoSeleccionado ? 'bg-primary-50 dark:bg-primary-900/30 font-bold text-primary-700 dark:text-primary-400' : 'text-slate-800 dark:text-slate-200' }}">{{ number_format($datos['alumnos_1']) }}</td>
            @endforeach
        </tr>
        <tr class="border-b border-slate-100 dark:border-slate-700">
            <td class="px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">Alumnos 2º</td>
            @foreach($evolucion as $curso => $datos)
	    <td class="px-4 py-3 text-center {{ $curso === $cursoSeleccionado ? 'bg-primary-50 dark:bg-primary-900/30 font-bold text-primary-700 dark:text-primary-400' : 'text-slate-800 dark:text-slate-200' }}">{{ number_format($datos['alumnos_2']) }}</td>
            @endforeach
        </tr>
        <tr class="border-b border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700">
            <td class="px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Total Alumnos</td>
            @foreach($evolucion as $curso => $datos)
	    <td class="px-4 py-3 text-center font-bold {{ $curso === $cursoSeleccionado ? 'bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-400' : 'text-slate-800 dark:text-slate-200' }}">{{ number_format($datos['alumnos']) }}</td>
            @endforeach
        </tr>
        <tr class="border-b border-slate-100 dark:border-slate-700">
            <td class="px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">Horas 1º</td>
            @foreach($evolucion as $curso => $datos)
	    <td class="px-4 py-3 text-center {{ $curso === $cursoSeleccionado ? 'bg-primary-50 dark:bg-primary-900/30 font-bold text-primary-700 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400' }}">{{ number_format($datos['horas_1']) }}</td>
            @endforeach
        </tr>
        <tr class="border-b border-slate-100 dark:border-slate-700">
            <td class="px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">Horas 2º</td>
            @foreach($evolucion as $curso => $datos)
	    <td class="px-4 py-3 text-center {{ $curso === $cursoSeleccionado ? 'bg-primary-50 dark:bg-primary-900/30 font-bold text-primary-700 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400' }}">{{ number_format($datos['horas_2']) }}</td>
            @endforeach
        </tr>
        <tr class="bg-slate-50 dark:bg-slate-700">
            <td class="px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Total Horas</td>
            @foreach($evolucion as $curso => $datos)
	    <td class="px-4 py-3 text-center font-bold {{ $curso === $cursoSeleccionado ? 'bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400' }}">{{ number_format($datos['horas']) }}</td>
            @endforeach
        </tr>
    </tbody>
</table>
        </div>
        
    <!-- Gráfico interactivo: evolución histórica -->
    <div class="mt-8">
        <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-4">Evolución de alumnos por curso académico</h3>
        <div style="position:relative;height:280px;">
            <canvas id="chartEvolucion"></canvas>
        </div>
    </div>
</div>

<!-- Gráficas: alumnos por ciclo + distribución por nivel -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Alumnos por ciclo — barras horizontales apiladas -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-1">
            Alumnos por Ciclo Formativo
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ $cursoSeleccionado }} · solo ciclos con actividad</p>
        <div style="position:relative;height:360px;">
            <canvas id="chartCiclos"></canvas>
        </div>
    </div>

    <!-- Distribución por nivel — donut -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-1">Distribución por Nivel</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ $cursoSeleccionado }}</p>
        <div class="flex-1" style="position:relative;min-height:260px;">
            <canvas id="chartNivel"></canvas>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@php
// Preparar datos de ciclos para JS (solo campos serializables)
$_cicloJs = [];
foreach ($estadisticasCiclo as $s) {
    $_cicloJs[] = [
        'codigo'    => $s['ciclo']->codigo,
        'nombre'    => $s['ciclo']->nombre,
        'nivel'     => $s['ciclo']->nivel,
        'alumnos_1' => (int) $s['primero']->alumnos,
        'alumnos_2' => (int) $s['segundo']->alumnos,
        'total'     => (int) $s['total_alumnos'],
    ];
}
// Totales por nivel
$_nivelJs = ['basica' => 0, 'media' => 0, 'superior' => 0];
foreach ($estadisticasCiclo as $s) {
    $niv = $s['ciclo']->nivel;
    if (array_key_exists($niv, $_nivelJs)) {
        $_nivelJs[$niv] += (int) $s['total_alumnos'];
    }
}
@endphp

(function () {
    'use strict';

    const evolucionRaw = @json($evolucion);
    const ciclosRaw    = @json($_cicloJs);
    const nivelRaw     = @json($_nivelJs);
    const cursoActual  = @json($cursoSeleccionado);

    Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
    Chart.defaults.color       = '#64748b';

    const C = {
        blue:   '#2563EB',
        blueL:  '#93C5FD',
        green:  '#16a34a',
        greenL: '#86EFAC',
        orange: '#ea580c',
        purple: '#9333ea',
        grid:   '#f1f5f9',
    };

    // ── 1. Evolución histórica ─────────────────────────────────
    (function () {
        const ctx = document.getElementById('chartEvolucion');
        if (!ctx) return;
        const cursos = Object.keys(evolucionRaw);
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: cursos,
                datasets: [
                    {
                        label: '1º Curso',
                        data: cursos.map(c => evolucionRaw[c].alumnos_1),
                        backgroundColor: cursos.map(c => c === cursoActual ? C.blue : C.blueL),
                        borderRadius: 4,
                        stack: 'alumnos',
                    },
                    {
                        label: '2º Curso',
                        data: cursos.map(c => evolucionRaw[c].alumnos_2),
                        backgroundColor: cursos.map(c => c === cursoActual ? C.green : C.greenL),
                        borderRadius: 4,
                        stack: 'alumnos',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            footer: items => 'Total: ' + items.reduce((s, i) => s + i.raw, 0),
                        },
                    },
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grid: { color: C.grid } },
                },
            },
        });
    })();

    // ── 2. Alumnos por ciclo ───────────────────────────────────
    (function () {
        const ctx = document.getElementById('chartCiclos');
        if (!ctx) return;
        const activos = ciclosRaw
            .filter(d => d.total > 0)
            .sort((a, b) => b.total - a.total)
            .slice(0, 16);
        if (!activos.length) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: activos.map(d => d.codigo),
                datasets: [
                    {
                        label: '1º Curso',
                        data: activos.map(d => d.alumnos_1),
                        backgroundColor: C.blue,
                        borderRadius: 3,
                        stack: 'alumnos',
                    },
                    {
                        label: '2º Curso',
                        data: activos.map(d => d.alumnos_2),
                        backgroundColor: C.green,
                        borderRadius: 3,
                        stack: 'alumnos',
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            afterTitle: items => activos[items[0].dataIndex]?.nombre ?? '',
                            footer: items => 'Total: ' + items.reduce((s, i) => s + i.raw, 0),
                        },
                    },
                },
                scales: {
                    x: { stacked: true, beginAtZero: true, grid: { color: C.grid } },
                    y: { stacked: true, grid: { display: false } },
                },
            },
        });
    })();

    // ── 3. Distribución por nivel ──────────────────────────────
    (function () {
        const ctx = document.getElementById('chartNivel');
        if (!ctx) return;
        const labels = ['FP Básica', 'Grado Medio', 'Grado Superior'];
        const values = [nivelRaw.basica || 0, nivelRaw.media || 0, nivelRaw.superior || 0];
        const total  = values.reduce((a, b) => a + b, 0);
        if (!total) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [C.orange, C.blue, C.purple],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const pct = Math.round(ctx.raw / total * 100);
                                return ` ${ctx.label}: ${ctx.raw} alum. (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    })();

})();
</script>
@endpush
