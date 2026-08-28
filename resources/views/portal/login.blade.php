@extends('layouts.portal')

@section('title', 'Acceso al Portal')

@section('content')
<div class="min-h-screen flex items-center justify-center -mt-8">
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 p-8 w-full max-w-md">

        <div class="text-center mb-6">
            <span class="inline-flex items-center justify-center mb-4">
                <img src="{{ asset('images/logo-ffe.png') }}" alt="Gestión FFE" class="h-14 w-auto">
            </span>
            <h1 class="text-2xl font-bold text-slate-800">Portal del Alumno</h1>
            <p class="text-slate-500 text-sm mt-1">{{ config('centro.nombre', 'FFE') }}</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('portal.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                    Correo electrónico
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="nombre@educa.madrid.org"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                    Contraseña
                </label>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent pr-10"
                    >
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @push('scripts')
                <script>
                function togglePassword() {
                    const input = document.getElementById('password');
                    const icon = document.getElementById('eye-icon');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />';
                    } else {
                        input.type = 'password';
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                    }
                }
                </script>
                @endpush
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="mr-2 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                <label for="remember" class="text-sm text-slate-600">Recordar sesión</label>
            </div>

            <button
                type="submit"
                class="w-full bg-primary-600 text-white py-2.5 px-4 rounded-xl font-medium text-sm hover:bg-primary-700 transition shadow-lg shadow-primary-500/20"
            >
                Acceder
            </button>
        </form>
    </div>
</div>
@endsection
