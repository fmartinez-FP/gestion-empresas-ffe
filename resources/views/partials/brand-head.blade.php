{{-- Recursos de marca compartidos: fuentes, Tailwind (CDN) y Alpine.js.
     Incluir en el <head> de cualquier layout/página independiente con @include('partials.brand-head') --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon-64.png') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                colors: {
                    // Azul principal — coherente con el degradado del logo de Gestión FFE
                    primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a',950:'#172554' },
                    // Rosa/magenta de marca — tomado del acento del logo (flechas, punto de la línea de tiempo)
                    accent: { 50:'#fdf2f8',100:'#fce7f3',200:'#fbcfe8',300:'#f9a8d4',400:'#f472b6',500:'#ec4899',600:'#db2777',700:'#be185d',800:'#9d174d',900:'#831843',950:'#500724' },
                    // Azul marino oscuro del texto del logo — para superficies/hero de marca puntuales
                    brand: { 400:'#33456b',500:'#22314f',600:'#1a2540',700:'#141d33',800:'#0f1626',900:'#0a0f1a',950:'#070a12' },
                }
            }
        }
    }
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
