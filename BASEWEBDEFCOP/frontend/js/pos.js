// ===================== ESTADO GLOBAL =====================
const IVA_PORCENTAJE = 0.15;
let carrito = []; // [{producto_id, codigo, nombre, precio, stock, cantidad}]
let clienteSeleccionado = { id: 1, nombre: 'Consumidor Final' };

// ===================== ELEMENTOS DOM =====================
const inputBuscadorProducto = document.getElementById('buscador-producto');
const resultadosProducto = document.getElementById('resultados-producto');
const cuerpoCarrito = document.getElementById('cuerpo-carrito');

const inputBuscadorCliente = document.getElementById('buscador-cliente');
const resultadosCliente = document.getElementById('resultados-cliente');
const clienteNombreSpan = document.getElementById('cliente-seleccionado-nombre');
const btnConsumidorFinal = document.getElementById('btn-consumidor-final');

const txtSubtotal = document.getElementById('txt-subtotal');
const txtIva = document.getElementById('txt-iva');
const txtTotal = document.getElementById('txt-total');
const inputMontoPagado = document.getElementById('input-monto-pagado');
const txtCambio = document.getElementById('txt-cambio');
const btnProcesarVenta = document.getElementById('btn-procesar-venta');

// ===================== BÚSQUEDA DE PRODUCTOS / LECTOR DE CÓDIGO DE BARRAS =====================
let debounceProducto;
inputBuscadorProducto.addEventListener('input', () => {
    clearTimeout(debounceProducto);
    const q = inputBuscadorProducto.value.trim();
    if (q.length === 0) {
        resultadosProducto.innerHTML = '';
        return;
    }
    debounceProducto = setTimeout(() => buscarProductos(q), 250);
});

// Las pistolas lectoras de código de barras "escriben" el código y luego mandan Enter.
inputBuscadorProducto.addEventListener('keydown', async (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const q = inputBuscadorProducto.value.trim();
        if (!q) return;
        await procesarCodigoEscaneado(q);
    }
});

// Reutilizable tanto por el Enter del input como por el escáner de cámara (QR/barras)
async function procesarCodigoEscaneado(codigo) {
    const productos = await buscarProductos(codigo, false);
    if (productos.length === 1) {
        // Coincidencia exacta (típico de escaneo de código de barras)
        agregarAlCarrito(productos[0]);
        inputBuscadorProducto.value = '';
        resultadosProducto.innerHTML = '';
    } else if (productos.length > 1) {
        // Varias coincidencias: se deja la lista visible para que el usuario elija
        mostrarResultadosProducto(productos);
    } else {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('No se encontró ningún producto con ese código o nombre.', 'error');
        } else {
            alert('No se encontró ningún producto con ese código o nombre.');
        }
    }
}

async function buscarProductos(q, mostrarLista = true) {
    try {
        const resp = await fetch(`backend/api_productos.php?q=${encodeURIComponent(q)}`);
        const productos = await resp.json();
        if (mostrarLista) mostrarResultadosProducto(productos);
        return productos;
    } catch (err) {
        console.error('Error buscando productos:', err);
        return [];
    }
}

function mostrarResultadosProducto(productos) {
    if (!productos.length) {
        resultadosProducto.innerHTML = '<div class="item-resultado text-muted">Sin resultados</div>';
        return;
    }
    resultadosProducto.innerHTML = productos.map(p => `
        <div class="item-resultado" data-id="${p.id}">
            <strong>${p.nombre}</strong> — $${parseFloat(p.precio).toFixed(2)}
            <span class="text-muted small"> (Stock: ${p.stock})</span>
        </div>
    `).join('');

    resultadosProducto.querySelectorAll('.item-resultado').forEach((el, i) => {
        el.addEventListener('click', () => {
            agregarAlCarrito(productos[i]);
            inputBuscadorProducto.value = '';
            resultadosProducto.innerHTML = '';
            inputBuscadorProducto.focus();
        });
    });
}

// ===================== CARRITO =====================
function agregarAlCarrito(producto) {
    if (producto.stock <= 0) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('Este producto no tiene stock disponible.', 'error');
        } else {
            alert('Este producto no tiene stock disponible.');
        }
        return;
    }
    const existente = carrito.find(item => item.producto_id == producto.id);
    if (existente) {
        if (existente.cantidad + 1 > producto.stock) {
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion('No hay más stock disponible de este producto.', 'error');
            } else {
                alert('No hay más stock disponible de este producto.');
            }
            return;
        }
        existente.cantidad++;
    } else {
        carrito.push({
            producto_id: producto.id,
            codigo: producto.codigo_barras,
            nombre: producto.nombre,
            precio: parseFloat(producto.precio),
            stock: producto.stock,
            cantidad: 1
        });
    }
    renderizarCarrito();
}

function cambiarCantidad(index, delta) {
    const item = carrito[index];
    const nuevaCantidad = item.cantidad + delta;
    if (nuevaCantidad <= 0) {
        carrito.splice(index, 1);
    } else if (nuevaCantidad > item.stock) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('No hay más stock disponible de este producto.', 'error');
        } else {
            alert('No hay más stock disponible de este producto.');
        }
        return;
    } else {
        item.cantidad = nuevaCantidad;
    }
    renderizarCarrito();
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    renderizarCarrito();
}

function renderizarCarrito() {
    if (carrito.length === 0) {
        cuerpoCarrito.innerHTML = `
            <tr id="fila-vacia">
                <td colspan="5" class="text-center text-muted py-4">
                    El carrito está vacío. Escanee o busque un producto para empezar.
                </td>
            </tr>`;
    } else {
        cuerpoCarrito.innerHTML = carrito.map((item, index) => `
            <tr>
                <td>${item.nombre}</td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-outline-secondary" onclick="cambiarCantidad(${index}, -1)">-</button>
                        <input type="text" class="form-control form-control-sm cantidad-box" value="${item.cantidad}" readonly>
                        <button class="btn btn-sm btn-outline-secondary" onclick="cambiarCantidad(${index}, 1)">+</button>
                    </div>
                </td>
                <td>$${item.precio.toFixed(2)}</td>
                <td>$${(item.precio * item.cantidad).toFixed(2)}</td>
                <td><button class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(${index})">🗑️</button></td>
            </tr>
        `).join('');
    }
    calcularTotales();
}

// ===================== CLIENTE =====================
let debounceCliente;
inputBuscadorCliente.addEventListener('input', () => {
    clearTimeout(debounceCliente);
    const q = inputBuscadorCliente.value.trim();
    if (q.length === 0) {
        resultadosCliente.innerHTML = '';
        return;
    }
    debounceCliente = setTimeout(async () => {
        try {
            const resp = await fetch(`backend/api_clientes.php?q=${encodeURIComponent(q)}`);
            const clientes = await resp.json();
            mostrarResultadosCliente(clientes);
        } catch (err) {
            console.error('Error buscando clientes:', err);
        }
    }, 250);
});

function mostrarResultadosCliente(clientes) {
    if (!clientes.length) {
        resultadosCliente.innerHTML = '<div class="item-resultado text-muted">Sin resultados</div>';
        return;
    }
    resultadosCliente.innerHTML = clientes.map(c => `
        <div class="item-resultado" data-id="${c.id}">
            <strong>${c.nombre}</strong> <span class="text-muted small">(${c.cedula})</span>
        </div>
    `).join('');

    resultadosCliente.querySelectorAll('.item-resultado').forEach((el, i) => {
        el.addEventListener('click', () => {
            clienteSeleccionado = { id: clientes[i].id, nombre: clientes[i].nombre };
            clienteNombreSpan.textContent = clientes[i].nombre;
            inputBuscadorCliente.value = '';
            resultadosCliente.innerHTML = '';
        });
    });
}

btnConsumidorFinal.addEventListener('click', () => {
    clienteSeleccionado = { id: 1, nombre: 'Consumidor Final' };
    clienteNombreSpan.textContent = 'Consumidor Final';
    inputBuscadorCliente.value = '';
    resultadosCliente.innerHTML = '';
});

// ===================== TOTALES Y PAGO =====================
function calcularTotales() {
    const subtotal = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
    const iva = subtotal * IVA_PORCENTAJE;
    const total = subtotal + iva;

    txtSubtotal.textContent = `$${subtotal.toFixed(2)}`;
    txtIva.textContent = `$${iva.toFixed(2)}`;
    txtTotal.textContent = `$${total.toFixed(2)}`;

    calcularCambio();
}

function calcularCambio() {
    const total = parseFloat(txtTotal.textContent.replace('$', '')) || 0;
    const pagado = parseFloat(inputMontoPagado.value) || 0;
    const cambio = pagado - total;
    txtCambio.textContent = `$${cambio >= 0 ? cambio.toFixed(2) : '0.00'}`;
    txtCambio.classList.toggle('text-danger', cambio < 0);
    txtCambio.classList.toggle('text-success', cambio >= 0);
}
inputMontoPagado.addEventListener('input', calcularCambio);

// ===================== PROCESAR VENTA =====================
btnProcesarVenta.addEventListener('click', async () => {
    if (carrito.length === 0) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('Agregue al menos un producto al carrito antes de procesar la venta.', 'error');
        } else {
            alert('Agregue al menos un producto al carrito.');
        }
        return;
    }
    const total = parseFloat(txtTotal.textContent.replace('$', ''));
    const montoPagado = parseFloat(inputMontoPagado.value) || 0;

    if (montoPagado < total) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('El monto pagado es menor al total de la venta.', 'error');
        } else {
            alert('El monto pagado es menor al total de la venta.');
        }
        return;
    }

    btnProcesarVenta.disabled = true;
    btnProcesarVenta.textContent = 'Procesando...';

    try {
        const resp = await fetch('backend/api_ventas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cliente_id: clienteSeleccionado.id,
                monto_pagado: montoPagado,
                carrito: carrito.map(item => ({
                    producto_id: item.producto_id,
                    cantidad: item.cantidad,
                    precio: item.precio
                }))
            })
        });
        const data = await resp.json();

        if (!resp.ok) {
            throw new Error(data.error || 'Error al procesar la venta');
        }

        ultimoCarritoVendido = JSON.parse(JSON.stringify(carrito));
        const nombreClienteVendido = clienteSeleccionado.nombre;
        generarReciboPDF(data, nombreClienteVendido);
        reiniciarVenta();
    } catch (err) {
        alert(err.message);
    } finally {
        btnProcesarVenta.disabled = false;
        btnProcesarVenta.textContent = '✅ PROCESAR VENTA';
    }
});

function reiniciarVenta() {
    carrito = [];
    clienteSeleccionado = { id: 1, nombre: 'Consumidor Final' };
    clienteNombreSpan.textContent = 'Consumidor Final';
    inputMontoPagado.value = '';
    renderizarCarrito();
    calcularCambio();
    inputBuscadorProducto.focus();
}

// ===================== RECIBO / FACTURA EN PDF =====================
let ultimoCarritoVendido = [];

function generarReciboPDF(venta, nombreCliente) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: [80, 150] }); // formato tipo ticket

    let y = 10;
    doc.setFontSize(12);
    doc.text('SISTEMA POS', 40, y, { align: 'center' });
    y += 5;
    doc.setFontSize(8);
    doc.text('Recibo de Venta', 40, y, { align: 'center' });
    y += 4;
    doc.text(`Venta N°: ${venta.venta_id}`, 5, y); y += 4;
    doc.text(`Fecha: ${new Date().toLocaleString()}`, 5, y); y += 4;
    doc.text(`Cliente: ${nombreCliente}`, 5, y); y += 4;
    doc.line(5, y, 75, y); y += 4;

    doc.setFontSize(7);

    // Detalle de productos vendidos (copia del carrito tomada justo antes de reiniciar la venta)
    ultimoCarritoVendido.forEach(item => {
        doc.text(`${item.cantidad}x ${item.nombre}`, 5, y);
        doc.text(`$${(item.precio * item.cantidad).toFixed(2)}`, 75, y, { align: 'right' });
        y += 4;
    });

    doc.line(5, y, 75, y); y += 4;
    doc.setFontSize(8);
    doc.text('Subtotal:', 5, y); doc.text(`$${venta.subtotal.toFixed(2)}`, 75, y, { align: 'right' }); y += 4;
    doc.text('IVA (15%):', 5, y); doc.text(`$${venta.iva.toFixed(2)}`, 75, y, { align: 'right' }); y += 4;
    doc.setFontSize(10);
    doc.text('TOTAL:', 5, y); doc.text(`$${venta.total.toFixed(2)}`, 75, y, { align: 'right' }); y += 5;
    doc.setFontSize(8);
    doc.text('Pagado:', 5, y); doc.text(`$${venta.monto_pagado.toFixed(2)}`, 75, y, { align: 'right' }); y += 4;
    doc.text('Cambio:', 5, y); doc.text(`$${venta.cambio.toFixed(2)}`, 75, y, { align: 'right' }); y += 6;

    doc.setFontSize(7);
    doc.text('¡Gracias por su compra!', 40, y, { align: 'center' });

    doc.save(`recibo_venta_${venta.venta_id}.pdf`);
}

// ===================== ESCÁNER POR CÁMARA (código de barras) =====================
let html5QrScanner = null;
let escaneando = false;
const btnEscanearCamara = document.getElementById('btn-escanear-camara');
const modalEscanerEl = document.getElementById('modal-escaner');
const modalEscaner = modalEscanerEl ? new bootstrap.Modal(modalEscanerEl) : null;
const contenedorLector = document.getElementById('lector-camara');

if (btnEscanearCamara) {
    btnEscanearCamara.addEventListener('click', async () => {
        if (escaneando) return; // evita doble clic mientras la cámara está iniciando
        escaneando = true;
        btnEscanearCamara.disabled = true;

        // Por si quedó algo de una sesión anterior, la limpiamos antes de iniciar una nueva
        await detenerEscanerCamara();
        contenedorLector.innerHTML = '';

        modalEscaner.show();

        html5QrScanner = new Html5Qrcode('lector-camara');
        try {
            await html5QrScanner.start(
                { facingMode: 'environment' }, // usa la cámara trasera en celulares
                {
                    fps: 10,
                    // Rectángulo ancho y bajo: se adapta mejor a la forma de un código de barras
                    qrbox: { width: 280, height: 120 },
                    // SOLO formatos de código de barras (1D), no QR
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                    ],
                },
                async (codigoDecodificado) => {
                    // Se detectó un código: procesarlo como si lo hubiera leído la pistola
                    await detenerEscanerCamara();
                    modalEscaner.hide();
                    await procesarCodigoEscaneado(codigoDecodificado);
                },
                () => { /* frame sin código detectado, se ignora */ }
            );
        } catch (err) {
            alert('No se pudo acceder a la cámara. Verifique los permisos del navegador.');
            console.error(err);
            modalEscaner.hide();
        } finally {
            escaneando = false;
            btnEscanearCamara.disabled = false;
        }
    });
}

async function detenerEscanerCamara() {
    if (html5QrScanner) {
        try {
            const estado = html5QrScanner.getState ? html5QrScanner.getState() : null;
            // Solo intentamos detener si realmente está escaneando (evita el error
            // "Cannot stop, scanner is not running or paused" que dejaba la cámara colgada)
            if (estado === Html5QrcodeScannerState.SCANNING || estado === Html5QrcodeScannerState.PAUSED) {
                await html5QrScanner.stop();
            }
            html5QrScanner.clear();
        } catch (err) {
            console.warn('Aviso al detener la cámara:', err);
        }
        html5QrScanner = null;
    }
}

// Si el usuario cierra el modal manualmente, apagamos la cámara
if (modalEscanerEl) {
    modalEscanerEl.addEventListener('hidden.bs.modal', detenerEscanerCamara);
}
