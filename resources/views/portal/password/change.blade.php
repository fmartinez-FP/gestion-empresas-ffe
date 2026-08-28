@extends('layouts.portal')

@section('title', 'Cambiar contraseña')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 p-8">

        <h2 class="text-xl font-bold text-slate-800 mb-2">Cambiar contraseña</h2>
        <p class="text-slate-500 text-sm mb-6">
            Debes establecer una nueva contraseña antes de continuar.
        </p>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.password.update') }}" class="space-y-4">
            @csrf

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                    Nueva contraseña
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="8"
                    class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                <p class="text-xs text-slate-400 mt-1">Mínimo 8 caracteres.</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                    Confirmar contraseña
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-primary-600 text-white py-2.5 px-4 rounded-xl font-medium text-sm hover:bg-primary-700 transition shadow-lg shadow-primary-500/20"
            >
                Guardar contraseña
            </button>
        </form>
    </div>
</div>
@endsection
