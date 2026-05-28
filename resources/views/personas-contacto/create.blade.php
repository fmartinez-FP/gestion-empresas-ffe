@extends('layouts.app')

@section('title', 'Nueva Persona de Contacto')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('empresas.show', $empresa) }}"
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Nueva Persona de Contacto</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $empresa->nombre }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('personas-contacto.store', $empresa) }}" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="md:col-span-2">
                    <label for="nombre" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre"
                           value="{{ old('nombre') }}"
                           placeholder="Nombre completo"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('nombre') border-red-300 @enderror">
                    @error('nombre')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="cargo" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Cargo</label>
                    <input type="text" id="cargo" name="cargo"
                           value="{{ old('cargo') }}"
                           placeholder="Director RRHH, Gerente..."
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Teléfono directo</label>
                    <input type="tel" id="telefono" name="telefono"
                           value="{{ old('telefono') }}"
                           placeholder="912 345 678"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email directo</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="contacto@empresa.com"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('email') border-red-300 @enderror">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notas" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Notas</label>
                    <textarea id="notas" name="notas" rows="3"
                              placeholder="Disponibilidad, preferencias de contacto..."
                              class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none">{{ old('notas') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="principal" value="1"
                               {{ old('principal') ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Marcar como contacto principal</span>
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 ml-8">Si es la primera persona, se marcará como principal automáticamente.</p>
                </div>

            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ route('empresas.show', $empresa) }}"
               class="px-6 py-2.5 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-xl font-medium hover:bg-slate-200 transition-colors text-center">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20">
                Guardar
            </button>
        </div>
    </form>
</div>
@endsection
