@extends('layouts.app')

@section('title', 'Elegibles FFE')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6 flex-wrap">
        <a href="{{ route('admin.curriculum.index') }}" class="hover:text-primary-600">Curriculum</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-white font-medium">Elegibles FFE</span>
    </nav>

    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Elegibles FFE</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Marca los resultados de aprendizaje que se trabajan en empresa este curso</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-slate-600 dark:text-slate-400">Curso academico:</label>
            <select name="curso" onchange="this.form.submit()"
                    class="px-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                @foreach($cursosDisponibles as $c)
                <option value="{{ $c }}" {{ $c === $cursoSeleccionado ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($cursoSeleccionado !== $cursoActivo)
    <div class="mb-4 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-300 text-sm">
        Estas viendo el curso <strong>{{ $cursoSeleccionado }}</strong>. El curso activo es <strong>{{ $cursoActivo }}</strong>.
    </div>
    @endif

    <div class="space-y-6">
        @forelse($ciclos as $ciclo)
        @if($ciclo->modulos->isNotEmpty())
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/30 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-white">{{ $ciclo->nombre }}</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($ciclo->modulos as $modulo)
                @if($modulo->resultadosAprendizaje->isNotEmpty())
                <div class="px-5 py-3">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">
                        <span class="font-mono">{{ $modulo->codigo }}</span> &mdash; {{ $modulo->nombre }}
                    </p>
                    <div class="space-y-2">
                        @foreach($modulo->resultadosAprendizaje as $ra)
                        @php $esElegible = $ra->elegibles->isNotEmpty(); @endphp
                        <div class="flex items-start gap-3">
                            <button type="button"
                                    class="elegible-toggle flex-shrink-0 mt-0.5 w-5 h-5 rounded border-2 transition-all duration-150 flex items-center justify-center {{ $esElegible ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 dark:border-slate-600 hover:border-emerald-400' }}"
                                    data-ra-id="{{ $ra->id }}"
                                    data-curso="{{ $cursoSeleccionado }}">
                                <svg class="w-3 h-3 text-white {{ $esElegible ? '' : 'hidden' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-800 dark:text-white leading-snug">
                                    <span class="font-mono text-xs text-slate-400 mr-1">{{ $ra->codigo }}</span>{{ $ra->descripcion }}
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $ra->criterios->count() }} criterios</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
        @empty
        <div class="text-center py-12 text-slate-400 text-sm">No hay ciclos formativos configurados.</div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggleUrl = '{{ route("admin.curriculum.elegibles.toggle") }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.elegible-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var raId = this.dataset.raId;
            var curso = this.dataset.curso;
            var checkIcon = this.querySelector('svg');
            var isActive = this.classList.contains('bg-emerald-500');

            if (isActive) {
                this.classList.remove('bg-emerald-500', 'border-emerald-500');
                this.classList.add('border-slate-300');
                checkIcon.classList.add('hidden');
            } else {
                this.classList.add('bg-emerald-500', 'border-emerald-500');
                this.classList.remove('border-slate-300');
                checkIcon.classList.remove('hidden');
            }

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ resultado_aprendizaje_id: raId, curso_academico: curso })
            }).catch(function () { location.reload(); });
        });
    });
});
</script>
@endpush
