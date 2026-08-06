@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @include('partials.nav-alumnos')

    {{-- Breadcrumb --}}
    <div class="mb-1 text-sm text-gray-500">
        <a href="{{ route('alumnos.index') }}" class="hover:text-gray-700">Alumnos</a>
        <span class="mx-1">›</span>
        <a href="{{ route('alumnos.show', $asignacion->alumno) }}" class="hover:text-gray-700">{{ $asignacion->alumno->nombre_completo }}</a>
        <span class="mx-1">›</span>
        <a href="{{ route('asignaciones.show', $asignacion) }}" class="hover:text-gray-700">Asignación FFE</a>
        <span class="mx-1">›</span>
        <span>Editar</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 mb-6">Editar asignación FFE</h1>

    @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-300 px-4 py-3 text-sm text-red-700">
        <p class="font-medium mb-1">Corrige los siguientes errores:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('asignaciones.update', $asignacion) }}"
          x-data="asignacionForm(
              {{ old('empresa_id', $asignacion->empresa_id) }},
              {{ old('sede_id', $asignacion->sede_id ?? 'null') }},
              {{ old('tutor_empresa_id', $asignacion->tutor_empresa_id ?? 'null') }},
              {{ Illuminate\Support\Js::from($horariosPrefill ?? []) }}
          )"
          @submit="if (!formularioValido()) $event.preventDefault()">
        @csrf
        @method('PUT')

        {{-- Alumno (solo lectura) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Alumno</h2>
            <p class="text-gray-900 font-medium">{{ $asignacion->alumno->nombre_completo }}</p>
            <p class="text-sm text-gray-500">{{ $asignacion->ciclo->codigo }} — {{ $asignacion->ciclo->nombre }} · {{ $asignacion->numero_curso }}º curso · {{ $asignacion->curso_academico }}</p>
        </div>

        {{-- Estado (solo admin/responsable_ffe) --}}
        @if($puedeEditarEstado)
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Estado</h2>
            <select name="estado"
                    class="w-full sm:w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @foreach(['activa', 'finalizada', 'cancelada'] as $est)
                <option value="{{ $est }}" {{ old('estado', $asignacion->estado) === $est ? 'selected' : '' }}>
                    {{ ucfirst($est) }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Empresa, sede y tutor empresa --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5 space-y-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Empresa</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa <span class="text-red-500">*</span></label>
                <select name="empresa_id" required x-model="empresaId" @change="cargarDatosEmpresa()"
                        class="w-full rounded-lg border @error('empresa_id') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecciona una empresa…</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ old('empresa_id', $asignacion->empresa_id) == $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('empresa_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div x-show="empresaId">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sede</label>
                <select name="sede_id" x-model="sedeId"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Sin sede específica</option>
                    <template x-for="s in sedes" :key="s.id">
                        <option :value="s.id" :selected="s.id == sedeId" x-text="s.label"></option>
                    </template>
                </select>
            </div>

            <div x-show="empresaId">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tutor de empresa</label>
                <select name="tutor_empresa_id" x-model="tutorEmpresaId"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Sin tutor de empresa</option>
                    <template x-for="c in contactos" :key="c.id">
                        <option :value="c.id" :selected="c.id == tutorEmpresaId" x-text="c.label"></option>
                    </template>
                </select>
            </div>
        </div>

        {{-- Tutor IES y fechas --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5 space-y-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Seguimiento IES</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tutor IES <span class="text-red-500">*</span></label>
                <select name="tutor_ies_id" required
                        class="w-full rounded-lg border @error('tutor_ies_id') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecciona un tutor…</option>
                    @foreach($tutoresIes as $tutor)
                        <option value="{{ $tutor->id }}" {{ old('tutor_ies_id', $asignacion->tutor_ies_id) == $tutor->id ? 'selected' : '' }}>
                            {{ $tutor->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('tutor_ies_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_inicio" required value="{{ old('fecha_inicio', $asignacion->fecha_inicio?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border @error('fecha_inicio') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    @error('fecha_inicio')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_fin" required value="{{ old('fecha_fin', $asignacion->fecha_fin?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border @error('fecha_fin') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    @error('fecha_fin')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Horario semanal --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Horario semanal <span class="text-red-500">*</span></h2>
            <p class="text-xs text-gray-400 mb-4">Guardar cambios sustituye por completo el horario anterior de esta asignación.</p>

            <div class="flex flex-col sm:flex-row gap-2 mb-5">
                <label class="flex-1 flex items-center gap-2 border rounded-lg px-3 py-2 cursor-pointer text-sm"
                       :class="modo === 'igual' ? 'border-blue-400 bg-blue-50 text-blue-800' : 'border-gray-200 text-gray-600'">
                    <input type="radio" :checked="modo === 'igual'" @change="cambiarModo('igual')" class="text-blue-600 focus:ring-blue-500">
                    Igual de lunes a viernes
                </label>
                <label class="flex-1 flex items-center gap-2 border rounded-lg px-3 py-2 cursor-pointer text-sm"
                       :class="modo === 'lj_v' ? 'border-blue-400 bg-blue-50 text-blue-800' : 'border-gray-200 text-gray-600'">
                    <input type="radio" :checked="modo === 'lj_v'" @change="cambiarModo('lj_v')" class="text-blue-600 focus:ring-blue-500">
                    Lunes a jueves igual, viernes diferente
                </label>
                <label class="flex-1 flex items-center gap-2 border rounded-lg px-3 py-2 cursor-pointer text-sm"
                       :class="modo === 'cada_dia' ? 'border-blue-400 bg-blue-50 text-blue-800' : 'border-gray-200 text-gray-600'">
                    <input type="radio" :checked="modo === 'cada_dia'" @change="cambiarModo('cada_dia')" class="text-blue-600 focus:ring-blue-500">
                    Diferente cada día
                </label>
            </div>

            {{-- Modo: igual de lunes a viernes --}}
            <div x-show="modo === 'igual'" x-cloak class="border border-gray-100 rounded-lg p-4 mb-3">
                <label class="flex items-center gap-2 mb-3 cursor-pointer">
                    <input type="checkbox" x-model="bloqueComun.jornadaPartida" @change="toggleComun()"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Jornada partida</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Entrada mañana</label>
                        <input type="time" x-model="bloqueComun.entrada_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Salida mañana</label>
                        <input type="time" x-model="bloqueComun.salida_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div x-show="bloqueComun.jornadaPartida">
                        <label class="block text-xs text-gray-500 mb-1">Entrada tarde</label>
                        <input type="time" x-model="bloqueComun.entrada_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div x-show="bloqueComun.jornadaPartida">
                        <label class="block text-xs text-gray-500 mb-1">Salida tarde</label>
                        <input type="time" x-model="bloqueComun.salida_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3">Lunes a viernes, <span x-text="horasDelDia(bloqueComun).toFixed(2)"></span>h/día.</p>
            </div>

            {{-- Modo: L-J igual, V diferente --}}
            <div x-show="modo === 'lj_v'" x-cloak class="space-y-3 mb-3">
                <div class="border border-gray-100 rounded-lg p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Lunes a jueves</p>
                    <label class="flex items-center gap-2 mb-3 cursor-pointer">
                        <input type="checkbox" x-model="bloqueLJ.jornadaPartida" @change="toggleLJ()"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Jornada partida</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Entrada mañana</label>
                            <input type="time" x-model="bloqueLJ.entrada_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Salida mañana</label>
                            <input type="time" x-model="bloqueLJ.salida_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div x-show="bloqueLJ.jornadaPartida">
                            <label class="block text-xs text-gray-500 mb-1">Entrada tarde</label>
                            <input type="time" x-model="bloqueLJ.entrada_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div x-show="bloqueLJ.jornadaPartida">
                            <label class="block text-xs text-gray-500 mb-1">Salida tarde</label>
                            <input type="time" x-model="bloqueLJ.salida_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3"><span x-text="horasDelDia(bloqueLJ).toFixed(2)"></span>h/día × 4 días.</p>
                </div>
                <div class="border border-gray-100 rounded-lg p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Viernes</p>
                    <label class="flex items-center gap-2 mb-3 cursor-pointer">
                        <input type="checkbox" x-model="bloqueViernes.jornadaPartida" @change="toggleViernes()"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Jornada partida</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Entrada mañana</label>
                            <input type="time" x-model="bloqueViernes.entrada_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Salida mañana</label>
                            <input type="time" x-model="bloqueViernes.salida_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div x-show="bloqueViernes.jornadaPartida">
                            <label class="block text-xs text-gray-500 mb-1">Entrada tarde</label>
                            <input type="time" x-model="bloqueViernes.entrada_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div x-show="bloqueViernes.jornadaPartida">
                            <label class="block text-xs text-gray-500 mb-1">Salida tarde</label>
                            <input type="time" x-model="bloqueViernes.salida_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3"><span x-text="horasDelDia(bloqueViernes).toFixed(2)"></span>h ese día.</p>
                </div>
            </div>

            {{-- Modo: diferente cada día --}}
            <div x-show="modo === 'cada_dia'" x-cloak class="space-y-2 mb-3">
                <template x-for="dia in diasIndividuales" :key="dia.dia">
                    <div class="border border-gray-100 rounded-lg p-3" :class="dia.activo ? 'bg-blue-50/40 border-blue-100' : ''">
                        <div class="flex items-center gap-4 flex-wrap">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="dia.activo" @change="toggleDiaIndividual(dia)"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-800 w-24" x-text="dia.label"></span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer" x-show="dia.activo">
                                <input type="checkbox" x-model="dia.jornadaPartida" @change="toggleJornadaDiaIndividual(dia)"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs text-gray-500">Jornada partida</span>
                            </label>
                            <span class="text-xs text-gray-400 ml-auto" x-show="dia.activo" x-text="horasDelDia(dia).toFixed(2) + 'h'"></span>
                        </div>

                        <div x-show="dia.activo" x-cloak class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 pl-6">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Entrada mañana</label>
                                <input type="time" x-model="dia.entrada_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Salida mañana</label>
                                <input type="time" x-model="dia.salida_manana" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div x-show="dia.jornadaPartida">
                                <label class="block text-xs text-gray-500 mb-1">Entrada tarde</label>
                                <input type="time" x-model="dia.entrada_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div x-show="dia.jornadaPartida">
                                <label class="block text-xs text-gray-500 mb-1">Salida tarde</label>
                                <input type="time" x-model="dia.salida_tarde" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </template>
                <p class="text-xs text-amber-600" x-show="incluyeFinde" x-cloak>Este horario incluye sábado y/o domingo ya guardados anteriormente — se mantienen visibles para no perderlos al guardar.</p>
            </div>

            {{-- Inputs ocultos reales que viajan al backend: horarios[index][campo] --}}
            <template x-for="(dia, index) in diasFinales()" :key="'hidden-' + dia.dia">
                <span>
                    <input type="hidden" :name="`horarios[${index}][dia]`" :value="dia.dia">
                    <input type="hidden" :name="`horarios[${index}][entrada_manana]`" :value="dia.entrada_manana">
                    <input type="hidden" :name="`horarios[${index}][salida_manana]`" :value="dia.salida_manana">
                    <input type="hidden" :name="`horarios[${index}][entrada_tarde]`" :value="dia.entrada_tarde">
                    <input type="hidden" :name="`horarios[${index}][salida_tarde]`" :value="dia.salida_tarde">
                </span>
            </template>

            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                <span class="text-gray-500">Total previsto por semana:</span>
                <span class="font-medium text-gray-800" x-text="totalSemanal().toFixed(2) + 'h'"></span>
            </div>

            <template x-if="erroresGlobales().length">
                <div class="mt-3 rounded-lg bg-red-50 border border-red-200 px-3 py-2">
                    <template x-for="e in erroresGlobales()" :key="e">
                        <p class="text-xs text-red-700" x-text="e"></p>
                    </template>
                </div>
            </template>

            <div x-show="advertencias().length" x-cloak class="mt-3 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2">
                <template x-for="a in advertencias()" :key="a">
                    <p class="text-xs text-amber-700" x-text="'⚠️ ' + a"></p>
                </template>
            </div>
        </div>

        {{-- RA / CE elegibles --}}
        @if($modulos->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Resultados de Aprendizaje y Criterios de Evaluación</h2>
            <p class="text-xs text-gray-400 mb-4">Solo se muestran los elegibles FFE para {{ $cursoActivo }}.</p>
            <div class="space-y-4">
                @foreach($modulos as $modulo)
                    @if($modulo->resultadosAprendizaje->isNotEmpty())
                    <div class="border border-gray-100 rounded-lg p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase mb-3">{{ $modulo->codigo }} — {{ $modulo->nombre }}</p>
                        <div class="space-y-3">
                            @foreach($modulo->resultadosAprendizaje as $ra)
                            <div>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="ra_ids[]" value="{{ $ra->id }}"
                                           {{ in_array($ra->id, old('ra_ids', $raSeleccionados)) ? 'checked' : '' }}
                                           class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-800 font-medium">{{ $ra->codigo }} — {{ $ra->nombre }}</span>
                                </label>
                                @if($ra->criterios->isNotEmpty())
                                <div class="ml-6 mt-2 space-y-1.5">
                                    @foreach($ra->criterios as $ce)
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" name="ce_ids[]" value="{{ $ce->id }}"
                                               {{ in_array($ce->id, old('ce_ids', $ceSeleccionados)) ? 'checked' : '' }}
                                               class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-xs text-gray-600">{{ $ce->codigo }} — {{ $ce->descripcion }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" :disabled="!formularioValido()"
                    :class="formularioValido() ? 'bg-blue-600 hover:bg-blue-700 text-white cursor-pointer' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                    class="px-5 py-2 text-sm font-medium rounded-lg transition-colors">
                Guardar cambios
            </button>
            <a href="{{ route('asignaciones.show', $asignacion) }}" class="px-5 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function asignacionForm(empresaIdInicial, sedeIdInicial, tutorEmpresaIdInicial, horariosPrefill) {
    const DIAS_SEMANA = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    const DIAS_FINDE  = ['sabado', 'domingo'];
    const LABELS = {
        lunes: 'Lunes', martes: 'Martes', miercoles: 'Miércoles', jueves: 'Jueves',
        viernes: 'Viernes', sabado: 'Sábado', domingo: 'Domingo',
    };
    const MAX_HORAS_DIA = 8;
    const MAX_HORAS_SEMANA = 40;

    const PRESET_COMUN_PARTIDA    = { entrada_manana: '09:00', salida_manana: '14:00', entrada_tarde: '16:00', salida_tarde: '19:00' };
    const PRESET_COMUN_CONTINUA   = { entrada_manana: '07:00', salida_manana: '15:00', entrada_tarde: '',      salida_tarde: ''      };
    const PRESET_LJ_PARTIDA       = { entrada_manana: '09:00', salida_manana: '14:00', entrada_tarde: '15:00', salida_tarde: '19:00' };
    const PRESET_LJ_CONTINUA      = { entrada_manana: '07:00', salida_manana: '16:00', entrada_tarde: '',      salida_tarde: ''      };
    const PRESET_VIERNES_CONTINUA = { entrada_manana: '09:00', salida_manana: '13:00', entrada_tarde: '',      salida_tarde: ''      };

    function esPartida(fuente) {
        return !!(fuente && fuente.entrada_tarde && fuente.salida_tarde);
    }

    function construirBloque(fuente, presetPartidaFallback) {
        if (!fuente) return { jornadaPartida: true, ...presetPartidaFallback };
        return {
            jornadaPartida: esPartida(fuente),
            entrada_manana: fuente.entrada_manana ?? '',
            salida_manana: fuente.salida_manana ?? '',
            entrada_tarde: fuente.entrada_tarde ?? '',
            salida_tarde: fuente.salida_tarde ?? '',
        };
    }

    function detectarEstadoInicial() {
        const porDia = horariosPrefill || {};
        const entradas = Object.keys(porDia);

        if (entradas.length === 0) {
            return {
                modo: 'igual',
                bloqueComun: { jornadaPartida: true, ...PRESET_COMUN_PARTIDA },
                bloqueLJ: { jornadaPartida: true, ...PRESET_LJ_PARTIDA },
                bloqueViernes: { jornadaPartida: false, ...PRESET_VIERNES_CONTINUA },
                diasIndividuales: DIAS_SEMANA.map(d => ({
                    dia: d, label: LABELS[d], activo: false, jornadaPartida: true,
                    entrada_manana: '', salida_manana: '', entrada_tarde: '', salida_tarde: '',
                })),
            };
        }

        const tieneFinde = DIAS_FINDE.some(d => porDia[d]);

        const igualFn = (a, b) => a && b
            && a.entrada_manana === b.entrada_manana && a.salida_manana === b.salida_manana
            && (a.entrada_tarde || '') === (b.entrada_tarde || '') && (a.salida_tarde || '') === (b.salida_tarde || '');

        const diasSemanaPresentes = DIAS_SEMANA.filter(d => porDia[d]);
        const todosIguales = diasSemanaPresentes.length === 5
            && diasSemanaPresentes.every(d => igualFn(porDia[d], porDia['lunes']));

        const ljPresentes = ['lunes', 'martes', 'miercoles', 'jueves'].every(d => porDia[d]);
        const ljIguales = ljPresentes && ['martes', 'miercoles', 'jueves'].every(d => igualFn(porDia[d], porDia['lunes']));
        const viernesDistinto = porDia['viernes'] && !igualFn(porDia['viernes'], porDia['lunes']);

        let modo = 'cada_dia';
        if (!tieneFinde && todosIguales) {
            modo = 'igual';
        } else if (!tieneFinde && ljIguales && porDia['viernes'] && viernesDistinto) {
            modo = 'lj_v';
        }

        const diasParaIndividual = tieneFinde ? [...DIAS_SEMANA, ...DIAS_FINDE.filter(d => porDia[d])] : DIAS_SEMANA;

        return {
            modo,
            bloqueComun: modo === 'igual'
                ? construirBloque(porDia['lunes'], PRESET_COMUN_PARTIDA)
                : { jornadaPartida: true, ...PRESET_COMUN_PARTIDA },
            bloqueLJ: modo === 'lj_v'
                ? construirBloque(porDia['lunes'], PRESET_LJ_PARTIDA)
                : { jornadaPartida: true, ...PRESET_LJ_PARTIDA },
            bloqueViernes: modo === 'lj_v'
                ? construirBloque(porDia['viernes'], PRESET_VIERNES_CONTINUA)
                : { jornadaPartida: false, ...PRESET_VIERNES_CONTINUA },
            diasIndividuales: diasParaIndividual.map(d => {
                const fuente = porDia[d];
                return {
                    dia: d, label: LABELS[d], activo: !!fuente,
                    jornadaPartida: fuente ? esPartida(fuente) : true,
                    entrada_manana: fuente?.entrada_manana ?? '',
                    salida_manana: fuente?.salida_manana ?? '',
                    entrada_tarde: fuente?.entrada_tarde ?? '',
                    salida_tarde: fuente?.salida_tarde ?? '',
                };
            }),
        };
    }

    const estadoInicial = detectarEstadoInicial();
    const incluyeFinde = estadoInicial.diasIndividuales.some(d => DIAS_FINDE.includes(d.dia));

    return {
        // --- Empresa / sede / tutor ---
        empresaId: empresaIdInicial ? String(empresaIdInicial) : '',
        sedeId: sedeIdInicial ? String(sedeIdInicial) : '',
        tutorEmpresaId: tutorEmpresaIdInicial ? String(tutorEmpresaIdInicial) : '',
        sedes: [],
        contactos: [],

        // --- Horario semanal (Fase D-UI v2: 3 modos) ---
        modo: estadoInicial.modo,
        bloqueComun: estadoInicial.bloqueComun,
        bloqueLJ: estadoInicial.bloqueLJ,
        bloqueViernes: estadoInicial.bloqueViernes,
        diasIndividuales: estadoInicial.diasIndividuales,
        incluyeFinde,

        init() {
            if (this.empresaId) {
                this.cargarDatosEmpresa();
            }
        },

        cargarDatosEmpresa() {
            if (!this.empresaId) {
                this.sedes = [];
                this.contactos = [];
                this.sedeId = '';
                this.tutorEmpresaId = '';
                return;
            }
            const base = '/interna/empresas/' + this.empresaId;
            fetch(base + '/sedes').then(r => r.json()).then(data => { this.sedes = data; });
            fetch(base + '/contactos').then(r => r.json()).then(data => { this.contactos = data; });
        },

        // --- Utilidades horario ---
        minutos(hhmm) {
            if (!hhmm) return null;
            const [h, m] = hhmm.split(':').map(Number);
            return (h * 60) + m;
        },

        diffHoras(inicio, fin) {
            const mi = this.minutos(inicio), mf = this.minutos(fin);
            if (mi === null || mf === null) return 0;
            const diff = mf - mi;
            return diff > 0 ? diff / 60 : 0;
        },

        horasDelDia(d) {
            if (!d.entrada_manana || !d.salida_manana) return 0;
            let horas = this.diffHoras(d.entrada_manana, d.salida_manana);
            if (d.entrada_tarde && d.salida_tarde) {
                horas += this.diffHoras(d.entrada_tarde, d.salida_tarde);
            }
            return Math.round(horas * 100) / 100;
        },

        // --- Toggles de jornada partida / continua por bloque ---
        toggleComun() {
            Object.assign(this.bloqueComun, this.bloqueComun.jornadaPartida ? PRESET_COMUN_PARTIDA : PRESET_COMUN_CONTINUA);
        },
        toggleLJ() {
            Object.assign(this.bloqueLJ, this.bloqueLJ.jornadaPartida ? PRESET_LJ_PARTIDA : PRESET_LJ_CONTINUA);
        },
        toggleViernes() {
            if (this.bloqueViernes.jornadaPartida) {
                this.bloqueViernes.entrada_tarde = '';
                this.bloqueViernes.salida_tarde = '';
            } else {
                Object.assign(this.bloqueViernes, PRESET_VIERNES_CONTINUA);
            }
        },
        toggleDiaIndividual(d) {
            if (!d.activo) {
                d.entrada_manana = ''; d.salida_manana = ''; d.entrada_tarde = ''; d.salida_tarde = '';
                return;
            }
            Object.assign(d, d.jornadaPartida ? PRESET_COMUN_PARTIDA : PRESET_COMUN_CONTINUA);
        },
        toggleJornadaDiaIndividual(d) {
            if (!d.activo) return;
            Object.assign(d, d.jornadaPartida ? PRESET_COMUN_PARTIDA : PRESET_COMUN_CONTINUA);
        },

        // --- Cambio de modo, conservando los valores ya introducidos ---
        cambiarModo(nuevo) {
            const actuales = this.diasFinales();
            const buscar = (dia) => actuales.find(d => d.dia === dia);

            if (nuevo === 'igual') {
                const base = buscar('lunes') || actuales[0];
                if (base) this.bloqueComun = construirBloque(base, PRESET_COMUN_PARTIDA);
            } else if (nuevo === 'lj_v') {
                const lunes = buscar('lunes') || actuales[0];
                const viernes = buscar('viernes') || actuales[actuales.length - 1];
                if (lunes) this.bloqueLJ = construirBloque(lunes, PRESET_LJ_PARTIDA);
                if (viernes) this.bloqueViernes = construirBloque(viernes, PRESET_VIERNES_CONTINUA);
            } else if (nuevo === 'cada_dia') {
                const dias = this.incluyeFinde ? [...DIAS_SEMANA, ...DIAS_FINDE] : DIAS_SEMANA;
                this.diasIndividuales = dias.map(d => {
                    const fuente = buscar(d);
                    return {
                        dia: d, label: LABELS[d], activo: !!fuente,
                        jornadaPartida: fuente ? esPartida(fuente) : true,
                        entrada_manana: fuente?.entrada_manana ?? '',
                        salida_manana: fuente?.salida_manana ?? '',
                        entrada_tarde: fuente?.entrada_tarde ?? '',
                        salida_tarde: fuente?.salida_tarde ?? '',
                    };
                });
            }
            this.modo = nuevo;
        },

        // --- Proyección del modo actual a la lista final de días a enviar/calcular ---
        diasFinales() {
            if (this.modo === 'igual') {
                return DIAS_SEMANA.map(d => ({ dia: d, label: LABELS[d], ...this.bloqueComun }));
            }
            if (this.modo === 'lj_v') {
                return [
                    ...['lunes', 'martes', 'miercoles', 'jueves'].map(d => ({ dia: d, label: LABELS[d], ...this.bloqueLJ })),
                    { dia: 'viernes', label: LABELS['viernes'], ...this.bloqueViernes },
                ];
            }
            return this.diasIndividuales.filter(d => d.activo);
        },

        totalSemanal() {
            const total = this.diasFinales().reduce((sum, d) => sum + this.horasDelDia(d), 0);
            return Math.round(total * 100) / 100;
        },

        erroresDia(d) {
            const errores = [];
            if (!d.entrada_manana || !d.salida_manana) {
                errores.push('Faltan entrada/salida de mañana.');
                return errores;
            }
            if (this.minutos(d.entrada_manana) >= this.minutos(d.salida_manana)) {
                errores.push('La entrada de mañana debe ser anterior a la salida.');
                return errores;
            }
            const tieneTarde = d.entrada_tarde || d.salida_tarde;
            if (tieneTarde) {
                if (!d.entrada_tarde || !d.salida_tarde) {
                    errores.push('Si hay turno de tarde, debe indicarse entrada y salida.');
                    return errores;
                }
                if (this.minutos(d.salida_manana) > this.minutos(d.entrada_tarde)) {
                    errores.push('La mañana y la tarde se solapan.');
                    return errores;
                }
                if (this.minutos(d.entrada_tarde) >= this.minutos(d.salida_tarde)) {
                    errores.push('La entrada de tarde debe ser anterior a la salida.');
                }
            }
            return errores;
        },

        erroresGlobales() {
            const dias = this.diasFinales();
            const errores = [];
            if (dias.length === 0) {
                errores.push('Debes definir al menos un día de horario.');
                return errores;
            }
            dias.forEach(d => {
                this.erroresDia(d).forEach(e => errores.push(`${d.label}: ${e}`));
            });
            return errores;
        },

        advertencias() {
            const avisos = [];
            this.diasFinales().forEach(d => {
                const h = this.horasDelDia(d);
                if (h > MAX_HORAS_DIA) {
                    avisos.push(`${d.label}: ${h.toFixed(2)}h supera el máximo diario recomendado de ${MAX_HORAS_DIA}h.`);
                }
            });
            const total = this.totalSemanal();
            if (total > MAX_HORAS_SEMANA) {
                avisos.push(`El total semanal de ${total.toFixed(2)}h supera el máximo recomendado de ${MAX_HORAS_SEMANA}h.`);
            }
            return avisos;
        },

        formularioValido() {
            return this.erroresGlobales().length === 0;
        },
    };
}
</script>
@endpush
@endsection
