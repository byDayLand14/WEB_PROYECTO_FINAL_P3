<?php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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

$clienteId = $input['cliente_id'] ?? 1; // 1 = Consumidor Final
$carrito = $input['carrito'] ?? [];      // [{producto_id, cantidad, precio}]
$montoPagado = (float)($input['monto_pagado'] ?? 0);
$usuarioId = $_SESSION['usuario_activo']['id'];

if (empty($carrito)) {
    http_response_code(400);
    echo json_encode(['error' => 'El carrito está vacío']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Calcular totales en el servidor (nunca confiar solo en el front)
    $subtotal = 0;
    foreach ($carrito as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    $iva = round($subtotal * 0.15, 2);
    $total = round($subtotal + $iva, 2);

    if ($montoPagado < $total) {
        throw new Exception('El monto pagado es menor al total de la venta');
    }
    $cambio = round($montoPagado - $total, 2);

    // 1. Insertar cabecera de la venta
    $stmt = $pdo->prepare(
        'INSERT INTO ventas (cliente_id, usuario_id, subtotal, iva, total, monto_pagado, cambio)
         VALUES (:cliente_id, :usuario_id, :subtotal, :iva, :total, :monto_pagado, :cambio)'
    );
    $stmt->execute([
        ':cliente_id' => $clienteId,
        ':usuario_id' => $usuarioId,
        ':subtotal' => $subtotal,
        ':iva' => $iva,
        ':total' => $total,
        ':monto_pagado' => $montoPagado,
        ':cambio' => $cambio,
    ]);
    $ventaId = $pdo->lastInsertId();

    // 2. Insertar cada línea del detalle y descontar stock
    $stmtDetalle = $pdo->prepare(
        'INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal)
         VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)'
    );
    $stmtStockCheck = $pdo->prepare('SELECT stock FROM productos WHERE id = :id FOR UPDATE');
    $stmtStockUpdate = $pdo->prepare('UPDATE productos SET stock = stock - :cantidad WHERE id = :id');

    foreach ($carrito as $item) {
        $stmtStockCheck->execute([':id' => $item['producto_id']]);
        $stockActual = $stmtStockCheck->fetchColumn();

        if ($stockActual === false || $stockActual < $item['cantidad']) {
            throw new Exception('Stock insuficiente para el producto ID ' . $item['producto_id']);
        }

        $stmtDetalle->execute([
            ':venta_id' => $ventaId,
            ':producto_id' => $item['producto_id'],
            ':cantidad' => $item['cantidad'],
            ':precio_unitario' => $item['precio'],
            ':subtotal' => $item['precio'] * $item['cantidad'],
        ]);

        $stmtStockUpdate->execute([
            ':cantidad' => $item['cantidad'],
            ':id' => $item['producto_id'],
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'message' => 'Venta procesada con éxito',
        'venta_id' => $ventaId,
        'subtotal' => $subtotal,
        'iva' => $iva,
        'total' => $total,
        'monto_pagado' => $montoPagado,
        'cambio' => $cambio,
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
