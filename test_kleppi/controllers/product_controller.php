<?php

session_start();

// Habilita CORS para permitir peticiones desde diferentes orígenes si es necesario.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

require_once 'Logger.php';
$logger = new Logger('logs/app.log');

// Verificar si el usuario está autenticado y tiene rol de administrador para peticiones POST, PUT y DELETE.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requiere ser administrador.']);
        $logger->error("Intento de acceso denegado a product_controller sin ser admin. Método: " . $_SERVER['REQUEST_METHOD']);
        exit;
    }
}

// Requerir el archivo de conexión a la base de datos
$pdo = require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Leer productos
        try {
            $sql = "SELECT * FROM products ORDER BY created_at DESC";
            $stmt = $pdo->query($sql);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'products' => $products]);
            $logger->info("Listado de productos solicitado.");
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => "Error al obtener productos: " . $e->getMessage()]);
            $logger->error("Error al obtener productos: " . $e->getMessage());
        }
        break;

    case 'POST':
        // Crear un producto
        $name = $data['name'] ?? '';
        $price = $data['price'] ?? 0;
        $description = $data['description'] ?? '';

        if (empty($name) || !is_numeric($price)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Faltan datos o son incorrectos.']);
            $logger->warning("Intento de crear producto fallido: datos incompletos.");
            exit;
        }

        try {
            $sql = "INSERT INTO products (name, price, description) VALUES (:name, :price, :description)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':description' => $description
            ]);
            echo json_encode(['success' => true, 'message' => 'Producto agregado exitosamente.']);
            $logger->info("Producto '{$name}' agregado por '{$_SESSION['username']}'.");
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => "Error al agregar producto: " . $e->getMessage()]);
            $logger->error("Error al agregar producto: " . $e->getMessage());
        }
        break;

    case 'PUT':
        // Actualizar un producto
        $id = $_GET['id'] ?? null;
        if (!$id || empty($data['name']) || !is_numeric($data['price'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Faltan datos o son incorrectos para la actualización.']);
            $logger->warning("Intento de actualizar producto fallido: datos incompletos.");
            exit;
        }

        try {
            $sql = "UPDATE products SET name = :name, price = :price, description = :description WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':price' => $data['price'],
                ':description' => $data['description'] ?? '',
                ':id' => $id
            ]);
            echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente.']);
            $logger->info("Producto ID: {$id} actualizado por '{$_SESSION['username']}'.");
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => "Error al actualizar producto: " . $e->getMessage()]);
            $logger->error("Error al actualizar producto: " . $e->getMessage());
        }
        break;

    case 'DELETE':
        // Borrar un producto
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Falta el ID del producto para borrar.']);
            $logger->warning("Intento de borrar producto fallido: ID no proporcionado.");
            exit;
        }

        try {
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Producto borrado exitosamente.']);
            $logger->info("Producto ID: {$id} borrado por '{$_SESSION['username']}'.");
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => "Error al borrar producto: " . $e->getMessage()]);
            $logger->error("Error al borrar producto: " . $e->getMessage());
        }
        break;

    case 'OPTIONS':
        // Respuesta para pre-vuelo de CORS
        http_response_code(200);
        exit;
}
