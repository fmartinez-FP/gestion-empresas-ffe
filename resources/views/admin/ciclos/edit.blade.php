@extends('layouts.app')

@section('title', 'Editar Ciclo')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.ciclos.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Editar Ciclo</h1>
            <p class="text-slate-500 dark:text-slate-400">{{ $ciclo->codigo }} - {{ $ciclo->nombre }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.ciclos.update', $ciclo) }}" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="codigo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Código <span class="text-red-500">*</span></label>
                <input type="text" id="codigo" name="codigo" value="{{ old('codigo', $ciclo->codigo) }}" required maxlength="10"
                       class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 @error('codigo') border-red-300 @enderror">
                @error('codigo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label for="nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nombre <span class="text-red-500">*</span></label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $ciclo->nombre) }}" required maxlength="150"
                       class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 @error('nombre') border-red-300 @enderror">
                @error('nombre')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Nivel <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-3 gap-4">
                @foreach(['basica' => ['FP Básica', 'orange'], 'media' => ['Grado Medio', 'blue'], 'superior' => ['Grado Superior', 'purple']] as $valor => [$etiqueta, $color])
                <label class="flex items-center p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-{{ $color }}-300 transition-colors {{ old('nivel', $ciclo->nivel) === $valor ? 'border-'.$color.'-500 bg-'.$color.'-50 dark:bg-'.$color.'-900/30' : '' }}">
                    <input type="radio" name="nivel" value="{{ $valor }}" {{ old('nivel', $ciclo->nivel) === $valor ? 'checked' : '' }} class="text-{{ $color }}-600 focus:ring-{{ $color }}-500">
                    <span class="ml-3 font-medium text-slate-800 dark:text-white">{{ $etiqueta }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="activo" value="1" {{ old('activo', $ciclo->activo) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
            <span class="font-medium text-slate-800 dark:text-white">Ciclo activo</span>
        </label>

        @if($ciclo->empresas_count > 0 || $ciclo->colocaciones_count > 0)
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
            <p class="text-sm text-amber-700 dark:text-amber-400">Este ciclo tiene {{ $ciclo->empresas_count }} empresas y {{ $ciclo->colocaciones_count }} colocaciones asociadas.</p>
        </div>
        @endif

        <div class="flex gap-3 justify-end pt-4 border-t border-slate-200 dark:border-slate-700">
            <a href="{{ route('admin.ciclos.index') }}" class="px-6 py-2.5 text-slate-600 bg-slate-100 rounded-xl font-medium hover:bg-slate-200">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 shadow-lg shadow-primary-500/20">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
