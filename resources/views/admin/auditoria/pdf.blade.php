<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { text-align: center; color: #1e3a8a; font-size: 18px; }
        .fecha { text-align: center; color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e3a8a; color: white; padding: 8px; text-align: left; font-size: 9px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 6px; font-size: 9px; }
        tr:nth-child(even) { background: #f8fafc; }
        .acceso { color: #d97706; }
        .crear { color: #16a34a; }
        .actualizar { color: #2563eb; }
        .eliminar { color: #dc2626; }
    </style>
</head>
<body>
    <h1>Registro de Auditoría</h1>
    <p class="fecha">Generado el {{ date('d/m/Y H:i') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Modelo</th>
                <th>Descripción</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $r)
            <tr>
                <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $r->user_nombre }}</td>
                <td class="{{ $r->accion }}">{{ $r->accion_etiqueta }}</td>
                <td>{{ $r->modelo }}</td>
                <td>{{ $r->descripcion }}</td>
                <td>{{ $r->ip }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
