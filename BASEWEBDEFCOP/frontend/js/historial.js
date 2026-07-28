// ===================== ELEMENTOS DOM =====================
const cardTotalVendido = document.getElementById('card-total-vendido');
const cardCantidadFacturas = document.getElementById('card-cantidad-facturas');
const cardTicketPromedio = document.getElementById('card-ticket-promedio');
const cuerpoFacturas = document.getElementById('cuerpo-facturas');

const filtroFechaInicio = document.getElementById('filtro-fecha-inicio');
const filtroFechaFin = document.getElementById('filtro-fecha-fin');
const filtroCliente = document.getElementById('filtro-cliente');
const filtroFactura = document.getElementById('filtro-factura');
const btnFiltrar = document.getElementById('btn-filtrar');
const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');

const modalDetalleEl = document.getElementById('modal-detalle');
const modalDetalle = new bootstrap.Modal(modalDetalleEl);
const btnReimprimirDesdeModal = document.getElementById('btn-reimprimir-desde-modal');

let facturaEnModal = null; // guarda la última factura consultada en el modal (para re-imprimir)

// ===================== CARGA INICIAL =====================
document.addEventListener('DOMContentLoaded', cargarHistorial);

// ===================== FILTROS =====================
btnFiltrar.addEventListener('click', cargarHistorial);
btnLimpiarFiltros.addEventListener('click', () => {
    filtroFechaInicio.value = '';
    filtroFechaFin.value = '';
    filtroCliente.value = '';
    filtroFactura.value = '';
    cargarHistorial();
});

// ===================== CARGAR TABLA + TARJETAS =====================
async function cargarHistorial() {
    cuerpoFacturas.innerHTML = `
        <tr><td colspan="7" class="text-center text-muted py-4">Cargando facturas...</td></tr>
    `;

    const params = new URLSearchParams();
    if (filtroFechaInicio.value) params.append('fecha_inicio', filtroFechaInicio.value);
    if (filtroFechaFin.value) params.append('fecha_fin', filtroFechaFin.value);
    if (filtroCliente.value.trim()) params.append('cliente', filtroCliente.value.trim());
    if (filtroFactura.value.trim()) params.append('factura', filtroFactura.value.trim());

    try {
        const resp = await fetch(`backend/api_historial.php?${params.toString()}`);
        const data = await resp.json();

        if (!resp.ok) throw new Error(data.error || 'Error al cargar el historial');

        renderizarTarjetas(data.totales);
        renderizarTabla(data.facturas);
    } catch (err) {
        cuerpoFacturas.innerHTML = `
            <tr><td colspan="7" class="text-center text-danger py-4">${err.message}</td></tr>
        `;
    }
}

function renderizarTarjetas(totales) {
    cardTotalVendido.textContent = `$${totales.total_vendido.toFixed(2)}`;
    cardCantidadFacturas.textContent = totales.cantidad_facturas;
    cardTicketPromedio.textContent = `$${totales.ticket_promedio.toFixed(2)}`;
}

function renderizarTabla(facturas) {
    if (facturas.length === 0) {
        cuerpoFacturas.innerHTML = `
            <tr><td colspan="7" class="text-center text-muted py-4">No se encontraron facturas con esos filtros.</td></tr>
        `;
        return;
    }

    cuerpoFacturas.innerHTML = facturas.map(f => {
        const esAnulada = f.estado === 'anulada';
        const badge = esAnulada
            ? '<span class="badge badge-anulada">Anulada</span>'
            : '<span class="badge badge-pagada">Pagada</span>';

        return `
            <tr class="${esAnulada ? 'fila-anulada' : ''}">
                <td>#${f.id}</td>
                <td>${formatearFecha(f.fecha)}</td>
                <td>${f.cliente_nombre}</td>
                <td>${f.vendedor}</td>
                <td>$${parseFloat(f.total).toFixed(2)}</td>
                <td>${badge}</td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary" title="Ver detalles" onclick="verDetalle(${f.id})">👁️</button>
                        <button class="btn btn-sm btn-outline-secondary" title="Re-imprimir" onclick="reimprimir(${f.id})">🖨️</button>
                        <button class="btn btn-sm btn-outline-danger" title="Anular factura"
                                onclick="anularFactura(${f.id})" ${esAnulada ? 'disabled' : ''}>🚫</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function formatearFecha(fechaSql) {
    const fecha = new Date(fechaSql.replace(' ', 'T'));
    return fecha.toLocaleString('es-EC', { dateStyle: 'short', timeStyle: 'short' });
}

// ===================== VER DETALLES (MODAL) =====================
async function verDetalle(ventaId) {
    try {
        const resp = await fetch(`backend/api_venta_detalle.php?id=${ventaId}`);
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.error || 'Error al obtener el detalle');

        facturaEnModal = data;

        document.getElementById('detalle-numero-factura').textContent = data.cabecera.id;
        document.getElementById('detalle-cliente').textContent = data.cabecera.cliente_nombre;
        document.getElementById('detalle-vendedor').textContent = data.cabecera.vendedor;
        document.getElementById('detalle-fecha').textContent = formatearFecha(data.cabecera.fecha);
        document.getElementById('detalle-estado').innerHTML = data.cabecera.estado === 'anulada'
            ? '<span class="badge badge-anulada">Anulada</span>'
            : '<span class="badge badge-pagada">Pagada</span>';

        document.getElementById('detalle-productos-cuerpo').innerHTML = data.detalle.map(item => `
            <tr>
                <td>${item.producto_nombre}</td>
                <td>${item.cantidad}</td>
                <td>$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
            </tr>
        `).join('');

        document.getElementById('detalle-subtotal').textContent = `$${parseFloat(data.cabecera.subtotal).toFixed(2)}`;
        document.getElementById('detalle-iva').textContent = `$${parseFloat(data.cabecera.iva).toFixed(2)}`;
        document.getElementById('detalle-total').textContent = `$${parseFloat(data.cabecera.total).toFixed(2)}`;

        modalDetalle.show();
    } catch (err) {
        alert(err.message);
    }
}

btnReimprimirDesdeModal.addEventListener('click', () => {
    if (facturaEnModal) generarReciboPDF(facturaEnModal);
});

// ===================== RE-IMPRIMIR (PDF) =====================
async function reimprimir(ventaId) {
    try {
        const resp = await fetch(`backend/api_venta_detalle.php?id=${ventaId}`);
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.error || 'Error al obtener la factura');
        generarReciboPDF(data);
    } catch (err) {
        alert(err.message);
    }
}

function generarReciboPDF(data) {
    const { cabecera, detalle } = data;
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: [80, 150] });

    let y = 10;
    doc.setFontSize(12);
    doc.text('SISTEMA POS', 40, y, { align: 'center' });
    y += 5;
    doc.setFontSize(8);
    doc.text(cabecera.estado === 'anulada' ? 'COPIA - FACTURA ANULADA' : 'Recibo de Venta (Reimpresión)', 40, y, { align: 'center' });
    y += 4;
    doc.text(`Venta N°: ${cabecera.id}`, 5, y); y += 4;
    doc.text(`Fecha: ${formatearFecha(cabecera.fecha)}`, 5, y); y += 4;
    doc.text(`Cliente: ${cabecera.cliente_nombre}`, 5, y); y += 4;
    doc.line(5, y, 75, y); y += 4;

    doc.setFontSize(7);
    detalle.forEach(item => {
        doc.text(`${item.cantidad}x ${item.producto_nombre}`, 5, y);
        doc.text(`$${parseFloat(item.subtotal).toFixed(2)}`, 75, y, { align: 'right' });
        y += 4;
    });

    doc.line(5, y, 75, y); y += 4;
    doc.setFontSize(8);
    doc.text('Subtotal:', 5, y); doc.text(`$${parseFloat(cabecera.subtotal).toFixed(2)}`, 75, y, { align: 'right' }); y += 4;
    doc.text('IVA (15%):', 5, y); doc.text(`$${parseFloat(cabecera.iva).toFixed(2)}`, 75, y, { align: 'right' }); y += 4;
    doc.setFontSize(10);
    doc.text('TOTAL:', 5, y); doc.text(`$${parseFloat(cabecera.total).toFixed(2)}`, 75, y, { align: 'right' }); y += 6;

    doc.setFontSize(7);
    doc.text('¡Gracias por su compra!', 40, y, { align: 'center' });

    doc.save(`recibo_venta_${cabecera.id}.pdf`);
}

// ===================== ANULAR FACTURA =====================
async function anularFactura(ventaId) {
    const confirmar = confirm(
        `¿Seguro que deseas anular la factura #${ventaId}?\n\nEsta acción devolverá automáticamente el stock de los productos al inventario y no se puede deshacer.`
    );
    if (!confirmar) return;

    try {
        const resp = await fetch('backend/api_anular_venta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ venta_id: ventaId }),
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.error || 'Error al anular la factura');

        alert('Factura anulada correctamente. El stock fue devuelto al inventario.');
        cargarHistorial();
    } catch (err) {
        alert(err.message);
    }
}
