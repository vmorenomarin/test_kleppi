<?php
// Iniciar la sesión al principio de cada script.
session_start();

// Si la variable de sesión 'logged_in' existe, redirigir a la página de productos.
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: products.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            color: #007bff;
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        p {
            color: #555;
            font-size: 1.1rem;
        }
        .links-container {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 2rem;
        }
        a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenido</h1>
        <p>Por favor, regístrate o inicia sesión para continuar.</p>
        <div class="links-container">
            <a href="register.php">Registrarse</a>
            <a href="login.php">Iniciar sesión</a>
        </div>
    </div>
</body>
</html>
