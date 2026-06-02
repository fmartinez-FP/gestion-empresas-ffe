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
    </div>
@else
    <div class="bg-white rounded-lg shadow p-6 text-gray-500 text-sm">
        No tienes ninguna asignación activa en este momento.
    </div>
@endif
@endsection
