{{-- Subnav Informes --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('informes.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors
              {{ request()->routeIs('informes.*') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600' }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Informes
    </a>
</div>
