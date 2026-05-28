<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #334155; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; }
        .footer { background: #1e293b; color: #94a3b8; padding: 15px; border-radius: 0 0 8px 8px; font-size: 12px; }
        .asignacion { background: white; border: 1px solid #bfdbfe; border-left: 4px solid #2563eb; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .empresa-nombre { font-weight: bold; color: #1e293b; }
        .asignacion-info { font-size: 14px; color: #64748b; margin-top: 5px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-green { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">📋 Nuevas Asignaciones en tus Empresas</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $usuario->nombre }}</strong>,</p>
            <p>Durante la última semana se han registrado las siguientes asignaciones en empresas que tutorizas:</p>
            
            @foreach($asignaciones as $asignacion)
            <div class="asignacion">
                <div class="empresa-nombre">{{ $asignacion->empresa->nombre }}</div>
                <div class="asignacion-info">
                    <span class="badge badge-primary">{{ $asignacion->ciclo->codigo }}</span>
                    <span class="badge badge-green">{{ $asignacion->num_alumnos }} alumnos · {{ $asignacion->numero_curso }}º</span><br>
                    Registrado por: {{ $asignacion->registradoPor->nombre ?? 'Sistema' }}<br>
                    Fecha: {{ $asignacion->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
            @endforeach

            <p style="margin-top: 20px;">
                <a href="{{ config('app.url') }}/colocaciones" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                    Ver histórico completo
                </a>
            </p>
        </div>
        <div class="footer">
            {{ config('app.name') }} · Curso {{ $cursoActual }}<br>
            Este es un mensaje automático, no responda a este correo.
        </div>
    </div>
</body>
</html>
