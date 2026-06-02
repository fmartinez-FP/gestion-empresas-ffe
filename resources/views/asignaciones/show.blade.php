@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <div class="mb-1 text-sm text-gray-500">
        <a href="{{ route('alumnos.index') }}" class="hover:text-gray-700">Alumnos</a>
        <span class="mx-1">›</span>
        <a href="{{ route('alumnos.show', $asignacion->alumno) }}" class="hover:text-gray-700">{{ $asignacion->alumno->nombre_completo }}</a>
        <span class="mx-1">›</span>
        <span>Asignación FFE</span>
    </div>

    <div class="flex items-center justify-between mt-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Asignación FFE</h1>
        <div class="flex gap-2">
            @if($puedeEditar)
            <a href="{{ route('asignaciones.edit', $asignacion) }}"
               class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                Editar
            </a>
            @endif
            @if($puedeCancelar)
            <button onclick="document.getElementById('modal-cancelar').style.display='flex'"
                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                Cancelar asignación
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-300 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- Estado --}}
    @php
        $colores = ['activa' => 'bg-green-100 text-green-800', 'finalizada' => 'bg-gray-100 text-gray-600', 'cancelada' => 'bg-red-100 text-red-700'];
    @endphp
    <div class="mb-5 flex items-center gap-3">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $colores[$asignacion->estado] ?? '' }}">
            {{ ucfirst($asignacion->estado) }}
        </span>
        <span class="text-sm text-gray-400">{{ $asignacion->curso_academico }} · {{ $asignacion->numero_curso }}º curso</span>
    </div>

    @if($asignacion->estado === 'cancelada' && $asignacion->motivo_baja)
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <span class="font-medium">Motivo de cancelación:</span> {{ $asignacion->motivo_baja }}
    </div>
    @endif

    {{-- Empresa --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Empresa</h2>
        <div class="space-y-1.5 text-sm">
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Empresa</span>
                <span class="text-gray-900 font-medium">{{ $asignacion->empresa->nombre }}</span>
            </div>
            @if($asignacion->sede)
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Sede</span>
                <span class="text-gray-700">
                    {{ trim(($asignacion->sede->tipo_via ? $asignacion->sede->tipo_via . ' ' : '') . $asignacion->sede->nombre_via . ($asignacion->sede->numero ? ', ' . $asignacion->sede->numero : '') . ($asignacion->sede->municipio ? ' (' . $asignacion->sede->municipio . ')' : '')) }}
                </span>
            </div>
            @endif
            @if($asignacion->tutorEmpresa)
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Tutor empresa</span>
                <span class="text-gray-700">{{ $asignacion->tutorEmpresa->nombre }}{{ $asignacion->tutorEmpresa->cargo ? ' — ' . $asignacion->tutorEmpresa->cargo : '' }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Seguimiento IES --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Seguimiento IES</h2>
        <div class="space-y-1.5 text-sm">
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Alumno</span>
                <span class="text-gray-900">{{ $asignacion->alumno->nombre_completo }}</span>
            </div>
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Tutor IES</span>
                <span class="text-gray-700">{{ $asignacion->tutorIes->nombre }}</span>
            </div>
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Fechas</span>
                <span class="text-gray-700">
                    @if($asignacion->fecha_inicio)
                        {{ $asignacion->fecha_inicio->format('d/m/Y') }}
                        @if($asignacion->fecha_fin) — {{ $asignacion->fecha_fin->format('d/m/Y') }} @endif
                    @else
                        <span class="text-gray-300">Sin fechas</span>
                    @endif
                </span>
            </div>
            @if($asignacion->num_horas)
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Horas</span>
                <span class="text-gray-700">{{ $asignacion->num_horas }} h</span>
            </div>
            @endif
            @if($asignacion->horario)
            <div class="flex gap-2">
                <span class="w-36 text-gray-400 shrink-0">Horario</span>
                <span class="text-gray-700">{{ $asignacion->horario }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- RA y CE asignados --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Resultados de Aprendizaje y Criterios de Evaluación</h2>

        @php
            $raAgrupados = $asignacion->resultadosAprendizaje->groupBy(fn($ra) => $ra->modulo->id ?? 0);
            $ceAgrupados = $asignacion->criteriosEvaluacion->groupBy(fn($ce) => $ce->resultadoAprendizaje->id ?? 0);
        @endphp

        @if($asignacion->resultadosAprendizaje->isEmpty() && $asignacion->criteriosEvaluacion->isEmpty())
            <p class="text-sm text-gray-400">No se han asignado RA ni CE.</p>
        @else
            <div class="space-y-4">
                @foreach($asignacion->resultadosAprendizaje->groupBy(fn($ra) => optional($ra->modulo)->nombre ?? 'Sin módulo') as $moduloNombre => $ras)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">{{ $moduloNombre }}</p>
                    @foreach($ras as $ra)
                    <div class="mb-2">
                        <p class="text-sm text-gray-800 font-medium">✓ {{ $ra->codigo }} — {{ $ra->nombre }}</p>
                        @php $ceDeEsteRa = $asignacion->criteriosEvaluacion->filter(fn($ce) => $ce->resultado_aprendizaje_id === $ra->id); @endphp
                        @if($ceDeEsteRa->isNotEmpty())
                        <ul class="ml-4 mt-1 space-y-0.5">
                            @foreach($ceDeEsteRa as $ce)
                            <li class="text-xs text-gray-500">· {{ $ce->codigo }} — {{ $ce->descripcion }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach

                {{-- CE huérfanos (sin RA asignado) --}}
                @php
                    $raIds = $asignacion->resultadosAprendizaje->pluck('id');
                    $ceHuerfanos = $asignacion->criteriosEvaluacion->filter(fn($ce) => !$raIds->contains($ce->resultado_aprendizaje_id));
                @endphp
                @if($ceHuerfanos->isNotEmpty())
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">CE sin RA asociado</p>
                    @foreach($ceHuerfanos as $ce)
                    <p class="text-xs text-gray-500 ml-4">· {{ $ce->codigo }} — {{ $ce->descripcion }}</p>
                    @endforeach
                </div>
                @endif
            </div>
        @endif
    </div>

</div>

{{-- Modal cancelar --}}
@if($puedeCancelar)
<div id="modal-cancelar" style="display:none"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancelar asignación</h3>
        <form method="POST" action="{{ route('asignaciones.cancelar', $asignacion) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo <span class="text-red-500">*</span></label>
                <textarea name="motivo_baja" rows="4" required minlength="5"
                          placeholder="Indica el motivo de la cancelación…"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-red-500"></textarea>
                @error('motivo_baja')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('modal-cancelar').style.display='none'"
                        class="px-4 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
                    Volver
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    Confirmar cancelación
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
