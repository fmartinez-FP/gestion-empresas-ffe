@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @include('partials.nav-alumnos')


    <div class="mb-6">
        <a href="{{ route('alumnos.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver al listado</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Nuevo alumno</h1>
    </div>

    <form method="POST" action="{{ route('alumnos.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                       class="w-full rounded-lg border @error('nombre') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('nombre')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                <input type="text" name="apellidos" value="{{ old('apellidos') }}" required
                       class="w-full rounded-lg border @error('apellidos') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('apellidos')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="alumno@educa.madrid.org"
                       class="w-full rounded-lg border @error('email') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo formativo <span class="text-red-500">*</span></label>
                @if($grupos->isEmpty())
                    <div class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        No hay grupos activos registrados. Crea uno desde el panel de administración antes de añadir alumnos.
                    </div>
                @else
                    {{-- Filtro visual, no se envia: solo acota las opciones del select de grupo --}}
                    <select id="filtro-ciclo"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un ciclo…</option>
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}">{{ $ciclo->codigo }} — {{ $ciclo->nombre }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grupo <span class="text-red-500">*</span></label>
                <select name="grupo_id" id="select-grupo" required
                        class="w-full rounded-lg border @error('grupo_id') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecciona un ciclo primero…</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}" data-ciclo-id="{{ $grupo->ciclo_id }}" hidden
                                {{ old('grupo_id') == $grupo->id ? 'selected' : '' }}>
                            {{ $grupo->etiqueta_con_ciclo }}
                        </option>
                    @endforeach
                </select>
                @error('grupo_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Curso académico <span class="text-red-500">*</span></label>
            <input type="text" name="curso_academico" value="{{ old('curso_academico', $cursoActivo) }}"
                   placeholder="2025-2026" pattern="\d{4}-\d{4}" required
                   class="w-full sm:w-1/2 rounded-lg border @error('curso_academico') border-red-400 @else border-gray-300 @enderror px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            @error('curso_academico')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <p class="text-xs text-gray-500 bg-gray-50 rounded-lg p-3">
            La cuenta de acceso al portal del alumno se crea automáticamente al registrar su primera asignación FFE.
        </p>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Guardar alumno
            </button>
            <a href="{{ route('alumnos.index') }}" class="px-5 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var filtroCiclo = document.getElementById('filtro-ciclo');
            var selectGrupo = document.getElementById('select-grupo');
            if (!filtroCiclo || !selectGrupo) return;

            function filtrarGrupos() {
                var cicloId = filtroCiclo.value;
                var opciones = selectGrupo.querySelectorAll('option[data-ciclo-id]');
                var huboSeleccionValida = false;

                opciones.forEach(function (opcion) {
                    var coincide = opcion.dataset.cicloId === cicloId;
                    opcion.hidden = !coincide;
                    if (coincide && opcion.selected) {
                        huboSeleccionValida = true;
                    }
                });

                if (!huboSeleccionValida) {
                    selectGrupo.value = '';
                }
            }

            filtroCiclo.addEventListener('change', filtrarGrupos);

            // Si volvemos de un 422 con grupo_id ya seleccionado (old()), preseleccionar
            // tambien el filtro de ciclo para que las opciones coincidan visualmente.
            var grupoPreseleccionado = selectGrupo.querySelector('option[selected]');
            if (grupoPreseleccionado) {
                filtroCiclo.value = grupoPreseleccionado.dataset.cicloId;
            }

            filtrarGrupos();
        });
    </script>
</div>
@endsection
