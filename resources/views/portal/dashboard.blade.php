@extends('layouts.portal')

@section('title', 'Inicio')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-800">
        Bienvenido, {{ $user->nombre }}
    </h2>
    <p class="text-gray-500 text-sm mt-1">Portal de seguimiento de prácticas FFE</p>
</div>

@if($alumno && $alumno->asignacionActiva)
    {{-- Tarjeta cuaderno de prácticas --}}
    @if(Route::has('portal.cuaderno.index'))
    @php
        $asignacionActiva = $alumno->asignacionActiva;
        $entradasSemana = $asignacionActiva->seguimientos()
            ->where('fecha', '>=', now()->subDays(7)->toDateString())
            ->count();
    @endphp
    <div class="bg-white rounded-lg shadow p-5 mb-6 border-l-4 border-primary-500">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary-50 rounded-lg">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Mi cuaderno de prácticas</p>
                    <p class="text-sm text-gray-500">
                        @if($entradasSemana > 0)
                            {{ $entradasSemana }} entrada{{ $entradasSemana !== 1 ? 's' : '' }} esta semana
                        @else
                            Sin entradas esta semana
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('portal.cuaderno.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                Ver cuaderno
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @if($entradasSemana === 0)
        <div class="mt-3 pt-3 border-t border-gray-100">
            <a href="{{ route('portal.cuaderno.create') }}"
               class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                + Registrar entrada de hoy
            </a>
        </div>
        @endif
    </div>
    @endif
@endif
@if($alumno && $alumno->asignacionActiva)
    @php $asignacion = $alumno->asignacionActiva; @endphp
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="font-semibold text-gray-700 mb-4">Tu asignación activa</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-gray-500">Empresa</dt>
                <dd class="font-medium">{{ $asignacion->empresa->nombre }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Ciclo</dt>
                <dd class="font-medium">{{ $asignacion->ciclo->nombre }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Fecha inicio</dt>
                <dd class="font-medium">{{ $asignacion->fecha_inicio?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Fecha fin</dt>
                <dd class="font-medium">{{ $asignacion->fecha_fin?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tutor IES</dt>
                <dd class="font-medium">{{ $asignacion->tutorIes->nombre }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Horas</dt>
                <dd class="font-medium">{{ $asignacion->num_horas ?? '—' }}</dd>
            </div>
        </dl>

        @if($horasPrevistas !== null)
        <div class="mt-5 pt-5 border-t border-gray-100 grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-xl font-bold text-green-700">{{ number_format($horasRealizadas, 2) }}h</p>
                <p class="text-xs text-gray-500 mt-1">Realizadas</p>
            </div>
            <div>
                <p class="text-xl font-bold text-yellow-700">{{ number_format($horasPendientes, 2) }}h</p>
                <p class="text-xs text-gray-500 mt-1">Pendientes</p>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-700">{{ number_format($horasPrevistas, 2) }}h</p>
                <p class="text-xs text-gray-500 mt-1">Total previsto</p>
            </div>
        </div>
        @if($horasPrevistas > 0)
        <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
            <div class="bg-green-600 h-2 rounded-full"
                 style="width: {{ min(100, round(($horasRealizadas / $horasPrevistas) * 100)) }}%"></div>
        </div>
        @endif
        @endif
    </div>
@else
    <div class="bg-white rounded-lg shadow p-6 text-gray-500 text-sm">
        No tienes ninguna asignación activa en este momento.
    </div>
@endif
@endsection
