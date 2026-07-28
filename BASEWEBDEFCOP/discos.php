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
    <title>Discos & Álbumes - Radio Wave FM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="frontend/css/estilos_fm.css">
</head>
<body>
    <?php include __DIR__ . '/backend/includes/header.php'; ?>

    <div class="container-fluid px-md-5">
        
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-extrabold mb-1" style="font-size: 1.65rem; letter-spacing: -0.02em;">
                    <i class="bi bi-disc-fill text-amber me-2"></i>Discos & Álbumes
                </h2>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">Catálogo de álbumes y discografía registrada en la emisora</p>
            </div>
            <button class="btn btn-primary fw-bold px-4 rounded-pill d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalDisco" onclick="limpiarFormulario()">
                <i class="bi bi-plus-circle"></i>
                <span>Nuevo disco / álbum</span>
            </button>
        </div>

        <!-- Buscador -->
        <div class="glass-card p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <div class="input-group search-wrapper">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="input-buscar" class="form-control"
                            placeholder="Buscar por título, grupo o discográfica..."
                            onkeyup="cargarDiscos()"
                            autocomplete="off"
                            aria-label="Buscar disco">
                    </div>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-cyan w-100 rounded-pill" onclick="cargarDiscos()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Muro de Vinilos/Álbumes -->
        <div class="row g-4 mb-4" id="discos-cards-container">
            <!-- Render dinámico -->
        </div>

        <!-- Tabla Completa de Discos -->
        <div class="glass-card p-0 mb-5 overflow-hidden">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="bi bi-disc-fill text-amber"></i>
                    Discoteca completa
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-radio align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Título del disco</th>
                            <th>Grupo musical</th>
                            <th>Año lanzamiento</th>
                            <th>Discográfica</th>
                            <th>Formato</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-disco-body">
                        <tr><td colspan="7"><div class="loading-spinner">
                            <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
                            Cargando catálogo...
                        </div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Formulario Disco -->
    <div class="modal fade" id="modalDisco" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-radio">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-cyan fw-bold" id="modalDiscoTitulo"><i class="bi bi-disc me-2"></i> Registrar Disco / Álbum</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-disco" onsubmit="guardarDisco(event)" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="disco-id" name="id">

                        <div class="mb-3">
                            <label for="disco-grupo" class="form-label text-light">Grupo Musical (Banda / Artista)</label>
                            <select class="form-select" id="disco-grupo" name="grupo_id" required>
                                <option value="">Seleccione un grupo...</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un grupo.</div>
                        </div>

                        <div class="mb-3">
                            <label for="disco-titulo" class="form-label text-light">Título del Disco / Álbum</label>
                            <input type="text" class="form-control" id="disco-titulo" name="titulo" required placeholder="Ej: Canción Animal">
                            <div class="invalid-feedback">El título del disco es obligatorio.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="disco-anio" class="form-label text-light">Año de Lanzamiento</label>
                                <input type="number" class="form-control" id="disco-anio" name="anio_lanzamiento" min="1950" max="2030" value="2000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="disco-formato" class="form-label text-light">Formato</label>
                                <select class="form-select" id="disco-formato" name="formato" required>
                                    <option value="Digital">Digital</option>
                                    <option value="CD">CD</option>
                                    <option value="Vinilo">Vinilo</option>
                                    <option value="Casete">Casete</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="disco-discografica" class="form-label text-light">Sello Discográfico</label>
                            <input type="text" class="form-control" id="disco-discografica" name="discografica" placeholder="Ej: Sony Music / CBS">
                        </div>

                        <div class="mb-3">
                            <label for="disco-imagen" class="form-label text-light">
                                <i class="bi bi-image text-cyan me-1"></i> URL de la Imagen de Portada (Carátula)
                            </label>
                            <input type="url" class="form-control" id="disco-imagen" name="imagen_url" placeholder="Ej: https://.../portada.jpg">
                            <div class="form-text text-muted">Pega el enlace web de la imagen de portada o carátula del álbum.</div>
                        </div>

                        <div class="mb-3">
                            <label for="disco-estado" class="form-label text-light">Estado</label>
                            <select class="form-select" id="disco-estado" name="estado">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary bg-gradient">Guardar Disco</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/backend/includes/player_bar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/validaciones.js"></script>
    <script src="frontend/js/radio_tuner.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            cargarGruposCombo();
            cargarDiscos();
        });

        function cargarGruposCombo() {
            fetch('backend/api_grupos.php?accion=listar')
                .then(res => res.json())
                .then(res => {
                    const select = document.getElementById('disco-grupo');
                    select.innerHTML = '<option value="">Seleccione un grupo...</option>';
                    if (res.estado === 'exito') {
                        res.datos.forEach(g => {
                            select.innerHTML += `<option value="${g.id}">${g.nombre} (${g.genero_musical})</option>`;
                        });
                    }
                });
        }

        function cargarDiscos() {
            const buscar = document.getElementById('input-buscar').value;
            fetch(`backend/api_discos.php?accion=listar&buscar=${encodeURIComponent(buscar)}`)
                .then(res => res.json())
                .then(res => {
                    const cardsContainer = document.getElementById('discos-cards-container');
                    const tbody = document.getElementById('tabla-disco-body');
                    cardsContainer.innerHTML = '';
                    tbody.innerHTML = '';

                    if (res.estado === 'exito' && res.datos.length > 0) {
                        res.datos.forEach(d => {
                            const vinylImgMap = {
                                1: 'frontend/img/vinyl_1.png',
                                2: 'frontend/img/vinyl_2.png',
                                3: 'frontend/img/vinyl_3.png',
                                4: 'frontend/img/vinyl_4.png'
                            };
                            const vinylImg = d.imagen_url && d.imagen_url.trim() !== '' ? d.imagen_url : (vinylImgMap[d.id] || 'frontend/img/album_cover.png');

                            cardsContainer.innerHTML += `
                                <div class="col-md-4 col-lg-3">
                                    <div class="glass-card text-center p-4 h-100 position-relative">
                                        <div class="mb-3 position-relative d-inline-block">
                                            <img src="${vinylImg}" alt="${d.titulo}" class="rounded-3 shadow-lg border border-cyan" style="width: 100px; height: 100px; object-fit: cover;" onerror="this.src='frontend/img/album_cover.png'">
                                            <span class="position-absolute bottom-0 end-0 badge bg-dark border border-warning text-warning">${d.formato}</span>
                                        </div>
                                        <h5 class="text-white fw-bold mb-1">${d.titulo}</h5>
                                        <span class="badge badge-cyan mb-2">${d.grupo_nombre}</span>
                                        <small class="text-secondary d-block mb-3">Lanzamiento: <strong class="text-white">${d.anio_lanzamiento}</strong></small>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-cyan" onclick='editarDisco(${JSON.stringify(d)})'><i class="bi bi-pencil me-1"></i> Editar</button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarDisco(${d.id})"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            `;

                            tbody.innerHTML += `
                                <tr>
                                    <td><strong class="text-white"><i class="bi bi-disc text-amber me-2"></i>${d.titulo}</strong></td>
                                    <td><span class="badge bg-dark border border-info text-cyan">${d.grupo_nombre}</span></td>
                                    <td>${d.anio_lanzamiento}</td>
                                    <td>${d.discografica || 'Independiente'}</td>
                                    <td><span class="badge bg-secondary">${d.formato}</span></td>
                                    <td>${d.estado == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-cyan me-1" onclick='editarDisco(${JSON.stringify(d)})'><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarDisco(${d.id})"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        cardsContainer.innerHTML = '<div class="col-12 text-center text-muted py-4">No hay discos registrados.</div>';
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron discos.</td></tr>';
                    }
                });
        }

        function limpiarFormulario() {
            document.getElementById('form-disco').reset();
            document.getElementById('disco-id').value = '';
            document.getElementById('disco-imagen').value = '';
            document.getElementById('modalDiscoTitulo').innerText = 'Registrar Disco / Álbum';
        }

        function editarDisco(d) {
            document.getElementById('disco-id').value = d.id;
            document.getElementById('disco-grupo').value = d.grupo_id;
            document.getElementById('disco-titulo').value = d.titulo;
            document.getElementById('disco-anio').value = d.anio_lanzamiento;
            document.getElementById('disco-formato').value = d.formato;
            document.getElementById('disco-discografica').value = d.discografica;
            document.getElementById('disco-imagen').value = d.imagen_url || '';
            document.getElementById('disco-estado').value = d.estado;

            document.getElementById('modalDiscoTitulo').innerText = 'Editar Disco / Álbum';
            const modal = new bootstrap.Modal(document.getElementById('modalDisco'));
            modal.show();
        }

        function guardarDisco(e) {
            e.preventDefault();
            const form = document.getElementById('form-disco');
            if (!form.checkValidity()) return;

            const formData = new FormData(form);
            formData.append('accion', 'guardar');

            fetch('backend/api_discos.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.estado === 'exito') {
                    mostrarNotificacion(res.mensaje, 'exito');
                    bootstrap.Modal.getInstance(document.getElementById('modalDisco')).hide();
                    cargarDiscos();
                } else {
                    mostrarNotificacion(res.mensaje, 'error');
                }
            });
        }

        function eliminarDisco(id) {
            if (confirm('¿Desea borrar este disco?')) {
                const formData = new FormData();
                formData.append('accion', 'eliminar');
                formData.append('id', id);

                fetch('backend/api_discos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.estado === 'exito') {
                        mostrarNotificacion(res.mensaje, 'exito');
                        cargarDiscos();
                    } else {
                        mostrarNotificacion(res.mensaje, 'error');
                    }
                });
            }
        }
    </script>
</body>
</html>
