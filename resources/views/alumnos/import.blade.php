@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('alumnos.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver al listado</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Importar alumnos</h1>
        <p class="text-sm text-gray-500 mt-1">Carga masiva desde Excel o CSV. La deduplicación es por nombre + apellidos + ciclo + curso académico.</p>
    </div>

    @if(session('import_errores'))
    <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 mb-6">
        <p class="text-sm font-medium text-amber-800 mb-2">Avisos durante la importación:</p>
        <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
            @foreach(session('import_errores') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('alumnos.import.submit') }}" enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Archivo Excel / CSV <span class="text-red-500">*</span></label>
            <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required
                   class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            @error('archivo')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-gray-400 mt-1">Máximo 5 MB. Columnas requeridas: <strong>Nombre</strong>, <strong>Apellidos</strong>. Opcionales: Email, Teléfono.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo formativo <span class="text-red-500">*</span></label>
            @if($ciclos->isEmpty())
                <div class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    No hay ciclos formativos. Crea uno antes de importar.
                </div>
                <input type="hidden" name="ciclo_id" value="">
            @else
                <select name="ciclo_id" required
                        class="w-full rounded-lg border @error('ciclo_id') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecciona un ciclo…</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}" {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                            {{ $ciclo->codigo }} — {{ $ciclo->nombre }}
                        </option>
                    @endforeach
                </select>
            @endif
            @error('ciclo_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Curso académico <span class="text-red-500">*</span></label>
                <input type="text" name="curso_academico" value="{{ old('curso_academico', $cursoActivo) }}"
                       placeholder="2025-2026" pattern="\d{4}-\d{4}" required
                       class="w-full rounded-lg border @error('curso_academico') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('curso_academico')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número de curso <span class="text-red-500">*</span></label>
                <select name="numero_curso" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="1" {{ old('numero_curso') == '1' ? 'selected' : '' }}>1º curso</option>
                    <option value="2" {{ old('numero_curso', '2') == '2' ? 'selected' : '' }}>2º curso</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Importar
            </button>
            <a href="{{ route('alumnos.plantilla') }}"
               class="px-5 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
                Descargar plantilla
            </a>
            <a href="{{ route('alumnos.index') }}" class="px-5 py-2 text-sm text-gray-400 hover:text-gray-600">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
