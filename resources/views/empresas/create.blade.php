@extends('layouts.app')

@section('title', isset($empresa) ? 'Editar Empresa' : 'Nueva Empresa')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Cabecera -->
    <div class="flex items-center gap-4">
        <a href="{{ isset($empresa) ? route('empresas.show', $empresa) : route('empresas.index') }}" 
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                {{ isset($empresa) ? 'Editar Empresa' : 'Nueva Empresa' }}
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">
                {{ isset($empresa) ? $empresa->nombre : 'Completa los datos de la empresa' }}
            </p>
        </div>
    </div>

    <!-- Formulario -->
    <form 
        method="POST" 
        action="{{ isset($empresa) ? route('empresas.update', $empresa) : route('empresas.store') }}"
        class="space-y-6"
    >
        @csrf
        @if(isset($empresa))
            @method('PUT')
        @endif

        <!-- Datos básicos -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Datos de la Empresa
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div class="md:col-span-2">
                    <label for="nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Nombre de la empresa <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        value="{{ old('nombre', $empresa->nombre ?? '') }}"
                        required
                        placeholder="Razón social completa"
                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('nombre') border-red-300 @enderror"
                    >
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CIF -->
                <div>
                    <label for="cif" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        CIF/NIF <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="cif" 
                        name="cif" 
                        value="{{ old('cif', $empresa->cif ?? '') }}"
                        required
                        placeholder="B12345678"
                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('cif') border-red-300 @enderror"
                    >
                    @error('cif')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>



</div>
        </div>

        <!-- Datos del convenio -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Datos del Convenio
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Número de convenio -->
                <div>
                    <label for="num_convenio" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Número de convenio
                    </label>
                    <input 
                        type="text" 
                        id="num_convenio" 
                        name="num_convenio" 
                        value="{{ old('num_convenio', $empresa->num_convenio ?? '') }}"
                        placeholder="CONV-2024-001"
                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>

                <!-- Fecha de firma -->
                <div>
                    <label for="fecha_firma" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Fecha de firma
                    </label>
                    <input 
                        type="date" 
                        id="fecha_firma" 
                        name="fecha_firma" 
                        value="{{ old('fecha_firma', isset($empresa) && $empresa->fecha_firma ? $empresa->fecha_firma->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                    <p class="mt-1 text-xs text-slate-500">El convenio tendrá una vigencia de 4 años desde esta fecha</p>
                </div>

                <!-- Notas -->
                <div class="md:col-span-2">
                    <label for="notas" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Notas / Observaciones
                    </label>
                    <textarea 
                        id="notas" 
                        name="notas" 
                        rows="3"
                        placeholder="Información adicional sobre la empresa o el convenio..."
                        class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                    >{{ old('notas', $empresa->notas ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Persona de Contacto (opcional) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Persona de Contacto
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Opcional. Podrás añadir o editar personas de contacto en cualquier momento desde la ficha de la empresa.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="contacto_nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nombre</label>
                    <input type="text" id="contacto_nombre" name="contacto_nombre"
                           value="{{ old('contacto_nombre') }}"
                           placeholder="Nombre completo del contacto"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('contacto_nombre') border-red-300 @enderror">
                    @error('contacto_nombre')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="contacto_cargo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Cargo</label>
                    <input type="text" id="contacto_cargo" name="contacto_cargo"
                           value="{{ old('contacto_cargo') }}"
                           placeholder="Director RRHH, Gerente..."
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label for="contacto_telefono" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Teléfono directo</label>
                    <input type="tel" id="contacto_telefono" name="contacto_telefono"
                           value="{{ old('contacto_telefono') }}"
                           placeholder="912 345 678"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label for="contacto_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email directo</label>
                    <input type="email" id="contacto_email" name="contacto_email"
                           value="{{ old('contacto_email') }}"
                           placeholder="persona@empresa.com"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('contacto_email') border-red-300 @enderror">
                    @error('contacto_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

                <!-- Ciclos formativos -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Ciclos Formativos
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Selecciona los ciclos que acepta esta empresa y marca si acepta alumnos de 1º, 2º o ambos.</p>

            <div class="space-y-4">
                @php
                    $empresaCiclos = isset($empresa) ? $empresa->ciclos->keyBy('id') : collect();
                @endphp
                
                @foreach($ciclos->groupBy('nivel') as $nivel => $ciclosNivel)
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-700 px-4 py-2 border-b border-slate-200 dark:border-slate-600">
                        <span class="font-medium text-slate-700 dark:text-slate-300">
                            @if($nivel === 'basica') FP Básica
                            @elseif($nivel === 'media') Grado Medio
                            @else Grado Superior
                            @endif
                        </span>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($ciclosNivel as $ciclo)
                        @php
                            $cicloEmpresa = $empresaCiclos->get($ciclo->id);
                            $seleccionado = old("ciclos.{$ciclo->id}.seleccionado", $cicloEmpresa ? true : false);
                            $aceptaPrimero = old("ciclos.{$ciclo->id}.acepta_primero", $cicloEmpresa ? $cicloEmpresa->pivot->acepta_primero : false);
                            $aceptaSegundo = old("ciclos.{$ciclo->id}.acepta_segundo", $cicloEmpresa ? $cicloEmpresa->pivot->acepta_segundo : true);
                        @endphp
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                            <label class="flex items-center gap-3 flex-1 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="ciclos[{{ $ciclo->id }}][seleccionado]" 
                                    value="1"
                                    {{ $seleccionado ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500 ciclo-checkbox"
                                    data-ciclo="{{ $ciclo->id }}"
                                >
                                <div>
                                    <span class="font-medium text-slate-800 dark:text-white">{{ $ciclo->codigo }}</span>
                                    <span class="text-slate-500 dark:text-slate-400">- {{ $ciclo->nombre }}</span>
                                </div>
                            </label>
                            <div class="flex items-center gap-4 sm:gap-6 ml-8 sm:ml-0 ciclo-cursos" data-ciclo="{{ $ciclo->id }}" style="{{ $seleccionado ? '' : 'opacity: 0.4; pointer-events: none;' }}">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="ciclos[{{ $ciclo->id }}][acepta_primero]" 
                                        value="1"
                                        {{ $aceptaPrimero ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    >
                                    <span class="text-sm text-slate-600 dark:text-slate-400">1º curso</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="ciclos[{{ $ciclo->id }}][acepta_segundo]" 
                                        value="1"
                                        {{ $aceptaSegundo ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    >
                                    <span class="text-sm text-slate-600 dark:text-slate-400">2º curso</span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ isset($empresa) ? route('empresas.show', $empresa) : route('empresas.index') }}" 
               class="px-6 py-2.5 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-center">
                Cancelar
            </a>
            <button 
                type="submit"
                class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20"
            >
                {{ isset($empresa) ? 'Guardar Cambios' : 'Crear Empresa' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Activar/desactivar opciones de curso según selección del ciclo
    document.querySelectorAll('.ciclo-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const cicloId = this.dataset.ciclo;
            const cursosDiv = document.querySelector(`.ciclo-cursos[data-ciclo="${cicloId}"]`);
            
            if (this.checked) {
                cursosDiv.style.opacity = '1';
                cursosDiv.style.pointerEvents = 'auto';
            } else {
                cursosDiv.style.opacity = '0.4';
                cursosDiv.style.pointerEvents = 'none';
            }
        });
    });
</script>
@endpush
@endsection
