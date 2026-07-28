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
    <meta name="description" content="Directorio de locutores y DJs de Radio Stereo Wave FM.">
    <title>Locutores & DJs — Radio Wave FM</title>
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
                    <i class="bi bi-person-badge text-cyan me-2"></i>Locutores & DJs
                </h2>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Equipo de locución y conductores al aire de Stereo Wave 98.1 FM
                </p>
            </div>
            <button class="btn btn-primary fw-bold px-4 rounded-pill d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalDj" onclick="limpiarFormulario()">
                <i class="bi bi-plus-circle"></i>
                <span>Nuevo Locutor / DJ</span>
            </button>
        </div>

        <!-- ── Buscador ── -->
        <div class="glass-card p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <div class="input-group search-wrapper">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="input-buscar" class="form-control"
                            placeholder="Buscar por nombre, apodo DJ o cédula..."
                            onkeyup="cargarDiscjockeys()"
                            aria-label="Buscar locutor"
                            autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-outline-cyan w-100 rounded-pill" onclick="cargarDiscjockeys()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar lista
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Vista en tarjetas ── -->
        <div class="row g-4 mb-4" id="dj-cards-container">
            <div class="col-12">
                <div class="loading-spinner">
                    <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
                    Cargando locutores...
                </div>
            </div>
        </div>

        <!-- ── Tabla completa ── -->
        <div class="glass-card p-0 mb-5 overflow-hidden">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="bi bi-people-fill text-cyan"></i>
                    Directorio completo
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-radio align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre Real</th>
                            <th>Apodo DJ</th>
                            <th>Experiencia</th>
                            <th>Turno</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-dj-body">
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

    <!-- ── Modal formulario DJ ── -->
    <div class="modal fade" id="modalDj" tabindex="-1" aria-labelledby="modalDjLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-radio">
                <div class="modal-header">
                    <h5 class="modal-title text-cyan fw-bold" id="modalDjLabel">
                        <i class="bi bi-person-plus-fill me-2"></i>
                        <span id="modalDjTitulo">Registrar Locutor / DJ</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="form-dj" onsubmit="guardarDiscjockey(event)" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="dj-id" name="id">

                        <div class="mb-3">
                            <label for="dj-cedula" class="form-label">
                                <i class="bi bi-card-heading me-1"></i> Cédula de identidad
                            </label>
                            <input type="text" class="form-control" id="dj-cedula" name="cedula"
                                maxlength="10" required placeholder="Ej: 1726549821"
                                pattern="[0-9]{6,10}" title="Solo números, entre 6 y 10 dígitos">
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Número de identificación oficial (solo dígitos).</div>
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>Ingresa una cédula válida (6–10 dígitos).</div>
                        </div>

                        <div class="mb-3">
                            <label for="dj-nombre" class="form-label">
                                <i class="bi bi-person me-1"></i> Nombre completo
                            </label>
                            <input type="text" class="form-control" id="dj-nombre" name="nombre"
                                required placeholder="Ej: Alejandro Vega Morales" minlength="3" maxlength="80">
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>El nombre es obligatorio (mín. 3 caracteres).</div>
                        </div>

                        <div class="mb-3">
                            <label for="dj-apodo" class="form-label">
                                <i class="bi bi-mic me-1"></i> Apodo artístico / DJ
                            </label>
                            <input type="text" class="form-control" id="dj-apodo" name="apodo_dj"
                                required placeholder="Ej: DJ Alex Wave" maxlength="50">
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>El nombre con el que sale al aire.</div>
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>Ingresa el apodo artístico.</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="dj-experiencia" class="form-label">
                                    <i class="bi bi-award me-1"></i> Años de experiencia
                                </label>
                                <input type="number" class="form-control" id="dj-experiencia"
                                    name="experiencia_anos" min="0" max="60" value="5" required>
                                <div class="form-text"><i class="bi bi-info-circle me-1"></i>Años de trayectoria en radio.</div>
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>Valor entre 0 y 60 años.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="dj-turno" class="form-label">
                                    <i class="bi bi-clock-history me-1"></i> Turno de emisión
                                </label>
                                <select class="form-select" id="dj-turno" name="turno">
                                    <option value="Mañana">🌅 Mañana</option>
                                    <option value="Tarde" selected>☀️ Tarde</option>
                                    <option value="Noche">🌙 Noche</option>
                                    <option value="Madrugada">⭐ Madrugada</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="dj-estado" class="form-label">
                                <i class="bi bi-toggle-on me-1"></i> Estado
                            </label>
                            <select class="form-select" id="dj-estado" name="estado">
                                <option value="1" selected>✅ Activo — En nómina</option>
                                <option value="0">⏸️ Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-dj">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="spinner-dj"></span>
                            <i class="bi bi-check-circle me-1"></i> Guardar locutor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal confirmación de eliminación -->
    <div class="modal fade" id="modalConfirmarEliminarDj" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content modal-content-radio">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--accent-coral); font-size: 0.95rem;">
                        <i class="bi bi-person-dash me-2"></i>Eliminar locutor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="font-size: 0.9rem; color: var(--text-secondary);">
                    ¿Deseas eliminar este locutor del directorio? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-confirmar-eliminar-dj">
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
        let djIdEliminar = null;

        document.addEventListener('DOMContentLoaded', () => {
            cargarDiscjockeys();
            document.getElementById('btn-confirmar-eliminar-dj').addEventListener('click', () => {
                if (djIdEliminar !== null) ejecutarEliminarDj(djIdEliminar);
            });
        });

        function cargarDiscjockeys() {
            const buscar = document.getElementById('input-buscar').value.trim();
            const cards  = document.getElementById('dj-cards-container');
            const tbody  = document.getElementById('tabla-dj-body');

            fetch(`backend/api_discjockeys.php?accion=listar&buscar=${encodeURIComponent(buscar)}`)
                .then(r => r.json())
                .then(res => {
                    cards.innerHTML = '';
                    tbody.innerHTML = '';

                    if (res.estado === 'exito' && res.datos.length > 0) {
                        res.datos.forEach(dj => {
                            const img       = dj.id % 2 === 0 ? 'frontend/img/dj_2.png' : 'frontend/img/dj_1.png';
                            const activoBadge = dj.estado == 1
                                ? '<span class="position-absolute bottom-0 end-0 badge bg-success rounded-circle p-1 border border-dark" title="Activo" style="width: 14px; height: 14px;"></span>'
                                : '';
                            const turnoMap  = { 'Mañana': '🌅', 'Tarde': '☀️', 'Noche': '🌙', 'Madrugada': '⭐' };
                            const turnoIcon = turnoMap[dj.turno] || '🎙️';

                            cards.innerHTML += `
                                <div class="col-sm-6 col-md-4 col-lg-3">
                                    <div class="glass-card text-center p-4 h-100 radio-card-effect d-flex flex-column">
                                        <div class="mb-3 position-relative d-inline-flex justify-content-center">
                                            <img src="${img}" class="artist-card-img" alt="${escapeHTML(dj.apodo_dj)}" loading="lazy">
                                            ${activoBadge}
                                        </div>
                                        <h6 class="fw-bold mb-1" style="color: var(--text-primary);">${escapeHTML(dj.apodo_dj)}</h6>
                                        <small class="text-cyan d-block mb-3">${escapeHTML(dj.nombre)}</small>
                                        <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
                                            <span class="badge badge-turno">${turnoIcon} ${escapeHTML(dj.turno)}</span>
                                            <span class="badge badge-cyan">${dj.experiencia_anos} años exp.</span>
                                        </div>
                                        <div class="d-flex justify-content-center gap-2 mt-auto">
                                            <button class="btn btn-sm btn-outline-cyan" title="Editar locutor"
                                                onclick='editarDj(${JSON.stringify(dj)})'>
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar locutor"
                                                onclick="confirmarEliminarDj(${dj.id})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;

                            tbody.innerHTML += `
                                <tr>
                                    <td><span class="font-monospace" style="color: var(--accent-cyan); font-size: 0.82rem;">${escapeHTML(dj.cedula)}</span></td>
                                    <td><strong style="color: var(--text-primary);">${escapeHTML(dj.nombre)}</strong></td>
                                    <td><span class="badge badge-dj">${escapeHTML(dj.apodo_dj)}</span></td>
                                    <td style="color: var(--text-secondary);">${dj.experiencia_anos} años</td>
                                    <td><span class="badge badge-turno">${turnoIcon} ${escapeHTML(dj.turno)}</span></td>
                                    <td>
                                        ${dj.estado == 1
                                            ? '<span class="badge badge-green"><i class="bi bi-circle-fill me-1" style="font-size:0.45rem;"></i>Activo</span>'
                                            : '<span class="badge bg-secondary">Inactivo</span>'}
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-cyan me-1 rounded-2" title="Editar"
                                            onclick='editarDj(${JSON.stringify(dj)})'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-2" title="Eliminar"
                                            onclick="confirmarEliminarDj(${dj.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        const msg = buscar
                            ? `No se encontraron locutores para "<strong>${escapeHTML(buscar)}</strong>".`
                            : 'No hay locutores registrados.';
                        cards.innerHTML = `<div class="col-12"><div class="empty-state"><i class="bi bi-person-slash"></i><p>${msg}</p></div></div>`;
                        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="bi bi-person-slash"></i><p>${msg}</p></div></td></tr>`;
                    }
                })
                .catch(() => {
                    const err = '<div class="text-center py-4" style="color: var(--accent-coral);"><i class="bi bi-wifi-off me-2"></i>Error al cargar datos.</div>';
                    document.getElementById('dj-cards-container').innerHTML = `<div class="col-12">${err}</div>`;
                    tbody.innerHTML = `<tr><td colspan="7">${err}</td></tr>`;
                });
        }

        function limpiarFormulario() {
            const form = document.getElementById('form-dj');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('dj-id').value = '';
            document.getElementById('modalDjTitulo').textContent = 'Registrar Locutor / DJ';
        }

        function editarDj(dj) {
            document.getElementById('dj-id').value            = dj.id;
            document.getElementById('dj-cedula').value        = dj.cedula;
            document.getElementById('dj-nombre').value        = dj.nombre;
            document.getElementById('dj-apodo').value         = dj.apodo_dj;
            document.getElementById('dj-experiencia').value   = dj.experiencia_anos;
            document.getElementById('dj-turno').value         = dj.turno;
            document.getElementById('dj-estado').value        = dj.estado;
            document.getElementById('modalDjTitulo').textContent = 'Editar Locutor / DJ';
            const form = document.getElementById('form-dj');
            form.classList.remove('was-validated');
            new bootstrap.Modal(document.getElementById('modalDj')).show();
        }

        function guardarDiscjockey(e) {
            e.preventDefault();
            const form = document.getElementById('form-dj');
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            const btn     = document.getElementById('btn-guardar-dj');
            const spinner = document.getElementById('spinner-dj');
            btn.disabled  = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(form);
            formData.append('accion', 'guardar');

            fetch('backend/api_discjockeys.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        bootstrap.Modal.getInstance(document.getElementById('modalDj')).hide();
                        cargarDiscjockeys();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al guardar.', 'error');
                    }
                })
                .catch(() => mostrarNotificacion('Error de conexión.', 'error'))
                .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
        }

        function confirmarEliminarDj(id) {
            djIdEliminar = id;
            new bootstrap.Modal(document.getElementById('modalConfirmarEliminarDj')).show();
        }

        function ejecutarEliminarDj(id) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminarDj'));
            if (modal) modal.hide();
            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', id);
            fetch('backend/api_discjockeys.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        cargarDiscjockeys();
                    } else {
                        mostrarNotificacion(res.mensaje || 'Error al eliminar.', 'error');
                    }
                })
                .catch(() => mostrarNotificacion('Error de conexión.', 'error'));
        }
    </script>
</body>
</html>
