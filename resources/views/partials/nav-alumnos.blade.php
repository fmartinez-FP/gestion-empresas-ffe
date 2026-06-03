{{-- Subnav Alumnos --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('alumnos.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors
              {{ request()->routeIs('alumnos.*') && !request()->routeIs('asignaciones.*') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        Alumnos
    </a>
    <a href="{{ route('alumnos.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors
              {{ request()->routeIs('asignaciones.*') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Asignaciones
    </a>
    @if(Route::has('asignaciones.seguimientos.index'))
    <a href="{{ route('alumnos.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors
              {{ request()->routeIs('asignaciones.seguimientos.*') || request()->routeIs('asignaciones.calendario.*') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        Cuaderno de seguimiento
    </a>
    @endif
</div>
