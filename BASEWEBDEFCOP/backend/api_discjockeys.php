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

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

try {
    if ($accion === 'listar') {
        $busqueda = trim($_GET['buscar'] ?? '');
        if ($busqueda !== '') {
            $stmt = $pdo->prepare("SELECT * FROM discjockey WHERE (nombre LIKE ? OR apodo_dj LIKE ? OR cedula LIKE ?) ORDER BY id DESC");
            $like = "%$busqueda%";
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $pdo->query("SELECT * FROM discjockey ORDER BY id DESC");
        }
        $discjockeys = $stmt->fetchAll();
        echo json_encode(['estado' => 'exito', 'datos' => $discjockeys]);
        exit();
    }

    if ($accion === 'guardar') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apodo_dj = trim($_POST['apodo_dj'] ?? '');
        $experiencia_anos = (int)($_POST['experiencia_anos'] ?? 1);
        $turno = trim($_POST['turno'] ?? 'Mañana');
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if (empty($cedula) || empty($nombre) || empty($apodo_dj)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Todos los campos obligatorios deben completarse.']);
            exit();
        }

        if ($id) {
            // Actualizar
            $stmtCheck = $pdo->prepare("SELECT id FROM discjockey WHERE cedula = ? AND id != ?");
            $stmtCheck->execute([$cedula, $id]);
            if ($stmtCheck->fetch()) {
                echo json_encode(['estado' => 'error', 'mensaje' => 'La cédula ya se encuentra registrada por otro locutor.']);
                exit();
            }

            $stmt = $pdo->prepare("UPDATE discjockey SET cedula = ?, nombre = ?, apodo_dj = ?, experiencia_anos = ?, turno = ?, estado = ? WHERE id = ?");
            $stmt->execute([$cedula, $nombre, $apodo_dj, $experiencia_anos, $turno, $estado, $id]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Locutor actualizado con éxito.']);
        } else {
            // Insertar
            $stmtCheck = $pdo->prepare("SELECT id FROM discjockey WHERE cedula = ?");
            $stmtCheck->execute([$cedula]);
            if ($stmtCheck->fetch()) {
                echo json_encode(['estado' => 'error', 'mensaje' => 'La cédula ya se encuentra registrada.']);
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO discjockey (cedula, nombre, apodo_dj, experiencia_anos, turno, estado) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cedula, $nombre, $apodo_dj, $experiencia_anos, $turno, $estado]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Locutor creado con éxito.']);
        }
        exit();
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de locutor inválido.']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM discjockey WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['estado' => 'exito', 'mensaje' => 'DiscJockey eliminado correctamente.']);
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error en base de datos: ' . $e->getMessage()]);
    exit();
}
