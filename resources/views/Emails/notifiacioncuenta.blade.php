<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cuenta</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #007BFF;
            color: #ffffff;
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content p {
            font-size: 16px;
            color: #333333;
            line-height: 1.5;
        }
        .code {
            display: inline-block;
            background: #007BFF;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .footer {
            background: #f4f4f4;
            color: #555555;
            text-align: center;
            padding: 15px;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #28a745;
            color: #ffffff;
            font-size: 18px;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
        	BICYCLES
        </div>
        <div class="content">
            <p><strong>¡Gracias por registrarte en nuestra app!</strong></p>
            <p>Para confirmar tu cuenta asociada a <strong>{{$correo}}</strong>, ingresa el siguiente código en la aplicación:</p>
            <div class="code">{{$codigo}}</div>
            <p>Si tienes algún problema, contáctanos.</p>
            <a href="mailto:bicyclesutt@gmail.com" class="button">Contactar Soporte</a>
        </div>
        <div class="footer">
            © 2025 Bicycles. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
