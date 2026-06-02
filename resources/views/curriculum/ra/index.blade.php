@extends('layouts.app')

@section('title', 'RA y CE - ' . $modulo->nombre)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6 flex-wrap">
        <a href="{{ route('admin.curriculum.index') }}" class="hover:text-primary-600">Curriculum</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.curriculum.modulos.index', $modulo->ciclo) }}" class="hover:text-primary-600">{{ $modulo->ciclo->nombre }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-white font-medium">{{ $modulo->codigo }}</span>
    </nav>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $modulo->nombre }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Codigo: <span class="font-mono">{{ $modulo->codigo }}</span>
                @if($modulo->horas_totales) &middot; {{ $modulo->horas_totales }}h @endif
                @if($modulo->curso) &middot; {{ $modulo->curso }}o curso @endif
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">

            @forelse($modulo->resultadosAprendizaje as $ra)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden"
                 x-data="{ openCe: false, editRa: false, editCe: null }">

                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 text-xs font-mono bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-1 rounded mt-0.5">{{ $ra->codigo }}</span>

                        <div class="flex-1 min-w-0" x-show="!editRa">
                            <p class="text-sm text-slate-800 dark:text-white leading-snug">{{ $ra->descripcion }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $ra->criterios->count() }} criterios de evaluacion</p>
                        </div>

                        @if(auth()->user()->can('gestionarCurriculum'))
                        <div class="flex-1 min-w-0" x-show="editRa" x-cloak>
                            <form method="POST" action="{{ route('admin.curriculum.ra.update', $ra) }}">
                                @csrf @method('PUT')
                                <div class="space-y-2">
                                    <input type="text" name="codigo" value="{{ $ra->codigo }}" required maxlength="10"
                                           class="w-full px-2 py-1.5 text-xs font-mono border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-1 focus:ring-primary-500">
                                    <textarea name="descripcion" required rows="3"
                                              class="w-full px-2 py-1.5 text-sm border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-1 focus:ring-primary-500 resize-none">{{ $ra->descripcion }}</textarea>
                                    <div class="flex gap-2">
                                        <button type="submit" class="px-3 py-1 bg-primary-600 text-white text-xs rounded hover:bg-primary-700">Guardar</button>
                                        <button type="button" @click="editRa=false" class="px-3 py-1 text-slate-500 text-xs rounded hover:bg-slate-100 dark:hover:bg-slate-700">Cancelar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @endif

                        <div class="flex items-center gap-1 flex-shrink-0" x-show="!editRa">
                            <button @click="openCe = !openCe"
                                    class="p-1.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors"
                                    title="Ver criterios">
                                <svg class="w-4 h-4 transition-transform duration-150" :class="openCe ? 'rotate-90' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            @if(auth()->user()->can('gestionarCurriculum'))
                            <button @click="editRa=true"
                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                    title="Editar RA">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('admin.curriculum.ra.destroy', $ra) }}"
                                  onsubmit="return confirm('Eliminar el RA y sus criterios?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div x-show="openCe" x-cloak class="border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                    <div class="px-4 py-3 space-y-2">
                        @forelse($ra->criterios as $ce)
                        <div class="flex items-start gap-2 group">
                            <span class="flex-shrink-0 text-xs font-mono text-slate-400 mt-0.5 w-12">{{ $ce->codigo }}</span>
                            <span class="flex-1 text-xs text-slate-600 dark:text-slate-300" x-show="editCe !== {{ $ce->id }}">{{ $ce->descripcion }}</span>

                            @if(auth()->user()->can('gestionarCurriculum'))
                            <div class="flex-1" x-show="editCe === {{ $ce->id }}" x-cloak>
                                <form method="POST" action="{{ route('admin.curriculum.ce.update', $ce) }}">
                                    @csrf @method('PUT')
                                    <div class="space-y-1">
                                        <input type="text" name="codigo" value="{{ $ce->codigo }}" required maxlength="10"
                                               class="w-24 px-2 py-1 text-xs font-mono border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-1 focus:ring-primary-500">
                                        <textarea name="descripcion" required rows="2"
                                                  class="w-full px-2 py-1 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-1 focus:ring-primary-500 resize-none">{{ $ce->descripcion }}</textarea>
                                        <div class="flex gap-1">
                                            <button type="submit" class="px-2 py-0.5 bg-primary-600 text-white text-xs rounded hover:bg-primary-700">Guardar</button>
                                            <button type="button" @click="editCe=null" class="px-2 py-0.5 text-slate-500 text-xs rounded hover:bg-slate-100 dark:hover:bg-slate-700">Cancelar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                                 x-show="editCe !== {{ $ce->id }}">
                                <button @click="editCe={{ $ce->id }}" class="p-1 text-slate-300 hover:text-blue-500 rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.curriculum.ce.destroy', $ce) }}"
                                      onsubmit="return confirm('Eliminar el criterio?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-300 hover:text-red-500 rounded">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 italic">Sin criterios de evaluacion.</p>
                        @endforelse

                        @if(auth()->user()->can('gestionarCurriculum'))
                        <form method="POST" action="{{ route('admin.curriculum.ce.store', $ra) }}"
                              class="flex items-start gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                            @csrf
                            <input type="text" name="codigo" placeholder="Cod." required maxlength="10"
                                   class="w-20 px-2 py-1.5 text-xs font-mono border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-1 focus:ring-primary-500">
                            <input type="text" name="descripcion" placeholder="Descripcion del criterio..." required
                                   class="flex-1 px-2 py-1.5 text-xs border border-slate-200 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-1 focus:ring-primary-500">
                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs rounded transition-colors flex-shrink-0">
                                + CE
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-10 text-slate-400 text-sm">No hay resultados de aprendizaje en este modulo.</div>
            @endforelse
        </div>

        @if(auth()->user()->can('gestionarCurriculum'))
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 sticky top-24">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-white mb-4">Anadir resultado de aprendizaje</h2>
                <form method="POST" action="{{ route('admin.curriculum.ra.store', $modulo) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Codigo *</label>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" required maxlength="10"
                               class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Ej: RA1">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Descripcion *</label>
                        <textarea name="descripcion" required rows="4"
                                  class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                                  placeholder="Descripcion del resultado de aprendizaje...">{{ old('descripcion') }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Anadir RA
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
