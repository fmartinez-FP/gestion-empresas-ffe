@extends('layouts.app')

@section('title', 'Seguimientos — ' . $asignacion->alumno->nombre . ' ' . $asignacion->alumno->apellidos)

@section('content')
<div class="max-w-5xl mx-auto">

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

    @if($seguimientos->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            El alumno aún no ha registrado ninguna entrada en su cuaderno.
        </div>
    @else
        <div class="space-y-4">
            @foreach($seguimientos as $s)
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="font-semibold text-gray-800">{{ $s->fecha->format('d/m/Y') }}</span>
                            <span class="text-xs text-gray-500">{{ $s->hora_entrada }} — {{ $s->hora_salida }}</span>
                            @if($s->confirmado_tutor)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Confirmado {{ $s->confirmado_at?->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pendiente de confirmar
                                </span>
                            @endif
                            @if($s->evidencia_path)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">📎 Evidencia</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700 mb-2">{{ $s->descripcion_tareas }}</p>
                        @if($s->comentario_tutor)
                            <p class="text-xs text-blue-700">💬 {{ $s->comentario_tutor }}</p>
                        @endif
                    </div>
                </div>

                @if(! $s->confirmado_tutor && auth()->user()->can('confirmarSeguimiento', $s))
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form method="POST"
                          action="{{ route('asignaciones.seguimientos.confirmar', [$asignacion, $s]) }}">
                        @csrf
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Comentario (opcional)
                                </label>
                                <input type="text" name="comentario_tutor"
                                    placeholder="Observación sobre esta jornada..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            </div>
                            <button type="submit"
                                class="px-4 py-1.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition whitespace-nowrap">
                                Confirmar entrada
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $seguimientos->links() }}</div>
    @endif
</div>
@endsection
