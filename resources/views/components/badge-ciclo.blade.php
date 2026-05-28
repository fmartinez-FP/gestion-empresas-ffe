@props(['ciclo', 'showName' => false])

@php
    $colorClass = match($ciclo->nivel) {
        'basica' => 'bg-orange-100 text-orange-700',
        'media' => 'bg-blue-100 text-blue-700',
        'superior' => 'bg-purple-100 text-purple-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {$colorClass}"]) }}>
    {{ $ciclo->codigo }}
    @if($showName)
        <span class="font-normal ml-1">{{ $ciclo->nombre }}</span>
    @endif
</span>
