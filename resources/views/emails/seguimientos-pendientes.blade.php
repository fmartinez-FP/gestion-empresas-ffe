<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="font-family: sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <h2 style="color: #1d4ed8;">Seguimientos FFE pendientes de validar</h2>

    <p>Hola,</p>

    <p>Tienes <strong>{{ $seguimientosPendientes->count() }} entrada(s)</strong> del cuaderno de prácticas
    de <strong>{{ $asignacion->alumno->nombre }} {{ $asignacion->alumno->apellidos }}</strong>
    ({{ $asignacion->empresa->nombre }}) pendientes de validar:</p>

    <table style="width:100%; border-collapse: collapse; margin: 16px 0;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="text-align:left; padding:8px 12px; font-size:13px;">Fecha</th>
                <th style="text-align:left; padding:8px 12px; font-size:13px;">Entrada</th>
                <th style="text-align:left; padding:8px 12px; font-size:13px;">Salida</th>
            </tr>
        </thead>
        <tbody>
            @foreach($seguimientosPendientes as $s)
            <tr style="border-top:1px solid #e5e7eb;">
                <td style="padding:8px 12px; font-size:13px;">{{ $s->fecha->format('d/m/Y') }}</td>
                <td style="padding:8px 12px; font-size:13px;">{{ $s->hora_entrada }}</td>
                <td style="padding:8px 12px; font-size:13px;">{{ $s->hora_salida }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p>Accede a la aplicación para revisar y validar las entradas.</p>

    <p style="color:#6b7280; font-size:12px; margin-top:32px;">
        Este mensaje ha sido enviado automáticamente por el sistema FFE del centro.
    </p>

</body>
</html>
