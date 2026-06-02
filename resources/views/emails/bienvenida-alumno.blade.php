<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .header { background-color: #1d4ed8; color: white; padding: 20px 24px; border-radius: 6px 6px 0 0; }
        .content { background: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; border-radius: 0 0 6px 6px; }
        .credentials { background: white; border: 1px solid #d1d5db; border-radius: 6px; padding: 16px; margin: 16px 0; }
        .credentials p { margin: 4px 0; font-size: 14px; }
        .credentials strong { display: inline-block; width: 120px; color: #6b7280; }
        .btn { display: inline-block; background: #1d4ed8; color: white; padding: 10px 24px; border-radius: 6px; text-decoration: none; margin-top: 16px; }
        .footer { font-size: 12px; color: #9ca3af; margin-top: 24px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin:0">Portal de Prácticas FFE</h2>
        <p style="margin:4px 0 0;font-size:14px;opacity:0.85">{{ config('centro.nombre', 'IES') }}</p>
    </div>
    <div class="content">
        <p>Hola, <strong>{{ $alumno->nombre }} {{ $alumno->apellidos }}</strong>:</p>
        <p>
            Se ha creado tu cuenta de acceso al <strong>Portal de Prácticas FFE</strong>.
            Utiliza las siguientes credenciales para acceder:
        </p>
        <div class="credentials">
            <p><strong>Email:</strong> {{ $alumno->email }}</p>
            <p><strong>Contraseña:</strong> {{ $passwordTemporal }}</p>
        </div>
        <p>
            Al acceder por primera vez se te pedirá que cambies la contraseña.
        </p>
        <a href="{{ url('/portal/login') }}" class="btn">Acceder al Portal</a>
        <p class="footer">
            Este mensaje es automático. Si tienes algún problema de acceso, contacta con tu tutor/a de prácticas.
        </p>
    </div>
</div>
</body>
</html>
