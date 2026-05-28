@extends('layouts.app')

@section('title', 'Ciclos Formativos')

@section('content')
<div class="space-y-6">

    @include('admin.partials.nav')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Ciclos Formativos</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Gestión de títulos del centro</p>
        </div>
        <a href="{{ route('admin.ciclos.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Ciclo
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-700">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Código</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Nombre</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Nivel</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Empresas</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Estado</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($ciclos as $ciclo)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-sm font-bold
                            @if($ciclo->nivel === 'basica') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400
                            @elseif($ciclo->nivel === 'media') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                            @else bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 @endif">
                            {{ $ciclo->codigo }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-800 dark:text-slate-200 font-medium">{{ $ciclo->nombre }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $ciclo->nombre_nivel }}</td>
                    <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">{{ $ciclo->empresas_count }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($ciclo->activo)
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Activo</span>
                        @else
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.ciclos.edit', $ciclo) }}" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($ciclo->empresas_count === 0 && $ciclo->colocaciones_count === 0)
                            <form action="{{ route('admin.ciclos.destroy', $ciclo) }}" method="POST" onsubmit="return confirm('¿Eliminar ciclo?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No hay ciclos formativos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
