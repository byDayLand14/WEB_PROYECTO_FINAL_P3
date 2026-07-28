<?php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'conexion.php';

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida, inicie sesión nuevamente']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ventaId = $input['venta_id'] ?? null;

if (!$ventaId) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID de la factura']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verificar que la factura exista y no esté ya anulada
    $stmt = $pdo->prepare('SELECT estado FROM ventas WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => $ventaId]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        throw new Exception('La factura no existe');
    }
    if ($venta['estado'] === 'anulada') {
        throw new Exception('Esta factura ya se encuentra anulada');
    }

    // 2. Devolver el stock de cada producto de la factura
    $stmtDetalle = $pdo->prepare('SELECT producto_id, cantidad FROM detalle_venta WHERE venta_id = :id');
    $stmtDetalle->execute([':id' => $ventaId]);
    $lineas = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

    $stmtDevolverStock = $pdo->prepare('UPDATE productos SET stock = stock + :cantidad WHERE id = :producto_id');
    foreach ($lineas as $linea) {
        $stmtDevolverStock->execute([
            ':cantidad' => $linea['cantidad'],
            ':producto_id' => $linea['producto_id'],
        ]);
    }

    // 3. Cambiar el estado de la factura a "anulada" (nunca se hace DELETE)
    $stmtAnular = $pdo->prepare('UPDATE ventas SET estado = "anulada" WHERE id = :id');
    $stmtAnular->execute([':id' => $ventaId]);

    $pdo->commit();

    echo json_encode(['message' => 'Factura anulada y stock devuelto correctamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
