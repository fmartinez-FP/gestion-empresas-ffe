<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Sedes / Direcciones
        </h3>
        @if($puedeEditar && !$mostrarFormulario)
        <button wire:click="agregar"
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Añadir sede
        </button>
        @endif
    </div>

    @if(session('sede_success'))
    <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl text-sm text-green-700 dark:text-green-400">
        {{ session('success') }}
    </div>
    @endif
    @if(session('sede_warning'))
    <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-xl text-sm text-yellow-700 dark:text-yellow-400">
        {{ session('warning') }}
    </div>
    @endif

    {{-- Lista de direcciones --}}
    @if($direcciones->count() > 0)
    <div class="space-y-3 mb-4">
        @foreach($direcciones as $dir)
        <div class="flex items-start justify-between gap-3 p-3 rounded-xl border
            {{ $dir->principal ? 'border-primary-200 dark:border-primary-700 bg-primary-50/50 dark:bg-primary-900/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30' }}">
            <div class="flex items-start gap-3">
                <div class="mt-0.5">
                    @if($dir->isGeocodificada())
                    <span title="Geocodificada" class="w-5 h-5 text-green-500 block">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    @else
                    <span title="Sin geocodificar" class="w-5 h-5 text-slate-300 dark:text-slate-600 block">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ $dir->formato_completo }}
                        @if($dir->principal)
                        <span class="ml-2 text-xs bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-400 px-1.5 py-0.5 rounded">Principal</span>
                        @endif
                    </p>
                    @if(!$dir->isGeocodificada())
                    <p class="text-xs text-slate-400 mt-0.5">Sin localización en mapa</p>
                    @endif
                </div>
            </div>
            @if($puedeEditar)
            <div class="flex items-center gap-1 flex-shrink-0">
                @if(!$dir->principal)
                <button wire:click="marcarPrincipal({{ $dir->id }})" title="Marcar como principal"
                        class="p-1.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
                @endif
                <button wire:click="editar({{ $dir->id }})"
                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </button>
                <button wire:click="eliminar({{ $dir->id }})"
                        wire:confirm="¿Eliminar esta dirección?"
                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @else
    @if(!$mostrarFormulario)
    <p class="text-slate-400 dark:text-slate-500 italic text-sm mb-4">No hay direcciones registradas</p>
    @endif
    @endif

    {{-- Formulario añadir/editar --}}
    @if($mostrarFormulario)
    <div class="border border-primary-200 dark:border-primary-700 rounded-xl p-4 bg-primary-50/30 dark:bg-primary-900/10">
        <h4 class="text-sm font-semibold text-slate-700 dark:text-white mb-4">
            {{ $editandoId ? 'Editar dirección' : 'Nueva sede' }}
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Tipo de vía</label>
                <select wire:model="tipo_via"
                        class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                    <option value="">— Sin tipo —</option>
                    @foreach($tiposVia as $tipo)
                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Nombre de la vía <span class="text-red-500">*</span></label>
                <input wire:model="nombre_via" type="text" placeholder="Ejemplo: Gran Vía"
                       class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 @error('nombre_via') border-red-300 @enderror">
                @error('nombre_via')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Número</label>
                <input wire:model="numero" type="text" placeholder="12 bis"
                       class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Código Postal</label>
                <input wire:model="codigo_postal" type="text" placeholder="28001" maxlength="5"
                       class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500 @error('codigo_postal') border-red-300 @enderror">
                @error('codigo_postal')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Municipio</label>
                <input wire:model="municipio" type="text" placeholder="Madrid"
                       class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button wire:click="guardar" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Geocodificando...</span>
            </button>
            <button wire:click="cancelar"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                Cancelar
            </button>
        </div>
    </div>
    @endif
</div>
