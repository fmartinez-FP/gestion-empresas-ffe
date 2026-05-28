@props([
    'id',
    'title' => '¿Estás seguro?',
    'message' => 'Esta acción no se puede deshacer.',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'confirmColor' => 'red',
])

@php
    $buttonColors = match($confirmColor) {
        'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        'blue' => 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500',
        default => 'bg-slate-600 hover:bg-slate-700 focus:ring-slate-500',
    };
@endphp

<div 
    x-data="{ open: false }"
    x-on:open-modal-{{ $id }}.window="open = true"
    x-on:close-modal-{{ $id }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-cloak
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50"
        @click="open = false"
    ></div>

    <!-- Modal -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="open = false"
    >
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.stop>
            <!-- Icono -->
            <div class="w-12 h-12 rounded-full bg-{{ $confirmColor }}-100 flex items-center justify-center mx-auto mb-4">
                @if($confirmColor === 'red')
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                @else
                <svg class="w-6 h-6 text-{{ $confirmColor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                @endif
            </div>

            <!-- Contenido -->
            <h3 class="text-lg font-semibold text-slate-900 text-center mb-2">{{ $title }}</h3>
            <p class="text-slate-500 text-center mb-6">{{ $message }}</p>

            <!-- Botones -->
            <div class="flex gap-3">
                <button 
                    type="button"
                    @click="open = false"
                    class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-medium hover:bg-slate-200 transition-colors"
                >
                    {{ $cancelText }}
                </button>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
