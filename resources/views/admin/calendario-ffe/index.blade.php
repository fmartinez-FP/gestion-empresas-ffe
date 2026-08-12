@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Calendario FFE — No lectivos del centro</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
        Los días marcados aquí se aplican como no laborables en el calendario de FFE de todos los alumnos.
    </p>

    @include('admin.partials.nav')

    @if (session('success'))
        <div class="mb-6 p-4 rounded-lg bg-accent-100 text-accent-800 dark:bg-accent-900/30 dark:text-accent-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-8">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Añadir rango de no lectivos</h2>

        <form method="POST" action="{{ route('admin.calendario-ffe.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required
                           class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Fecha fin</label>
                    <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" required
                           class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Motivo (opcional)</label>
                    <input type="text" name="motivo" value="{{ old('motivo') }}" maxlength="200"
                           placeholder="Ej. Vacaciones de Navidad"
                           class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Se marcarán automáticamente solo los días laborables (L-V) del rango. Máximo 3 meses de duración.
                El motivo es solo informativo para esta pantalla; en el cuaderno de los alumnos aparecerá siempre como "No lectivo".
            </p>

            @error('fecha_inicio')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            @error('fecha_fin')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-primary-600 text-white hover:bg-primary-700">
                Añadir rango
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white p-6 pb-0">Días marcados</h2>

        @if ($noLectivos->isEmpty())
            <p class="p-6 text-sm text-slate-500 dark:text-slate-400">No hay ningún día no lectivo registrado.</p>
        @else
            <table class="w-full text-sm">
                <thead class="border-b border-slate-200 dark:border-slate-700">
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-3 font-medium">Fecha</th>
                        <th class="px-6 py-3 font-medium">Motivo</th>
                        <th class="px-6 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($noLectivos as $dia)
                        <tr>
                            <td class="px-6 py-3 text-slate-700 dark:text-slate-200">{{ $dia->fecha->format('d/m/Y') }} ({{ ucfirst($dia->fecha->translatedFormat('l')) }})</td>
                            <td class="px-6 py-3 text-slate-500 dark:text-slate-400">{{ $dia->motivo ?? '—' }}</td>
                            <td class="px-6 py-3 text-right">
                                <form method="POST" action="{{ route('admin.calendario-ffe.destroy', $dia) }}"
                                      onsubmit="return confirm('¿Eliminar este día no lectivo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
