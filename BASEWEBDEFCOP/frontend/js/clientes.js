// ===================== ESTADO GLOBAL =====================
let clientes = []; // copia local de los clientes cargados, para editar sin volver a pedir al servidor

// ===================== ELEMENTOS DOM =====================
const inputBusqueda = document.getElementById('input-busqueda');
const cuerpoTabla = document.getElementById('cuerpo-tabla');
const modalClienteEl = document.getElementById('modal-cliente');
const modalCliente = new bootstrap.Modal(modalClienteEl);
const tituloModalCliente = document.getElementById('titulo-modal-cliente');

const inputId = document.getElementById('cliente-id');
const inputCedula = document.getElementById('cliente-cedula');
const inputNombre = document.getElementById('cliente-nombre');
const inputTelefono = document.getElementById('cliente-telefono');
const inputEmail = document.getElementById('cliente-email');

// ===================== CARGA INICIAL =====================
document.addEventListener('DOMContentLoaded', () => cargarClientes());

// ===================== BÚSQUEDA (con debounce, igual que en catalogo.js) =====================
let debounceBusqueda;
inputBusqueda.addEventListener('input', () => {
    clearTimeout(debounceBusqueda);
    debounceBusqueda = setTimeout(() => cargarClientes(inputBusqueda.value.trim()), 300);
});

// ===================== LISTAR CLIENTES =====================
async function cargarClientes(busqueda = '') {
    cuerpoTabla.innerHTML = `
        <tr><td colspan="5" class="text-center text-muted py-4">Cargando clientes...</td></tr>
    `;
    try {
        const resp = await fetch(`backend/api_clientes.php?q=${encodeURIComponent(busqueda)}`);
        clientes = await resp.json();
        renderizarTabla();
    } catch (err) {
        cuerpoTabla.innerHTML = `
            <tr><td colspan="5" class="text-center text-danger py-4">Error al cargar los clientes.</td></tr>
        `;
        console.error(err);
    }
}

function renderizarTabla() {
    if (!Array.isArray(clientes) || clientes.length === 0) {
        cuerpoTabla.innerHTML = `
            <tr><td colspan="5" class="text-center text-muted py-4">No hay clientes registrados.</td></tr>
        `;
        return;
    }

    cuerpoTabla.innerHTML = clientes.map(c => `
        <tr>
            <td>${c.cedula}</td>
            <td>${c.nombre}</td>
            <td>${c.telefono ?? '-'}</td>
            <td>${c.email ?? '-'}</td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" onclick="editarCliente(${c.id})">✏️ Editar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente(${c.id})">🗑️ Eliminar</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ===================== MODAL: NUEVO / EDITAR =====================
function abrirModal() {
    tituloModalCliente.textContent = '+ Nuevo Cliente';
    inputId.value = '';
    inputCedula.value = '';
    inputNombre.value = '';
    inputTelefono.value = '';
    inputEmail.value = '';
    inputCedula.disabled = false; // la cédula solo se puede definir al crear, no al modificar
    modalCliente.show();
}

function editarCliente(id) {
    const cliente = clientes.find(c => c.id == id);
    if (!cliente) return;

    tituloModalCliente.textContent = '✏️ Editar Cliente';
    inputId.value = cliente.id;
    inputCedula.value = cliente.cedula;
    inputNombre.value = cliente.nombre;
    inputTelefono.value = cliente.telefono ?? '';
    inputEmail.value = cliente.email ?? '';
    inputCedula.disabled = true; // no se cambia la cédula de un cliente ya existente
    modalCliente.show();
}

// ===================== GUARDAR (crear o actualizar) =====================
async function guardarCliente() {
    const id = inputId.value;
    const cedula = inputCedula.value.trim();
    const nombre = inputNombre.value.trim();

    if (!cedula || !nombre) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('Complete la cédula y el nombre del cliente.', 'error');
        } else {
            alert('Complete la cédula y el nombre del cliente.');
        }
        return;
    }

    const payload = {
        id: id || undefined,
        cedula,
        nombre,
        telefono: inputTelefono.value.trim() || null,
        email: inputEmail.value.trim() || null,
    };

    try {
        const resp = await fetch('backend/api_clientes.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.error || 'Error al guardar el cliente');

        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('Cliente guardado exitosamente.', 'exito');
        }

        modalCliente.hide();
        cargarClientes(inputBusqueda.value.trim());
    } catch (err) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(err.message, 'error');
        } else {
            alert(err.message);
        }
    }
}

// ===================== ELIMINAR (desactivar) =====================
async function eliminarCliente(id) {
    const confirmar = confirm('¿Seguro que deseas eliminar este cliente?');
    if (!confirmar) return;

    try {
        const resp = await fetch('backend/api_clientes.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.error || 'Error al eliminar el cliente');

        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('Cliente eliminado correctamente.', 'exito');
        }

        cargarClientes(inputBusqueda.value.trim());
    } catch (err) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(err.message, 'error');
        } else {
            alert(err.message);
        }
    }
}
