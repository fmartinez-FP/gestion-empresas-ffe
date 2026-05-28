<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #1e293b; }
        .header { background: #2563eb; color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin-bottom: 5px; }
        .header p { font-size: 12px; opacity: 0.9; }
        .estado-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        .estado-activo { background: #d1fae5; color: #065f46; }
        .estado-por_caducar { background: #fef3c7; color: #92400e; }
        .estado-caducado { background: #fee2e2; color: #991b1b; }
        .estado-sin_convenio { background: #f3f4f6; color: #4b5563; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { background: #f1f5f9; padding: 8px 12px; font-size: 13px; font-weight: bold; color: #334155; border-left: 4px solid #2563eb; margin-bottom: 10px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 30%; padding: 6px 10px; background: #f8fafc; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        .info-value { display: table-cell; padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th { background: #2563eb; color: white; padding: 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.data-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        table.data-table tr:nth-child(even) { background: #f8fafc; }
        table.data-table tfoot td { background: #f1f5f9; font-weight: bold; }
        .ciclo-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; margin: 2px; }
        .ciclo-basica { background: #fed7aa; color: #9a3412; }
        .ciclo-media { background: #bfdbfe; color: #1e40af; }
        .ciclo-superior { background: #e9d5ff; color: #6b21a8; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; }
        .stats-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 15px; margin-top: 10px; }
        .stats-grid { display: table; width: 100%; }
        .stats-item { display: table-cell; text-align: center; padding: 10px; }
        .stats-value { font-size: 24px; font-weight: bold; color: #2563eb; }
        .stats-label { font-size: 10px; color: #64748b; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $empresa->nombre }}</h1>
        <p>CIF: {{ $empresa->cif }}</p>
        <span class="estado-badge estado-{{ $empresa->estado_convenio }}">{{ $empresa->estado_etiqueta }}</span>
    </div>

    <div class="section">
        <div class="section-title">Datos de Contacto</div>
        <div class="info-grid">
            @if($empresa->persona_contacto)
            <div class="info-row"><div class="info-label">Persona de contacto</div><div class="info-value">{{ $empresa->persona_contacto }}</div></div>
            @endif
            @if($empresa->telefono)
            <div class="info-row"><div class="info-label">Teléfono</div><div class="info-value">{{ $empresa->telefono }}</div></div>
            @endif
            @if($empresa->email)
            <div class="info-row"><div class="info-label">Email</div><div class="info-value">{{ $empresa->email }}</div></div>
            @endif
            @if($empresa->direccion)
            <div class="info-row"><div class="info-label">Dirección</div><div class="info-value">{{ $empresa->direccion }}</div></div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Convenio</div>
        <div class="info-grid">
            @if($empresa->num_convenio)
            <div class="info-row"><div class="info-label">Número de convenio</div><div class="info-value">{{ $empresa->num_convenio }}</div></div>
            @endif
            @if($empresa->fecha_firma)
            <div class="info-row"><div class="info-label">Fecha de firma</div><div class="info-value">{{ $empresa->fecha_firma->format('d/m/Y') }}</div></div>
            <div class="info-row"><div class="info-label">Vigencia hasta</div><div class="info-value">{{ $empresa->fecha_vencimiento->format('d/m/Y') }}</div></div>
            @endif
            <div class="info-row"><div class="info-label">Profesor responsable</div><div class="info-value">{{ $empresa->creador->nombre ?? 'No asignado' }}</div></div>
        </div>
    </div>

    @if($empresa->ciclos->count() > 0)
    <div class="section">
        <div class="section-title">Ciclos Formativos que Acepta</div>
        <table class="data-table">
            <thead><tr><th>Ciclo</th><th style="text-align:center;width:80px;">1º Curso</th><th style="text-align:center;width:80px;">2º Curso</th></tr></thead>
            <tbody>
                @foreach($empresa->ciclos as $ciclo)
                <tr>
                    <td><span class="ciclo-badge ciclo-{{ $ciclo->nivel }}">{{ $ciclo->codigo }}</span> {{ $ciclo->nombre }}</td>
                    <td style="text-align:center;">{{ $ciclo->pivot->acepta_primero ? '✓' : '—' }}</td>
                    <td style="text-align:center;">{{ $ciclo->pivot->acepta_segundo ? '✓' : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($empresa->colocaciones->count() > 0)
    <div class="section">
        <div class="section-title">Resumen Histórico</div>
        <div class="stats-box">
            <div class="stats-grid">
                <div class="stats-item"><div class="stats-value">{{ $empresa->total_alumnos }}</div><div class="stats-label">Total Alumnos</div></div>
                <div class="stats-item"><div class="stats-value">{{ number_format($empresa->total_horas) }}</div><div class="stats-label">Total Horas</div></div>
                <div class="stats-item"><div class="stats-value">{{ $empresa->colocaciones->count() }}</div><div class="stats-label">Envíos</div></div>
                <div class="stats-item"><div class="stats-value">{{ $colocacionesPorCurso->count() }}</div><div class="stats-label">Cursos</div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Histórico de Asignaciones</div>
        <table class="data-table">
            <thead><tr><th>Curso Académico</th><th>Ciclo</th><th style="text-align:center;">Nº</th><th style="text-align:right;">Alumnos</th><th style="text-align:right;">Horas</th></tr></thead>
            <tbody>
                @foreach($colocacionesPorCurso as $curso => $colocaciones)
                    @foreach($colocaciones as $colocacion)
                    <tr>
                        <td>{{ $curso }}</td>
                        <td><span class="ciclo-badge ciclo-{{ $colocacion->ciclo->nivel }}">{{ $colocacion->ciclo->codigo }}</span></td>
                        <td style="text-align:center;">{{ $colocacion->numero_curso }}º</td>
                        <td style="text-align:right;">{{ $colocacion->num_alumnos }}</td>
                        <td style="text-align:right;">{{ number_format($colocacion->num_horas) }}h</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot><tr><td colspan="3"><strong>TOTAL</strong></td><td style="text-align:right;"><strong>{{ $empresa->total_alumnos }}</strong></td><td style="text-align:right;"><strong>{{ number_format($empresa->total_horas) }}h</strong></td></tr></tfoot>
        </table>
    </div>
    @endif

    @if($empresa->notas)
    <div class="section">
        <div class="section-title">Notas / Observaciones</div>
        <p style="padding:10px;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;">{{ $empresa->notas }}</p>
    </div>
    @endif

    <div class="footer">
        <table width="100%"><tr><td>{{ config('app.name') }}</td><td style="text-align:center;">Generado: {{ $fechaGeneracion }}</td><td style="text-align:right;">Ficha de empresa</td></tr></table>
    </div>
</body>
</html>
