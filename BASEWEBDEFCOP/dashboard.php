<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/backend/conexion.php';

// Consultas PDO para métricas
$totalDjs           = (int) $pdo->query("SELECT COUNT(*) FROM discjockey WHERE estado = 1")->fetchColumn();
$totalGrupos        = (int) $pdo->query("SELECT COUNT(*) FROM grupo WHERE estado = 1")->fetchColumn();
$totalDiscos        = (int) $pdo->query("SELECT COUNT(*) FROM disco WHERE estado = 1")->fetchColumn();
$totalCanciones     = (int) $pdo->query("SELECT COUNT(*) FROM cancion WHERE estado = 1")->fetchColumn();
$totalTransmisiones = (int) $pdo->query("SELECT COUNT(*) FROM reproduccion")->fetchColumn();

// Últimas 5 transmisiones
$stmtUltimas = $pdo->query("
    SELECT r.frecuencia_fm, r.fecha_hora, r.nivel_audiencia,
           dj.apodo_dj,
           c.titulo AS cancion_titulo,
           g.nombre AS grupo_nombre
    FROM reproduccion r
    JOIN discjockey dj ON r.discjockey_id = dj.id
    JOIN cancion c     ON r.cancion_id    = c.id
    JOIN disco d       ON c.disco_id      = d.id
    JOIN grupo g       ON d.grupo_id      = g.id
    ORDER BY r.fecha_hora DESC LIMIT 5
");
$ultimasTransmisiones = $stmtUltimas->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard general del sistema de gestión de Radio Stereo Wave FM 98.1.">
    <title>Dashboard — Radio Stereo Wave FM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="frontend/css/estilos_fm.css">
</head>
<body>
    <?php include __DIR__ . '/backend/includes/header.php'; ?>

    <div class="container-fluid px-md-5">

        <!-- ── Hero Banner ── -->
        <div class="hero-banner-radio mb-4 radio-card-effect">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position: relative; z-index: 2;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="pulse-red"></span>
                        <span style="color: #93c5fd; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Transmisión activa</span>
                    </div>
                    <h2 class="fw-extrabold mb-1">Cabina de Radio FM</h2>
                    <p class="mb-0">Panel de control · Monitoreo en tiempo real · Stereo Wave 98.1 MHz</p>
                </div>
                <a href="sintonizador.php" class="btn btn-warning fw-bold px-4 py-2 rounded-pill shadow-lg d-flex align-items-center gap-2"
                   style="white-space: nowrap; background: linear-gradient(135deg, #f59e0b, #fbbf24); border: none; color: #0f172a;">
                    <i class="bi bi-radioactive fs-5"></i>
                    <span>Abrir Sintonizador</span>
                </a>
            </div>
        </div>

        <!-- ── Tarjetas de Métricas ── -->
        <div class="row g-3 mb-4">

            <div class="col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center gap-3 radio-card-effect h-100">
                    <div class="metric-circle-1 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 54px; height: 54px;">
                        <i class="bi bi-person-badge fs-3 text-white"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1" style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em;">
                            Locutores / DJs
                        </span>
                        <h3 class="fw-black mb-0" style="font-size: 2rem; letter-spacing: -0.02em; color: var(--text-primary);">
                            <?php echo $totalDjs; ?>
                        </h3>
                        <small style="color: var(--accent-green); font-size: 0.75rem; font-weight: 600;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Activos
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center gap-3 radio-card-effect h-100">
                    <div class="metric-circle-2 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 54px; height: 54px;">
                        <i class="bi bi-people-fill fs-3 text-white"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1" style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em;">
                            Grupos Musicales
                        </span>
                        <h3 class="fw-black mb-0" style="font-size: 2rem; letter-spacing: -0.02em; color: var(--text-primary);">
                            <?php echo $totalGrupos; ?>
                        </h3>
                        <small style="color: var(--accent-green); font-size: 0.75rem; font-weight: 600;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Registrados
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center gap-3 radio-card-effect h-100">
                    <div class="metric-circle-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 54px; height: 54px;">
                        <i class="bi bi-disc-fill fs-3 text-white"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1" style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em;">
                            Discos / Álbumes
                        </span>
                        <h3 class="fw-black mb-0" style="font-size: 2rem; letter-spacing: -0.02em; color: var(--text-primary);">
                            <?php echo $totalDiscos; ?>
                        </h3>
                        <small style="color: var(--accent-green); font-size: 0.75rem; font-weight: 600;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Disponibles
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="glass-card p-3 d-flex align-items-center gap-3 radio-card-effect h-100">
                    <div class="metric-circle-4 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 54px; height: 54px;">
                        <i class="bi bi-broadcast-pin fs-3 text-white"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1" style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em;">
                            Total Emisiones
                        </span>
                        <h3 class="fw-black mb-0" style="font-size: 2rem; letter-spacing: -0.02em; color: var(--text-primary);">
                            <?php echo $totalTransmisiones; ?>
                        </h3>
                        <small style="color: var(--accent-cyan); font-size: 0.75rem; font-weight: 600;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Al aire
                        </small>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Contenido principal ── -->
        <div class="row g-4 mb-5">

            <!-- Tabla de últimas transmisiones -->
            <div class="col-lg-8">
                <div class="glass-card p-0 overflow-hidden h-100">
                    <div class="section-header">
                        <h5 class="section-title">
                            <i class="bi bi-activity text-cyan"></i>
                            Últimas canciones al aire
                        </h5>
                        <a href="reproducciones.php" class="btn btn-sm btn-outline-cyan rounded-pill px-3">
                            <i class="bi bi-arrow-right me-1"></i> Ver todas
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-radio align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Frecuencia</th>
                                    <th>Locutor (DJ)</th>
                                    <th>Canción / Artista</th>
                                    <th>Fecha y Hora</th>
                                    <th>Audiencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($ultimasTransmisiones)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="bi bi-broadcast"></i>
                                                <p>No hay emisiones registradas todavía.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($ultimasTransmisiones as $t): ?>
                                        <tr>
                                            <td>
                                                <span class="badge freq-badge px-2 py-1">
                                                    <?php echo number_format((float)$t['frecuencia_fm'], 1); ?> MHz
                                                </span>
                                            </td>
                                            <td>
                                                <strong style="color: var(--text-primary);">
                                                    <?php echo htmlspecialchars($t['apodo_dj']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="d-block fw-bold" style="color: var(--text-primary); font-size: 0.88rem;">
                                                    <?php echo htmlspecialchars($t['cancion_titulo']); ?>
                                                </span>
                                                <small class="text-amber fw-semibold">
                                                    <?php echo htmlspecialchars($t['grupo_nombre']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="font-monospace" style="color: var(--text-secondary); font-size: 0.8rem;">
                                                    <?php echo date('d/m/Y H:i', strtotime($t['fecha_hora'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-green px-2 py-1">
                                                    <i class="bi bi-reception-4 me-1"></i>
                                                    <?php echo $t['nivel_audiencia']; ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel lateral -->
            <div class="col-lg-4">

                <!-- Acciones rápidas -->
                <div class="glass-card mb-3">
                    <h6 class="section-title mb-3">
                        <i class="bi bi-lightning-charge-fill text-amber"></i>
                        Acciones rápidas
                    </h6>
                    <div class="d-grid gap-2">
                        <a href="reproducciones.php" class="btn btn-action-card text-start p-3 rounded-3">
                            <i class="bi bi-mic-fill me-2" style="color: var(--accent-cyan);"></i>
                            Emitir canción al aire
                        </a>
                        <a href="discjockeys.php" class="btn btn-action-card text-start p-3 rounded-3">
                            <i class="bi bi-person-plus-fill me-2" style="color: var(--accent-cyan);"></i>
                            Registrar nuevo locutor
                        </a>
                        <a href="canciones.php" class="btn btn-action-card text-start p-3 rounded-3">
                            <i class="bi bi-music-note-beamed me-2" style="color: var(--accent-cyan);"></i>
                            Agregar canción al catálogo
                        </a>
                        <a href="grupos.php" class="btn btn-action-card text-start p-3 rounded-3">
                            <i class="bi bi-people-fill me-2" style="color: var(--accent-cyan);"></i>
                            Registrar grupo musical
                        </a>
                    </div>
                </div>

                <!-- Estado de la transmisión -->
                <div class="glass-card" style="border-color: var(--border-accent);">
                    <h6 class="section-title mb-3">
                        <i class="bi bi-broadcast text-cyan"></i>
                        Estado de transmisión
                    </h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="color: var(--text-secondary); font-size: 0.85rem;">Cobertura FM Stereo</span>
                            <span class="badge badge-green">100% activa</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="color: var(--text-secondary); font-size: 0.85rem;">Frecuencia principal</span>
                            <span class="font-monospace fw-bold text-amber">98.1 MHz</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="color: var(--text-secondary); font-size: 0.85rem;">Canciones en catálogo</span>
                            <span class="fw-bold text-cyan"><?php echo $totalCanciones; ?> pistas</span>
                        </div>
                    </div>
                    <hr style="border-color: var(--border-subtle); margin: 1rem 0;">
                    <!-- Nivel de señal visual -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600;">NIVEL DE SEÑAL</small>
                            <small class="text-cyan fw-bold" style="font-size: 0.75rem;">94%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: 94%; background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green));"
                                 aria-valuenow="94" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <?php include __DIR__ . '/backend/includes/player_bar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/validaciones.js"></script>
    <script src="frontend/js/radio_tuner.js"></script>
</body>
</html>
