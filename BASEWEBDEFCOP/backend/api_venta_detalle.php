<?php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'conexion.php';

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no válida, inicie sesión nuevamente']);
    exit;
}

$ventaId = $_GET['id'] ?? null;
if (!$ventaId) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID de la factura']);
    exit;
}

try {
    // Cabecera
    $stmt = $pdo->prepare(
        "SELECT v.*, c.nombre AS cliente_nombre, c.cedula AS cliente_cedula, u.usuario AS vendedor
         FROM ventas v
         INNER JOIN clientes c ON c.id = v.cliente_id
         INNER JOIN usuarios u ON u.id = v.usuario_id
         WHERE v.id = :id"
    );
    $stmt->execute([':id' => $ventaId]);
    $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cabecera) {
        http_response_code(404);
        echo json_encode(['error' => 'Factura no encontrada']);
        exit;
    }

    // Detalle (productos vendidos en esa factura)
    $stmtDetalle = $pdo->prepare(
        "SELECT dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre AS producto_nombre, p.codigo_barras
         FROM detalle_venta dv
         INNER JOIN productos p ON p.id = dv.producto_id
         WHERE dv.venta_id = :id"
    );
    $stmtDetalle->execute([':id' => $ventaId]);
    $detalle = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'cabecera' => $cabecera,
        'detalle' => $detalle,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
