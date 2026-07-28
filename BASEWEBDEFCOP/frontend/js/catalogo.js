// ===================== ESTADO GLOBAL =====================
let productos = []; // copia local de los productos cargados, para editar sin volver a pedir al servidor

// ===================== ELEMENTOS DOM =====================
const inputBusqueda = document.getElementById('input-busqueda');
const cuerpoTabla = document.getElementById('cuerpo-tabla');
const modalProductoEl = document.getElementById('modal-producto');
const modalProducto = new bootstrap.Modal(modalProductoEl);
const tituloModalProducto = document.getElementById('titulo-modal-producto');

const inputId = document.getElementById('producto-id');
const inputCodigo = document.getElementById('producto-codigo');
const inputNombre = document.getElementById('producto-nombre');
const inputDescripcion = document.getElementById('producto-descripcion');
const inputPrecio = document.getElementById('producto-precio');
const inputStock = document.getElementById('producto-stock');

// ===================== CARGA INICIAL =====================
document.addEventListener('DOMContentLoaded', () => cargarProductos());

// ===================== BÚSQUEDA (con debounce, igual que en pos.js) =====================
let debounceBusqueda;
inputBusqueda.addEventListener('input', () => {
    clearTimeout(debounceBusqueda);
    debounceBusqueda = setTimeout(() => cargarProductos(inputBusqueda.value.trim()), 300);
});

// ===================== LISTAR PRODUCTOS =====================
async function cargarProductos(busqueda = '') {
    cuerpoTabla.innerHTML = `
        <tr><td colspan="5" class="text-center text-muted py-4">Cargando productos...</td></tr>
    `;
    try {
        const resp = await fetch(`backend/api_productos.php?q=${encodeURIComponent(busqueda)}`);
        productos = await resp.json();
        renderizarTabla();
    } catch (err) {
        cuerpoTabla.innerHTML = `
            <tr><td colspan="5" class="text-center text-danger py-4">Error al cargar los productos.</td></tr>
        `;
        console.error(err);
    }
}

function renderizarTabla() {
    if (productos.length === 0) {
        cuerpoTabla.innerHTML = `
            <tr><td colspan="5" class="text-center text-muted py-4">No hay productos registrados.</td></tr>
        `;
        return;
    }

    cuerpoTabla.innerHTML = productos.map(p => `
        <tr>
            <td>${p.codigo_barras}</td>
            <td>${p.nombre}</td>
            <td>$${parseFloat(p.precio).toFixed(2)}</td>
            <td>${p.stock}</td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" onclick="editarProducto(${p.id})">✏️ Editar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(${p.id})">🗑️ Eliminar</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ===================== MODAL: NUEVO / EDITAR =====================
function abrirModal() {
    tituloModalProducto.textContent = '+ Nuevo Producto';
    inputId.value = '';
    inputCodigo.value = '';
    inputNombre.value = '';
    inputDescripcion.value = '';
    inputPrecio.value = '';
    inputStock.value = '';
    inputCodigo.disabled = false; // el código solo se puede editar al crear, no al modificar
    modalProducto.show();
}

function editarProducto(id) {
    const producto = productos.find(p => p.id == id);
    if (!producto) return;

    tituloModalProducto.textContent = '✏️ Editar Producto';
    inputId.value = producto.id;
    inputCodigo.value = producto.codigo_barras;
    inputNombre.value = producto.nombre;
    inputDescripcion.value = producto.descripcion ?? '';
    inputPrecio.value = producto.precio;
    inputStock.value = producto.stock;
    inputCodigo.disabled = true; // no se cambia el código de barras de un producto ya existente
    modalProducto.show();
}

// ===================== GUARDAR (crear o actualizar) =====================
async function guardarProducto() {
    const id = inputId.value;
    const nombre = inputNombre.value.trim();
    const codigo = inputCodigo.value.trim();
    const precio = parseFloat(inputPrecio.value);
    const stock = parseInt(inputStock.value, 10);

    if (!nombre || !codigo || isNaN(precio) || isNaN(stock)) {
        alert('Complete el código, nombre, precio y stock del producto.');
        return;
    }

    const payload = {
        id: id || undefined,
        codigo,
        nombre,
        descripcion: inputDescripcion.value.trim() || null,
        precio,
        stock,
    };

    try {
        const resp = await fetch('backend/api_productos.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.error || 'Error al guardar el producto');

        modalProducto.hide();
        cargarProductos(inputBusqueda.value.trim());
    } catch (err) {
        alert(err.message);
    }
}

// ===================== ELIMINAR (desactivar) =====================
async function eliminarProducto(id) {
    const confirmar = confirm('¿Seguro que deseas eliminar este producto del catálogo?');
    if (!confirmar) return;

    try {
        const resp = await fetch('backend/api_productos.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.error || 'Error al eliminar el producto');

        cargarProductos(inputBusqueda.value.trim());
    } catch (err) {
        alert(err.message);
    }
}
