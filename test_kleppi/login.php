<?php

session_start();

require_once 'Logger.php';
$logger = new Logger('logs/app.log');

// Requerir el archivo que contiene la conexión a la base de datos
$pdo = require 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $plain_password = $_POST['password'];

    if (empty($username) || empty($plain_password)) {
        $message = "Por favor, introduce tu nombre de usuario y contraseña.";
        $logger->warning("Intento de login fallido: campos vacíos.");
    } else {
        // Se selecciona también la columna 'role'
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar la contraseña hasheada
            if (password_verify($plain_password, $user['password'])) {
                // Autenticación exitosa
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                $logger->info("Usuario '{$username}' inició sesión exitosamente.");

                // Redirigir según el rol del usuario
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: products.php");
                }
                exit;
            } else {
                $message = "Credenciales incorrectas. Por favor, inténtalo de nuevo.";
                $logger->warning("Intento de login fallido para '{$username}': contraseña incorrecta.");
            }
        } else {
            $message = "Credenciales incorrectas. Por favor, inténtalo de nuevo.";
            $logger->warning("Intento de login fallido: usuario '{$username}' no encontrado.");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Usuario</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
            color: #0056b3;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }
        .message {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            color: #fff;
            background-color: #dc3545;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            max-height: 0;
            overflow: hidden;
            padding: 0;
        }
        .message.show {
            opacity: 1;
            padding: 1rem;
            max-height: 200px;
        }
        .success {
            background-color: #28a745;
        }
        .error {
            background-color: #dc3545;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
        }
        button:hover {
            background-color: #0056b3;
        }
        button:active {
            transform: scale(0.99);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Iniciar sesión</h1>
    <form method="POST">
        <div class="form-group">
            <label for="username">Nombre de usuario</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Iniciar sesión</button>
    </form>

    <?php if (isset($message)): ?>
        <div class="message <?php echo strpos($message, 'exitoso') !== false ? 'success' : 'error'; ?> show">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
