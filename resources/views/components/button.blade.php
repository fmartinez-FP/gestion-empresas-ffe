@props([
    'type' => 'submit',
    'color' => 'primary',
    'size' => 'md',
    'loading' => false,
])

@php
    $colorClasses = match($color) {
        'primary' => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-lg shadow-primary-500/20',
        'green' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-lg shadow-green-500/20',
        'red' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-500',
        'outline' => 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 focus:ring-primary-500',
        default => 'bg-slate-600 text-white hover:bg-slate-700 focus:ring-slate-500',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2.5 text-sm',
    };
@endphp

<button
    type="{{ $type }}"
    x-data="{ loading: {{ $loading ? 'true' : 'false' }} }"
    x-on:click="loading = true"
    x-bind:disabled="loading"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 font-medium rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed {$colorClasses} {$sizeClasses}"]) }}
>
    <!-- Spinner de carga -->
    <svg 
        x-show="loading" 
        class="animate-spin h-4 w-4" 
        fill="none" 
        viewBox="0 0 24 24"
    >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    
    <span x-show="!loading">{{ $slot }}</span>
    <span x-show="loading">Procesando...</span>
</button>
