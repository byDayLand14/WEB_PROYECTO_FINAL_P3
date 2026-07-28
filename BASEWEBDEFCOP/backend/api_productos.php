<?php
declare(strict_types=1);
session_start();

//cabeceras necesarias para el intercambio de JSON
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

//Incluir la conexión

require_once 'conexion.php';

//Capturar el metodo de GET POST

$method = $_SERVER['REQUEST_METHOD'];

//Capturamos el cuerpo de la petición para el POST, PUT

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida, inicie sesión nuevamente']);
    exit;
}

try{
    switch($method) {
        case 'GET':
            //Obtener todos los productos activos (o filtrar por código de barras / nombre)
            $search = $_GET['q'] ?? '';
            $sql='SELECT * FROM productos WHERE estado = 1 AND (nombre LIKE ? OR codigo_barras LIKE ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$search%", "%$search%"]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        case 'POST':
            //Insertar un nuevo producto
            $sql='INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock) VALUES (:codigo, :nombre, :descripcion, :precio, :stock)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':codigo' => $input['codigo'],
                ':nombre' => $input['nombre'],
                ':descripcion' => $input['descripcion'] ?? null,
                ':precio' => $input['precio'],
                ':stock' => $input['stock']
            ]);
            echo json_encode(['message' => 'Producto creado con éxito']);
            break;
        case 'PUT':
            //Actualizar un producto existente
            $sql='UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $input['id'],
                ':nombre' => $input['nombre'],
                ':descripcion' => $input['descripcion'] ?? null,
                ':precio' => $input['precio'],
                ':stock' => $input['stock']
            ]);
            echo json_encode(['message' => 'Producto actualizado con éxito']);
            break;
        case 'DELETE':
            //Desactivar un producto (nunca se borra físicamente, para no romper el historial de ventas)
            $sql='UPDATE productos SET estado = 0 WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $input['id']
            ]);
            echo json_encode(['message' => 'Producto desactivado con éxito']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
}catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
