<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #334155; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc2626; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; }
        .footer { background: #1e293b; color: #94a3b8; padding: 15px; border-radius: 0 0 8px 8px; font-size: 12px; }
        .empresa { background: white; border: 1px solid #fecaca; border-left: 4px solid #dc2626; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .empresa-nombre { font-weight: bold; color: #1e293b; }
        .empresa-info { font-size: 14px; color: #64748b; margin-top: 5px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-warning { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">⚠️ Convenios Próximos a Caducar</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $usuario->nombre }}</strong>,</p>
            <p>Los siguientes convenios caducarán en los próximos 30 días:</p>
            
            @foreach($empresas as $empresa)
            <div class="empresa">
                <div class="empresa-nombre">{{ $empresa->nombre }}</div>
                <div class="empresa-info">
                    <span class="badge badge-warning">Vence: {{ $empresa->fecha_vencimiento->format('d/m/Y') }}</span>
                    ({{ $empresa->dias_hasta_vencimiento }} días)<br>
                    Ciclos: {{ $empresa->ciclos->pluck('codigo')->join(', ') }}<br>
                    @if($empresa->creador)
                    Profesor: {{ $empresa->creador->nombre }}
                    @endif
                </div>
            </div>
            @endforeach

            <p style="margin-top: 20px;">
                <a href="{{ config('app.url') }}/empresas" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                    Ver en el sistema
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
