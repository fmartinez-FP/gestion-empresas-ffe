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
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Marca los RA y CE que se trabajan en empresa este curso.
                Expande cada RA marcado para seleccionar sus criterios.
            </p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-slate-600 dark:text-slate-400">Curso:</label>
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
        Viendo curso <strong>{{ $cursoSeleccionado }}</strong>. Curso activo: <strong>{{ $cursoActivo }}</strong>.
    </div>
    @endif

    {{-- Leyenda --}}
    <div class="flex items-center gap-4 mb-4 text-xs text-slate-500 dark:text-slate-400">
        <span class="flex items-center gap-1.5">
            <span class="w-4 h-4 rounded border-2 bg-emerald-500 border-emerald-500 inline-block"></span> RA elegible
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3.5 h-3.5 rounded border-2 bg-blue-500 border-blue-500 inline-block"></span> CE elegible
        </span>
        <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            Expandir CE
        </span>
    </div>

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
                    <div class="space-y-1">
                        @foreach($modulo->resultadosAprendizaje as $ra)
                        @php $raElegible = $ra->elegibles->isNotEmpty(); @endphp
                        <div x-data="{ open: {{ $raElegible ? 'true' : 'false' }} }">

                            {{-- Fila RA --}}
                            <div class="flex items-center gap-2 py-1">
                                {{-- Toggle RA --}}
                                <button type="button"
                                        class="elegible-ra-toggle flex-shrink-0 w-5 h-5 rounded border-2 transition-all duration-150 flex items-center justify-center
                                               {{ $raElegible ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 dark:border-slate-600 hover:border-emerald-400' }}"
                                        data-ra-id="{{ $ra->id }}"
                                        data-curso="{{ $cursoSeleccionado }}"
                                        data-ce-container="ce-container-{{ $ra->id }}">
                                    <svg class="w-3 h-3 text-white {{ $raElegible ? '' : 'hidden' }}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>

                                {{-- Expandir CE (solo si tiene criterios) --}}
                                @if($ra->criterios->isNotEmpty())
                                <button type="button" @click="open = !open"
                                        class="flex-shrink-0 p-0.5 text-slate-400 hover:text-primary-600 rounded transition-colors"
                                        :title="open ? 'Ocultar criterios' : 'Ver criterios de evaluacion'">
                                    <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="open ? 'rotate-90' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                @else
                                <span class="w-5 flex-shrink-0"></span>
                                @endif

                                {{-- Texto RA --}}
                                <span class="text-sm text-slate-800 dark:text-white leading-snug">
                                    <span class="font-mono text-xs text-slate-400 mr-1">{{ $ra->codigo }}</span>{{ $ra->descripcion }}
                                </span>
                            </div>

                            {{-- CE expandibles --}}
                            @if($ra->criterios->isNotEmpty())
                            <div x-show="open" x-cloak
                                 id="ce-container-{{ $ra->id }}"
                                 class="ml-10 mt-1 mb-2 space-y-1 border-l-2 border-slate-100 dark:border-slate-700 pl-3">
                                @foreach($ra->criterios as $ce)
                                @php $ceElegible = $ce->elegibles->isNotEmpty(); @endphp
                                <div class="flex items-start gap-2 py-0.5">
                                    <button type="button"
                                            class="elegible-ce-toggle flex-shrink-0 mt-0.5 w-4 h-4 rounded border-2 transition-all duration-150 flex items-center justify-center
                                                   {{ $raElegible ? ($ceElegible ? 'bg-blue-500 border-blue-500' : 'border-slate-300 dark:border-slate-600 hover:border-blue-400') : 'border-slate-200 dark:border-slate-700 opacity-40 cursor-not-allowed' }}"
                                            data-ce-id="{{ $ce->id }}"
                                            data-curso="{{ $cursoSeleccionado }}"
                                            {{ $raElegible ? '' : 'disabled' }}>
                                        <svg class="w-2.5 h-2.5 text-white {{ $ceElegible ? '' : 'hidden' }}"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <span class="text-xs text-slate-600 dark:text-slate-300 leading-snug">
                                        <span class="font-mono text-slate-400 mr-1">{{ $ce->codigo }}</span>{{ $ce->descripcion }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                            @endif

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
    var toggleRaUrl  = '{{ route("admin.curriculum.elegibles.toggle") }}';
    var toggleCeUrl  = '{{ route("admin.curriculum.elegibles.toggle-ce") }}';
    var csrfToken    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function postToggle(url, body, onSuccess) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(function (r) { return r.json(); })
        .then(onSuccess)
        .catch(function () { location.reload(); });
    }

    // ── Toggle RA ─────────────────────────────────────────────────────────────
    document.querySelectorAll('.elegible-ra-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var raId        = this.dataset.raId;
            var curso       = this.dataset.curso;
            var checkIcon   = this.querySelector('svg');
            var isActive    = this.classList.contains('bg-emerald-500');
            var ceContainer = document.getElementById(this.dataset.ceContainer);

            // Optimistic UI — RA
            if (isActive) {
                this.classList.remove('bg-emerald-500', 'border-emerald-500');
                this.classList.add('border-slate-300');
                checkIcon.classList.add('hidden');
                // Deshabilitar y desmarcar CE visualmente
                if (ceContainer) {
                    ceContainer.querySelectorAll('.elegible-ce-toggle').forEach(function (ceBtn) {
                        ceBtn.classList.remove('bg-blue-500', 'border-blue-500');
                        ceBtn.classList.add('border-slate-200', 'opacity-40', 'cursor-not-allowed');
                        ceBtn.querySelector('svg').classList.add('hidden');
                        ceBtn.disabled = true;
                    });
                }
            } else {
                this.classList.add('bg-emerald-500', 'border-emerald-500');
                this.classList.remove('border-slate-300');
                checkIcon.classList.remove('hidden');
                // Habilitar CE visualmente
                if (ceContainer) {
                    ceContainer.querySelectorAll('.elegible-ce-toggle').forEach(function (ceBtn) {
                        ceBtn.classList.remove('opacity-40', 'cursor-not-allowed', 'border-slate-200');
                        ceBtn.classList.add('border-slate-300');
                        ceBtn.disabled = false;
                    });
                }
            }

            postToggle(toggleRaUrl, { resultado_aprendizaje_id: raId, curso_academico: curso }, function () {});
        });
    });

    // ── Toggle CE ─────────────────────────────────────────────────────────────
    document.querySelectorAll('.elegible-ce-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (this.disabled) return;

            var ceId      = this.dataset.ceId;
            var curso     = this.dataset.curso;
            var checkIcon = this.querySelector('svg');
            var isActive  = this.classList.contains('bg-blue-500');

            if (isActive) {
                this.classList.remove('bg-blue-500', 'border-blue-500');
                this.classList.add('border-slate-300');
                checkIcon.classList.add('hidden');
            } else {
                this.classList.add('bg-blue-500', 'border-blue-500');
                this.classList.remove('border-slate-300');
                checkIcon.classList.remove('hidden');
            }

            postToggle(toggleCeUrl, { criterio_evaluacion_id: ceId, curso_academico: curso }, function () {});
        });
    });
});
</script>
@endpush
