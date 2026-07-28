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
        if ($busqueda !== '') {
            $stmt = $pdo->prepare("SELECT * FROM grupo WHERE (nombre LIKE ? OR genero_musical LIKE ? OR pais_origen LIKE ?) ORDER BY id DESC");
            $like = "%$busqueda%";
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $pdo->query("SELECT * FROM grupo ORDER BY id DESC");
        }
        $grupos = $stmt->fetchAll();
        echo json_encode(['estado' => 'exito', 'datos' => $grupos]);
        exit();
    }

    if ($accion === 'guardar') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $nombre = trim($_POST['nombre'] ?? '');
        $genero_musical = trim($_POST['genero_musical'] ?? '');
        $pais_origen = trim($_POST['pais_origen'] ?? '');
        $anio_formacion = (int)($_POST['anio_formacion'] ?? date('Y'));
        $imagen_url = trim($_POST['imagen_url'] ?? '');
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if (empty($nombre) || empty($genero_musical) || empty($pais_origen)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Por favor complete todos los datos del grupo musical.']);
            exit();
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE grupo SET nombre = ?, genero_musical = ?, pais_origen = ?, anio_formacion = ?, imagen_url = ?, estado = ? WHERE id = ?");
            $stmt->execute([$nombre, $genero_musical, $pais_origen, $anio_formacion, $imagen_url, $estado, $id]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Grupo musical actualizado correctamente.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO grupo (nombre, genero_musical, pais_origen, anio_formacion, imagen_url, estado) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $genero_musical, $pais_origen, $anio_formacion, $imagen_url, $estado]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Grupo musical registrado correctamente.']);
        }
        exit();
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de grupo inválido.']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM grupo WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['estado' => 'exito', 'mensaje' => 'Grupo musical eliminado correctamente.']);
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error en base de datos: ' . $e->getMessage()]);
    exit();
}
