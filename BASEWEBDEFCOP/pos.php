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
    <title>Punto de Venta - Sistema POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="frontend/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        .btn-verde {
            background-color: var(--verde-oscuro);
            color: white;
        }
        .btn-verde:hover {
            background-color: var(--verde-medio);
            color: white;
        }
        #buscador-producto {
            font-size: 1.4rem;
            padding: 0.9rem;
        }
        #tabla-carrito tbody tr td {
            vertical-align: middle;
        }
        .cantidad-box {
            width: 70px;
            text-align: center;
        }
        .panel-facturacion {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            padding: 1.25rem;
        }
        .total-final {
            font-size: 2rem;
            font-weight: 700;
            color: var(--verde-oscuro);
        }
        #btn-procesar-venta {
            font-size: 1.5rem;
            padding: 1.25rem;
            font-weight: bold;
        }
        #resultados-cliente, #resultados-producto {
            position: absolute;
            z-index: 20;
            background: white;
            width: 100%;
            max-height: 220px;
            overflow-y: auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            border-radius: 0 0 8px 8px;
        }
        #resultados-cliente .item-resultado,
        #resultados-producto .item-resultado {
            padding: 8px 12px;
            cursor: pointer;
        }
        #resultados-cliente .item-resultado:hover,
        #resultados-producto .item-resultado:hover {
            background: var(--fondo-gris);
        }

        /* ===== Menú hamburguesa ===== */
        #btn-hamburguesa {
            background: none;
            border: none;
            font-size: 1.6rem;
            color: var(--verde-oscuro);
            line-height: 1;
            padding: 0 0.5rem;
        }
        #btn-hamburguesa:hover {
            color: var(--verde-medio);
        }
        #sidebar {
            transition: margin-left 0.3s ease;
        }
        #sidebar.sidebar-oculto {
            margin-left: -280px;
        }
        #content {
            transition: margin-left 0.3s ease;
        }
        #content.content-expandido {
            margin-left: 0 !important;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include __DIR__ . '/backend/includes/sidebar.php'; ?>
        <div id="content" class="w-100" style="margin-left: 280px;">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm p-3 mb-4">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button id="btn-hamburguesa" type="button" title="Mostrar/ocultar menú">☰</button>
                        <span class="navbar-brand mb-0 h4 text-secondary ms-2">&gt;PUNTO DE VENTA</span>
                    </div>
                    <div>
                        <span class="me-4 fw-bold" style="color: var(--verde-oscuro);">
                            👤<?php echo strtoupper($usuario['nombre'] . ' | Rol:' . ucfirst($usuario['rol'])); ?>
                        </span>
                        <a href="backend/logout.php" class="btn btn-sm btn-outline-danger fw-bold">Cerrar sesión</a>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4 pb-5">
                <div class="row g-4">

                    <!-- ===================== ZONA DE OPERACIÓN (70%) ===================== -->
                    <div class="col-lg-8">
                        <div class="position-relative mb-3">
                            <div class="input-group">
                                <input type="text" id="buscador-producto" class="form-control"
                                       placeholder="🔎 Escanear código de barras o buscar producto..." autocomplete="off" autofocus>
                                <button class="btn btn-verde" type="button" id="btn-escanear-camara" title="Escanear código de barras con la cámara">
                                    📷 Escanear código
                                </button>
                            </div>
                            <div id="resultados-producto"></div>
                        </div>

                        <div class="card-shadow-sm">
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0" id="tabla-carrito">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th style="width:150px;">Cantidad</th>
                                            <th style="width:110px;">P. Unit.</th>
                                            <th style="width:110px;">Subtotal</th>
                                            <th style="width:50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cuerpo-carrito">
                                        <tr id="fila-vacia">
                                            <td colspan="5" class="text-center text-muted py-4">
                                                El carrito está vacío. Escanee o busque un producto para empezar.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ===================== PANEL DE FACTURACIÓN (30%) ===================== -->
                    <div class="col-lg-4">
                        <div class="panel-facturacion mb-3">
                            <label class="form-label fw-bold">Cliente</label>
                            <div class="position-relative mb-2">
                                <input type="text" id="buscador-cliente" class="form-control"
                                       placeholder="Buscar cliente por nombre o cédula..." autocomplete="off">
                                <div id="resultados-cliente"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Seleccionado:</span>
                                <span id="cliente-seleccionado-nombre" class="fw-bold">Consumidor Final</span>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary w-100 mt-2" id="btn-consumidor-final">
                                Usar "Consumidor Final"
                            </button>
                        </div>

                        <div class="panel-facturacion mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal</span>
                                <span id="txt-subtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>IVA (15%)</span>
                                <span id="txt-iva">$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">TOTAL</span>
                                <span class="total-final" id="txt-total">$0.00</span>
                            </div>
                        </div>

                        <div class="panel-facturacion mb-3">
                            <label class="form-label fw-bold">Pago del cliente</label>
                            <input type="number" step="0.01" min="0" id="input-monto-pagado" class="form-control mb-2" placeholder="0.00">
                            <div class="d-flex justify-content-between">
                                <span>Cambio / Vuelto</span>
                                <span id="txt-cambio" class="fw-bold text-success">$0.00</span>
                            </div>
                        </div>

                        <button class="btn btn-verde w-100" id="btn-procesar-venta">
                            ✅ PROCESAR VENTA
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL: ESCÁNER POR CÁMARA ===================== -->
    <div class="modal fade" id="modal-escaner" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">📷 Escanear código de barras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">Apunte la cámara al código de barras del producto.</p>
                    <div id="lector-camara" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/validaciones.js"></script>
    <script src="frontend/js/pos.js"></script>
    <script>
        // Menú hamburguesa: oculta/muestra el sidebar y expande el contenido
        document.getElementById('btn-hamburguesa').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('sidebar-oculto');
            document.getElementById('content').classList.toggle('content-expandido');
        });
    </script>
</body>
</html>
