@extends('layouts.app')

@section('title', 'Editar modulo')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6 flex-wrap">
        <a href="{{ route('admin.curriculum.index') }}" class="hover:text-primary-600">Curriculum</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.curriculum.modulos.index', $ciclo) }}" class="hover:text-primary-600">{{ $ciclo->nombre }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-white font-medium">Editar modulo</span>
    </nav>

    <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-6">Editar modulo</h1>

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6">
        <form method="POST" action="{{ route('admin.curriculum.modulos.update', [$ciclo, $modulo]) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Codigo *</label>
                <input type="text" name="codigo" value="{{ old('codigo', $modulo->codigo) }}" required maxlength="20"
                       class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('codigo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $modulo->nombre) }}" required maxlength="200"
                       class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('nombre')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Horas totales</label>
                    <input type="number" name="horas_totales" value="{{ old('horas_totales', $modulo->horas_totales) }}" min="1" max="9999"
                           class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Curso</label>
                    <select name="curso" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">-</option>
                        <option value="1" {{ old('curso', $modulo->curso) == '1' ? 'selected' : '' }}>1o</option>
                        <option value="2" {{ old('curso', $modulo->curso) == '2' ? 'selected' : '' }}>2o</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Guardar cambios</button>
                <a href="{{ route('admin.curriculum.modulos.index', $ciclo) }}" class="px-5 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-sm font-medium rounded-lg transition-colors">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
