@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="space-y-6">
    
    @include('admin.partials.nav')
    
    <!-- Cabecera -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Gestión de Usuarios</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $usuarios->total() }} usuarios registrados</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o email..."
                       class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
            </div>
            <select name="rol" class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                <option value="">Todos los roles</option>
                <option value="admin" {{ request('rol') == 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="responsable_ffe" {{ request('rol') == 'responsable_ffe' ? 'selected' : '' }}>Responsable FFE</option>
                <option value="responsable_ciclo" {{ request('rol') == 'responsable_ciclo' ? 'selected' : '' }}>Responsable de Ciclo</option>
                <option value="profesor" {{ request('rol') == 'profesor' ? 'selected' : '' }}>Profesor</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600">Filtrar</button>
            @if(request()->hasAny(['buscar', 'rol']))
            <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2.5 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white">Limpiar</a>
            @endif
        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        @if($usuarios->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Rol</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Ciclo</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($usuarios as $usuario)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 {{ !$usuario->activo ? 'opacity-50' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold text-sm
 				    @if($usuario->rol === 'admin') bg-gradient-to-br from-red-500 to-red-600
				    @elseif($usuario->rol === 'responsable_ffe') bg-gradient-to-br from-teal-500 to-teal-600
				    @elseif($usuario->rol === 'responsable_ciclo') bg-gradient-to-br from-amber-500 to-amber-600
                                    @else bg-gradient-to-br from-primary-500 to-primary-600 @endif">
                                    {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800 dark:text-white">{{ $usuario->nombre }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $usuario->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
			    @php
                                $rolColor = match($usuario->rol) {
                                    'admin' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                    'responsable_ffe' => 'bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400',
                                    'responsable_ciclo' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                    default => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold {{ $rolColor }}">{{ $usuario->nombre_rol }}</span>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            @if($usuario->ciclo)
                            <span class="text-sm text-slate-700 dark:text-slate-300"><span class="font-medium">{{ $usuario->ciclo->codigo }}</span> - {{ $usuario->ciclo->nombre }}</span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($usuario->activo)
                            <span class="inline-flex items-center gap-1 text-green-600"><span class="w-2 h-2 bg-green-500 rounded-full"></span><span class="text-xs font-medium">Activo</span></span>
                            @else
                            <span class="inline-flex items-center gap-1 text-slate-400"><span class="w-2 h-2 bg-slate-300 rounded-full"></span><span class="text-xs font-medium">Inactivo</span></span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($usuarios->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">{{ $usuarios->links() }}</div>
        @endif
        @else
        <div class="p-12 text-center">
            <p class="text-slate-500 dark:text-slate-400">No se encontraron usuarios</p>
        </div>
        @endif
    </div>
</div>
@endsection
