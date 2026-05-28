<!DOCTYPE html>
<html lang="es" class="h-full {{ auth()->user()?->modoOscuro() ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#172554' },
                        accent: { 50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .nav-link { transition: all 0.2s ease; }
    </style>
    @stack('styles')
    @livewireStyles
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 font-sans antialiased">
    <div class="min-h-full flex flex-col">
        
        <!-- Navegación -->
        <nav class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    
                    <!-- Logo -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 flex-shrink-0">
                        <img src="{{ asset('favicon.png') }}" alt="FFE" class="h-10 w-10 rounded-lg object-contain">
                        <div class="hidden sm:block leading-tight">
                            <span class="block text-sm font-bold text-slate-800 dark:text-white">Gestión de Empresas</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ config('app.name') }}</span>
                        </div>
                    </a>

                    <!-- Navegación central -->
                    @auth
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('empresas.index') }}" class="nav-link px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 {{ request()->routeIs('empresas.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Empresas
                        </a>
                        <a href="{{ route('seguimientos.index') }}" class="nav-link px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 {{ request()->routeIs('seguimientos.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Agenda
                        </a>
                                                <a href="{{ route('colocaciones.index') }}" class="nav-link px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 {{ request()->routeIs('colocaciones.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Histórico
                        </a>
                        <a href="{{ route('informes.index') }}" class="nav-link px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 {{ request()->routeIs('informes.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Informes
                        </a>
                        @admin
                        <a href="{{ route('admin.usuarios.index') }}" class="nav-link px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 {{ request()->routeIs('admin.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Administración
                        </a>
                        @endadmin
                    </nav>
                    @endauth

                    <!-- Derecha: buscador + usuario -->
                    @auth
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <!-- Buscador -->
                        <div class="hidden lg:block relative" x-data="{ open: false, query: '', results: [] }">
                            <input type="text" placeholder="Buscar empresas..." x-model="query"
                                   @input.debounce.300ms="if(query.length >= 2) { fetch('/buscar?q='+query).then(r=>r.json()).then(d=>{ results=d; open=true; })} else { results=[]; open=false; }"
                                   @focus="if(results.length) open=true" @click.away="open=false"
                                   class="w-40 px-3 py-1.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary-500">
                            <div x-show="open && results.length" x-cloak class="absolute right-0 top-full mt-1 w-72 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 z-50">
                                <template x-for="r in results" :key="r.id">
                                    <a :href="r.url" class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-100 dark:border-slate-700 last:border-0">
                                        <p class="text-sm font-medium text-slate-800 dark:text-white" x-text="r.nombre"></p>
                                        <p class="text-xs text-slate-500" x-text="r.cif + (r.ciclos ? ' · ' + r.ciclos : '')"></p>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- Modo oscuro -->
                        <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg" title="Cambiar tema">
                            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>


                        <!-- Notificaciones -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg" title="Notificaciones">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if($notificacionesNav->count() > 0)
                                <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold leading-none">
                                    {{ $notificacionesNav->count() > 9 ? '9+' : $notificacionesNav->count() }}
                                </span>
                                @endif
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Notificaciones</h3>
                                    @if($notificacionesNav->count() > 0)
                                    <span class="text-xs text-slate-400">{{ $notificacionesNav->count() }} sin leer</span>
                                    @endif
                                </div>

                                @if($notificacionesNav->count() > 0)
                                <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-96 overflow-y-auto">
                                    @foreach($notificacionesNav as $notif)
                                    <a href="{{ route('notificaciones.leer', $notif) }}"
                                       class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                        <div class="flex-shrink-0 mt-0.5">
                                            @php
                                                $ic = match($notif->tipo) {
                                                    'convenio_caducando'   => ['bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                                                    'colocacion_registrada'=> ['bg-green-100 dark:bg-green-900/30 text-green-600', 'M12 4v16m8-8H4'],
                                                    'empresa_editada'      => ['bg-blue-100 dark:bg-blue-900/30 text-blue-600', 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                                                    'convenio_renovado'    => ['bg-green-100 dark:bg-green-900/30 text-green-600', 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                                                    default                => ['bg-primary-100 dark:bg-primary-900/30 text-primary-600', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                                                };
                                            @endphp
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $ic[0] }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ic[1] }}"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-slate-800 dark:text-white leading-snug">{{ $notif->titulo }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                <div class="px-4 py-8 text-center">
                                    <svg class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <p class="text-sm text-slate-400">Sin notificaciones pendientes</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Perfil dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                                <div class="hidden sm:block text-right max-w-[100px]">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-white truncate">{{ auth()->user()->nombre }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->nombre_rol }}</p>
                                </div>
                                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg shadow-primary-500/20">
                                    {{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}
                                </div>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 py-2 z-50">
                                <div class="px-4 py-2 border-b border-slate-200 dark:border-slate-700">
                                    <p class="text-sm font-medium text-slate-800 dark:text-white truncate">{{ auth()->user()->nombre }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                </div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Menú móvil -->
                        <button type="button" class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">Iniciar sesión</a>
                    @endauth
                </div>
            </div>

            <!-- Menú móvil -->
            @auth
            <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                <div class="px-4 py-3 space-y-1">
                    <a href="{{ route('empresas.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('empresas.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-600' }}">Empresas</a>
                    <a href="{{ route('seguimientos.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('seguimientos.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-600' }}">Agenda</a>
                    <a href="{{ route('colocaciones.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('colocaciones.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-600' }}">Histórico</a>
                    <a href="{{ route('informes.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('informes.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-600' }}">Informes</a>
                    @admin
                    <a href="{{ route('admin.usuarios.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-600' }}">Administración</a>
                    @endadmin
                </div>
            </div>
            @endauth
        </nav>

        <!-- Contenido principal -->
        <main class="flex-1 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Mensajes flash -->
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-green-500 hover:text-green-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-500 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                @endif

                @if($errors->any())
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Por favor, corrige los siguientes errores:</p>
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-auto border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('centro.nombre_corto') }}" class="h-8 w-auto opacity-60 dark:opacity-40">
                        <p class="text-sm text-slate-500 dark:text-slate-400">© {{ date('Y') }} {{ config('app.name') }}</p>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 flex-shrink-0">v2.0.0</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
    function toggleDarkMode() {
        document.documentElement.classList.toggle('dark');
        fetch('/preferencias/modo-oscuro', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
        });
    }
    </script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
