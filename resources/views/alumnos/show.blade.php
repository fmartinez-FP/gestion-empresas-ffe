@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('alumnos.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver al listado</a>
    </div>

    {{-- Cabecera --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $alumno->nombre_completo }}</h1>
                <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-500">
                    @if($alumno->email)
                        <span>{{ $alumno->email }}</span>
                    @endif
                    @if($alumno->telefono)
                        <span>· {{ $alumno->telefono }}</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    @if($alumno->ciclo)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $alumno->ciclo->codigo }} — {{ $alumno->ciclo->nombre }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                        {{ $alumno->curso_academico }} · {{ $alumno->numero_curso }}º curso
                    </span>
                    @if($alumno->importado_via === 'excel')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">Importado</span>
                    @endif
                    @if($alumno->user)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            Cuenta portal {{ $alumno->user->activo ? 'activa' : 'inactiva' }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 mt-1">
                @can('editarAlumno')
                <a href="{{ route('alumnos.edit', $alumno) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50">
                    Editar
                </a>
                @endcan
                @can('eliminarAlumno')
                <form method="POST" action="{{ route('alumnos.destroy', $alumno) }}"
                      onsubmit="return confirm('¿Dar de baja a {{ addslashes($alumno->nombre_completo) }}? El alumno pasará al Archivo y su cuenta de portal quedará desactivada.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-red-300 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50">
                        Dar de baja
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    {{-- Asignaciones FFE --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Asignaciones FFE</h2>
            @can('crearAlumno')
            @if(Route::has('alumnos.asignaciones.create'))
            <a href="{{ route('alumnos.asignaciones.create', $alumno) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                + Nueva asignación
            </a>
            @endif
            @endcan
        </div>

        @if($alumno->asignaciones->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-gray-400">
                Este alumno no tiene asignaciones FFE registradas.
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Empresa</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Período</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Tutor IES</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($alumno->asignaciones as $asignacion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $asignacion->empresa->nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            @if($asignacion->fecha_inicio)
                                {{ \Carbon\Carbon::parse($asignacion->fecha_inicio)->format('d/m/Y') }}
                                @if($asignacion->fecha_fin)
                                    — {{ \Carbon\Carbon::parse($asignacion->fecha_fin)->format('d/m/Y') }}
                                @endif
                            @else
                                <span class="text-gray-300">Sin fechas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $asignacion->tutorIes->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $colores = ['activa' => 'bg-green-100 text-green-800', 'finalizada' => 'bg-gray-100 text-gray-600', 'cancelada' => 'bg-red-100 text-red-700'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$asignacion->estado] ?? '' }}">
                                {{ ucfirst($asignacion->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('asignaciones.show', $asignacion) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Ver →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection
