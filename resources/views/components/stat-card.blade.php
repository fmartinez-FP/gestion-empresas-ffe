@props([
    'titulo',
    'valor',
    'color' => 'primary',
    'icono' => null,
])

@php
    $colorClasses = match($color) {
        'green' => ['bg' => 'bg-green-100', 'icon' => 'text-green-600', 'value' => 'text-green-600'],
        'yellow' => ['bg' => 'bg-yellow-100', 'icon' => 'text-yellow-600', 'value' => 'text-yellow-600'],
        'red' => ['bg' => 'bg-red-100', 'icon' => 'text-red-600', 'value' => 'text-red-600'],
        default => ['bg' => 'bg-primary-100', 'icon' => 'text-primary-600', 'value' => 'text-slate-800'],
    };
@endphp

<div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">{{ $titulo }}</p>
            <p class="text-3xl font-bold {{ $colorClasses['value'] }} mt-1">{{ $valor }}</p>
        </div>
        @if($icono)
        <div class="w-12 h-12 {{ $colorClasses['bg'] }} rounded-xl flex items-center justify-center">
            {!! $icono !!}
        </div>
        @endif
    </div>
    @if($slot->isNotEmpty())
    <div class="mt-3 pt-3 border-t border-slate-100">
        {{ $slot }}
    </div>
    @endif
</div>
