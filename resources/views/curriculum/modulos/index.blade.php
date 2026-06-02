@extends('layouts.app')

@section('title', 'Modulos - ' . $ciclo->nombre)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6 flex-wrap">
        <a href="{{ route('admin.curriculum.index') }}" class="hover:text-primary-600">Curriculum</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-white font-medium">{{ $ciclo->nombre }}</span>
    </nav>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $ciclo->nombre }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Modulos profesionales del ciclo</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-3">
            @forelse($modulos as $modulo)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded">{{ $modulo->codigo }}</span>
                            @if($modulo->curso)
                            <span class="text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 px-2 py-0.5 rounded">{{ $modulo->curso }}o curso</span>
                            @endif
                            @if($modulo->horas_totales)
                            <span class="text-xs text-slate-400">{{ $modulo->horas_totales }}h</span>
                            @endif
                        </div>
                        <h3 class="font-medium text-slate-800 dark:text-white text-sm leading-snug">{{ $modulo->nombre }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ $modulo->resultados_aprendizaje_count }} resultados de aprendizaje</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <a href="{{ route('admin.curriculum.ra.index', $modulo) }}"
                           class="p-1.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
                           title="Ver RA y CE">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @if(auth()->user()->can('gestionarCurriculum'))
                        <a href="{{ route('admin.curriculum.modulos.edit', [$ciclo, $modulo]) }}"
                           class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                           title="Editar modulo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.curriculum.modulos.destroy', [$ciclo, $modulo]) }}"
                              onsubmit="return confirm('Eliminar el modulo? Esta accion no se puede deshacer.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-10 text-slate-400 text-sm">No hay modulos profesionales en este ciclo.</div>
            @endforelse
        </div>

        @if(auth()->user()->can('gestionarCurriculum'))
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 sticky top-24">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-white mb-4">Anadir modulo</h2>
                <form method="POST" action="{{ route('admin.curriculum.modulos.store', $ciclo) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Codigo *</label>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" required maxlength="20"
                               class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Ej: MP01">
                        @error('codigo')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nombre *</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="200"
                               class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Nombre del modulo">
                        @error('nombre')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Horas</label>
                            <input type="number" name="horas_totales" value="{{ old('horas_totales') }}" min="1" max="9999"
                                   class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Curso</label>
                            <select name="curso" class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">-</option>
                                <option value="1" {{ old('curso') == '1' ? 'selected' : '' }}>1o</option>
                                <option value="2" {{ old('curso') == '2' ? 'selected' : '' }}>2o</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Anadir modulo
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
