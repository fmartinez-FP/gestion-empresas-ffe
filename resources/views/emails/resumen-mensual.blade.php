<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #334155; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #7c3aed, #2563eb); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; }
        .footer { background: #1e293b; color: #94a3b8; padding: 15px; border-radius: 0 0 8px 8px; font-size: 12px; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .stat-box { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: center; }
        .stat-number { font-size: 28px; font-weight: bold; color: #1e293b; }
        .stat-label { font-size: 12px; color: #64748b; text-transform: uppercase; }
        .stat-green .stat-number { color: #16a34a; }
        .stat-yellow .stat-number { color: #ca8a04; }
        .stat-red .stat-number { color: #dc2626; }
        .stat-blue .stat-number { color: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">📊 Resumen FCT - {{ $nombreMes }}</h1>
            <p style="margin:5px 0 0 0; opacity: 0.9;">Curso {{ $cursoActual }}</p>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $usuario->nombre }}</strong>,</p>
            <p>Aquí tienes el resumen de actividad FCT del mes de {{ $nombreMes }}:</p>
            
            <div class="stats-grid">
                <div class="stat-box stat-blue">
                    <div class="stat-number">{{ $stats['num_asignaciones'] }}</div>
                    <div class="stat-label">Asignaciones</div>
                </div>
                <div class="stat-box stat-green">
                    <div class="stat-number">{{ $stats['alumnos_colocados'] }}</div>
                    <div class="stat-label">Alumnos enviados a empresas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ number_format($stats['horas_totales']) }}</div>
                    <div class="stat-label">Horas de prácticas</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $stats['nuevas_empresas'] }}</div>
                    <div class="stat-label">Nuevas empresas</div>
                </div>
            </div>

            <h3 style="color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Estado de Convenios</h3>
            <div class="stats-grid">
                <div class="stat-box stat-yellow">
                    <div class="stat-number">{{ $stats['convenios_por_caducar'] }}</div>
                    <div class="stat-label">Próximos a caducar</div>
                </div>
                <div class="stat-box stat-red">
                    <div class="stat-number">{{ $stats['convenios_caducados'] }}</div>
                    <div class="stat-label">Caducados</div>
                </div>
            </div>

            <p style="margin-top: 20px;">
                <a href="{{ config('app.url') }}/informes" style="background: #7c3aed; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                    Ver informes completos
                </a>
            </p>
        </div>
        <div class="footer">
            {{ config('app.name') }}<br>
            Este es un mensaje automático, no responda a este correo.
        </div>
    </div>
</body>
</html>
