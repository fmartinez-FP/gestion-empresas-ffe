<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .login-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1d4ed8 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        .floating-delayed {
            animation: float 8s ease-in-out infinite;
            animation-delay: -2s;
        }
    </style>
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-full flex login-bg relative overflow-hidden">
        
        <!-- Elementos decorativos de fondo -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full floating"></div>
            <div class="absolute top-1/4 -left-20 w-60 h-60 bg-white/5 rounded-full floating-delayed"></div>
            <div class="absolute bottom-20 right-1/4 w-40 h-40 bg-white/10 rounded-full floating"></div>
            <div class="absolute -bottom-20 left-1/3 w-72 h-72 bg-white/5 rounded-full floating-delayed"></div>
        </div>

        <!-- Panel izquierdo: Información -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 items-center justify-center p-12 relative">
            <div class="max-w-lg text-white">
                <div class="mb-8">
                    @if(file_exists(public_path('images/logo.png')))
                        <img 
                            src="{{ asset('images/logo.png') }}" 
                            alt="{{ config('centro.nombre_corto') }}" 
                            class="h-20 w-auto"
                        >
                    @else
                        <div class="h-16 w-16 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur">
                            <span class="text-3xl font-bold">IP</span>
                        </div>
                    @endif
                </div>
                
                <h1 class="text-4xl xl:text-5xl font-bold mb-6 leading-tight">
                    Gestión de Empresas<br>
                    <span class="text-primary-200">con Convenio</span>
                </h1>
                
                <p class="text-lg text-primary-100 mb-8 leading-relaxed">
                    Plataforma centralizada para la gestión de empresas colaboradoras 
                    y el seguimiento de prácticas formativas del centro.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <span class="text-primary-100">Catálogo centralizado de empresas</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <span class="text-primary-100">Histórico de asignaciones por ciclo</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-primary-100">Control automático de vigencia de convenios</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel derecho: Formulario -->
        <div class="w-full lg:w-1/2 xl:w-2/5 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                <div class="glass-card rounded-3xl shadow-2xl p-8 sm:p-10">
                    
                    <!-- Logo móvil -->
                    <div class="lg:hidden text-center mb-8">
                        @if(file_exists(public_path('images/logo.png')))
                            <img 
                                src="{{ asset('images/logo.png') }}" 
                                alt="{{ config('centro.nombre_corto') }}" 
                                class="h-16 w-auto mx-auto"
                            >
                        @else
                            <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-primary-600 to-primary-700 flex items-center justify-center mx-auto shadow-lg shadow-primary-500/30">
                                <span class="text-white text-2xl font-bold">IP</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-slate-800">Bienvenido</h2>
                        <p class="text-slate-500 mt-2">Inicia sesión para continuar</p>
                    </div>

                    <!-- Errores -->
                    @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Formulario -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-700 mb-2">
                                Usuario
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
                                    value="{{ old('username') }}"
                                    required 
                                    autofocus
                                    autocomplete="username"
                                    placeholder="nombre.usuario"
                                    class="w-full pl-12 pr-4 py-3 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-shadow"
                                >
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500">Tu nombre de usuario (parte antes de la @ de tu correo)</p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                                Contraseña
                            </label>
                            <div class="relative" x-data="{ showPassword: false }">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    id="password" 
                                    name="password" 
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full pl-12 pr-12 py-3 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-shadow"
                                >
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                >
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="remember" 
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                >
                                <span class="text-sm text-slate-600">Recordar sesión</span>
                            </label>
                        </div>

                        <button 
                            type="submit"
                            class="w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40"
                        >
                            Iniciar sesión
                        </button>
                    </form>
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-white/60 mt-8">
                    © {{ date('Y') }} {{ config('centro.nombre_corto') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
