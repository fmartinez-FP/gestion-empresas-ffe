@extends('layouts.portal')

@section('title', 'Editar entrada')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar entrada del {{ $seguimiento->fecha->format('d/m/Y') }}</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('portal.cuaderno.update', $seguimiento) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="hora_entrada" class="block text-sm font-medium text-gray-700 mb-1">Hora entrada</label>
                    <input type="time" id="hora_entrada" name="hora_entrada"
                        value="{{ old('hora_entrada', substr($seguimiento->hora_entrada, 0, 5)) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('hora_entrada') border-red-500 @enderror"
                        required>
                    @error('hora_entrada')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="hora_salida" class="block text-sm font-medium text-gray-700 mb-1">Hora salida</label>
                    <input type="time" id="hora_salida" name="hora_salida"
                        value="{{ old('hora_salida', substr($seguimiento->hora_salida, 0, 5)) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('hora_salida') border-red-500 @enderror"
                        required>
                    @error('hora_salida')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-6">
                <label for="descripcion_tareas" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción de tareas realizadas
                </label>
                <textarea id="descripcion_tareas" name="descripcion_tareas" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('descripcion_tareas') border-red-500 @enderror"
                    required>{{ old('descripcion_tareas', $seguimiento->descripcion_tareas) }}</textarea>
                @error('descripcion_tareas')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            @if($seguimiento->evidencia_path)
            <div class="mb-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                📎 Ya tienes una evidencia adjunta. No es posible reemplazarla una vez guardada.
            </div>
            @endif

            <div class="flex gap-3">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Guardar cambios
                </button>
                <a href="{{ route('portal.cuaderno.index') }}"
                    class="px-6 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
