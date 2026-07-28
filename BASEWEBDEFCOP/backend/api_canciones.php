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
        $disco_id = (int)($_GET['disco_id'] ?? 0);

        $sql = "SELECT c.*, d.titulo AS disco_titulo, g.nombre AS grupo_nombre 
                FROM cancion c 
                JOIN disco d ON c.disco_id = d.id 
                JOIN grupo g ON d.grupo_id = g.id ";
        $params = [];
        $condiciones = [];

        if ($disco_id > 0) {
            $condiciones[] = "c.disco_id = ?";
            $params[] = $disco_id;
        }

        if ($busqueda !== '') {
            $condiciones[] = "(c.titulo LIKE ? OR c.genero LIKE ? OR d.titulo LIKE ? OR g.nombre LIKE ?)";
            $like = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $canciones = $stmt->fetchAll();

        echo json_encode(['estado' => 'exito', 'datos' => $canciones]);
        exit();
    }

    if ($accion === 'guardar') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $disco_id = (int)($_POST['disco_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $duracion_segundos = (int)($_POST['duracion_segundos'] ?? 180);
        $numero_pista = (int)($_POST['numero_pista'] ?? 1);
        $genero = trim($_POST['genero'] ?? 'Pop');
        $audio_url = trim($_POST['audio_url'] ?? '');
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if ($disco_id <= 0 || empty($titulo)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Seleccione un disco e ingrese el título de la canción.']);
            exit();
        }

        if (empty($audio_url)) {
            $audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3';
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE cancion SET disco_id = ?, titulo = ?, duracion_segundos = ?, numero_pista = ?, genero = ?, audio_url = ?, estado = ? WHERE id = ?");
            $stmt->execute([$disco_id, $titulo, $duracion_segundos, $numero_pista, $genero, $audio_url, $estado, $id]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Canción actualizada con éxito.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cancion (disco_id, titulo, duracion_segundos, numero_pista, genero, audio_url, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$disco_id, $titulo, $duracion_segundos, $numero_pista, $genero, $audio_url, $estado]);
            echo json_encode(['estado' => 'exito', 'mensaje' => 'Canción registrada con éxito.']);
        }
        exit();
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de canción inválido.']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM cancion WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['estado' => 'exito', 'mensaje' => 'Canción eliminada correctamente.']);
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error en base de datos: ' . $e->getMessage()]);
    exit();
}
