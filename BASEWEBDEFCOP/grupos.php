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
    <meta name="description" content="Catálogo de grupos y bandas musicales de Radio Stereo Wave FM.">
    <title>Grupos Musicales — Radio Wave FM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="frontend/css/estilos_fm.css">
</head>
<body>
    <?php include __DIR__ . '/backend/includes/header.php'; ?>

    <div class="container-fluid px-md-5">

        <!-- ── Page Header ── -->
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-extrabold mb-1" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    <i class="bi bi-people-fill text-cyan me-2"></i>Grupos & Bandas Musicales
                </h2>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Directorio de grupos, bandas y artistas del catálogo de Radio Wave FM
                </p>
            </div>
            <button class="btn btn-primary fw-bold px-4 rounded-pill d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalGrupo" onclick="limpiarFormulario()">
                <i class="bi bi-plus-circle"></i>
                <span>Nuevo grupo musical</span>
            </button>
        </div>

        <!-- ── Buscador ── -->
        <div class="glass-card p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <div class="input-group search-wrapper">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="input-buscar" class="form-control"
                            placeholder="Buscar por nombre de grupo, género o país..."
                            onkeyup="cargarGrupos()"
                            autocomplete="off"
                            aria-label="Buscar grupo">
                    </div>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-cyan w-100 rounded-pill" onclick="cargarGrupos()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar lista
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Vista en tarjetas ── -->
        <div class="row g-4 mb-4" id="grupos-cards-container">
            <div class="col-12">
                <div class="loading-spinner">
                    <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
                    Cargando grupos...
                </div>
            </div>
        </div>

        <!-- ── Tabla completa ── -->
        <div class="glass-card p-0 mb-5 overflow-hidden">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="bi bi-people-fill text-cyan"></i>
                    Bandas y artistas registrados
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-radio align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre del grupo / Banda</th>
                            <th>Género musical</th>
                            <th>País de origen</th>
                            <th>Año formación</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-grupo-body">
                        <tr>
                            <td colspan="7">
                                <div class="loading-spinner">
                                    <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
                                    Cargando...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ── Modal Formulario Grupo ── -->
    <div class="modal fade" id="modalGrupo" tabindex="-1" aria-labelledby="modalGrupoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-radio">
                <div class="modal-header">
                    <h5 class="modal-title text-cyan fw-bold" id="modalGrupoLabel">
                        <i class="bi bi-people me-2"></i>
                        <span id="modalGrupoTitulo">Registrar grupo musical</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="form-grupo" onsubmit="guardarGrupo(event)" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="grupo-id" name="id">

                        <div class="mb-3">
                            <label for="grupo-nombre" class="form-label">
                                <i class="bi bi-music-note-list me-1"></i> Nombre del grupo o artista
                            </label>
                            <input type="text" class="form-control" id="grupo-nombre" name="nombre"
                                required placeholder="Ej: Soda Stereo" minlength="2" maxlength="80">
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>El nombre es obligatorio.</div>
                        </div>

                        <div class="mb-3">
                            <label for="grupo-genero" class="form-label">
                                <i class="bi bi-music-note me-1"></i> Género musical
                            </label>
                            <input type="text" class="form-control" id="grupo-genero" name="genero_musical"
                                required placeholder="Ej: Rock Latino" maxlength="50">
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>El género es obligatorio.</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="grupo-pais" class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i> País de origen
                                </label>
                                <input type="text" class="form-control" id="grupo-pais" name="pais_origen"
                                    required placeholder="Ej: Argentina" maxlength="50">
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>El país es obligatorio.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="grupo-anio" class="form-label">
                                    <i class="bi bi-calendar me-1"></i> Año de formación
                                </label>
                                <input type="number" class="form-control" id="grupo-anio" name="anio_formacion"
                                    min="1900" max="2030" value="1990" required>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>Entre 1900 y 2030.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="grupo-imagen" class="form-label">
                                <i class="bi bi-image me-1"></i> URL de la Imagen / Logo de la Banda
                            </label>
                            <input type="url" class="form-control" id="grupo-imagen" name="imagen_url"
                                placeholder="Ej: https://.../banda.jpg">
                            <div class="form-text text-muted">Pega la URL de una foto o logo del grupo.</div>
                        </div>

                        <div class="mb-2">
                            <label for="grupo-estado" class="form-label">
                                <i class="bi bi-toggle-on me-1"></i> Estado
                            </label>
                            <select class="form-select" id="grupo-estado" name="estado">
                                <option value="1" selected>✅ Activo</option>
                                <option value="0">⏸️ Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-grupo">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="spinner-grupo"></span>
                            <i class="bi bi-check-circle me-1"></i> Guardar grupo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal confirmación eliminar -->
    <div class="modal fade" id="modalConfirmarEliminarGrupo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content modal-content-radio">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--accent-coral); font-size: 0.95rem;">
                        <i class="bi bi-people me-2"></i>Eliminar grupo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="font-size: 0.9rem; color: var(--text-secondary);">
                    ¿Deseas eliminar este grupo del catálogo? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-confirmar-eliminar-grupo">
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
        let idGrupoAEliminar = null;

        document.addEventListener('DOMContentLoaded', () => {
            cargarGrupos();

            document.getElementById('btn-confirmar-eliminar-grupo').addEventListener('click', () => {
                if (idGrupoAEliminar) ejecutarEliminarGrupo(idGrupoAEliminar);
            });
        });

        function cargarGrupos() {
            const buscar = document.getElementById('input-buscar').value.trim();
            const url    = `backend/api_grupos.php?accion=listar&buscar=${encodeURIComponent(buscar)}`;

            fetch(url)
                .then(r => r.json())
                .then(res => {
                    const cards = document.getElementById('grupos-cards-container');
                    const tbody = document.getElementById('tabla-grupo-body');
                    cards.innerHTML = '';
                    tbody.innerHTML = '';

                    if (res.estado === 'exito' && res.datos.length > 0) {
                        res.datos.forEach(g => {
                            const fotoGrupo = g.imagen_url && g.imagen_url.trim() !== '' 
                                ? g.imagen_url 
                                : 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=300&auto=format&fit=crop&q=60';

                            cards.innerHTML += `
                                <div class="col-md-6 col-lg-4">
                                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between radio-card-effect">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="${fotoGrupo}" alt="${escapeHTML(g.nombre)}" class="rounded-circle border border-cyan" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=300&auto=format&fit=crop&q=60'">
                                                    <div>
                                                        <h5 class="fw-bold mb-0" style="color: var(--text-primary); font-size: 1.1rem;">
                                                            ${escapeHTML(g.nombre)}
                                                        </h5>
                                                        <span class="badge badge-cyan mt-1">${escapeHTML(g.genero_musical)}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column gap-2 mb-3" style="font-size: 0.85rem; color: var(--text-secondary);">
                                                <div>
                                                    <i class="bi bi-geo-alt-fill text-coral me-2"></i>Origen:
                                                    <strong style="color: var(--text-primary);">${escapeHTML(g.pais_origen)}</strong>
                                                </div>
                                                <div>
                                                    <i class="bi bi-calendar-event text-amber me-2"></i>Formación:
                                                    <strong style="color: var(--text-primary);">${g.anio_formacion}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: var(--border-subtle) !important;">
                                            ${g.estado == 1
                                                ? '<span class="badge badge-green"><i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Activo</span>'
                                                : '<span class="badge bg-secondary">Inactivo</span>'}
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-cyan rounded-2" title="Editar" onclick='editarGrupo(${JSON.stringify(g)})'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger rounded-2" title="Eliminar" onclick="confirmarEliminarGrupo(${g.id})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            tbody.innerHTML += `
                                <tr>
                                    <td><span class="font-monospace text-muted" style="font-size: 0.78rem;">#${g.id}</span></td>
                                    <td><strong style="color: var(--text-primary);">${escapeHTML(g.nombre)}</strong></td>
                                    <td><span class="badge badge-cyan">${escapeHTML(g.genero_musical)}</span></td>
                                    <td style="color: var(--text-secondary);">
                                        <i class="bi bi-geo-alt-fill text-coral me-1" style="font-size: 0.75rem;"></i>${escapeHTML(g.pais_origen)}
                                    </td>
                                    <td><strong style="color: var(--text-primary);">${g.anio_formacion}</strong></td>
                                    <td>
                                        ${g.estado == 1
                                            ? '<span class="badge badge-green"><i class="bi bi-circle-fill me-1" style="font-size:0.45rem;"></i>Activo</span>'
                                            : '<span class="badge bg-secondary">Inactivo</span>'}
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-cyan me-1 rounded-2" title="Editar" onclick='editarGrupo(${JSON.stringify(g)})'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-2" title="Eliminar" onclick="confirmarEliminarGrupo(${g.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        const msg = buscar
                            ? `No se encontraron grupos para "<strong>${escapeHTML(buscar)}</strong>".`
                            : 'No hay grupos registrados en el catálogo.';
                        cards.innerHTML = `<div class="col-12"><div class="empty-state"><i class="bi bi-people"></i><p>${msg}</p></div></div>`;
                        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i><p>${msg}</p></div></td></tr>`;
                    }
                })
                .catch(() => {
                    const err = '<div class="text-center py-4" style="color:var(--accent-coral);"><i class="bi bi-wifi-off me-2"></i>Error al cargar datos.</div>';
                    document.getElementById('grupos-cards-container').innerHTML = `<div class="col-12">${err}</div>`;
                    document.getElementById('tabla-grupo-body').innerHTML = `<tr><td colspan="7">${err}</td></tr>`;
                });
        }

        function limpiarFormulario() {
            const form = document.getElementById('form-grupo');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('grupo-id').value = '';
            document.getElementById('grupo-imagen').value = '';
            document.getElementById('modalGrupoTitulo').textContent = 'Registrar grupo musical';
        }

        function editarGrupo(g) {
            document.getElementById('grupo-id').value     = g.id;
            document.getElementById('grupo-nombre').value = g.nombre;
            document.getElementById('grupo-genero').value = g.genero_musical;
            document.getElementById('grupo-pais').value   = g.pais_origen;
            document.getElementById('grupo-anio').value   = g.anio_formacion;
            document.getElementById('grupo-imagen').value = g.imagen_url || '';
            document.getElementById('grupo-estado').value = g.estado;
            document.getElementById('modalGrupoTitulo').textContent = 'Editar grupo musical';
            const form = document.getElementById('form-grupo');
            form.classList.remove('was-validated');
            new bootstrap.Modal(document.getElementById('modalGrupo')).show();
        }

        function guardarGrupo(e) {
            e.preventDefault();
            const form = document.getElementById('form-grupo');
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            const btn     = document.getElementById('btn-guardar-grupo');
            const spinner = document.getElementById('spinner-grupo');
            btn.disabled  = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(form);
            formData.append('accion', 'guardar');

            fetch('backend/api_grupos.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        bootstrap.Modal.getInstance(document.getElementById('modalGrupo')).hide();
                        cargarGrupos();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al guardar.', 'error');
                    }
                })
                .catch(() => mostrarNotificacion('Error de conexión.', 'error'))
                .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
        }

        function confirmarEliminarGrupo(id) {
            grupoIdEliminar = id;
            new bootstrap.Modal(document.getElementById('modalConfirmarEliminarGrupo')).show();
        }

        function ejecutarEliminarGrupo(id) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminarGrupo'));
            if (modal) modal.hide();
            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', id);
            fetch('backend/api_grupos.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        cargarGrupos();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al eliminar.', 'error');
                    }
                })
                .catch(() => mostrarNotificacion('Error de conexión.', 'error'));
        }
    </script>
</body>
</html>
