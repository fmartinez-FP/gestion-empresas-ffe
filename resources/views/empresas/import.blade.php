@extends('layouts.app')

@section('title', 'Importar Empresas')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('empresas.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Importar Empresas</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Carga masiva desde archivo Excel</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">
        <!-- Descargar plantilla -->
        <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-800">
            <div>
                <p class="font-medium text-primary-800 dark:text-primary-200">Plantilla de importación</p>
                <p class="text-sm text-primary-600 dark:text-primary-400">Descarga la plantilla con el formato correcto</p>
            </div>
            <a href="{{ route('empresas.import.plantilla') }}" class="px-4 py-2 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700">
                Descargar
            </a>
        </div>

        <form method="POST" action="{{ route('empresas.import.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Archivo Excel <span class="text-red-500">*</span></label>
                <input type="file" name="archivo" accept=".xlsx,.xls" required
                       class="w-full px-4 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-100 file:text-primary-700 hover:file:bg-primary-200">
                <p class="mt-1 text-sm text-slate-500">Formatos: .xlsx, .xls. Máximo 10MB.</p>
                @error('archivo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="actualizar_existentes" value="1" class="w-5 h-5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                <div>
                    <span class="font-medium text-slate-800 dark:text-white">Actualizar existentes</span>
                    <p class="text-sm text-slate-500">Si el CIF ya existe, actualiza los datos en lugar de omitir</p>
                </div>
            </label>

            <div class="flex gap-3 justify-end pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('empresas.index') }}" class="px-6 py-2.5 text-slate-600 bg-slate-100 rounded-xl font-medium hover:bg-slate-200">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 shadow-lg shadow-green-500/20">Importar</button>
            </div>
        </form>
    </div>

    @if(session('errores_importacion'))
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
        <h3 class="font-medium text-amber-800 dark:text-amber-200 mb-2">Advertencias durante la importación:</h3>
        <ul class="text-sm text-amber-700 dark:text-amber-400 space-y-1">
            @foreach(session('errores_importacion') as $error)
            <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
