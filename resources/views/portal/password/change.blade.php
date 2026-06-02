@extends('layouts.portal')

@section('title', 'Cambiar contraseña')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white shadow-md rounded-lg p-8">

        <h2 class="text-xl font-bold text-gray-800 mb-2">Cambiar contraseña</h2>
        <p class="text-gray-500 text-sm mb-6">
            Debes establecer una nueva contraseña antes de continuar.
        </p>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
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
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Nueva contraseña
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="8"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                >
                <p class="text-xs text-gray-400 mt-1">Mínimo 8 caracteres.</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmar contraseña
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-primary text-white py-2 px-4 rounded font-medium text-sm hover:bg-blue-700 transition"
            >
                Guardar contraseña
            </button>
        </form>
    </div>
</div>
@endsection
