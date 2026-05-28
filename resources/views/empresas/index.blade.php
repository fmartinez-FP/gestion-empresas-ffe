@extends('layouts.app')

@section('title', 'Empresas')

@section('content')
<div class="space-y-6">
    
    <!-- Cabecera -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Empresas</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Catálogo de empresas con convenio</p>
        </div>
        <div class="flex items-center gap-3">
            @admin
            <a href="{{ route('empresas.import') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition-colors shadow-lg shadow-green-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Importar
            </a>
            @endadmin
            <a href="{{ route('empresas.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Empresa
            </a>
        </div>
    </div>
    
    <!-- Tabs Lista / Mapa -->
    <div>
        <div class="flex gap-1 mb-4 bg-slate-100 dark:bg-slate-700 p-1 rounded-xl w-fit">
            <button onclick="switchTab('lista')" id="btn-lista"
                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2 bg-white dark:bg-slate-800 shadow text-slate-800 dark:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Lista
            </button>
            <button onclick="switchTab('mapa')" id="btn-mapa"
                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Mapa
            </button>
        </div>

        <div id="tab-lista">
            @livewire('buscador-empresas')
        </div>

        <div id="tab-mapa" style="display:none">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-4">
                <div class="flex flex-wrap gap-3 items-center">
                    <select id="filtro-ciclo" onchange="cargarDatosMapa()"
                            class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">Todos los ciclos</option>
                        @foreach(\App\Models\CicloFormativo::activos()->orderBy('nombre')->get() as $ciclo)
                        <option value="{{ $ciclo->id }}">{{ $ciclo->codigo }} - {{ $ciclo->nombre }}</option>
                        @endforeach
                    </select>
                    <select id="filtro-estado" onchange="cargarDatosMapa()"
                            class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="por_caducar">Próximo a caducar</option>
                        <option value="caducado">Caducado</option>
                    </select>
                    <select id="filtro-curso" onchange="cargarDatosMapa()"
                            class="px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">1º y 2º</option>
                        <option value="primero">Solo 1º</option>
                        <option value="segundo">Solo 2º</option>
                    </select>
                    <span id="mapa-contador" class="text-xs text-slate-400 ml-auto"></span>
                </div>
            </div>
            <div id="mapa-empresas"
                 class="w-full rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700"
                 style="height:600px"></div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    @endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endpush
    <script>
    let mapaLeaflet  = null;
    let marcadores    = [];
    let mapaLeyenda   = null;
    const MAPA_URL    = '{{ route("mapa.datos") }}';

    const COLORES = {
        'basica':                '#f97316',
        'media':                 '#3b82f6',
        'superior':              '#a855f7',
        'basica+media':          '#eab308',
        'basica+superior':       '#ef4444',
        'media+superior':        '#6366f1',
        'basica+media+superior': '#10b981',
        '':                      '#64748b',
    };

    const ETIQUETAS = {
        'basica':                'Solo FP B\u00e1sica',
        'media':                 'Solo Grado Medio',
        'superior':              'Solo Grado Superior',
        'basica+media':          'FP B\u00e1sica + Grado Medio',
        'basica+superior':       'FP B\u00e1sica + Grado Superior',
        'media+superior':        'Grado Medio + Grado Superior',
        'basica+media+superior': 'Todos los niveles',
        '':                      'Sin ciclos asignados',
    };

    function switchTab(tab) {
        document.getElementById('tab-lista').style.display = tab === 'lista' ? 'block' : 'none';
        document.getElementById('tab-mapa').style.display  = tab === 'mapa'  ? 'block' : 'none';
        const on  = 'tab-btn px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2 bg-white dark:bg-slate-800 shadow text-slate-800 dark:text-white';
        const off = 'tab-btn px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2 text-slate-500 hover:text-slate-700 dark:text-slate-400';
        document.getElementById('btn-lista').className = tab === 'lista' ? on : off;
        document.getElementById('btn-mapa').className  = tab === 'mapa'  ? on : off;
        if (tab === 'mapa') setTimeout(initMapa, 80);
    }

    function initMapa() {
        if (mapaLeaflet) { mapaLeaflet.invalidateSize(); return; }
        mapaLeaflet = L.map('mapa-empresas').setView([40.4168, -3.7038], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '\u00a9 <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(mapaLeaflet);
        cargarDatosMapa();
    }

    async function cargarDatosMapa() {
        if (!mapaLeaflet) return;
        const params = new URLSearchParams();
        const ciclo  = document.getElementById('filtro-ciclo')?.value;
        const estado = document.getElementById('filtro-estado')?.value;
        const curso  = document.getElementById('filtro-curso')?.value;
        if (ciclo)  params.append('ciclo',  ciclo);
        if (estado) params.append('estado', estado);
        if (curso)  params.append('curso',  curso);
        const res   = await fetch(MAPA_URL + '?' + params.toString());
        const datos = await res.json();
        actualizarMarcadores(datos);
        actualizarLeyenda(datos);
    }

    function actualizarMarcadores(datos) {
        marcadores.forEach(m => { try { mapaLeaflet.removeLayer(m); } catch(e){} });
        marcadores = [];

        datos.forEach(e => {
            const color = COLORES[e.niveles] ?? '#64748b';
            const icono = L.divIcon({
                className: '',
                html: '<div style="width:13px;height:13px;border-radius:50%;background:' + color + ';border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>',
                iconSize: [13,13], iconAnchor: [6,6],
            });
            const m = L.marker([e.lat, e.lng], {icon: icono})
                .bindPopup(
                    '<div style="min-width:190px">' +
                    '<p style="font-weight:600;margin-bottom:3px;color:#1e293b">' + e.nombre + '</p>' +
                    '<p style="font-size:11px;color:#64748b;margin-bottom:6px">' + e.direccion + '</p>' +
                    '<a href="' + e.url + '" style="font-size:12px;color:#4f46e5;text-decoration:underline">Ver ficha \u2192</a>' +
                    '</div>'
                )
                .addTo(mapaLeaflet);
            marcadores.push(m);
        });

        const el = document.getElementById('mapa-contador');
        if (el) el.textContent = datos.length
            ? datos.length + ' sede' + (datos.length !== 1 ? 's' : '') + ' en el mapa'
            : 'Sin resultados con coordenadas';

        if (datos.length) mapaLeaflet.fitBounds(L.featureGroup(marcadores).getBounds().pad(0.1));
    }

    function actualizarLeyenda(datos) {
        if (!mapaLeaflet) return;
        if (mapaLeyenda) { mapaLeyenda.remove(); mapaLeyenda = null; }
        if (!datos.length) return;

        const presentes = [...new Set(datos.map(e => e.niveles ?? ''))];

        const items = Object.entries(COLORES)
            .filter(([k]) => presentes.includes(k))
            .map(([k, v]) =>
                '<div style="display:flex;align-items:center;gap:8px;margin-bottom:5px">' +
                '<div style="width:12px;height:12px;border-radius:50%;flex-shrink:0;background:' + v + ';border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3)"></div>' +
                '<span style="color:#475569;font-size:11px">' + (ETIQUETAS[k] ?? k) + '</span>' +
                '</div>'
            ).join('');

        mapaLeyenda = L.control({ position: 'bottomright' });
        mapaLeyenda.onAdd = function() {
            const div = L.DomUtil.create('div', '');
            div.innerHTML =
                '<div style="background:white;padding:12px 14px;border-radius:10px;' +
                'box-shadow:0 2px 8px rgba(0,0,0,.15);font-family:inherit;min-width:210px">' +
                '<p style="font-weight:600;margin:0 0 8px;color:#1e293b;font-size:12px">Niveles formativos</p>' +
                items + '</div>';
            return div;
        };
        mapaLeyenda.addTo(mapaLeaflet);
    }
    </script>


</div>
@endsection
