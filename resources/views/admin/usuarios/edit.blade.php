@extends('layouts.app')

@section('title', 'Asignar Rol — ' . $usuario->nombre)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.usuarios.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Asignar Rol</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $usuario->nombre }}</p>
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-800 dark:text-blue-200">
                Los datos del usuario (nombre, email, contraseña y estado activo) se gestionan desde la <strong>aplicación de administración LDAP</strong>. Aquí solo se asigna el rol dentro del sistema FFE.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide mb-4">Datos del usuario (solo lectura)</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-xs text-slate-400 dark:text-slate-500 mb-1">Nombre</dt>
                <dd class="font-medium text-slate-800 dark:text-white">{{ $usuario->nombre }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-400 dark:text-slate-500 mb-1">Usuario LDAP</dt>
                <dd class="font-mono text-sm text-slate-700 dark:text-slate-300">{{ $usuario->username }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-slate-400 dark:text-slate-500 mb-1">Email</dt>
                <dd class="text-slate-700 dark:text-slate-300">{{ $usuario->email }}</dd>
            </div>
        </dl>
    </div>

    <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                    Rol en el sistema FFE <span class="text-red-500">*</span>
                </label>
                @if($usuario->id === auth()->id() && $usuario->esAdmin())
                <div class="mb-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-700 dark:text-amber-300">
                    No puedes cambiar tu propio rol de administrador.
                </div>
                @endif
                @php $esYoAdmin = $usuario->id === auth()->id() && $usuario->esAdmin(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-colors {{ $esYoAdmin ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="radio" name="rol" value="profesor" {{ old('rol', $usuario->rol) === 'profesor' ? 'checked' : '' }}
                               class="mt-0.5 text-primary-600 focus:ring-primary-500" onchange="toggleCicloSelect()" {{ $esYoAdmin ? 'disabled' : '' }}>
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Profesor</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Gestiona sus propias empresas</span>
                        </div>
                    </label>
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-amber-300 hover:bg-amber-50/50 dark:hover:bg-amber-900/20 transition-colors {{ $esYoAdmin ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="radio" name="rol" value="responsable_ciclo" {{ old('rol', $usuario->rol) === 'responsable_ciclo' ? 'checked' : '' }}
                               class="mt-0.5 text-amber-600 focus:ring-amber-500" onchange="toggleCicloSelect()" {{ $esYoAdmin ? 'disabled' : '' }}>
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Responsable de Ciclo</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Gestiona empresas de sus ciclos</span>
                        </div>
                    </label>
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-teal-300 hover:bg-teal-50/50 dark:hover:bg-teal-900/20 transition-colors {{ $esYoAdmin ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="radio" name="rol" value="responsable_ffe" {{ old('rol', $usuario->rol) === 'responsable_ffe' ? 'checked' : '' }}
                               class="mt-0.5 text-teal-600 focus:ring-teal-500" onchange="toggleCicloSelect()" {{ $esYoAdmin ? 'disabled' : '' }}>
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Responsable FFE</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Accede a todas las empresas</span>
                        </div>
                    </label>
                    <label class="flex items-start p-4 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50/50 dark:hover:bg-red-900/20 transition-colors">
                        <input type="radio" name="rol" value="admin" {{ old('rol', $usuario->rol) === 'admin' ? 'checked' : '' }}
                               class="mt-0.5 text-red-600 focus:ring-red-500" onchange="toggleCicloSelect()">
                        <div class="ml-3">
                            <span class="block font-medium text-slate-800 dark:text-white">Administrador</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Acceso total al sistema</span>
                        </div>
                    </label>
                </div>
                @error('rol')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="ciclo-container" class="{{ old('rol', $usuario->rol) === 'responsable_ciclo' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Ciclos formativos asignados <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Selecciona uno o varios ciclos que gestionará este responsable</p>
                @php $ciclosUsuario = $usuario->ciclos->pluck('id')->toArray(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($ciclos as $ciclo)
                    <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 dark:hover:bg-primary-900/20 transition-colors">
                        <input type="checkbox" name="ciclos[]" value="{{ $ciclo->id }}"
                               {{ in_array($ciclo->id, old('ciclos', $ciclosUsuario)) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold mr-2
                                @if($ciclo->nivel === 'basica') bg-orange-100 text-orange-700
                                @elseif($ciclo->nivel === 'media') bg-blue-100 text-blue-700
                                @else bg-purple-100 text-purple-700 @endif">
                                {{ $ciclo->codigo }}
                            </span>
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $ciclo->nombre }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('ciclos')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="grupo-container" class="{{ old('rol', $usuario->rol) === 'profesor' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Grupo que tutoriza
                </label>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Un profesor tutoriza como máximo un grupo en el curso académico activo ({{ \App\Models\Configuracion::cursoActivo() }}). Puede dejarse vacío por ahora; se podrá completar más adelante.</p>

                @php $gruposUsuario = $usuario->gruposTutor->pluck('id')->toArray(); @endphp
                @php $grupoSeleccionado = old('grupos.0', $gruposUsuario[0] ?? null); @endphp

                @if($grupos->isEmpty())
                    <div class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        No hay grupos activos registrados.
                    </div>
                @else
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        1. Ciclo formativo
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                        @foreach($ciclos as $ciclo)
                        <label class="flex items-center gap-3 p-3 border border-slate-200 dark:border-slate-600 rounded-xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 dark:hover:bg-primary-900/20 transition-colors">
                            <input type="radio" name="filtro_ciclo_grupo" id="filtro-ciclo-{{ $ciclo->id }}" value="{{ $ciclo->id }}"
                                   class="w-4 h-4 border-slate-300 text-primary-600 focus:ring-primary-500">
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold mr-2
                                    @if($ciclo->nivel === 'basica') bg-orange-100 text-orange-700
                                    @elseif($ciclo->nivel === 'media') bg-blue-100 text-blue-700
                                    @else bg-purple-100 text-purple-700 @endif">
                                    {{ $ciclo->codigo }}
                                </span>
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ $ciclo->nombre }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        2. Grupo
                    </label>
                    <div class="relative" id="combo-grupo">
                        <button type="button" id="combo-grupo-btn" aria-expanded="false"
                                class="w-full flex items-center justify-between gap-2 rounded-lg border @error('grupos') border-red-400 @else border-slate-300 @enderror dark:border-slate-600 dark:bg-slate-700 px-3 py-2 text-sm text-left bg-white hover:border-primary-300 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <span id="combo-grupo-label" class="truncate text-slate-400 dark:text-slate-400">Selecciona un ciclo primero…</span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" id="combo-grupo-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="combo-grupo-panel"
                             class="hidden absolute z-10 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 shadow-lg max-h-60 overflow-y-auto py-1">
                            @foreach($grupos as $grupo)
                            <button type="button"
                                    class="combo-grupo-option hidden w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-primary-50 dark:hover:bg-primary-900/30"
                                    data-ciclo-id="{{ $grupo->ciclo_id }}" data-grupo-id="{{ $grupo->id }}">
                                <span>{{ $grupo->etiquetaConCiclo }}</span>
                                <svg class="w-4 h-4 text-primary-600 shrink-0 combo-grupo-check hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="grupos[]" id="select-grupo-tutor" value="{{ $grupoSeleccionado }}">
                @endif
                @error('grupos')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="{{ route('admin.usuarios.index') }}" class="px-6 py-2.5 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 shadow-lg shadow-primary-500/20">
                Guardar Rol
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleCicloSelect() {
    const rol = document.querySelector('input[name="rol"]:checked')?.value;
    document.getElementById('ciclo-container').classList.toggle('hidden', rol !== 'responsable_ciclo');
    document.getElementById('grupo-container').classList.toggle('hidden', rol !== 'profesor');
}
document.addEventListener('DOMContentLoaded', toggleCicloSelect);

document.addEventListener('DOMContentLoaded', function () {
    var radiosCiclo  = document.querySelectorAll('input[name="filtro_ciclo_grupo"]');
    var comboWrapper = document.getElementById('combo-grupo');
    var comboBtn     = document.getElementById('combo-grupo-btn');
    var comboPanel   = document.getElementById('combo-grupo-panel');
    var comboLabel   = document.getElementById('combo-grupo-label');
    var comboChevron = document.getElementById('combo-grupo-chevron');
    var hiddenInput  = document.getElementById('select-grupo-tutor');
    var opciones     = document.querySelectorAll('.combo-grupo-option');
    if (!radiosCiclo.length || !comboBtn || !hiddenInput) return;

    function cerrarPanel() {
        comboPanel.classList.add('hidden');
        comboBtn.setAttribute('aria-expanded', 'false');
        comboChevron.classList.remove('rotate-180');
    }

    function abrirPanel() {
        comboPanel.classList.remove('hidden');
        comboBtn.setAttribute('aria-expanded', 'true');
        comboChevron.classList.add('rotate-180');
    }

    function limpiarSeleccion(mensaje) {
        hiddenInput.value = '';
        comboLabel.textContent = mensaje;
        comboLabel.classList.add('text-slate-400');
        opciones.forEach(function (o) {
            o.querySelector('.combo-grupo-check').classList.add('hidden');
        });
    }

    function seleccionarGrupo(opcion) {
        hiddenInput.value = opcion.dataset.grupoId;
        comboLabel.textContent = opcion.querySelector('span').textContent;
        comboLabel.classList.remove('text-slate-400');
        opciones.forEach(function (o) {
            o.querySelector('.combo-grupo-check').classList.toggle('hidden', o !== opcion);
        });
        cerrarPanel();
    }

    function filtrarGrupos() {
        var cicloId = document.querySelector('input[name="filtro_ciclo_grupo"]:checked')?.value ?? '';
        var huboSeleccionValida = false;

        opciones.forEach(function (opcion) {
            var coincide = opcion.dataset.cicloId === cicloId;
            opcion.classList.toggle('hidden', !coincide);
            if (coincide && opcion.dataset.grupoId === hiddenInput.value) {
                huboSeleccionValida = true;
            }
        });

        if (!huboSeleccionValida) {
            limpiarSeleccion(cicloId === '' ? 'Selecciona un ciclo primero…' : 'Selecciona un grupo…');
        }
    }

    comboBtn.addEventListener('click', function () {
        if (comboPanel.classList.contains('hidden')) {
            abrirPanel();
        } else {
            cerrarPanel();
        }
    });

    document.addEventListener('click', function (e) {
        if (!comboWrapper.contains(e.target)) {
            cerrarPanel();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrarPanel();
    });

    opciones.forEach(function (opcion) {
        opcion.addEventListener('click', function () {
            seleccionarGrupo(opcion);
        });
    });

    radiosCiclo.forEach(function (radio) {
        radio.addEventListener('change', filtrarGrupos);
    });

    // Si ya hay un grupo asignado (edicion) o volvemos de un 422 con grupo
    // seleccionado (old()), preseleccionar tambien el radio de ciclo correspondiente
    // y mostrar la etiqueta en el boton.
    if (hiddenInput.value) {
        var opcionInicial = document.querySelector('.combo-grupo-option[data-grupo-id="' + hiddenInput.value + '"]');
        if (opcionInicial) {
            var radioCiclo = document.getElementById('filtro-ciclo-' + opcionInicial.dataset.cicloId);
            if (radioCiclo) {
                radioCiclo.checked = true;
            }
            comboLabel.textContent = opcionInicial.querySelector('span').textContent;
            comboLabel.classList.remove('text-slate-400');
            opcionInicial.querySelector('.combo-grupo-check').classList.remove('hidden');
        }
    }

    filtrarGrupos();
});
</script>
@endpush
@endsection
