@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('alumnos.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Alumnos activos</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Archivo de alumnos</h1>
        <p class="text-sm text-gray-500 mt-1">Alumnos dados de baja. Acceso de solo lectura. {{ $alumnos->total() }} registro{{ $alumnos->total() !== 1 ? 's' : '' }}.</p>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('alumnos.archivo') }}" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o apellidos…"
                       class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500">
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
            <a href="{{ route('alumnos.archivo') }}" class="px-4 py-2 bg-white border border-gray-300 text-sm text-gray-600 rounded-lg hover:bg-gray-50">Limpiar</a>
            @endif
        </div>
    </form>

    @if($alumnos->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 text-sm">
            No hay alumnos en el archivo con los filtros seleccionados.
        </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Ciclo</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Curso</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Dado de baja</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Asignaciones</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($alumnos as $alumno)
                <tr class="hover:bg-gray-50 opacity-80">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-700">{{ $alumno->nombre_completo }}</div>
                        @if($alumno->email)<div class="text-xs text-gray-400">{{ $alumno->email }}</div>@endif
                    </td>
                    <td class="px-4 py-3">
                        @if($alumno->ciclo)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ $alumno->ciclo->codigo }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $alumno->curso_academico }} · {{ $alumno->numero_curso }}º
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">
                        {{ \Carbon\Carbon::parse($alumno->deleted_at)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $alumno->asignaciones->count() }} asignación{{ $alumno->asignaciones->count() !== 1 ? 'es' : '' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('alumnos.show', $alumno->id) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Ver →</a>
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
