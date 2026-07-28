<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
    exit();
}
$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Sistema POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/dashboard.css">
    <style>
        .btn-verde {
            background-color: var(--verde-oscuro);
            color: white;
        }
        .btn-verde:hover {
            background-color: var(--verde-medio);
            color: white;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div id="content" class="w-100" style="margin-left: 280px;">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm p-3 mb-4">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <span class="navbar-brand mb-0 h4 text-secondary">&gt;CATALOGO</span>
                    <div>
                        <span class="me-4 fw-bold" style="color: var(--verde-oscuro);">
                            👤<?php echo strtoupper($usuario['nombre'] . ' | Rol:' . ucfirst($usuario['rol'])); ?>
                        </span>
                        <a href="backend/logout.php" class="btn btn-sm btn-outline-danger fw-bold">Cerrar sesión</a>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between mb-4">
                    <input type="text" id="input-busqueda" class="form-control w-25" placeholder="🔎 Buscar por código o nombre">
                    <button class="btn btn-verde" onclick="abrirModal()"> + Nuevo Producto</button>
                </div>
                <div class="card-shadow-sm">
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpo-tabla">
                                <tr id="fila-cargando">
                                    <td colspan="5" class="text-center text-muted py-4">Cargando productos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL: NUEVO / EDITAR PRODUCTO ===================== -->
    <div class="modal fade" id="modal-producto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo-modal-producto">+ Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="producto-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código de barras</label>
                        <input type="text" id="producto-codigo" class="form-control" placeholder="Ej: 7501234567890">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" id="producto-nombre" class="form-control" placeholder="Ej: Coca Cola 500ml">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <input type="text" id="producto-descripcion" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Precio</label>
                            <input type="number" step="0.01" min="0" id="producto-precio" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" min="0" id="producto-stock" class="form-control" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-verde" onclick="guardarProducto()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/catalogo.js"></script>
</body>
</html>
