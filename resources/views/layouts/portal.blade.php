<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal FFE') — {{ config('centro.nombre_corto', 'FFE') }}</title>
    @include('partials.brand-head')
    @stack('styles')
</head>
<body class="bg-slate-100 min-h-screen font-sans antialiased">

    {{-- Navbar --}}
    @auth('web_externo')
    <nav class="bg-gradient-to-r from-primary-800 to-primary-600 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center bg-white rounded-lg px-2 py-1.5 shadow-sm">
                        <img src="{{ asset('images/logo-ffe.png') }}" alt="Gestión FFE" class="h-8 w-auto">
                    </span>
                    <span class="hidden sm:block text-primary-100 text-sm">{{ config('centro.nombre_corto', '') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('portal.dashboard') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-primary-700 bg-white hover:bg-primary-50 transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h3a1 1 0 001-1v-3a1 1 0 011-1h1a1 1 0 011 1v3a1 1 0 001 1h3a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                        Inicio
                    </a>
                    <span class="hidden sm:block text-primary-100 text-sm">{{ auth('web_externo')->user()->nombre }}</span>
                    <form method="POST" action="{{ route('portal.logout') }}">
                        @csrf
                        <button type="submit" class="text-primary-200 hover:text-white text-sm underline">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    {{-- Contenido --}}
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
