<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal FFE') — {{ config('centro.nombre_corto', 'FFE') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Escala completa de 'primary', igual que layouts/app.blade.php (sesion 2026-08-10):
        // antes 'primary' era un solo valor, que en Tailwind solo genera la clase base
        // bg-primary/text-primary, no bg-primary-600 ni hover:bg-primary-700 -- de ahi que
        // varios botones del portal (ej. "Ver cuaderno") quedaran sin fondo, invisibles.
        // Se mantiene DEFAULT con el mismo valor de antes para que bg-primary (navbar) no cambie.
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#1d4ed8',
                            50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#172554'
                        },
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- Navbar --}}
    @auth('web_externo')
    <nav class="bg-primary shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <span class="text-white font-bold text-lg">Portal FFE</span>
                    <span class="text-blue-200 text-sm">{{ config('centro.nombre_corto', '') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-blue-100 text-sm">{{ auth('web_externo')->user()->nombre }}</span>
                    <form method="POST" action="{{ route('portal.logout') }}">
                        @csrf
                        <button type="submit" class="text-blue-200 hover:text-white text-sm underline">
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
