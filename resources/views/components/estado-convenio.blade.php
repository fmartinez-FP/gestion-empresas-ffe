@props(['estado'])

@php
    $config = match($estado) {
        'activo' => [
            'bg' => 'bg-green-50',
            'text' => 'text-green-700',
            'dot' => 'bg-green-500',
            'label' => 'Activo'
        ],
        'por_caducar' => [
            'bg' => 'bg-yellow-50',
            'text' => 'text-yellow-700',
            'dot' => 'bg-yellow-500',
            'label' => 'Por caducar'
        ],
        'caducado' => [
            'bg' => 'bg-red-50',
            'text' => 'text-red-700',
            'dot' => 'bg-red-500',
            'label' => 'Caducado'
        ],
        default => [
            'bg' => 'bg-slate-50',
            'text' => 'text-slate-600',
            'dot' => 'bg-slate-400',
            'label' => 'Sin convenio'
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {$config['bg']} {$config['text']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
    {{ $config['label'] }}
</span>
