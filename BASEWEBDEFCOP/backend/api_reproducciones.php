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
        $frecuencia = (float)($_GET['frecuencia'] ?? 0);

        $sql = "SELECT r.*, 
                       dj.nombre AS dj_nombre, dj.apodo_dj, 
                       c.titulo AS cancion_titulo, c.duracion_segundos, c.genero, c.audio_url,
                       g.nombre AS grupo_nombre, d.titulo AS disco_titulo
                FROM reproduccion r
                JOIN discjockey dj ON r.discjockey_id = dj.id
                JOIN cancion c ON r.cancion_id = c.id
                JOIN disco d ON c.disco_id = d.id
                JOIN grupo g ON d.grupo_id = g.id ";
        
        $params = [];
        $condiciones = [];

        if ($frecuencia > 0) {
            $condiciones[] = "r.frecuencia_fm = ?";
            $params[] = $frecuencia;
        }

        if ($busqueda !== '') {
            $condiciones[] = "(dj.nombre LIKE ? OR dj.apodo_dj LIKE ? OR c.titulo LIKE ? OR g.nombre LIKE ?)";
            $like = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY r.fecha_hora DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $reproducciones = $stmt->fetchAll();

        echo json_encode(['estado' => 'exito', 'datos' => $reproducciones]);
        exit();
    }

    if ($accion === 'emitir_al_aire') {
        $discjockey_id = (int)($_POST['discjockey_id'] ?? 0);
        $cancion_id = (int)($_POST['cancion_id'] ?? 0);
        $frecuencia_fm = (float)($_POST['frecuencia_fm'] ?? 98.10);
        $notas = trim($_POST['notas'] ?? 'Transmisión directa al aire');

        if ($discjockey_id <= 0 || $cancion_id <= 0) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Seleccione el locutor de cabina y la canción a emitir.']);
            exit();
        }

        // Obtener duracion de la cancion
        $stmtC = $pdo->prepare("SELECT duracion_segundos, titulo FROM cancion WHERE id = ?");
        $stmtC->execute([$cancion_id]);
        $cancionInfo = $stmtC->fetch();

        if (!$cancionInfo) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'La canción seleccionada no existe en la base de datos.']);
            exit();
        }

        $duracion_emision = (int)$cancionInfo['duracion_segundos'];
        $nivel_audiencia = rand(75, 99); // Simulación realista de sintonía FM

        $stmt = $pdo->prepare("INSERT INTO reproduccion (discjockey_id, cancion_id, frecuencia_fm, fecha_hora, duracion_emision, nivel_audiencia, notas) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
        $stmt->execute([$discjockey_id, $cancion_id, $frecuencia_fm, $duracion_emision, $nivel_audiencia, $notas]);

        echo json_encode([
            'estado' => 'exito', 
            'mensaje' => "¡EN EL AIRE! Transmitiendo " . $cancionInfo['titulo'] . " por " . number_format($frecuencia_fm, 1) . " MHz FM."
        ]);
        exit();
    }

    if ($accion === 'detener_emision') {
        echo json_encode([
            'estado' => 'exito', 
            'mensaje' => "Transmisión en vivo detenida. Estación FM en señal libre / fuera del aire."
        ]);
        exit();
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de reproducción inválido.']);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM reproduccion WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['estado' => 'exito', 'mensaje' => 'Registro de reproducción eliminado.']);
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error en base de datos: ' . $e->getMessage()]);
    exit();
}
