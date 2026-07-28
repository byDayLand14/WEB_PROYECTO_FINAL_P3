<?php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida, inicie sesión nuevamente']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            // Búsqueda en vivo / listado por nombre o cédula
            $search = $_GET['q'] ?? '';
            $sql = 'SELECT * FROM clientes WHERE (nombre LIKE ? OR cedula LIKE ?) AND estado = 1 ORDER BY nombre LIMIT 100';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$search%", "%$search%"]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            // Registrar un nuevo cliente (desde el POS o desde la página de Clientes)
            $sql = 'INSERT INTO clientes (cedula, nombre, telefono, email) VALUES (:cedula, :nombre, :telefono, :email)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cedula' => $input['cedula'],
                ':nombre' => $input['nombre'],
                ':telefono' => $input['telefono'] ?? null,
                ':email' => $input['email'] ?? null,
            ]);
            echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Cliente creado con éxito']);
            break;

        case 'PUT':
            // Actualizar un cliente existente
            $sql = 'UPDATE clientes SET nombre = :nombre, telefono = :telefono, email = :email WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $input['id'],
                ':nombre' => $input['nombre'],
                ':telefono' => $input['telefono'] ?? null,
                ':email' => $input['email'] ?? null,
            ]);
            echo json_encode(['message' => 'Cliente actualizado con éxito']);
            break;

        case 'DELETE':
            // Desactivar un cliente (nunca se borra físicamente, para no romper el historial de ventas)
            $sql = 'UPDATE clientes SET estado = 0 WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $input['id'],
            ]);
            echo json_encode(['message' => 'Cliente desactivado con éxito']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
