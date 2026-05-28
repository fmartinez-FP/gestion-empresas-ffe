@extends('layouts.app')

@section('title', 'Editar Colocación')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Cabecera -->
    <div class="flex items-center gap-4">
        <a href="{{ route('empresas.show', $empresa) }}" 
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Editar Colocación</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $empresa->nombre }}</p>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('colocaciones.update', $colocacion) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">
            
            <!-- Curso académico (solo lectura) -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Curso Académico
                </label>
                <div class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 rounded-xl text-slate-700 dark:text-slate-300 font-medium">
                    {{ $colocacion->curso_academico }}
                </div>
            </div>

            <!-- Ciclo formativo -->
            <div>
                <label for="ciclo_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Ciclo Formativo <span class="text-red-500">*</span>
                </label>
                <select 
                    name="ciclo_id" 
                    id="ciclo_id" 
                    required
                    class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 focus:ring-2 focus:ring-primary-500 @error('ciclo_id') border-red-300 @enderror"
                >
                    <option value="">Selecciona un ciclo</option>
                    @foreach($empresa->ciclos as $ciclo)
                    <option value="{{ $ciclo->id }}" {{ old('ciclo_id', $colocacion->ciclo_id) == $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->codigo }} - {{ $ciclo->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('ciclo_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Número de curso -->
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Curso <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="radio" 
                            name="numero_curso" 
                            value="1" 
                            {{ old('numero_curso', $colocacion->numero_curso) == 1 ? 'checked' : '' }}
                            class="w-4 h-4 text-primary-600 focus:ring-primary-500"
                        >
                        <span class="text-slate-700 dark:text-slate-300">1º Curso</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="radio" 
                            name="numero_curso" 
                            value="2" 
                            {{ old('numero_curso', $colocacion->numero_curso) == 2 ? 'checked' : '' }}
                            class="w-4 h-4 text-primary-600 focus:ring-primary-500"
                        >
                        <span class="text-slate-700 dark:text-slate-300">2º Curso</span>
                    </label>
                </div>
                @error('numero_curso')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alumnos y horas -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="num_alumnos" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Nº de Alumnos <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="num_alumnos" 
                        id="num_alumnos" 
                        value="{{ old('num_alumnos', $colocacion->num_alumnos) }}"
                        min="1"
                        required
                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 focus:ring-2 focus:ring-primary-500 @error('num_alumnos') border-red-300 @enderror"
                    >
                    @error('num_alumnos')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="num_horas" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Horas por Alumno <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="num_horas" 
                        id="num_horas" 
                        required
                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 focus:ring-2 focus:ring-primary-500 @error('num_horas') border-red-300 @enderror"
                    >
                        <option value="150" {{ old('num_horas', $colocacion->num_horas) == 150 ? 'selected' : '' }}>150 horas</option>
                        <option value="250" {{ old('num_horas', $colocacion->num_horas) == 250 ? 'selected' : '' }}>250 horas</option>
                        <option value="350" {{ old('num_horas', $colocacion->num_horas) == 350 ? 'selected' : '' }}>350 horas</option>
                        <option value="400" {{ old('num_horas', $colocacion->num_horas) == 400 ? 'selected' : '' }}>400 horas</option>
                        <option value="500" {{ old('num_horas', $colocacion->num_horas) == 500 ? 'selected' : '' }}>500 horas</option>
                    </select>
                    @error('num_horas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Observaciones -->
            <div>
                <label for="observaciones" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Observaciones
                </label>
                <textarea 
                    name="observaciones" 
                    id="observaciones" 
                    rows="3"
                    class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 focus:ring-2 focus:ring-primary-500 resize-none"
                >{{ old('observaciones', $colocacion->observaciones) }}</textarea>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('empresas.show', $empresa) }}" 
               class="px-6 py-2.5 text-slate-600 bg-slate-100 rounded-xl font-medium hover:bg-slate-200 transition-colors">
                Cancelar
            </a>
            <button 
                type="submit"
                class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20"
            >
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
