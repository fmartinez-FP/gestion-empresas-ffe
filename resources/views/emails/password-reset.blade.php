<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 40px; text-align: center; border-radius: 16px 16px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">
                                {{ config('centro.nombre_corto') }}
                            </h1>
                            <p style="color: #bfdbfe; margin: 8px 0 0 0; font-size: 14px;">
                                Gestión de Empresas con Convenio
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #1e293b; margin: 0 0 16px 0; font-size: 20px; font-weight: 600;">
                                Restablecer contraseña
                            </h2>
                            
                            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 24px 0;">
                                Hola,
                            </p>
                            
                            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 24px 0;">
                                Has solicitado restablecer la contraseña de tu cuenta en el sistema de Gestión de Empresas del {{ config('centro.nombre_corto') }}. Haz clic en el siguiente botón para crear una nueva contraseña:
                            </p>
                            
                            <!-- Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 32px 0;">
                                <tr>
                                    <td style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 12px;">
                                        <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; padding: 16px 32px; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600;">
                                            Restablecer Contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 0 0 16px 0;">
                                Este enlace caducará en <strong>60 minutos</strong>.
                            </p>
                            
                            <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 0 0 24px 0;">
                                Si no has solicitado restablecer tu contraseña, puedes ignorar este correo. Tu contraseña actual seguirá siendo válida.
                            </p>
                            
                            <!-- Divider -->
                            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 32px 0;">
                            
                            <p style="color: #94a3b8; font-size: 13px; line-height: 1.6; margin: 0;">
                                Si tienes problemas con el botón, copia y pega la siguiente URL en tu navegador:
                            </p>
                            <p style="color: #3b82f6; font-size: 13px; line-height: 1.6; margin: 8px 0 0 0; word-break: break-all;">
                                {{ $resetUrl }}
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 24px 40px; text-align: center; border-radius: 0 0 16px 16px; border-top: 1px solid #e2e8f0;">
                            <p style="color: #94a3b8; font-size: 13px; margin: 0;">
                                © {{ date('Y') }} {{ config('app.name') }}
                            </p>
                            <p style="color: #94a3b8; font-size: 12px; margin: 8px 0 0 0;">
                                Este es un correo automático, por favor no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
