@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @include('partials.nav-alumnos')

    {{-- Breadcrumb --}}
    <div class="mb-1 text-sm text-gray-500">
        <a href="{{ route('alumnos.index') }}" class="hover:text-gray-700">Alumnos</a>
        <span class="mx-1">›</span>
        <a href="{{ route('alumnos.show', $asignacion->alumno) }}" class="hover:text-gray-700">{{ $asignacion->alumno->nombre_completo }}</a>
        <span class="mx-1">›</span>
        <a href="{{ route('asignaciones.show', $asignacion) }}" class="hover:text-gray-700">Asignación FFE</a>
        <span class="mx-1">›</span>
        <span>Plan de Formación</span>
    </div>

    <div class="flex items-center justify-between mt-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Configurar Plan de Formación</h1>
    </div>

    @if($datos)
    <div class="mb-5 flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Datos guardados de una generación anterior. Se conservarán hasta el <strong>{{ $datos->purgar_after->format('d/m/Y') }}</strong>.</span>
    </div>
    @else
    <div class="mb-5 flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Estos datos se guardarán durante <strong>10 días</strong> por si necesitas realizar alguna modificación sin tener que rellenarlos de nuevo.</span>
    </div>
    @endif

    <form method="POST" action="{{ route('documentos.plan-formativo.generar', $asignacion) }}">
        @csrf

        {{-- MEDIDAS DISCAPACIDAD --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Adaptaciones y autorizaciones</h2>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">¿Requiere medidas/adaptaciones extraordinarias por discapacidad?</p>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="medidas_discapacidad" value="1"
                               {{ old('medidas_discapacidad', $datos?->medidas_discapacidad ? '1' : '0') === '1' ? 'checked' : '' }}
                               x-on:change="medidas = true"
                               class="text-primary-600">
                        Sí
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="medidas_discapacidad" value="0"
                               {{ old('medidas_discapacidad', $datos?->medidas_discapacidad ? '1' : '0') !== '1' ? 'checked' : '' }}
                               x-on:change="medidas = false"
                               class="text-primary-600">
                        No
                    </label>
                </div>
            </div>

            <div x-data="{ medidas: {{ old('medidas_discapacidad', $datos?->medidas_discapacidad ? '1' : '0') === '1' ? 'true' : 'false' }} }"
                 x-show="medidas" class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Especificar medidas/adaptaciones:</label>
                <textarea name="medidas_discapacidad_detalle" rows="3"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500"
                          placeholder="Describe las medidas o adaptaciones necesarias...">{{ old('medidas_discapacidad_detalle', $datos?->medidas_discapacidad_detalle) }}</textarea>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">¿Requiere autorización extraordinaria?</p>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="autorizacion_extraordinaria" value="1"
                               {{ old('autorizacion_extraordinaria', $datos?->autorizacion_extraordinaria ? '1' : '0') === '1' ? 'checked' : '' }}
                               x-on:change="autorizacion = true"
                               class="text-primary-600">
                        Sí
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="autorizacion_extraordinaria" value="0"
                               {{ old('autorizacion_extraordinaria', $datos?->autorizacion_extraordinaria ? '1' : '0') !== '1' ? 'checked' : '' }}
                               x-on:change="autorizacion = false"
                               class="text-primary-600">
                        No
                    </label>
                </div>
            </div>

            <div x-data="{ autorizacion: {{ old('autorizacion_extraordinaria', $datos?->autorizacion_extraordinaria ? '1' : '0') === '1' ? 'true' : 'false' }} }"
                 x-show="autorizacion">
                <label class="block text-sm font-medium text-gray-700 mb-1">Indicar causa/s:</label>
                <textarea name="autorizacion_extraordinaria_detalle" rows="3"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500"
                          placeholder="Describe la causa de la autorización extraordinaria...">{{ old('autorizacion_extraordinaria_detalle', $datos?->autorizacion_extraordinaria_detalle) }}</textarea>
            </div>
        </div>

        {{-- INTERVALO --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Planificación</h2>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Intervalo de formación:</p>
                <div class="flex flex-wrap gap-4">
                    @foreach(App\Models\PlanFormativoDatos::intervalos() as $valor => $etiqueta)
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="radio" name="intervalo" value="{{ $valor }}"
                               {{ old('intervalo', $datos?->intervalo ?? 'diario') === $valor ? 'checked' : '' }}
                               class="text-primary-600">
                        {{ $etiqueta }}
                    </label>
                    @endforeach
                </div>
                @error('intervalo')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Periodos de formación (calendario y horario):</label>
                <textarea name="periodos" rows="4"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500"
                          placeholder="Ej: Periodo 1 — Del 01/02/2026 al 30/04/2026, de lunes a viernes de 9:00 a 14:00&#10;Periodo 2 — ...">{{ old('periodos', $datos?->periodos) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones:</label>
                <textarea name="observaciones" rows="3"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500"
                          placeholder="Observaciones adicionales sobre la planificación...">{{ old('observaciones', $datos?->observaciones) }}</textarea>
            </div>
        </div>

        {{-- IMPARTICION POR MÓDULO --}}
        @if($asignacion->resultadosAprendizaje->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Impartición por módulo/RA</h2>
            <p class="text-xs text-gray-400 mb-4">Indica si cada resultado de aprendizaje se imparte íntegramente en la empresa o de forma compartida con el centro.</p>

            @php
                $raAgrupados = $asignacion->resultadosAprendizaje->groupBy(fn($ra) => optional($ra->modulo)->nombre ?? 'Sin módulo');
                $imparticionGuardada = $datos?->imparticion_modulos ?? [];
                $imparticionMap = collect($imparticionGuardada)->keyBy('ra_id');
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-2 text-left font-medium text-gray-500">Módulo / RA</th>
                            <th class="px-3 py-2 text-center font-medium text-gray-500 w-36">Íntegramente en empresa</th>
                            <th class="px-3 py-2 text-center font-medium text-gray-500 w-36">Compartida con centro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($raAgrupados as $moduloNombre => $ras)
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase">{{ $moduloNombre }}</td>
                        </tr>
                        @foreach($ras as $ra)
                        @php $valorGuardado = $imparticionMap->get($ra->id)['tipo'] ?? 'compartida'; @endphp
                        <tr>
                            <td class="px-3 py-2 text-gray-700">{{ $ra->codigo }} — {{ $ra->nombre }}</td>
                            <td class="px-3 py-2 text-center">
                                <input type="radio"
                                       name="imparticion_modulos[{{ $ra->id }}]"
                                       value="integra"
                                       {{ old('imparticion_modulos.' . $ra->id, $valorGuardado) === 'integra' ? 'checked' : '' }}
                                       class="text-primary-600">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="radio"
                                       name="imparticion_modulos[{{ $ra->id }}]"
                                       value="compartida"
                                       {{ old('imparticion_modulos.' . $ra->id, $valorGuardado) === 'compartida' ? 'checked' : '' }}
                                       class="text-primary-600">
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- FORMACIONES ESPECÍFICAS --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Formaciones específicas</h2>
            <p class="text-xs text-gray-400 mb-3">Formaciones no vinculadas al currículo del Ciclo Formativo / Curso de Especialización / Programa de especialización.</p>
            <textarea name="formaciones_especificas" rows="4"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500"
                      placeholder="Describe aquí cualquier formación específica adicional no vinculada al currículo...">{{ old('formaciones_especificas', $datos?->formaciones_especificas) }}</textarea>
        </div>

        {{-- ACCIONES --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('asignaciones.show', $asignacion) }}"
               class="px-4 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generar Plan de Formación
            </button>
        </div>
    </form>
</div>
@endsection
