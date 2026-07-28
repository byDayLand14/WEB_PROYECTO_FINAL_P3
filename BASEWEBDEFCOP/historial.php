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
    <title>Historial de Facturas - Sistema POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <style>
        .btn-verde {
            background-color: var(--verde-oscuro);
            color: white;
        }
        .btn-verde:hover {
            background-color: var(--verde-medio);
            color: white;
        }
        .tarjeta-totalizador {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            padding: 1.25rem;
            height: 100%;
        }
        .tarjeta-totalizador .valor {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--verde-oscuro);
        }
        .tarjeta-totalizador .etiqueta {
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .badge-pagada {
            background-color: #198754;
        }
        .badge-anulada {
            background-color: #dc3545;
        }
        #tabla-facturas tbody tr td {
            vertical-align: middle;
        }
        .fila-anulada {
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div id="content" class="w-100" style="margin-left: 280px;">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm p-3 mb-4">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <span class="navbar-brand mb-0 h4 text-secondary">&gt;HISTORIAL DE FACTURAS</span>
                    <div>
                        <span class="me-4 fw-bold" style="color: var(--verde-oscuro);">
                            👤<?php echo strtoupper($usuario['nombre'] . ' | Rol:' . ucfirst($usuario['rol'])); ?>
                        </span>
                        <a href="backend/logout.php" class="btn btn-sm btn-outline-danger fw-bold">Cerrar sesión</a>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4 pb-5">

                <!-- ===================== TARJETAS TOTALIZADORAS ===================== -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="tarjeta-totalizador">
                            <div class="etiqueta">💰 Total Vendido</div>
                            <div class="valor" id="card-total-vendido">$0.00</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tarjeta-totalizador">
                            <div class="etiqueta">🧾 Cantidad de Facturas</div>
                            <div class="valor" id="card-cantidad-facturas">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tarjeta-totalizador">
                            <div class="etiqueta">📊 Ticket Promedio</div>
                            <div class="valor" id="card-ticket-promedio">$0.00</div>
                        </div>
                    </div>
                </div>

                <!-- ===================== FILTROS (BÚSQUEDA AVANZADA) ===================== -->
                <div class="card-shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Fecha inicio</label>
                                <input type="date" id="filtro-fecha-inicio" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Fecha fin</label>
                                <input type="date" id="filtro-fecha-fin" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Cliente (nombre o cédula)</label>
                                <input type="text" id="filtro-cliente" class="form-control" placeholder="Ej: Juan Pérez">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">N° Factura</label>
                                <input type="number" id="filtro-factura" class="form-control" placeholder="Ej: 15">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button class="btn btn-verde w-50" id="btn-filtrar">🔎 Buscar</button>
                                <button class="btn btn-outline-secondary w-50" id="btn-limpiar-filtros">Limpiar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== TABLA PRINCIPAL ===================== -->
                <div class="card-shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0" id="tabla-facturas">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Factura</th>
                                    <th>Fecha y Hora</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpo-facturas">
                                <tr id="fila-cargando">
                                    <td colspan="7" class="text-center text-muted py-4">Cargando facturas...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL: VER DETALLES DE FACTURA ===================== -->
    <div class="modal fade" id="modal-detalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🧾 Detalle de Factura #<span id="detalle-numero-factura"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Cliente:</strong> <span id="detalle-cliente"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Vendedor:</strong> <span id="detalle-vendedor"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha:</strong> <span id="detalle-fecha"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong> <span id="detalle-estado"></span>
                        </div>
                    </div>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>P. Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detalle-productos-cuerpo"></tbody>
                    </table>
                    <div class="text-end">
                        <div>Subtotal: <strong id="detalle-subtotal"></strong></div>
                        <div>IVA (15%): <strong id="detalle-iva"></strong></div>
                        <div class="fs-5">TOTAL: <strong id="detalle-total" style="color: var(--verde-oscuro);"></strong></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-verde" id="btn-reimprimir-desde-modal">🖨️ Re-imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/historial.js"></script>
</body>
</html>
