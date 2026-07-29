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
    <meta name="description" content="Catálogo de canciones de Radio Stereo Wave FM — gestiona todas las pistas musicales.">
    <title>Catálogo de Canciones — Radio Wave FM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="frontend/css/estilos_fm.css">
</head>
<body>
    <?php include __DIR__ . '/backend/includes/header.php'; ?>

    <main class="container-fluid px-md-5">

        <!-- ── Page Header ── -->
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-extrabold mb-1" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    <i class="bi bi-music-note-list text-cyan me-2"></i>Biblioteca de Canciones
                </h2>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Catálogo completo de pistas disponibles para emisión en radio FM
                </p>
            </div>
            <button class="btn btn-primary fw-bold px-4 rounded-pill d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalCancion" onclick="limpiarFormulario()">
                <i class="bi bi-plus-circle"></i>
                <span>Nueva canción</span>
            </button>
        </div>

        <!-- ── Buscador + Filtros ── -->
        <div class="glass-card p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <div class="input-group search-wrapper">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="input-buscar" class="form-control"
                            placeholder="Buscar por título, género, disco o grupo..."
                            onkeyup="cargarCanciones()"
                            list="lista-generos"
                            autocomplete="off"
                            aria-label="Buscar canción">
                    </div>
                    <datalist id="lista-generos">
                        <option value="Rock Latino">
                        <option value="Rock & Pop">
                        <option value="Pop Ballad">
                        <option value="Salsa Stereo">
                        <option value="Reggaeton / Urbano">
                        <option value="Electrónica / Dance">
                        <option value="Bachata">
                        <option value="Clásicos 80s / 90s">
                    </datalist>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-outline-cyan flex-fill rounded-pill" onclick="cargarCanciones()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Tabla de canciones ── -->
        <section class="glass-card p-0 mb-5 overflow-hidden">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="bi bi-music-note-beamed text-cyan"></i>
                    Listado de pistas registradas
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-radio align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título de la canción</th>
                            <th>Grupo / Artista</th>
                            <th>Disco / Álbum</th>
                            <th>Duración</th>
                            <th>Género</th>
                            <th>Escuchar</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-cancion-body">
                        <tr>
                            <td colspan="8">
                                <div class="loading-spinner">
                                    <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
                                    Cargando canciones...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- ── Modal formulario Canción ── -->
    <div class="modal fade" id="modalCancion" tabindex="-1" aria-labelledby="modalCancionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-radio">
                <div class="modal-header">
                    <h5 class="modal-title text-cyan fw-bold" id="modalCancionLabel">
                        <i class="bi bi-music-note-beamed me-2"></i>
                        <span id="modalCancionTitulo">Registrar canción</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="form-cancion" onsubmit="guardarCancion(event)" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="cancion-id" name="id">

                        <div class="mb-3">
                            <label for="cancion-disco" class="form-label">
                                <i class="bi bi-disc-fill me-1"></i> Disco / Álbum perteneciente
                            </label>
                            <select class="form-select" id="cancion-disco" name="disco_id" required>
                                <option value="">— Selecciona un disco —</option>
                            </select>
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Selecciona el disco o álbum al que pertenece esta canción.</div>
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>Debes seleccionar un disco.</div>
                        </div>

                        <div class="mb-3">
                            <label for="cancion-titulo" class="form-label">
                                <i class="bi bi-type me-1"></i> Título de la canción
                            </label>
                            <input type="text" class="form-control" id="cancion-titulo" name="titulo"
                                required placeholder="Ej: De Música Ligera" minlength="2" maxlength="100">
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>El título es obligatorio (mín. 2 caracteres).</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="cancion-duracion" class="form-label">
                                    <i class="bi bi-clock me-1"></i> Duración (segundos)
                                </label>
                                <input type="number" class="form-control" id="cancion-duracion" name="duracion_segundos"
                                    min="10" max="1800" value="210" required step="1" placeholder="Ej: 210">
                                <div class="form-text"><i class="bi bi-info-circle me-1"></i>210 s = 3 min 30 s</div>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>Entre 10 y 1800 segundos.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cancion-pista" class="form-label">
                                    <i class="bi bi-list-ol me-1"></i> N° de pista
                                </label>
                                <input type="number" class="form-control" id="cancion-pista" name="numero_pista"
                                    min="1" max="99" value="1" required step="1" placeholder="Ej: 1">
                                <div class="form-text"><i class="bi bi-info-circle me-1"></i>Orden en el disco.</div>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>Entre 1 y 99.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cancion-estado" class="form-label">
                                    <i class="bi bi-toggle-on me-1"></i> Estado
                                </label>
                                <select class="form-select" id="cancion-estado" name="estado">
                                    <option value="1" selected>✅ Activa</option>
                                    <option value="0">⏸️ Inactiva</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cancion-genero" class="form-label">
                                <i class="bi bi-music-note me-1"></i> Género musical
                            </label>
                            <input type="text" class="form-control" id="cancion-genero" name="genero"
                                required placeholder="Ej: Rock Latino" list="lista-generos" maxlength="50">
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Escribe o elige de la lista de géneros disponibles.</div>
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>El género es obligatorio.</div>
                        </div>

                        <div class="mb-3">
                            <label for="cancion-archivo" class="form-label">
                                <i class="bi bi-upload me-1"></i> Subir archivo MP3 desde tu equipo <small class="text-success fw-bold">(Gratis y Permanente)</small>
                            </label>
                            <input type="file" class="form-control" id="cancion-archivo" name="archivo_mp3" accept="audio/*">
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Selecciona una canción en formato .mp3 de tu computadora.</div>
                        </div>

                        <div class="mb-1">
                            <label for="cancion-audio" class="form-label">
                                <i class="bi bi-link-45deg me-1"></i> O ingresa una URL de audio externa (.mp3) <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="url" class="form-control" id="cancion-audio" name="audio_url"
                                placeholder="Si la dejas vacía, se auto-asigna un audio gratis de prueba">
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Si dejas este campo en blanco, el sistema le asigna música automáticamente.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-cancion">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="spinner-cancion"></span>
                            <i class="bi bi-check-circle me-1"></i> Guardar canción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal confirmación eliminar -->
    <div class="modal fade" id="modalConfirmarEliminarCancion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content modal-content-radio">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--accent-coral); font-size: 0.95rem;">
                        <i class="bi bi-music-note me-2"></i>Eliminar canción
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="font-size: 0.9rem; color: var(--text-secondary);">
                    ¿Deseas eliminar esta canción del catálogo? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-confirmar-eliminar-cancion">
                        <i class="bi bi-trash me-1"></i> Sí, eliminar
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
        let cancionIdEliminar = null;

        document.addEventListener('DOMContentLoaded', () => {
            cargarDiscosCombo();
            cargarCanciones();
            document.getElementById('btn-confirmar-eliminar-cancion').addEventListener('click', () => {
                if (cancionIdEliminar !== null) ejecutarEliminarCancion(cancionIdEliminar);
            });
        });

        function cargarDiscosCombo() {
            fetch('backend/api_discos.php?accion=listar')
                .then(r => r.json())
                .then(res => {
                    const sel = document.getElementById('cancion-disco');
                    sel.innerHTML = '<option value="">— Selecciona un disco —</option>';
                    if (res.estado === 'exito') {
                        res.datos.forEach(d => {
                            sel.innerHTML += `<option value="${d.id}">${escapeHTML(d.titulo)} — ${escapeHTML(d.grupo_nombre)}</option>`;
                        });
                    }
                })
                .catch(() => {});
        }

        function formatearMinutos(segundos) {
            const min = Math.floor(segundos / 60);
            const seg = segundos % 60;
            return `${min}:${seg < 10 ? '0' : ''}${seg}`;
        }

        function cargarCanciones() {
            const buscar = document.getElementById('input-buscar').value.trim();
            const tbody  = document.getElementById('tabla-cancion-body');
            tbody.innerHTML = `<tr><td colspan="8"><div class="loading-spinner">
                <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
                Buscando canciones...
            </div></td></tr>`;

            fetch(`backend/api_canciones.php?accion=listar&buscar=${encodeURIComponent(buscar)}`)
                .then(r => r.json())
                .then(res => {
                    tbody.innerHTML = '';
                    if (res.estado === 'exito' && res.datos.length > 0) {
                        res.datos.forEach(c => {
                            const tieneAudio = c.audio_url && c.audio_url.trim() !== '';
                            tbody.innerHTML += `
                                <tr>
                                    <td>
                                        <span class="badge badge-cyan font-monospace" style="font-size: 0.75rem;">#${c.numero_pista}</span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--text-primary);">${escapeHTML(c.titulo)}</strong>
                                        ${c.estado == 0 ? '<span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Inactiva</span>' : ''}
                                    </td>
                                    <td>
                                        <span class="text-amber fw-bold" style="font-size: 0.88rem;">${escapeHTML(c.grupo_nombre)}</span>
                                    </td>
                                    <td>
                                        <small style="color: var(--text-secondary);">${escapeHTML(c.disco_titulo)}</small>
                                    </td>
                                    <td>
                                        <span class="font-monospace" style="color: var(--text-secondary); font-size: 0.82rem;">${formatearMinutos(c.duracion_segundos)}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-magenta" style="font-size: 0.73rem;">${escapeHTML(c.genero)}</span>
                                    </td>
                                    <td>
                                        ${tieneAudio
                                            ? `<button class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: var(--accent-green); border: none; color: #fff;"
                                                title="Reproducir: ${escapeHTML(c.titulo)}"
                                                onclick="reproducirPistaBar('${c.audio_url}', '${escapeHTML(c.titulo)}', '${escapeHTML(c.grupo_nombre)}')">
                                                <i class="bi bi-play-fill"></i>
                                               </button>`
                                            : `<button class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; background: var(--bg-elevated); border: 1px solid var(--border-normal); color: var(--text-muted);"
                                                disabled title="Sin audio disponible">
                                                <i class="bi bi-play-fill"></i>
                                               </button>`
                                        }
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-cyan me-1 rounded-2" title="Editar canción"
                                            onclick='editarCancion(${JSON.stringify(c)})'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-2" title="Eliminar canción"
                                            onclick="confirmarEliminarCancion(${c.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        const msg = buscar
                            ? `No se encontraron canciones para "<strong>${escapeHTML(buscar)}</strong>".`
                            : 'No hay canciones registradas en el catálogo.';
                        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state">
                            <i class="bi bi-music-note-beamed"></i><p>${msg}</p>
                        </div></td></tr>`;
                    }
                })
                .catch(() => {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4" style="color: var(--accent-coral);">
                        <i class="bi bi-wifi-off me-2"></i>Error al cargar el catálogo.
                    </td></tr>`;
                });
        }

        function reproducirPistaBar(url, titulo, artista) {
            if (window.radioFM) {
                window.radioFM.playAudio(url, `${artista} — ${titulo}`);
            }
        }

        function limpiarFormulario() {
            const form = document.getElementById('form-cancion');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('cancion-id').value = '';
            document.getElementById('modalCancionTitulo').textContent = 'Registrar canción';
        }

        function editarCancion(c) {
            document.getElementById('cancion-id').value        = c.id;
            document.getElementById('cancion-disco').value     = c.disco_id;
            document.getElementById('cancion-titulo').value    = c.titulo;
            document.getElementById('cancion-duracion').value  = c.duracion_segundos;
            document.getElementById('cancion-pista').value     = c.numero_pista;
            document.getElementById('cancion-genero').value    = c.genero;
            document.getElementById('cancion-audio').value     = c.audio_url || '';
            document.getElementById('cancion-estado').value    = c.estado;
            document.getElementById('modalCancionTitulo').textContent = 'Editar canción';
            const form = document.getElementById('form-cancion');
            form.classList.remove('was-validated');
            new bootstrap.Modal(document.getElementById('modalCancion')).show();
        }

        function guardarCancion(e) {
            e.preventDefault();
            const form = document.getElementById('form-cancion');
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            const btn     = document.getElementById('btn-guardar-cancion');
            const spinner = document.getElementById('spinner-cancion');
            btn.disabled  = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(form);
            formData.append('accion', 'guardar');

            fetch('backend/api_canciones.php', { method: 'POST', body: formData })
                .then(async r => {
                    const rawText = await r.text();
                    try {
                        return JSON.parse(rawText);
                    } catch (e) {
                        console.error("Respuesta no-JSON del servidor:", rawText);
                        throw new Error("El archivo MP3 excede el límite permitido por PHP o la respuesta del servidor no es válida.");
                    }
                })
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        const modalElem = document.getElementById('modalCancion');
                        const instance = bootstrap.Modal.getInstance(modalElem);
                        if (instance) instance.hide();
                        cargarCanciones();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al guardar la canción.', 'error');
                    }
                })
                .catch(err => {
                    console.error("Error al guardar:", err);
                    mostrarNotificacion(err.message || 'Error de conexión al servidor.', 'error');
                })
                .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
        }

        function confirmarEliminarCancion(id) {
            cancionIdEliminar = id;
            new bootstrap.Modal(document.getElementById('modalConfirmarEliminarCancion')).show();
        }

        function ejecutarEliminarCancion(id) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminarCancion'));
            if (modal) modal.hide();
            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', id);
            fetch('backend/api_canciones.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        cargarCanciones();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al eliminar.', 'error');
                    }
                })
                .catch(() => mostrarNotificacion('Error de conexión.', 'error'));
        }
    </script>
</body>
</html>
