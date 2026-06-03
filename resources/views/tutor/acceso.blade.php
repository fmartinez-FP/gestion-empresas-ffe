<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento FFE — Tutor empresa</title>
    <style>body{font-family:sans-serif;} *{box-sizing:border-box;}</style>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-3xl mx-auto py-10 px-4">

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h1 class="text-xl font-bold text-gray-800 mb-1">Cuaderno de seguimiento FFE</h1>
        <p class="text-sm text-gray-500">
            Alumno: <strong>{{ $asignacion->alumno->nombre }} {{ $asignacion->alumno->apellidos }}</strong> —
            Empresa: <strong>{{ $asignacion->empresa->nombre }}</strong>
        </p>
        <p class="text-xs text-gray-400 mt-1">
            Periodo: {{ $asignacion->fecha_inicio?->format('d/m/Y') }} — {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'en curso' }}
        </p>
        @if($registro->fueUsado())
        <p class="text-xs text-blue-600 mt-2">
            Acceso registrado el {{ $registro->usado_at->format('d/m/Y H:i') }}
        </p>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if($seguimientos->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            El alumno aún no ha registrado ninguna entrada.
        </div>
    @else
        <div class="space-y-4">
            @foreach($seguimientos as $s)
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <span class="font-semibold text-gray-800">{{ $s->fecha->format('d/m/Y') }}</span>
                    <span class="text-xs text-gray-500">{{ $s->hora_entrada }} — {{ $s->hora_salida }}</span>
                    @if($s->confirmado_tutor)
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Validado</span>
                    @endif
                </div>
                <p class="text-sm text-gray-700 mb-3">{{ $s->descripcion_tareas }}</p>

                @if($s->comentario_tutor)
                    <p class="text-xs text-blue-700 mb-3">💬 {{ $s->comentario_tutor }}</p>
                @endif

                {{-- Formulario comentar --}}
                <div class="pt-3 border-t border-gray-100">
                    <form method="POST"
                          action="{{ route('tutor.comentar', ['token' => $registro->token, 'seguimiento' => $s]) }}">
                        @csrf
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <input type="text" name="comentario_tutor"
                                    value="{{ $s->comentario_tutor }}"
                                    placeholder="Añadir o actualizar comentario..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            </div>
                            <button type="submit"
                                class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <p class="text-center text-xs text-gray-400 mt-8">
        Acceso proporcionado por el IES. Este enlace es válido hasta el {{ $registro->expires_at->format('d/m/Y') }}.
    </p>
</div>

</body>
</html>
