@extends('layouts.app')

@section('title', 'Agenda de Seguimientos')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Agenda de Seguimientos</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Contactos con fecha de seguimiento programada</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-2">
        @foreach(['pendientes' => 'Pendientes', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'vencidos' => 'Vencidos'] as $key => $label)
        <a href="{{ route('seguimientos.index', ['periodo' => $key]) }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $periodo === $key ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        @if($seguimientos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Empresa</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase hidden md:table-cell">Notas</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Seguimiento</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase hidden lg:table-cell">Registrado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($seguimientos as $contacto)
                    @php
                        $dias = now()->startOfDay()->diffInDays($contacto->fecha_seguimiento->startOfDay(), false);
                        $colorDias = match(true) {
                            $dias < 0  => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            $dias <= 2 => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                            $dias <= 7 => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            default    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        };
                        $labelDias = match(true) {
                            $dias < 0  => abs($dias) . 'd vencido',
                            $dias === 0 => 'Hoy',
                            $dias === 1 => 'Mañana',
                            default    => $dias . ' días',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700">
                        <td class="px-6 py-4">
                            <a href="{{ route('empresas.show', $contacto->empresa) }}" class="font-medium text-slate-800 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                {{ $contacto->empresa->nombre }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600 dark:text-slate-400">{{ $contacto->tipo_etiqueta }}</span>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <p class="text-sm text-slate-500 dark:text-slate-400 truncate max-w-xs">{{ $contacto->notas ?: '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold {{ $colorDias }}">{{ $labelDias }}</span>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $contacto->fecha_seguimiento->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $contacto->registradoPor->nombre ?? '—' }}</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($seguimientos->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">{{ $seguimientos->links() }}</div>
        @endif
        @else
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium">
                @if($periodo === 'vencidos') No hay seguimientos vencidos
                @elseif($periodo === 'semana') No hay seguimientos esta semana
                @elseif($periodo === 'mes') No hay seguimientos este mes
                @else Todo al día. ¡Sin seguimientos pendientes!
                @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
