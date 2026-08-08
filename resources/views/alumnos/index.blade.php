@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @include('partials.nav-alumnos')

    {{-- Cabecera --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Alumnos en FFE</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $alumnos->total() }} alumno{{ $alumnos->total() !== 1 ? 's' : '' }} activo{{ $alumnos->total() !== 1 ? 's' : '' }}</p>
        </div>
        <div class="flex gap-2">
            @can('importarAlumnos')
            <a href="{{ route('alumnos.import') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Importar
            </a>
            @endcan
            @can('verArchivo')
            <a href="{{ route('alumnos.archivo') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8"/></svg>
                Archivo
            </a>
            @endcan
            @can('crearAlumno')
            <a href="{{ route('alumnos.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo alumno
            </a>
            @endcan
        </div>
    </div>

    @if($sinGruposTutor ?? false)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm text-amber-800">
                Todavía no tienes grupos asignados para este curso académico. Pide a un administrador o responsable FFE que te asigne los grupos que vas a tutorizar.
            </p>
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="{{ route('alumnos.index') }}" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, apellidos o email…"
                       class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <select name="ciclo_id" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los ciclos</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}" {{ request('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                            {{ $ciclo->codigo }} — {{ $ciclo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="curso_academico" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los cursos</option>
                    @foreach($cursosAcademicos as $ca)
                        <option value="{{ $ca }}" {{ request('curso_academico') === $ca ? 'selected' : '' }}>{{ $ca }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="numero_curso" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">1º y 2º</option>
                    <option value="1" {{ request('numero_curso') == '1' ? 'selected' : '' }}>1º curso</option>
                    <option value="2" {{ request('numero_curso') == '2' ? 'selected' : '' }}>2º curso</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Filtrar</button>
            @if(request()->hasAny(['q','ciclo_id','curso_academico','numero_curso']))
            <a href="{{ route('alumnos.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">Limpiar</a>
            @endif
        </div>
    </form>

    {{-- Tabla --}}
    @if($alumnos->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            <p class="mt-4 text-gray-500">No hay alumnos que coincidan con los filtros.</p>
            @can('crearAlumno')
            <a href="{{ route('alumnos.create') }}" class="mt-4 inline-block text-blue-600 text-sm hover:underline">Añadir el primero</a>
            @endcan
        </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Ciclo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Curso</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Empresa FFE</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($alumnos as $alumno)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ $alumno->nombre_completo }}</div>
                        @if($alumno->email)
                        <div class="text-xs text-gray-400">{{ $alumno->email }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        @if($alumno->ciclo)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">{{ $alumno->ciclo->codigo }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $alumno->curso_academico }}<br>
                        <span class="text-xs text-gray-400">{{ $alumno->numero_curso }}º</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        @if($alumno->asignacionActiva && $alumno->asignacionActiva->empresa)
                            {{ $alumno->asignacionActiva->empresa->nombre }}
                        @else
                            <span class="text-gray-400 text-xs">Sin asignación</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($alumno->asignacionActiva)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">En FFE</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('alumnos.show', $alumno) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs">Ver ficha →</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $alumnos->links() }}</div>
    @endif

</div>
@endsection
