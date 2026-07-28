/**
 * Módulo de Validaciones, Seguridad OWASP (XSS) y Utilidades — Radio FM System
 * Versión 3.0 — Notificaciones premium, validación en tiempo real, exportación CSV
 */

// ── OWASP: Escapado de entidades HTML (prevención XSS) ──
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}

// ── Validación automática en formularios .needs-validation ──
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form.needs-validation');

    forms.forEach(form => {
        // Prevenir envío si inválido
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                mostrarNotificacion('Por favor completa correctamente los campos requeridos.', 'error');
            }
            form.classList.add('was-validated');
        }, false);

        // Validación visual en tiempo real (blur + input)
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.addEventListener('blur', () => validarCampo(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    validarCampo(field);
                }
            });
        });
    });
});

function validarCampo(field) {
    if (!field.required && field.value.trim() === '') {
        field.classList.remove('is-valid', 'is-invalid');
        return;
    }
    if (field.checkValidity()) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
}

// ── Sistema de notificaciones Toast premium ──
let toastContainer = null;

function getToastContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-stack';
        toastContainer.style.cssText = `
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
            max-width: 380px;
        `;
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

function mostrarNotificacion(mensaje, tipo = 'exito') {
    const container = getToastContainer();

    // Configuración por tipo
    const config = {
        exito: {
            bg:     'linear-gradient(135deg, #064e3b, #065f46)',
            border: '1px solid rgba(16, 185, 129, 0.5)',
            icon:   'bi-check-circle-fill',
            color:  '#34d399',
            title:  '¡Operación exitosa!',
        },
        error: {
            bg:     'linear-gradient(135deg, #4c0519, #881337)',
            border: '1px solid rgba(244, 63, 94, 0.5)',
            icon:   'bi-exclamation-circle-fill',
            color:  '#fda4af',
            title:  'Atención',
        },
        info: {
            bg:     'linear-gradient(135deg, #0c1a40, #1e3a5f)',
            border: '1px solid rgba(59, 130, 246, 0.5)',
            icon:   'bi-info-circle-fill',
            color:  '#93c5fd',
            title:  'Información',
        },
    };

    const c = config[tipo] || config.info;

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${c.bg};
        border: ${c.border};
        border-radius: 12px;
        padding: 0.9rem 1.1rem;
        box-shadow: 0 8px 30px rgba(0,0,0,0.4), 0 2px 8px rgba(0,0,0,0.3);
        display: flex;
        align-items: flex-start;
        gap: 10px;
        pointer-events: all;
        opacity: 0;
        transform: translateX(24px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        font-family: 'Outfit', sans-serif;
        width: 100%;
    `;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');

    // Ícono
    const iconEl = document.createElement('i');
    iconEl.className = `bi ${c.icon}`;
    iconEl.style.cssText = `color: ${c.color}; font-size: 1.1rem; flex-shrink: 0; margin-top: 1px;`;

    // Contenido
    const content = document.createElement('div');
    content.style.flex = '1';

    const titleEl = document.createElement('strong');
    titleEl.style.cssText = `display: block; color: ${c.color}; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;`;
    titleEl.textContent = c.title;

    const msgEl = document.createElement('span');
    msgEl.style.cssText = 'display: block; color: #e2e8f0; font-size: 0.87rem; font-weight: 500; line-height: 1.4;';
    msgEl.textContent = mensaje;

    content.appendChild(titleEl);
    content.appendChild(msgEl);

    // Botón cerrar
    const closeBtn = document.createElement('button');
    closeBtn.style.cssText = `
        background: none; border: none; color: #64748b;
        cursor: pointer; padding: 0; line-height: 1;
        font-size: 0.9rem; flex-shrink: 0; transition: color 0.2s;
    `;
    closeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
    closeBtn.setAttribute('aria-label', 'Cerrar notificación');
    closeBtn.addEventListener('mouseenter', () => closeBtn.style.color = '#e2e8f0');
    closeBtn.addEventListener('mouseleave', () => closeBtn.style.color = '#64748b');

    toast.appendChild(iconEl);
    toast.appendChild(content);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    const dismissToast = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(24px)';
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 300);
    };

    closeBtn.addEventListener('click', dismissToast);
    toast.addEventListener('click', dismissToast);

    // Animar entrada
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });
    });

    // Auto-cerrar
    const timer = setTimeout(dismissToast, 5000);
    toast.addEventListener('mouseenter', () => clearTimeout(timer));

    // Modo claro: ajustar colores
    if (document.body.classList.contains('light-mode')) {
        const lightConfig = {
            exito: { bg: 'linear-gradient(135deg, #f0fdf4, #dcfce7)', border: '1px solid #86efac', textC: '#065f46' },
            error: { bg: 'linear-gradient(135deg, #fff1f2, #ffe4e6)', border: '1px solid #fca5a5', textC: '#9f1239' },
            info:  { bg: 'linear-gradient(135deg, #eff6ff, #dbeafe)', border: '1px solid #93c5fd', textC: '#1e40af' },
        };
        const lc = lightConfig[tipo] || lightConfig.info;
        toast.style.background = lc.bg;
        toast.style.border     = lc.border;
        titleEl.style.color    = lc.textC;
        msgEl.style.color      = lc.textC;
        iconEl.style.color     = lc.textC;
    }
}

// ── Exportar tabla a CSV ──
function exportarTablaCSV(tbodyId, filename = 'reporte_radio.csv') {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) {
        mostrarNotificacion('No se encontró la tabla para exportar.', 'error');
        return;
    }

    let csv = [];
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        if (cols.length === 0) return;
        const rowData = Array.from(cols).map(col => {
            const text = col.innerText
                .replace(/(\r\n|\n|\r)/gm, ' ')
                .replace(/,/g, ';')
                .trim();
            return `"${text}"`;
        });
        if (rowData.length > 0) csv.push(rowData.join(','));
    });

    if (csv.length === 0) {
        mostrarNotificacion('No hay datos para exportar.', 'error');
        return;
    }

    const blob = new Blob(['\ufeff' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.download = filename;
    link.href     = URL.createObjectURL(blob);
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(link.href);

    mostrarNotificacion('Reporte CSV descargado correctamente.', 'exito');
}
