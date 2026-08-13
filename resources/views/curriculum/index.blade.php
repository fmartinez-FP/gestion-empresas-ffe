@extends('layouts.app')

@section('title', 'Curriculum - Modulos y RA')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">


    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Curriculum</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Modulos, resultados de aprendizaje y criterios de evaluacion por ciclo formativo</p>
        </div>
        @if(auth()->user()->can('gestionarElegibles'))
        <a href="{{ route('admin.curriculum.elegibles.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Elegibles FFE
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($ciclos->isEmpty())
    <div class="text-center py-16 text-slate-400">
        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p class="text-sm">No hay ciclos formativos configurados</p>
    </div>
    @else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($ciclos as $ciclo)
        <a href="{{ route('admin.curriculum.modulos.index', $ciclo) }}"
           class="group block p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:border-primary-400 dark:hover:border-primary-500 hover:shadow-md transition-all">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <h3 class="font-semibold text-slate-800 dark:text-white text-sm leading-snug mb-1">{{ $ciclo->nombre }}</h3>
            @if($ciclo->codigo ?? null)
            <p class="text-xs text-slate-400 mb-3">{{ $ciclo->codigo }}</p>
            @endif
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span>{{ $ciclo->modulos_count }} modulos</span>
                <span>{{ $ciclo->ra_count }} RA</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
