@extends('layouts.app')

@section('title', 'Calendario — ' . $asignacion->alumno->nombre . ' ' . $asignacion->alumno->apellidos)

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Calendario de asignación</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $asignacion->alumno->nombre }} {{ $asignacion->alumno->apellidos }} —
                {{ $asignacion->empresa->nombre }}
            </p>
        </div>
        <a href="{{ route('asignaciones.show', $asignacion) }}"
           class="text-sm text-blue-600 hover:underline">← Volver a la asignación</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Resumen días laborables --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold text-gray-700 mb-3">Resumen</h2>
            <div class="flex items-center gap-3">
                <span class="text-4xl font-bold text-blue-600">{{ $diasLaborables }}</span>
                <span class="text-sm text-gray-500">días laborables estimados<br>(excluidos fines de semana y días marcados)</span>
            </div>
            @if($asignacion->num_horas)
            <p class="text-xs text-gray-400 mt-2">Horas previstas: {{ $asignacion->num_horas }} h</p>
            @endif
        </div>

        {{-- Formulario marcar día --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold text-gray-700 mb-3">Marcar día especial</h2>
            <form method="POST" action="{{ route('asignaciones.calendario.store', $asignacion) }}">
                @csrf
                @if(session('success'))
                    <div class="mb-3 p-2 bg-green-100 text-green-800 rounded text-xs">{{ session('success') }}</div>
                @endif
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm @error('fecha') border-red-500 @enderror">
                    @error('fecha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                    <select name="tipo" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm @error('tipo') border-red-500 @enderror">
                        <option value="">— Selecciona —</option>
                        <option value="festivo" {{ old('tipo') === 'festivo' ? 'selected' : '' }}>Festivo</option>
                        <option value="no_lectivo" {{ old('tipo') === 'no_lectivo' ? 'selected' : '' }}>No lectivo</option>
                        <option value="baja" {{ old('tipo') === 'baja' ? 'selected' : '' }}>Baja</option>
                    </select>
                    @error('tipo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Motivo (opcional)</label>
                    <input type="text" name="motivo" value="{{ old('motivo') }}" maxlength="200"
                        placeholder="Ej: Festivo local, Baja médica..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                </div>
                <button type="submit"
                    class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Marcar día
                </button>
            </form>
        </div>
    </div>

    {{-- Listado días especiales --}}
    @if($diasEspeciales->isNotEmpty())
    <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Tipo</th>
                    <th class="px-4 py-3 text-left">Motivo</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($diasEspeciales as $dia)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $dia->fecha->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($dia->tipo === 'festivo') bg-orange-100 text-orange-800
                            @elseif($dia->tipo === 'no_lectivo') bg-purple-100 text-purple-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $dia->tipo)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $dia->motivo ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST"
                              action="{{ route('asignaciones.calendario.destroy', [$asignacion, $dia]) }}"
                              onsubmit="return confirm('¿Eliminar este día del calendario?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
