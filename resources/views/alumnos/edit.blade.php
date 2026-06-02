@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('alumnos.show', $alumno) }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver a la ficha</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Editar alumno</h1>
        <p class="text-sm text-gray-500">{{ $alumno->nombre_completo }}</p>
    </div>

    <form method="POST" action="{{ route('alumnos.update', $alumno) }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" value="{{ old('nombre', $alumno->nombre) }}" required
                       class="w-full rounded-lg border @error('nombre') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('nombre')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                <input type="text" name="apellidos" value="{{ old('apellidos', $alumno->apellidos) }}" required
                       class="w-full rounded-lg border @error('apellidos') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('apellidos')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $alumno->email) }}"
                       class="w-full rounded-lg border @error('email') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $alumno->telefono) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo formativo <span class="text-red-500">*</span></label>
            <select name="ciclo_id" required
                    class="w-full rounded-lg border @error('ciclo_id') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Selecciona un ciclo…</option>
                @foreach($ciclos as $ciclo)
                    <option value="{{ $ciclo->id }}" {{ old('ciclo_id', $alumno->ciclo_id) == $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->codigo }} — {{ $ciclo->nombre }}
                    </option>
                @endforeach
            </select>
            @error('ciclo_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Curso académico <span class="text-red-500">*</span></label>
                <input type="text" name="curso_academico" value="{{ old('curso_academico', $alumno->curso_academico) }}"
                       pattern="\d{4}-\d{4}" required
                       class="w-full rounded-lg border @error('curso_academico') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('curso_academico')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número de curso <span class="text-red-500">*</span></label>
                <select name="numero_curso" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="1" {{ old('numero_curso', $alumno->numero_curso) == 1 ? 'selected' : '' }}>1º curso</option>
                    <option value="2" {{ old('numero_curso', $alumno->numero_curso) == 2 ? 'selected' : '' }}>2º curso</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Guardar cambios
            </button>
            <a href="{{ route('alumnos.show', $alumno) }}" class="px-5 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
