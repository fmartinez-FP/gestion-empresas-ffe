@extends('layouts.app')

@section('title', 'Asignar Alumnos')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('empresas.show', $empresa) }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Asignar Alumnos</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $empresa->nombre }}</p>
        </div>
    </div>

    <!-- Indicador del curso activo -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Registrando para el curso <strong>{{ $cursoActivo }}</strong>
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('colocaciones.store') }}" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">
        @csrf
        <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">

        <div>
            <label for="ciclo_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Ciclo formativo <span class="text-red-500">*</span></label>
            @if($empresa->ciclos->count() > 0)
            <select name="ciclo_id" id="ciclo_id" required class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                <option value="">Selecciona un ciclo</option>
                @foreach($empresa->ciclos as $ciclo)
                <option value="{{ $ciclo->id }}" {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>{{ $ciclo->codigo }} - {{ $ciclo->nombre }}</option>
                @endforeach
            </select>
            @else
            <p class="text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 p-3 rounded-lg">
                Esta empresa no tiene ciclos formativos asignados. 
                <a href="{{ route('empresas.edit', $empresa) }}" class="underline">Editar empresa</a> para añadir ciclos.
            </p>
            @endif
            @error('ciclo_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Curso del ciclo <span class="text-red-500">*</span></label>
            <div class="flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="numero_curso" value="1" {{ old('numero_curso') == 1 ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                    <span class="text-slate-700 dark:text-slate-300">1º curso</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="numero_curso" value="2" {{ old('numero_curso', 2) == 2 ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                    <span class="text-slate-700 dark:text-slate-300">2º curso</span>
                </label>
            </div>
            @error('numero_curso')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label for="num_alumnos" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nº de alumnos <span class="text-red-500">*</span></label>
                <input type="number" name="num_alumnos" id="num_alumnos" min="1" required value="{{ old('num_alumnos') }}" 
                       class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                @error('num_alumnos')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
	       <label for="num_horas" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Horas por alumno <span class="text-red-500">*</span></label>
                <select name="num_horas" id="num_horas" required 
                       class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                    <option value="">Selecciona las horas</option>
                    <option value="150" {{ old('num_horas') == '150' ? 'selected' : '' }}>150 horas</option>
                    <option value="250" {{ old('num_horas') == '250' ? 'selected' : '' }}>250 horas</option>
                    <option value="350" {{ old('num_horas') == '350' ? 'selected' : '' }}>350 horas</option>
                    <option value="400" {{ old('num_horas') == '400' ? 'selected' : '' }}>400 horas</option>
                    <option value="500" {{ old('num_horas') == '500' ? 'selected' : '' }}>500 horas</option>
                </select>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Horas de prácticas por cada alumno</p>
                @error('num_horas')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror 
            </div>
        </div>

        <div>
            <label for="observaciones" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Observaciones</label>
            <textarea name="observaciones" id="observaciones" rows="2" 
                      class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                      placeholder="Notas adicionales (opcional)">{{ old('observaciones') }}</textarea>
            @error('observaciones')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3 justify-end pt-4 border-t border-slate-200 dark:border-slate-700">
            <a href="{{ route('empresas.show', $empresa) }}" class="px-6 py-2.5 text-slate-600 bg-slate-100 dark:bg-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 shadow-lg shadow-green-500/20">
                Asignar Alumnos
            </button>
        </div>
    </form>
</div>
@endsection
