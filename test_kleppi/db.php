<?php

// Incluimos el archivo de configuración para obtener las credenciales
$config = require 'config/config.php';

$host = $config['host'];
$dbname = $config['dbname'];
$user = $config['user'];
$password = $config['password'];

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Registra el error pero no lo muestra al usuario
    // Esta es una mejor práctica para evitar revelar información sensible
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Lo sentimos, no pudimos conectar a la base de datos en este momento.");
}

return $pdo;

?>
