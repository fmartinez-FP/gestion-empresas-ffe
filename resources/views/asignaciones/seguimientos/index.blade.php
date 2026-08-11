@extends('layouts.app')

@section('title', 'Seguimientos — ' . $asignacion->alumno->nombre . ' ' . $asignacion->alumno->apellidos)

@section('content')
<div class="max-w-6xl mx-auto">

    @include('partials.nav-alumnos')


    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Cuaderno de seguimiento</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $asignacion->alumno->nombre }} {{ $asignacion->alumno->apellidos }} —
                {{ $asignacion->empresa->nombre }}
            </p>
        </div>
        <a href="{{ route('asignaciones.show', $asignacion) }}"
           class="text-sm text-blue-600 hover:underline">← Volver a la asignación</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-5 mb-6">
        <div class="grid grid-cols-3 gap-4 text-center mb-3">
            <div>
                <p class="text-2xl font-bold text-green-700">{{ number_format($horasRealizadas, 2) }}h</p>
                <p class="text-xs text-gray-500 mt-1">Horas realizadas</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-yellow-700">{{ number_format($horasPendientes, 2) }}h</p>
                <p class="text-xs text-gray-500 mt-1">Horas pendientes</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-700">{{ number_format($horasPrevistas, 2) }}h</p>
                <p class="text-xs text-gray-500 mt-1">Total previsto</p>
            </div>
        </div>
        @if($horasPrevistas > 0)
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-green-600 h-2.5 rounded-full"
                 style="width: {{ min(100, round(($horasRealizadas / $horasPrevistas) * 100)) }}%"></div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna izquierda: semanas del mes --}}
        <div class="lg:col-span-2 space-y-3">

            <div class="flex items-center justify-between">
                <a href="{{ request()->fullUrlWithQuery(['mes' => $mesActual->copy()->subMonth()->format('Y-m')]) }}"
                   class="text-sm text-blue-600 hover:underline">‹ Mes anterior</a>
                <span class="text-sm font-semibold text-gray-700">
                    {{ ucfirst($mesActual->isoFormat('MMMM YYYY')) }}
                </span>
                <a href="{{ request()->fullUrlWithQuery(['mes' => $mesActual->copy()->addMonth()->format('Y-m')]) }}"
                   class="text-sm text-blue-600 hover:underline">Mes siguiente ›</a>
            </div>

            @forelse($semanas as $semana)
            @php
                $pendientesConfirmar = $semana['dias']->filter(
                    fn ($d) => $d['estado'] === 'pendiente' && $d['seguimiento']
                )->count();
            @endphp
            <details class="bg-white rounded-lg shadow" @if($loop->first) open @endif>
                <summary class="cursor-pointer select-none px-5 py-4 flex items-center justify-between gap-4">
                    <span class="font-semibold text-gray-800">
                        Semana del {{ $semana['lunes']->format('d/m/Y') }}
                        @if($pendientesConfirmar > 0)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⚠ {{ $pendientesConfirmar }} pendiente{{ $pendientesConfirmar === 1 ? '' : 's' }} de confirmar
                            </span>
                        @endif
                    </span>
                    <span class="text-sm text-gray-500 whitespace-nowrap">
                        {{ number_format($semana['horas']['realizadas'], 2) }}h
                        / {{ number_format($semana['horas']['previstas'], 2) }}h prevista{{ $semana['horas']['previstas'] == 1 ? '' : 's' }}
                        @if($semana['horas']['ajuste'] != 0)
                            <span class="text-xs text-blue-600">(ajuste {{ $semana['horas']['ajuste'] > 0 ? '+' : '' }}{{ number_format($semana['horas']['ajuste'], 2) }}h)</span>
                        @endif
                    </span>
                </summary>

                <div class="border-t border-gray-100 px-5 py-4 space-y-3">
                    @foreach($semana['dias'] as $dia)
                    <div class="flex items-start justify-between gap-4 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <div class="flex items-start gap-3">
                            <span @class([
                                    'inline-flex items-center justify-center w-16 shrink-0 rounded px-2 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800' => $dia['estado'] === 'confirmado',
                                    'bg-yellow-100 text-yellow-800' => $dia['estado'] === 'pendiente',
                                    'bg-orange-100 text-orange-800' => $dia['estado'] === 'no_trabajado',
                                    'bg-red-100 text-red-800' => $dia['estado'] === 'ausencia',
                                    'bg-gray-200 text-gray-600' => $dia['estado'] === 'festivo',
                                ])>
                                {{ $dia['fecha']->isoFormat('ddd D/M') }}
                            </span>
                            <div>
                                @if($dia['estado'] === 'confirmado')
                                    <p class="text-sm text-gray-700">Confirmado
                                        @if($dia['seguimiento']?->confirmado_at)
                                            ({{ $dia['seguimiento']->confirmado_at->format('d/m H:i') }})
                                        @endif
                                    </p>
                                    @if($dia['seguimiento']?->descripcion_tareas)
                                        <p class="text-xs text-gray-500 line-clamp-1">{{ $dia['seguimiento']->descripcion_tareas }}</p>
                                    @endif
                                @elseif($dia['estado'] === 'pendiente' && $dia['seguimiento'])
                                    <p class="text-sm text-gray-700">Pendiente de confirmar</p>
                                    <p class="text-xs text-gray-500 line-clamp-1">{{ $dia['seguimiento']->descripcion_tareas }}</p>
                                @elseif($dia['estado'] === 'pendiente')
                                    <p class="text-sm text-gray-400">Sin registrar todavía</p>
                                @elseif($dia['estado'] === 'no_trabajado')
                                    <p class="text-sm text-gray-700">No trabajado</p>
                                @elseif($dia['estado'] === 'ausencia')
                                    <p class="text-sm text-gray-700">Ausencia no justificada @if($dia['motivo']): {{ $dia['motivo'] }}@endif</p>
                                @elseif($dia['estado'] === 'festivo')
                                    <p class="text-sm text-gray-700">{{ ucfirst($dia['tipo']) }}@if($dia['motivo']): {{ $dia['motivo'] }}@endif</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($dia['estado'] === 'pendiente' && $dia['seguimiento'] && auth()->user()->can('confirmarSeguimiento', $dia['seguimiento']))
                            <form method="POST"
                                  action="{{ route('asignaciones.seguimientos.confirmar', [$asignacion, $dia['seguimiento']]) }}">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition whitespace-nowrap">
                                    Confirmar
                                </button>
                            </form>
                            @endif

                            @if($dia['estado'] === 'no_trabajado' && $puedeMarcarNoTrabajado)
                            <form method="POST"
                                  action="{{ route('asignaciones.marcar-no-trabajado.store', $asignacion) }}">
                                @csrf
                                <input type="hidden" name="fecha" value="{{ $dia['fecha']->toDateString() }}">
                                <button type="submit"
                                    class="px-3 py-1 bg-orange-600 text-white text-xs font-medium rounded-lg hover:bg-orange-700 transition whitespace-nowrap">
                                    Marcar ausencia no justificada
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if($puedeAjustar)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <form method="POST" action="{{ route('asignaciones.ajustes-horas-semana.store', $asignacion) }}"
                              class="flex items-end gap-3 flex-wrap">
                            @csrf
                            <input type="hidden" name="semana" value="{{ $semana['lunes']->toDateString() }}">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Ajuste horas (+/-)</label>
                                <input type="number" step="0.25" name="ajuste"
                                    value="{{ $semana['horas']['ajuste'] }}"
                                    class="w-28 border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <div class="flex-1 min-w-[160px]">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Motivo (opcional)</label>
                                <input type="text" name="motivo" maxlength="500"
                                    placeholder="Ej. recuperación de horas..."
                                    class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <button type="submit"
                                class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                                Guardar ajuste
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </details>
            @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                Este mes no tiene días laborables dentro del período de la asignación.
            </div>
            @endforelse
        </div>

        {{-- Columna derecha: overview mensual --}}
        <div class="bg-white rounded-lg shadow p-5 h-fit">
            <div class="flex items-center justify-between mb-4">
                <a href="{{ request()->fullUrlWithQuery(['mes' => $mesActual->copy()->subMonth()->format('Y-m')]) }}"
                   class="text-gray-400 hover:text-gray-700 text-sm">‹</a>
                <span class="text-sm font-semibold text-gray-800">
                    {{ ucfirst($mesActual->isoFormat('MMMM YYYY')) }}
                </span>
                <a href="{{ request()->fullUrlWithQuery(['mes' => $mesActual->copy()->addMonth()->format('Y-m')]) }}"
                   class="text-gray-400 hover:text-gray-700 text-sm">›</a>
            </div>

            <div class="grid grid-cols-5 gap-1 text-center text-xs text-gray-400 mb-1">
                <span>L</span><span>M</span><span>X</span><span>J</span><span>V</span>
            </div>

            @php
                $inicioMes = $mesActual->copy()->startOfMonth();
                $finMes    = $mesActual->copy()->endOfMonth();
                $cursor    = $inicioMes->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            @endphp

            <div class="grid grid-cols-5 gap-1">
                @while($cursor->lte($finMes))
                    @for($i = 0; $i < 5; $i++)
                        @php $fechaCelda = $cursor->copy()->addDays($i); @endphp
                        @if($fechaCelda->month !== $mesActual->month)
                            <span class="aspect-square"></span>
                        @else
                            @php $estadoCelda = $estadosMes->get($fechaCelda->toDateString()); @endphp
                            <span title="{{ $fechaCelda->format('d/m/Y') }}@if($estadoCelda && $estadoCelda['motivo']): {{ $estadoCelda['motivo'] }}@endif"
                                @class([
                                    'aspect-square flex items-center justify-center rounded text-[11px]',
                                    'bg-green-500 text-white' => $estadoCelda && $estadoCelda['estado'] === 'confirmado',
                                    'bg-yellow-400 text-gray-900' => $estadoCelda && $estadoCelda['estado'] === 'pendiente',
                                    'bg-orange-400 text-white' => $estadoCelda && $estadoCelda['estado'] === 'no_trabajado',
                                    'bg-red-400 text-white' => $estadoCelda && $estadoCelda['estado'] === 'ausencia',
                                    'bg-gray-200 text-gray-500' => $estadoCelda && $estadoCelda['estado'] === 'festivo',
                                    'text-gray-300' => ! $estadoCelda,
                                ])>
                                {{ $fechaCelda->day }}
                            </span>
                        @endif
                    @endfor
                    @php $cursor->addWeek(); @endphp
                @endwhile
            </div>

            <div class="mt-4 space-y-1.5 text-xs text-gray-600">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-green-500"></span> Confirmado</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-yellow-400"></span> Pendiente</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-orange-400"></span> No trabajado</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-red-400"></span> Ausencia no justificada</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-gray-200"></span> Festivo / no lectivo</div>
            </div>
        </div>
    </div>
</div>
@endsection
