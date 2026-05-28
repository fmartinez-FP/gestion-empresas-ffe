@extends('layouts.app')

@section('title', $empresa->nombre)

@section('content')
<div class="space-y-6">
    
    <!-- Cabecera -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div class="flex items-start gap-4">
            <a href="{{ route('empresas.index') }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors mt-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $empresa->nombre }}</h1>
                    @php
                        $colorClass = match($empresa->estado_convenio) {
                            'activo' => 'status-green bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                            'por_caducar' => 'status-yellow bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                            'caducado' => 'status-red bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                            default => 'status-gray bg-slate-600 dark:bg-slate-600 text-slate-300',
                        };
                    @endphp
                    <span class="status-badge {{ $colorClass }}">
                        {{ $empresa->estado_etiqueta }}
                    </span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 mt-1">CIF: {{ $empresa->cif }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('export.empresa.pdf', $empresa) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition-colors"
               title="Descargar ficha PDF">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                PDF
            </a>
            
            @if(auth()->user()->can('colocar', $empresa))
            <a href="{{ route('colocaciones.create', $empresa) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Asignar Alumnos
            </a>
            @endif
            
            @if(auth()->user()->can('update', $empresa))
            <a href="{{ route('empresas.edit', $empresa) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna principal -->
        <div class="lg:col-span-2 space-y-6">
            


            {{-- Personas de Contacto --}}
            @can('verPersonasContacto', $empresa)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Personas de Contacto
                    </h2>
                    <a href="{{ route('personas-contacto.create', $empresa) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir
                    </a>
                </div>

                @if($empresa->personasContacto->count() > 0)
                <div class="space-y-3">
                    @foreach($empresa->personasContacto as $persona)
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-slate-800 dark:text-white">{{ $persona->nombre }}</span>
                                    @if($persona->principal)
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">Principal</span>
                                    @endif
                                </div>
                                @if($persona->cargo)
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $persona->cargo }}</p>
                                @endif
                                <div class="flex flex-wrap gap-4 mt-2">
                                    @if($persona->telefono)
                                    <a href="tel:{{ $persona->telefono }}" class="text-sm text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $persona->telefono }}
                                    </a>
                                    @endif
                                    @if($persona->email)
                                    <a href="mailto:{{ $persona->email }}" class="text-sm text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ $persona->email }}
                                    </a>
                                    @endif
                                </div>
                                @if($persona->notas)
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">{{ $persona->notas }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <a href="{{ route('personas-contacto.edit', [$empresa, $persona]) }}"
                                   class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('personas-contacto.destroy', [$empresa, $persona]) }}"
                                      x-data @submit.prevent="if(confirm('¿Eliminar esta persona de contacto?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6">
                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400">No hay personas de contacto registradas</p>
                    <a href="{{ route('personas-contacto.create', $empresa) }}" class="inline-block mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Añadir primera persona →
                    </a>
                </div>
                @endif
            </div>
            @endcan

                        <!-- Ciclos que acepta -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Ciclos Formativos</h2>
                
                @if($empresa->ciclos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="text-left py-2 text-sm font-medium text-slate-500 dark:text-slate-400">Ciclo</th>
                                <th class="text-center py-2 text-sm font-medium text-slate-500 w-20">1º</th>
                                <th class="text-center py-2 text-sm font-medium text-slate-500 w-20">2º</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($empresa->ciclos as $ciclo)
                            <tr>
                                <td class="py-3">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold @if($ciclo->nivel === 'basica') bg-orange-100 text-orange-700 @elseif($ciclo->nivel === 'media') bg-blue-100 text-blue-700 @else bg-purple-100 text-purple-700 @endif">{{ $ciclo->codigo }}</span>
                                        <span class="text-slate-700 dark:text-slate-300">{{ $ciclo->nombre }}</span>
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    @if($ciclo->pivot->acepta_primero)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                    <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    @if($ciclo->pivot->acepta_segundo)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                    <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-slate-400 dark:text-slate-500 italic">No hay ciclos asignados</p>
                @endif
            </div>

            <!-- Histórico de colocaciones -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-800">Histórico de Asignaciones</h2>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $empresa->colocaciones->count() }} registros</span>
                </div>
                
                @if($empresa->colocaciones->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="text-left py-2 text-sm font-medium text-slate-500 dark:text-slate-400">Curso</th>
                                <th class="text-left py-2 text-sm font-medium text-slate-500 dark:text-slate-400">Ciclo</th>
                                <th class="text-center py-2 text-sm font-medium text-slate-500 dark:text-slate-400">Nº</th>
                                <th class="text-right py-2 text-sm font-medium text-slate-500 dark:text-slate-400">Alumnos</th>
                                <th class="text-right py-2 text-sm font-medium text-slate-500 dark:text-slate-400">Horas</th>
                                <th class="w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
			    @foreach($empresa->colocaciones->sortByDesc('curso_academico') as $colocacion)
<tr>
    <td class="py-3 text-slate-800 dark:text-white">{{ $colocacion->curso_academico }}</td>
    <td class="py-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-300">{{ $colocacion->ciclo->codigo }}</span></td>
    <td class="py-3 text-center text-slate-600 dark:text-slate-400">{{ $colocacion->numero_curso }}º</td>
    <td class="py-3 text-right font-medium text-slate-800 dark:text-white">{{ $colocacion->num_alumnos }}</td>
    <td class="py-3 text-right text-slate-600 dark:text-slate-400">{{ number_format($colocacion->num_horas) }}h</td>
    @if(auth()->user()->esAdmin() || $colocacion->registrado_por_id === auth()->id())
    <td class="py-3 text-right">
        <div class="flex items-center justify-end gap-1">
            <a href="{{ route('colocaciones.edit', $colocacion) }}" class="p-1 text-slate-400 hover:text-blue-600 transition-colors" title="Editar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            <form method="POST" action="{{ route('colocaciones.destroy', $colocacion) }}" onsubmit="return confirm('¿Eliminar esta asignación?')" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition-colors" title="Eliminar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </td>
    @else
    <td></td>
    @endif
</tr>
@endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-slate-500 dark:text-slate-400 mb-3">No hay asignaciones registradas</p>
                    @if(auth()->user()->can('colocar', $empresa))
                    <a href="{{ route('colocaciones.create', $empresa) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Registrar primera asignación →</a>
                    @endif
                </div>
                @endif
            </div>


            <!-- Historial de Contactos -->
            @php
                $user = auth()->user();
                $esCreador = $user->id === $empresa->creador_id;
                $puedeVerContactos = $esCreador || $user->esAdmin() || $user->esResponsableFFE() || 
                    ($user->esResponsableCiclo() && $empresa->ciclos->pluck('id')->intersect($user->ciclos->pluck('id'))->isNotEmpty());
            @endphp
            
            @if($puedeVerContactos)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Historial de Contactos
                    </h3>
                    @if($esCreador)
                    <a href="{{ route('contactos.create', $empresa) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo Contacto
                    </a>
                    @endif
                </div>

                @if($empresa->contactos->count() > 0)
                <div class="space-y-4">
                    @foreach($empresa->contactos as $contacto)
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <!-- Icono tipo -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    @if($contacto->tipo == 'llamada') bg-blue-100 dark:bg-blue-900/30 text-blue-600
                                    @elseif($contacto->tipo == 'email') bg-purple-100 dark:bg-purple-900/30 text-purple-600
                                    @elseif($contacto->tipo == 'visita') bg-green-100 dark:bg-green-900/30 text-green-600
                                    @elseif($contacto->tipo == 'reunion_online') bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600
                                    @else bg-slate-100 dark:bg-slate-700 text-slate-600
                                    @endif">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $contacto->tipo_icono }}"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-slate-800 dark:text-white">{{ $contacto->tipo_etiqueta }}</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium
                                            @if($contacto->resultado == 'exitoso') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                            @elseif($contacto->resultado == 'sin_respuesta') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400
                                            @elseif($contacto->resultado == 'pendiente') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400
                                            @else bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400
                                            @endif">
                                            {{ $contacto->resultado_etiqueta }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                        {{ $contacto->fecha_contacto->format('d/m/Y H:i') }}
                                        @if($contacto->persona_contacto)
                                            · con <span class="text-slate-700 dark:text-slate-300">{{ $contacto->persona_contacto }}</span>
                                        @endif
                                    </p>
                                    @if($contacto->notas)
                                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-2">{{ Str::limit($contacto->notas, 200) }}</p>
                                    @endif
                                    @if($contacto->fecha_seguimiento)
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-2 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Seguimiento: {{ $contacto->fecha_seguimiento->format('d/m/Y H:i') }}
                                    </p>
                                    @endif
                                    @if($contacto->archivo_adjunto)
                                    <a href="{{ route('contactos.archivo', [$empresa, $contacto]) }}" 
                                       class="inline-flex items-center gap-1 text-xs text-primary-600 hover:underline mt-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        {{ $contacto->archivo_nombre }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @if($esCreador)
                            <div class="flex items-center gap-2">
                                <a href="{{ route('contactos.edit', [$empresa, $contacto]) }}" 
                                   class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('contactos.destroy', [$empresa, $contacto]) }}" 
                                      x-data @submit.prevent="if(confirm('¿Eliminar este contacto?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400">No hay contactos registrados</p>
                    @if($esCreador)
                    <a href="{{ route('contactos.create', $empresa) }}" class="inline-block mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Registrar primer contacto →
                    </a>
                    @endif
                </div>
                @endif
            </div>
            @endif

            @livewire('gestor-direcciones', ['empresa' => $empresa])

            {{-- Historial de Auditoría --}}
            @can('verAuditoria', $empresa)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Historial de Auditoría
                    </h3>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $auditorias->count() }} registros</span>
                </div>

                @if($auditorias->count() > 0)
                <div class="space-y-3">
                    @foreach($auditorias as $entrada)
                    @php
                        $colorClasses = match($entrada->accion) {
                            'crear'      => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                            'actualizar' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                            'eliminar'   => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                            'asignacion' => 'bg-slate-100 dark:bg-slate-600 text-slate-700 dark:text-slate-300',
                            default      => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
                        };
                    @endphp
                    <div class="flex gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                        <div class="flex-shrink-0 pt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                {{ $entrada->accion_etiqueta }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 dark:text-slate-300">{{ $entrada->descripcion }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                {{ $entrada->user_nombre }} · {{ $entrada->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6">
                    <p class="text-slate-400 dark:text-slate-500 italic">No hay registros de auditoría para esta empresa</p>
                </div>
                @endif
            </div>
            @endcan

        </div>

        <!-- Columna lateral -->
        <div class="space-y-6">

            <!-- Valoración de la Empresa -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Valoración
                    </h3>
                    @if(auth()->id() === $empresa->creador_id)
                    <a href="{{ route('valoraciones.form', $empresa) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($empresa->valoracion)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            @endif
                        </svg>
                        {{ $empresa->valoracion ? 'Editar' : 'Valorar' }}
                    </a>
                    @endif
                </div>

                @if($empresa->valoracion)
                @php $valoracion = $empresa->valoracion; $criterios = \App\Models\Valoracion::getCriterios(); @endphp
                
                <!-- Media general -->
                <div class="flex items-center gap-3 mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                    <div class="text-3xl font-bold text-yellow-600">{{ number_format($valoracion->media, 1) }}</div>
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($valoracion->media) ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300 dark:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        @endfor
                    </div>
                    <span class="text-sm text-slate-500 dark:text-slate-400">Puntuación media</span>
                </div>

                <!-- Criterios detallados -->
                <div class="space-y-3">
                    @foreach($criterios as $campo => $criterio)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $criterio['nombre'] }}</span>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $valoracion->$campo ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300 dark:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($valoracion->observaciones)
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Observaciones:</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ $valoracion->observaciones }}</p>
                </div>
                @endif

                <p class="text-xs text-slate-400 dark:text-slate-500 mt-4">
                    Valorado por {{ $valoracion->valoradoPor->nombre }} · {{ $valoracion->updated_at->format('d/m/Y') }}
                </p>

                @else
                <div class="text-center py-6">
                    <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400">Esta empresa aún no ha sido valorada</p>
                    @if(auth()->id() === $empresa->creador_id)
                    <a href="{{ route('valoraciones.form', $empresa) }}" class="inline-block mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Añadir valoración →
                    </a>
                    @endif
                </div>
                @endif
            </div>
            

            {{-- Historial de valoraciones por curso --}}
            @if($empresa->valoraciones->count() > 1)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historial de Valoraciones
                </h3>
                <div class="space-y-2">
                    @foreach($empresa->valoraciones as $v)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                        <div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $v->curso_academico }}</span>
                            <p class="text-xs text-slate-400">{{ $v->valoradoPor->nombre ?? '—' }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-sm font-bold text-yellow-600">{{ number_format($v->media, 1) }}</span>
                            <svg class="w-4 h-4 text-yellow-400 fill-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Datos del convenio -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Convenio</h2>
                <div class="space-y-4">
                    @if($empresa->num_convenio)<div><p class="text-sm text-slate-500 dark:text-slate-400">Número</p><p class="font-medium text-slate-800 dark:text-white">{{ $empresa->num_convenio }}</p></div>@endif
                    @if($empresa->fecha_firma)
                    <div><p class="text-sm text-slate-500 dark:text-slate-400">Fecha de firma</p><p class="font-medium text-slate-800 dark:text-white">{{ $empresa->fecha_firma->format('d/m/Y') }}</p></div>
                    <div><p class="text-sm text-slate-500 dark:text-slate-400">Vigencia hasta</p><p class="font-medium text-slate-800 dark:text-white">{{ $empresa->fecha_vencimiento->format('d/m/Y') }}</p></div>
                    @if($empresa->dias_hasta_vencimiento !== null)
                    <div><p class="text-sm text-slate-500 dark:text-slate-400">Tiempo restante</p>
                    <p class="font-medium {{ $empresa->dias_hasta_vencimiento < 0 ? 'text-red-600' : ($empresa->dias_hasta_vencimiento < 180 ? 'text-yellow-600' : 'text-green-600') }}">
                        @if($empresa->dias_hasta_vencimiento < 0) Caducado hace {{ abs($empresa->dias_hasta_vencimiento) }} días
                        @elseif($empresa->dias_hasta_vencimiento === 0) Caduca hoy
                        @else {{ $empresa->dias_hasta_vencimiento }} días @endif
                    </p></div>
                    @endif
                    
                    <!-- Botón Renovar Convenio -->
                    @if(auth()->user()->can('update', $empresa) && ($empresa->estado_convenio === 'por_caducar' || $empresa->estado_convenio === 'caducado'))
                    <div class="pt-2 border-t border-slate-200">
                        <form method="POST" action="{{ route('empresas.renovar', $empresa) }}" 
                              x-data
                              @submit.prevent="if(confirm('¿Renovar el convenio? La fecha de firma se actualizará a hoy.')) $el.submit()">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Renovar Convenio
                            </button>
                        </form>
                    </div>
                    @endif
                    
                    @else<p class="text-slate-400 dark:text-slate-500 italic">Sin datos de convenio</p>@endif
                </div>
            </div>

            <!-- Profesor responsable -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Profesor Responsable</h2>
                @if($empresa->creador)
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold">{{ strtoupper(substr($empresa->creador->nombre, 0, 1)) }}</div>
                    <div><p class="font-medium text-slate-800 dark:text-white">{{ $empresa->creador->nombre }}</p><p class="text-sm text-slate-500 dark:text-slate-400">{{ $empresa->creador->email }}</p></div>
                </div>
                @else<p class="text-slate-400 dark:text-slate-500 italic">Sin asignar</p>@endif
            </div>

            @if($empresa->notas)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Notas</h2>
                <p class="text-slate-600 dark:text-slate-400 whitespace-pre-line">{{ $empresa->notas }}</p>
            </div>
            @endif

            <!-- Acciones rápidas -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Acciones</h2>
                <div class="space-y-2">
                    <form method="POST" action="{{ route('empresas.duplicar', $empresa) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Duplicar empresa
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-700 rounded-2xl p-4">
                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                    <p>Creada: {{ $empresa->created_at->format('d/m/Y H:i') }}</p>
                    <p>Actualizada: {{ $empresa->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            @admin
            <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl p-6 border border-red-200 dark:border-red-800">
                <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-3">Zona de peligro</h3>
                <form method="POST" action="{{ route('empresas.destroy', $empresa) }}" 
                      x-data 
                      @submit.prevent="if(confirm('¿Estás seguro de eliminar esta empresa? Esta acción no se puede deshacer.')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                        Eliminar empresa
                    </button>
                </form>
            </div>
            @endadmin
        </div>
    </div>
</div>
@endsection
