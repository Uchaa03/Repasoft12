<!DOCTYPE html>
<html>
<head>
    <title>Nuevas Credenciales</title>
</head>
<body style="font-family: Arial, sans-serif;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #91002E;">¡Bienvenido a {{ config('app.name') }}!</h2>

    <p>Hola {{ $clientName }},</p>

    <p>Estas son tus credenciales de acceso:</p>

    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p><strong>Contraseña temporal:</strong> {{ $password }}</p>
    </div>

    <p style="margin-top: 20px;">
        <a href="https://repasoft.netlify.app/login"
           style="background: #91002E; color: white; padding: 10px 20px;
                      text-decoration: none; border-radius: 5px;">
            Iniciar Sesión
        </a>
    </p>

    <p style="margin-top: 30px; color: #6b7280;">
        Por seguridad, cambia tu contraseña después del primer acceso.
    </p>
</div>
</body>
</html>
