<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cabina de transmisión FM — gestiona y registra emisiones de canciones al aire en Radio Wave FM.">
    <title>Cabina FM — Radio Wave FM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="frontend/css/estilos_fm.css">
</head>
<body>
    <?php include __DIR__ . '/backend/includes/header.php'; ?>

    <main class="container-fluid px-md-5">

        <!-- ── Page Header ── -->
        <div class="hero-banner-radio mb-4 radio-card-effect">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position: relative; z-index: 2;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="pulse-red"></span>
                        <span style="color: #fca5a5; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Cabina en vivo</span>
                    </div>
                    <h2 class="fw-extrabold mb-1">Consola de Transmisión FM</h2>
                    <p class="mb-0">Emite canciones al aire · Gestiona el historial de reproducciones</p>
                </div>
                <span class="badge d-flex align-items-center gap-2 px-3 py-2"
                      style="background: rgba(244,63,94,0.2); border: 1px solid rgba(244,63,94,0.4); color: #fca5a5; font-size: 0.82rem;">
                    <i class="bi bi-record-circle-fill" style="animation: pulseRed 1s infinite;"></i> ON-AIR
                </span>
            </div>
        </div>

        <!-- ── Panel de emisión + Monitor ── -->
        <div class="row g-4 mb-4">

            <!-- Formulario de emisión -->
            <div class="col-lg-5">
                <div class="glass-card radio-card-effect h-100">
                    <h5 class="section-title mb-4">
                        <i class="bi bi-mic-fill text-coral"></i>
                        Lanzar canción al aire
                    </h5>

                    <form id="form-emision" onsubmit="emitirAlAire(event)" class="needs-validation" novalidate>

                        <div class="mb-3">
                            <label for="repro-dj" class="form-label">
                                <i class="bi bi-person-badge me-1"></i> Locutor en cabina
                            </label>
                            <select class="form-select" id="repro-dj" name="discjockey_id" required aria-describedby="repro-dj-help">
                                <option value="">Cargando locutores...</option>
                            </select>
                            <div class="form-text" id="repro-dj-help">
                                <i class="bi bi-info-circle me-1"></i>Selecciona el DJ activo en cabina ahora.
                            </div>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i> Debes seleccionar un locutor.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="repro-cancion" class="form-label">
                                <i class="bi bi-music-note-beamed me-1"></i> Canción a transmitir
                            </label>
                            <select class="form-select" id="repro-cancion" name="cancion_id" required aria-describedby="repro-cancion-help">
                                <option value="">Cargando catálogo...</option>
                            </select>
                            <div class="form-text" id="repro-cancion-help">
                                <i class="bi bi-info-circle me-1"></i>Elige la pista que se emitirá en directo.
                            </div>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i> Selecciona una canción del catálogo.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="repro-frecuencia" class="form-label">
                                    <i class="bi bi-broadcast me-1"></i> Frecuencia FM
                                </label>
                                <select class="form-select" id="repro-frecuencia" name="frecuencia_fm" required>
                                    <option value="98.10" selected>98.1 MHz — Matriz</option>
                                    <option value="88.50">88.5 MHz — Stereo Pop</option>
                                    <option value="92.30">92.3 MHz — Rock FM</option>
                                    <option value="102.50">102.5 MHz — Latino FM</option>
                                    <option value="104.90">104.9 MHz — Urbana</option>
                                    <option value="107.30">107.3 MHz — Noticias</option>
                                </select>
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-circle me-1"></i> Selecciona la frecuencia.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="repro-notas" class="form-label">
                                    <i class="bi bi-chat-left-text me-1"></i> Notas del programa
                                </label>
                                <input type="text" class="form-control" id="repro-notas" name="notas"
                                    value="Programa Estelar FM"
                                    maxlength="100"
                                    placeholder="Ej: Programa de tarde...">
                            </div>
                        </div>

                        <button type="submit" id="btn-emitir"
                            class="btn btn-danger w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2"
                            style="border-radius: 10px; font-size: 0.95rem; letter-spacing: 0.04em;">
                            <span class="spinner-border spinner-border-sm d-none" id="spinner-emitir" role="status" aria-hidden="true"></span>
                            <i class="bi bi-broadcast-pin fs-5"></i>
                            <span>TRANSMITIR EN DIRECTO</span>
                        </button>

                    </form>
                </div>
            </div>

            <!-- Monitor de transmisión -->
            <div class="col-lg-7">
                <div class="glass-card h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="section-title mb-0">
                            <i class="bi bi-soundwave text-cyan"></i>
                            Monitor de transmisión
                        </h5>
                        <span class="badge badge-green">
                            <i class="bi bi-wifi me-1"></i> Señal estable · 10 KW
                        </span>
                    </div>

                    <!-- Display actual -->
                    <div class="p-4 rounded-3 mb-4 text-center position-relative" style="background: var(--bg-base); border: 1px solid var(--border-subtle);">
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <span class="badge bg-danger text-uppercase px-3 py-1 font-monospace" id="monitor-badge-estado" style="font-size: 0.75rem;">
                                <i class="bi bi-record-circle-fill me-1" style="animation: pulseRed 1s infinite;"></i> SEÑAL EN VIVO (ON-AIR)
                            </span>
                        </div>
                        <small style="color: var(--text-muted); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.5rem;">
                            Emisión en la Frecuencia Matriz
                        </small>
                        <h4 class="fw-bold mb-1" id="monitor-song" style="color: var(--text-primary); font-size: clamp(1rem, 2.5vw, 1.3rem);">
                            Selecciona una emisión para iniciar
                        </h4>
                        <p class="mb-3 text-cyan font-monospace" id="monitor-dj" style="font-size: 0.85rem;">
                            Stereo Wave 98.1 FM
                        </p>
                        
                        <!-- Botón de Apagar / Detener Transmisión en Vivo -->
                        <button type="button" id="btn-apagar-emision" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold" onclick="apagarTransmision()">
                            <i class="bi bi-power me-1"></i> DETENER / APAGAR TRANSMISIÓN EN VIVO
                        </button>
                    </div>

                    <!-- Nivel de audiencia -->
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small style="color: var(--text-muted); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;">
                                Nivel de audiencia y potencia
                            </small>
                            <small class="text-cyan fw-bold">88%</small>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 99px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: 88%; background: linear-gradient(90deg, var(--accent-cyan), var(--accent-magenta)); border-radius: 99px;"
                                 aria-valuenow="88" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted" style="font-size: 0.75rem;">RDS Activo</small>
                            <small class="text-muted" style="font-size: 0.75rem;">Transmisor High-End</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Historial de reproducciones ── -->
        <section class="glass-card p-0 mb-5 overflow-hidden">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="bi bi-clock-history text-cyan"></i>
                    Historial de emisiones
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-cyan rounded-pill px-3" onclick="cargarReproducciones()" title="Actualizar historial">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-radio align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Frecuencia</th>
                            <th>Locutor (DJ)</th>
                            <th>Canción y Artista</th>
                            <th>Fecha / Hora</th>
                            <th>Duración</th>
                            <th>Audiencia</th>
                            <th>Notas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-reproducciones-body">
                        <tr>
                            <td colspan="9">
                                <div class="loading-spinner">
                                    <div class="spinner-border spinner-border-sm text-cyan" role="status" aria-hidden="true"></div>
                                    Cargando emisiones...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Modal confirmación de eliminación -->
    <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content modal-content-radio">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarLabel" style="color: var(--accent-coral); font-size: 0.95rem;">
                        <i class="bi bi-trash me-2"></i>Eliminar emisión
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="font-size: 0.9rem; color: var(--text-secondary);">
                    ¿Estás seguro de que deseas eliminar esta emisión del historial? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-confirmar-eliminar">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/backend/includes/player_bar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/validaciones.js"></script>
    <script src="frontend/js/radio_tuner.js"></script>
    <script>
        let reproIdEliminar = null;

        document.addEventListener('DOMContentLoaded', () => {
            cargarCombosEmision();
            cargarReproducciones();

            // Confirmar eliminación desde modal
            document.getElementById('btn-confirmar-eliminar').addEventListener('click', () => {
                if (reproIdEliminar !== null) {
                    ejecutarEliminar(reproIdEliminar);
                }
            });
        });

        // ── Cargar selectores del formulario ──
        function cargarCombosEmision() {
            fetch('backend/api_discjockeys.php?accion=listar')
                .then(r => r.json())
                .then(res => {
                    const sel = document.getElementById('repro-dj');
                    sel.innerHTML = '<option value="">— Selecciona un locutor —</option>';
                    if (res.estado === 'exito') {
                        res.datos.forEach(dj => {
                            sel.innerHTML += `<option value="${dj.id}">${escapeHTML(dj.apodo_dj)} (${escapeHTML(dj.nombre)})</option>`;
                        });
                    }
                })
                .catch(() => {
                    document.getElementById('repro-dj').innerHTML = '<option value="">Error al cargar locutores</option>';
                });

            fetch('backend/api_canciones.php?accion=listar')
                .then(r => r.json())
                .then(res => {
                    const sel = document.getElementById('repro-cancion');
                    sel.innerHTML = '<option value="">— Selecciona una canción —</option>';
                    if (res.estado === 'exito') {
                        res.datos.forEach(c => {
                            sel.innerHTML += `<option value="${c.id}">${escapeHTML(c.titulo)} — ${escapeHTML(c.grupo_nombre)} (${escapeHTML(c.genero)})</option>`;
                        });
                    }
                })
                .catch(() => {
                    document.getElementById('repro-cancion').innerHTML = '<option value="">Error al cargar catálogo</option>';
                });
        }

        // ── Cargar historial de reproducciones ──
        function cargarReproducciones() {
            const tbody = document.getElementById('tabla-reproducciones-body');
            tbody.innerHTML = `<tr><td colspan="9"><div class="loading-spinner">
                <div class="spinner-border spinner-border-sm text-cyan" role="status" aria-hidden="true"></div>
                Cargando emisiones...
            </div></td></tr>`;

            fetch('backend/api_reproducciones.php?accion=listar')
                .then(r => r.json())
                .then(res => {
                    tbody.innerHTML = '';
                    if (res.estado === 'exito' && res.datos.length > 0) {
                        // Actualizar el monitor con la última emisión
                        const primera = res.datos[0];
                        document.getElementById('monitor-song').textContent = `${primera.cancion_titulo} — ${primera.grupo_nombre}`;
                        document.getElementById('monitor-dj').textContent   = `${primera.apodo_dj} · ${parseFloat(primera.frecuencia_fm).toFixed(1)} MHz`;

                        res.datos.forEach(r => {
                            tbody.innerHTML += `
                                <tr>
                                    <td><span class="font-monospace text-muted" style="font-size: 0.78rem;">#${r.id}</span></td>
                                    <td>
                                        <span class="badge freq-badge">${parseFloat(r.frecuencia_fm).toFixed(1)} MHz</span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--text-primary);">${escapeHTML(r.apodo_dj)}</strong>
                                    </td>
                                    <td>
                                        <span class="d-block fw-bold" style="font-size: 0.88rem; color: var(--text-primary);">${escapeHTML(r.cancion_titulo)}</span>
                                        <small class="text-amber">${escapeHTML(r.grupo_nombre)}</small>
                                    </td>
                                    <td>
                                        <span class="font-monospace" style="font-size: 0.78rem; color: var(--text-secondary);">${r.fecha_hora}</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace" style="color: var(--text-secondary); font-size: 0.82rem;">${r.duracion_emision} s</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-green">
                                            <i class="bi bi-reception-4 me-1"></i>${r.nivel_audiencia}%
                                        </span>
                                    </td>
                                    <td>
                                        <small style="color: var(--text-muted);">${escapeHTML(r.notas || '—')}</small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button class="btn btn-sm btn-outline-danger rounded-2" title="Eliminar emisión"
                                            aria-label="Eliminar emisión #${r.id}"
                                            onclick="confirmarEliminar(${r.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state">
                            <i class="bi bi-broadcast"></i>
                            <p>No hay emisiones registradas todavía.</p>
                        </div></td></tr>`;
                    }
                })
                .catch(() => {
                    tbody.innerHTML = `<tr><td colspan="9" class="text-center py-4" style="color:var(--accent-coral);">
                        <i class="bi bi-wifi-off me-2"></i>Error al cargar el historial. Intenta de nuevo.
                    </td></tr>`;
                });
        }

        // ── Emitir al aire ──
        function emitirAlAire(e) {
            e.preventDefault();
            const form = document.getElementById('form-emision');
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            const btn     = document.getElementById('btn-emitir');
            const spinner = document.getElementById('spinner-emitir');
            btn.disabled  = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(form);
            formData.append('accion', 'emitir_al_aire');

            fetch('backend/api_reproducciones.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        
                        // Actualizar indicador del monitor a TRANSMITIENDO
                        const badgeEstado = document.getElementById('monitor-badge-estado');
                        if (badgeEstado) {
                            badgeEstado.className = 'badge bg-danger text-uppercase px-3 py-1 font-monospace';
                            badgeEstado.innerHTML = '<i class="bi bi-record-circle-fill me-1" style="animation: pulseRed 1s infinite;"></i> SEÑAL EN VIVO (ON-AIR)';
                        }
                        
                        const btnApagar = document.getElementById('btn-apagar-emision');
                        if (btnApagar) btnApagar.style.display = 'inline-flex';

                        form.reset();
                        form.classList.remove('was-validated');
                        cargarReproducciones();
                        cargarCombosEmision();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al emitir.', 'error');
                    }
                })
                .catch(() => mostrarNotificacion('Error de conexión. Intenta de nuevo.', 'error'))
                .finally(() => {
                    btn.disabled = false;
                    spinner.classList.add('d-none');
                });
        }

        // ── Apagar / Detener Transmisión en Vivo ──
        function apagarTransmision() {
            fetch('backend/api_reproducciones.php?accion=detener_emision')
                .then(r => r.json())
                .then(res => {
                    mostrarNotificacion(res.mensaje, 'info');

                    const songEl = document.getElementById('monitor-song');
                    const djEl = document.getElementById('monitor-dj');
                    const badgeEstado = document.getElementById('monitor-badge-estado');
                    const btnApagar = document.getElementById('btn-apagar-emision');

                    if (songEl) songEl.textContent = 'Emisora Fuera del Aire — Señal Disponible';
                    if (djEl) djEl.textContent = 'Stereo Wave FM — Sin Locutor al Aire';
                    if (badgeEstado) {
                        badgeEstado.className = 'badge bg-secondary text-uppercase px-3 py-1 font-monospace';
                        badgeEstado.innerHTML = '<i class="bi bi-slash-circle me-1"></i> SEÑAL DETENIDA (OFF-AIR)';
                    }
                    if (btnApagar) btnApagar.style.display = 'none';
                })
                .catch(() => {
                    mostrarNotificacion('Error al intentar detener la transmisión.', 'error');
                });
        }

        // ── Modal confirmación eliminación ──
        function confirmarEliminar(id) {
            reproIdEliminar = id;
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
            modal.show();
        }

        function ejecutarEliminar(id) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminar'));
            if (modal) modal.hide();

            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', id);

            fetch('backend/api_reproducciones.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        cargarReproducciones();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al eliminar.', 'error');
                    }
                })
                .catch(() => mostrarNotificacion('Error de conexión.', 'error'));
        }
    </script>
</body>
</html>
