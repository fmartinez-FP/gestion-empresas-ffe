@extends('layouts.portal')

@section('title', 'Mi cuaderno de prácticas')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Cuaderno de prácticas</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $asignacion->empresa->nombre }} —
                {{ $asignacion->fecha_inicio?->format('d/m/Y') }} a {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'en curso' }}
            </p>
        </div>
        @if(Route::has('portal.cuaderno.create'))
        <a href="{{ route('portal.cuaderno.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            + Registrar hoy
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 p-3 bg-blue-100 text-blue-800 rounded-lg text-sm">{{ session('info') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <a href="{{ request()->fullUrlWithQuery(['mes' => $mesActual->copy()->subMonth()->format('Y-m')]) }}"
           class="text-sm text-blue-600 hover:underline">‹ Mes anterior</a>
        <span class="text-sm font-semibold text-gray-700">
            {{ ucfirst($mesActual->isoFormat('MMMM YYYY')) }}
        </span>
        <a href="{{ request()->fullUrlWithQuery(['mes' => $mesActual->copy()->addMonth()->format('Y-m')]) }}"
           class="text-sm text-blue-600 hover:underline">Mes siguiente ›</a>
    </div>

    @if($semanas->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            No hay entradas registradas este mes.
        </div>
    @else
        <div class="space-y-4">
            @foreach($semanas as $semana)
            <div>
                <h2 class="text-sm font-semibold text-gray-500 mb-2">
                    Semana del {{ $semana['lunes']->format('d/m/Y') }}
                </h2>
                <div class="space-y-3">
                    @foreach($semana['seguimientos'] as $s)
                    <div class="bg-white rounded-lg shadow p-4 flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-semibold text-gray-800">{{ $s->fecha->format('d/m/Y') }}</span>
                                <span class="text-xs text-gray-500">{{ $s->hora_entrada }} — {{ $s->hora_salida }}</span>
                                @if($s->confirmado_tutor)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Confirmado
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pendiente
                                    </span>
                                @endif
                                @if($s->evidencia_path)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                        📎 Evidencia
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $s->descripcion_tareas }}</p>
                            @if($s->comentario_tutor)
                                <p class="text-xs text-blue-700 mt-1">💬 {{ $s->comentario_tutor }}</p>
                            @endif
                        </div>
                        @if(! $s->confirmado_tutor && $s->fecha->isToday())
                        <a href="{{ route('portal.cuaderno.edit', $s) }}"
                           class="text-sm text-blue-600 hover:underline whitespace-nowrap">Editar</a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
