<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode(['estado' => 'error', 'mensaje' => 'No autorizado. Inicie sesión.']);
    exit();
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

try {
    if ($accion === 'listar') {
        $busqueda = trim($_GET['buscar'] ?? '');
        $grupo_id = (int)($_GET['grupo_id'] ?? 0);

        $sql = "SELECT d.*, g.nombre AS grupo_nombre 
                FROM disco d 
                JOIN grupo g ON d.grupo_id = g.id ";
        $params = [];
        $condiciones = [];

        if ($grupo_id > 0) {
            $condiciones[] = "d.grupo_id = ?";
            $params[] = $grupo_id;
        }

        if ($busqueda !== '') {
            $condiciones[] = "(d.titulo LIKE ? OR g.nombre LIKE ? OR d.discografica LIKE ?)";
            $like = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY d.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $discos = $stmt->fetchAll();

        echo json_encode(['estado' => 'exito', 'datos' => $discos]);
        exit();
    }

    if ($accion === 'guardar') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $grupo_id = (int)($_POST['grupo_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $anio_lanzamiento = (int)($_POST['anio_lanzamiento'] ?? date('Y'));
        $discografica = trim($_POST['discografica'] ?? '');
        $formato = trim($_POST['formato'] ?? 'Digital');
        $imagen_url = trim($_POST['imagen_url'] ?? '');
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if ($grupo_id <= 0 || empty($titulo)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Seleccione un grupo y especifique el título del disco.']);
            exit();
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE disco SET grupo_id = ?, titulo = ?, anio_lanzamiento = ?, discografica = ?, formato = ?, imagen_url = ?, estado = ? WHERE id = ?");
            $stmt->execute([$grupo_id, $titulo, $anio_lanzamiento, $discografica, $formato, $imagen_url, $estado, $id]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Disco actualizado correctamente.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO disco (grupo_id, titulo, anio_lanzamiento, discografica, formato, imagen_url, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$grupo_id, $titulo, $anio_lanzamiento, $discografica, $formato, $imagen_url, $estado]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Disco guardado correctamente.']);
        }
        exit();
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de disco inválido.']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM disco WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['estado' => 'exito', 'mensaje' => 'Disco eliminado correctamente.']);
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error en base de datos: ' . $e->getMessage()]);
    exit();
}
