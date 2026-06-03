@extends('portal.layouts.app')

@section('title', 'Cuaderno de prácticas')

@section('content')
<div class="max-w-2xl mx-auto mt-10 text-center">
    <div class="bg-white rounded-lg shadow p-8">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
        </svg>
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Sin asignación activa</h2>
        <p class="text-gray-500">No tienes ninguna asignación FFE activa en este momento.<br>
            Contacta con tu tutor IES si crees que esto es un error.</p>
    </div>
</div>
@endsection
