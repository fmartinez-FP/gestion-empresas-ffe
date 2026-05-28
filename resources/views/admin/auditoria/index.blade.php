@extends('layouts.app')

@section('title', 'Auditoría')

@section('content')
<div class="space-y-6">

    @include('admin.partials.nav')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Historial de Cambios</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Registro de todas las acciones en el sistema</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.auditoria.export.excel', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel
            </a>
            <a href="{{ route('admin.auditoria.export.pdf', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <div class="grid grid-cols-2 md:grid-cols-7 gap-3">
            <select name="modelo" class="px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-sm">
                <option value="">Todos los modelos</option>
                @foreach($modelos as $m)<option value="{{ $m }}" {{ request('modelo') === $m ? 'selected' : '' }}>{{ $m }}</option>@endforeach
            </select>
            <select name="accion" class="px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-sm">
                <option value="">Todas las acciones</option>
                <option value="crear" {{ request('accion') === 'crear' ? 'selected' : '' }}>Creación</option>
                <option value="actualizar" {{ request('accion') === 'actualizar' ? 'selected' : '' }}>Actualización</option>
                <option value="eliminar" {{ request('accion') === 'eliminar' ? 'selected' : '' }}>Eliminación</option>
                <option value="acceso" {{ request('accion') === 'acceso' ? 'selected' : '' }}>Acceso</option>
		<option value="asignacion" {{ request('accion') === 'asignacion' ? 'selected' : '' }}>Asignación</option>
            </select>
            <select name="user_id" class="px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-sm">
                <option value="">Todos los usuarios</option>
                @foreach($usuarios as $id => $nombre)<option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $nombre }}</option>@endforeach
            </select>
            <input type="date" name="desde" value="{{ request('desde') }}" class="px-2 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-sm">
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="px-2 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-sm">
            <button type="submit" class="px-3 py-2 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700">Filtrar</button>
            <a href="{{ route('admin.auditoria.index') }}" class="px-3 py-2 bg-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-300 text-center">Limpiar</a>
        </div>
    </form>

    <!-- Tabla -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Acción</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">Descripción</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($registros as $r)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">{{ $r->user_nombre }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
			    @if($r->accion === 'crear') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
			    @elseif($r->accion === 'actualizar') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
  			    @elseif($r->accion === 'acceso') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
			    @elseif($r->accion === 'asignacion') bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300
  			    @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 @endif">
                            {{ $r->accion_etiqueta }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $r->descripcion }}</td>
                    <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-500 font-mono">{{ $r->ip }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">No hay registros de auditoría.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $registros->links() }}
</div>
@endsection
