@extends('layouts.portal')

@section('title', 'Registrar jornada')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Registrar jornada de hoy</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('portal.cuaderno.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                <input type="text" value="{{ now()->format('d/m/Y') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-500 text-sm" disabled>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="hora_entrada" class="block text-sm font-medium text-gray-700 mb-1">Hora entrada</label>
                    <input type="time" id="hora_entrada" name="hora_entrada"
                        value="{{ old('hora_entrada') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('hora_entrada') border-red-500 @enderror"
                        required>
                    @error('hora_entrada')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="hora_salida" class="block text-sm font-medium text-gray-700 mb-1">Hora salida</label>
                    <input type="time" id="hora_salida" name="hora_salida"
                        value="{{ old('hora_salida') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('hora_salida') border-red-500 @enderror"
                        required>
                    @error('hora_salida')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="descripcion_tareas" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción de tareas realizadas
                </label>
                <textarea id="descripcion_tareas" name="descripcion_tareas" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('descripcion_tareas') border-red-500 @enderror"
                    placeholder="Describe las tareas que has realizado hoy en la empresa..."
                    required>{{ old('descripcion_tareas') }}</textarea>
                @error('descripcion_tareas')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label for="evidencia" class="block text-sm font-medium text-gray-700 mb-1">
                    Evidencia fotográfica <span class="text-gray-400 font-normal">(opcional, máx. 5 MB)</span>
                </label>
                <input type="file" id="evidencia" name="evidencia" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('evidencia')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Guardar entrada
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
