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

// ---- Filtros recibidos ----
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin    = $_GET['fecha_fin'] ?? '';
$cliente     = trim($_GET['cliente'] ?? '');
$factura     = trim($_GET['factura'] ?? '');

$condiciones = [];
$parametros  = [];

if ($fechaInicio !== '') {
    $condiciones[] = 'v.fecha >= :fecha_inicio';
    $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
}
if ($fechaFin !== '') {
    $condiciones[] = 'v.fecha <= :fecha_fin';
    $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
}
if ($cliente !== '') {
    $condiciones[] = '(c.nombre LIKE :cliente OR c.cedula LIKE :cliente)';
    $parametros[':cliente'] = "%$cliente%";
}
if ($factura !== '') {
    $condiciones[] = 'v.id = :factura';
    $parametros[':factura'] = $factura;
}

$whereSql = count($condiciones) > 0 ? 'WHERE ' . implode(' AND ', $condiciones) : '';

try {
    // ---- Listado de facturas (cabeceras) ----
    $sql = "SELECT v.id, v.fecha, v.total, v.estado,
                   c.nombre AS cliente_nombre, c.cedula AS cliente_cedula,
                   u.usuario AS vendedor
            FROM ventas v
            INNER JOIN clientes c ON c.id = v.cliente_id
            INNER JOIN usuarios u ON u.id = v.usuario_id
            $whereSql
            ORDER BY v.fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- Totalizadores (solo sobre facturas pagadas del periodo filtrado) ----
    $sqlTotales = "SELECT
                        COALESCE(SUM(CASE WHEN v.estado = 'pagada' THEN v.total ELSE 0 END), 0) AS total_vendido,
                        SUM(CASE WHEN v.estado = 'pagada' THEN 1 ELSE 0 END) AS cantidad_facturas
                   FROM ventas v
                   INNER JOIN clientes c ON c.id = v.cliente_id
                   INNER JOIN usuarios u ON u.id = v.usuario_id
                   $whereSql";
    $stmtTotales = $pdo->prepare($sqlTotales);
    $stmtTotales->execute($parametros);
    $totales = $stmtTotales->fetch(PDO::FETCH_ASSOC);

    $totalVendido = (float)$totales['total_vendido'];
    $cantidadFacturas = (int)$totales['cantidad_facturas'];
    $ticketPromedio = $cantidadFacturas > 0 ? $totalVendido / $cantidadFacturas : 0;

    echo json_encode([
        'facturas' => $facturas,
        'totales' => [
            'total_vendido' => round($totalVendido, 2),
            'cantidad_facturas' => $cantidadFacturas,
            'ticket_promedio' => round($ticketPromedio, 2),
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
