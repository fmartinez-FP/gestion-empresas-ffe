@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

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
          x-data="asignacionForm({{ old('empresa_id', $asignacion->empresa_id) }}, {{ old('sede_id', $asignacion->sede_id ?? 'null') }}, {{ old('tutor_empresa_id', $asignacion->tutor_empresa_id ?? 'null') }})">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $asignacion->fecha_inicio?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                    <input type="date" name="fecha_fin" value="{{ old('fecha_fin', $asignacion->fecha_fin?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de horas</label>
                    <input type="number" name="num_horas" value="{{ old('num_horas', $asignacion->num_horas) }}" min="1" max="9999"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Horario</label>
                    <input type="text" name="horario" value="{{ old('horario', $asignacion->horario) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
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
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
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
function asignacionForm(empresaIdInicial, sedeIdInicial, tutorEmpresaIdInicial) {
    return {
        empresaId: empresaIdInicial ? String(empresaIdInicial) : '',
        sedeId: sedeIdInicial ? String(sedeIdInicial) : '',
        tutorEmpresaId: tutorEmpresaIdInicial ? String(tutorEmpresaIdInicial) : '',
        sedes: [],
        contactos: [],

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
            fetch(base + '/sedes')
                .then(r => r.json())
                .then(data => { this.sedes = data; });
            fetch(base + '/contactos')
                .then(r => r.json())
                .then(data => { this.contactos = data; });
        }
    }
}
</script>
@endpush
@endsection
