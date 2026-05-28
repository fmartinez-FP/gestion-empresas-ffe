<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; line-height: 1.4; color: #1e293b; }
        .header { background: #2563eb; color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 11px; opacity: 0.9; }
        .section { margin-bottom: 20px; }
        .section-title { background: #f1f5f9; padding: 8px 12px; font-size: 12px; font-weight: bold; color: #334155; border-left: 4px solid #2563eb; margin-bottom: 10px; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th { background: #2563eb; color: white; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table.data-table td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        table.data-table tr:nth-child(even) { background: #f8fafc; }
        table.data-table tfoot td { background: #1e40af; color: white; font-weight: bold; }
        .ciclo-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .ciclo-basica { background: #fed7aa; color: #9a3412; }
        .ciclo-media { background: #bfdbfe; color: #1e40af; }
        .ciclo-superior { background: #e9d5ff; color: #6b21a8; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; }
        .totals-box { background: #eff6ff; border: 2px solid #2563eb; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .totals-grid { display: table; width: 100%; }
        .totals-item { display: table-cell; text-align: center; padding: 10px; border-right: 1px solid #bfdbfe; }
        .totals-item:last-child { border-right: none; }
        .totals-value { font-size: 28px; font-weight: bold; color: #2563eb; }
        .totals-label { font-size: 10px; color: #64748b; text-transform: uppercase; margin-top: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Informe de Colocaciones</h1>
        <p>Curso Académico {{ $cursoAcademico }} · {{ config('centro.nombre_corto') }}</p>
    </div>

    <div class="totals-box">
        <div class="totals-grid">
            <div class="totals-item">
                <div class="totals-value">{{ number_format($totales['alumnos_1'] + $totales['alumnos_2']) }}</div>
                <div class="totals-label">Total Alumnos</div>
            </div>
            <div class="totals-item">
                <div class="totals-value">{{ number_format($totales['horas_1'] + $totales['horas_2']) }}</div>
                <div class="totals-label">Total Horas</div>
            </div>
            <div class="totals-item">
                <div class="totals-value">{{ number_format($totales['alumnos_1']) }}</div>
                <div class="totals-label">Alumnos 1º</div>
            </div>
            <div class="totals-item">
                <div class="totals-value">{{ number_format($totales['alumnos_2']) }}</div>
                <div class="totals-label">Alumnos 2º</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Desglose por Ciclo Formativo</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Ciclo Formativo</th>
                    <th style="width: 15%;">Nivel</th>
                    <th class="text-right" style="width: 10%;">1º Alum.</th>
                    <th class="text-right" style="width: 10%;">1º Horas</th>
                    <th class="text-right" style="width: 10%;">2º Alum.</th>
                    <th class="text-right" style="width: 10%;">2º Horas</th>
                    <th class="text-right" style="width: 10%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estadisticas as $stat)
                @php
                    $nivelTexto = match($stat['ciclo']->nivel) {
                        'basica' => 'FP Básica',
                        'media' => 'Grado Medio',
                        'superior' => 'Grado Superior',
                        default => $stat['ciclo']->nivel,
                    };
                @endphp
                <tr>
                    <td>
                        <span class="ciclo-badge ciclo-{{ $stat['ciclo']->nivel }}">{{ $stat['ciclo']->codigo }}</span>
                        {{ $stat['ciclo']->nombre }}
                    </td>
                    <td>{{ $nivelTexto }}</td>
                    <td class="text-right">{{ $stat['primero']->alumnos }}</td>
                    <td class="text-right">{{ number_format($stat['primero']->horas) }}</td>
                    <td class="text-right">{{ $stat['segundo']->alumnos }}</td>
                    <td class="text-right">{{ number_format($stat['segundo']->horas) }}</td>
                    <td class="text-right"><strong>{{ $stat['primero']->alumnos + $stat['segundo']->alumnos }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>TOTALES</strong></td>
                    <td class="text-right">{{ $totales['alumnos_1'] }}</td>
                    <td class="text-right">{{ number_format($totales['horas_1']) }}</td>
                    <td class="text-right">{{ $totales['alumnos_2'] }}</td>
                    <td class="text-right">{{ number_format($totales['horas_2']) }}</td>
                    <td class="text-right"><strong>{{ $totales['alumnos_1'] + $totales['alumnos_2'] }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <table width="100%">
            <tr>
                <td>{{ config('app.name') }}</td>
                <td style="text-align: center;">Generado: {{ $fechaGeneracion }}</td>
                <td style="text-align: right;">Informe de Colocaciones {{ $cursoAcademico }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
