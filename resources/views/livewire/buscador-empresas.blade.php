<div class="space-y-6">
    
    <!-- Filtros -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Búsqueda -->
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="buscar"
                        placeholder="Buscar por nombre, CIF, contacto, profesor..."
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                    @if($buscar)
                    <button wire:click="$set('buscar', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Filtro ciclo -->
            <div class="w-full lg:w-52">
                <select wire:model.live="ciclo" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Todos los ciclos</option>
                    @foreach($ciclos as $c)
                        <option value="{{ $c->id }}">{{ $c->codigo }} - {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro curso (solo si hay ciclo seleccionado) -->
            @if($ciclo)
            <div class="w-full lg:w-36">
                <select wire:model.live="curso" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">1º y 2º</option>
                    <option value="1">Solo 1º</option>
                    <option value="2">Solo 2º</option>
                </select>
            </div>
            @endif

            <!-- Filtro estado -->
            <div class="w-full lg:w-48">
                <select wire:model.live="estado" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">Todos ({{ $contadores['total'] }})</option>
                    <option value="activo">✓ Activos ({{ $contadores['activo'] }})</option>
                    <option value="por_caducar">⚠ Por caducar ({{ $contadores['por_caducar'] }})</option>
                    <option value="caducado">✕ Caducados ({{ $contadores['caducado'] }})</option>
                </select>
            </div>

            <!-- Limpiar -->
            @if($buscar || $ciclo || $estado)
            <button wire:click="limpiarFiltros" class="px-4 py-2.5 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                Limpiar
            </button>
            @endif

            <!-- Exportar Excel -->
            <a href="{{ route('export.empresas.excel', ['buscar' => $buscar, 'ciclo' => $ciclo, 'estado' => $estado]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel
            </a>
        </div>
    </div>

    <!-- Indicador de carga -->
    <div wire:loading class="flex items-center justify-center py-4">
        <svg class="animate-spin h-6 w-6 text-primary-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="ml-2 text-slate-500">Buscando...</span>
    </div>

    <!-- Tabla de empresas -->
    <div wire:loading.remove class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        @if($empresas->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                    <tr>
                        <th class="px-6 py-4 text-left">
                            <button wire:click="ordenar('nombre')" class="flex items-center gap-1 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hover:text-slate-700 dark:hover:text-white">
                                Empresa
                                @if($ordenarPor === 'nombre')
                                <svg class="w-4 h-4 {{ $ordenDir === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Ciclos</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Responsable</th>
                        <th class="px-6 py-4 text-left">
                            <button wire:click="ordenar('estado')" class="flex items-center gap-1 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hover:text-slate-700 dark:hover:text-white">
                                Estado
                                @if($ordenarPor === 'estado')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($empresas as $empresa)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors" wire:key="empresa-{{ $empresa->id }}">
                        <td class="px-6 py-4">
                            <div>
                                <a href="{{ route('empresas.show', $empresa) }}" class="font-medium text-slate-800 dark:text-white hover:text-primary-600">
                                    {{ $empresa->nombre }}
                                </a>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $empresa->cif }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <div class="flex flex-wrap gap-1">
                                @forelse($empresa->ciclos->take(4) as $ciclo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                    @if($ciclo->nivel === 'basica') bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400
                                    @elseif($ciclo->nivel === 'media') bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400
                                    @else bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 @endif">
                                    {{ $ciclo->codigo }}
                                </span>
                                @empty
                                <span class="text-sm text-slate-400">—</span>
                                @endforelse
                                @if($empresa->ciclos->count() > 4)
                                <span class="text-xs text-slate-400">+{{ $empresa->ciclos->count() - 4 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <span class="text-sm text-slate-600 dark:text-slate-400">{{ $empresa->creador->nombre ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $colorClass = match($empresa->estado_convenio) {
                                    'activo' => 'status-green bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                                    'por_caducar' => 'status-yellow bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                                    'caducado' => 'status-red bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                    default => 'status-gray bg-slate-600 dark:bg-slate-600 text-slate-600 dark:text-slate-300',
                                };
                            @endphp
                            <span class="status-badge {{ $colorClass }}">
                                {{ $empresa->estado_etiqueta }}
                            </span>
                            @if($empresa->fecha_vencimiento && $empresa->estado_convenio !== 'sin_convenio')
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $empresa->fecha_vencimiento->format('d/m/Y') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('empresas.show', $empresa) }}" 
                                   class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors"
                                   title="Ver detalle">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->can('update', $empresa))
                                <a href="{{ route('empresas.edit', $empresa) }}" 
                                   class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg transition-colors"
                                   title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($empresas->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $empresas->links() }}
        </div>
        @endif

        @else
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-white mb-1">No se encontraron empresas</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-4">
                @if($buscar || $ciclo || $estado)
                    Prueba con otros filtros de búsqueda
                @else
                    Comienza añadiendo tu primera empresa
                @endif
            </p>
            @if(!$buscar && !$ciclo && !$estado)
            <a href="{{ route('empresas.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Empresa
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
