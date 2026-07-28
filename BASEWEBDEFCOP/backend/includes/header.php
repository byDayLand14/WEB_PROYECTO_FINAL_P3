<?php
if (!isset($_SESSION['usuario_activo'])) {
    return;
}
$paginaActual = basename($_SERVER['PHP_SELF']);
// Nombre del operador activo
$operadorNombre = $_SESSION['usuario_activo']['nombre'] ?? 'Administrador FM';
$_SESSION['usuario_activo']['nombre'] = 'Administrador FM';

// Mapa de páginas para breadcrumb
$paginaTitulos = [
    'sintonizador.php'  => 'Sintonizador',
    'dashboard.php'     => 'Dashboard',
    'discjockeys.php'   => 'Locutores / DJs',
    'grupos.php'        => 'Grupos Musicales',
    'discos.php'        => 'Discos / Álbumes',
    'canciones.php'     => 'Canciones',
    'reproducciones.php'=> 'Cabina FM',
];
$tituloPagina = $paginaTitulos[$paginaActual] ?? 'Panel';
?>
<header class="site-header">
<nav class="navbar navbar-expand-lg navbar-radio mb-4" aria-label="Navegación Principal Radio FM">
    <div class="container-fluid px-md-4">

        <!-- ── Logo / Marca ── -->
        <a class="navbar-brand radio-brand d-flex align-items-center gap-2 me-4 text-decoration-none" href="sintonizador.php" aria-label="Ir al sintonizador">
            <img src="frontend/img/logo_radio.png" alt="Logo Radio Wave" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover; border: 1.5px solid var(--accent-cyan);">
            <div style="line-height: 1.15;">
                <span class="d-block fw-black" style="font-size: 1.05rem; letter-spacing: -0.02em; color: var(--text-primary);">STEREO WAVE</span>
                <span class="badge-fm">98.1 FM</span>
            </div>
        </a>

        <!-- ── Toggle móvil ── -->
        <button class="navbar-toggler border-0 p-2 rounded-2" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarMain"
            aria-controls="navbarMain" aria-expanded="false" aria-label="Abrir menú"
            style="background: var(--bg-elevated); color: var(--text-primary);">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- ── Navegación colapsable ── -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 py-2 py-lg-0">

                <li class="nav-item">
                    <a class="nav-link nav-link-radio <?php echo $paginaActual === 'sintonizador.php' ? 'active' : ''; ?>"
                       href="sintonizador.php" title="Sintonizador FM en vivo">
                        <i class="bi bi-radioactive"></i>
                        <span>Sintonizador</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-radio <?php echo $paginaActual === 'dashboard.php' ? 'active' : ''; ?>"
                       href="dashboard.php" title="Panel de control general">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-radio <?php echo $paginaActual === 'discjockeys.php' ? 'active' : ''; ?>"
                       href="discjockeys.php" title="Gestión de locutores y DJs">
                        <i class="bi bi-person-badge"></i>
                        <span>Locutores</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-radio <?php echo $paginaActual === 'grupos.php' ? 'active' : ''; ?>"
                       href="grupos.php" title="Grupos y bandas musicales">
                        <i class="bi bi-people-fill"></i>
                        <span>Grupos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-radio <?php echo $paginaActual === 'discos.php' ? 'active' : ''; ?>"
                       href="discos.php" title="Discos y álbumes">
                        <i class="bi bi-disc-fill"></i>
                        <span>Discos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-radio <?php echo $paginaActual === 'canciones.php' ? 'active' : ''; ?>"
                       href="canciones.php" title="Catálogo de canciones">
                        <i class="bi bi-music-note-beamed"></i>
                        <span>Canciones</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-radio <?php echo $paginaActual === 'reproducciones.php' ? 'active' : ''; ?>"
                       href="reproducciones.php" title="Cabina de transmisión FM">
                        <i class="bi bi-mic-fill" style="<?php echo $paginaActual === 'reproducciones.php' ? '' : 'color: var(--accent-coral);'; ?>"></i>
                        <span>Cabina FM</span>
                        <span class="pulse-red ms-1" style="width: 6px; height: 6px;"></span>
                    </a>
                </li>

            </ul>

            <!-- ── Controles derechos ── -->
            <div class="d-flex align-items-center gap-2 pt-2 pt-lg-0" style="border-top: 1px solid; border-color: transparent;">

                <!-- Botón modo claro/oscuro -->
                <button class="btn btn-sm rounded-pill px-3 d-flex align-items-center gap-2"
                    id="theme-toggle-btn"
                    onclick="toggleThemeMode()"
                    title="Cambiar tema"
                    aria-label="Cambiar entre modo claro y oscuro"
                    style="background: var(--bg-elevated); border: 1px solid var(--border-normal); color: var(--text-secondary); font-size: 0.82rem; font-weight: 600;">
                    <i class="bi bi-sun-fill text-amber" id="theme-icon"></i>
                    <span id="theme-text" class="d-none d-md-inline">Claro</span>
                </button>

                <!-- Info del operador -->
                <div class="d-none d-xl-flex flex-column text-end px-1" style="line-height: 1.2;">
                    <small style="color: var(--text-muted); font-size: 0.65rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.07em;">Operador</small>
                    <span style="color: var(--accent-amber); font-weight: 700; font-size: 0.82rem;">
                        <?php echo htmlspecialchars($operadorNombre); ?>
                    </span>
                </div>

                <!-- Botón cerrar sesión -->
                <a href="backend/logout.php"
                   class="btn btn-sm rounded-pill px-3 d-flex align-items-center gap-1"
                   style="background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.4); color: var(--accent-coral); font-weight: 700; font-size: 0.82rem;"
                   title="Cerrar sesión"
                   aria-label="Cerrar sesión"
                   onclick="return confirm('¿Deseas cerrar la sesión actual?')">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-md-inline">Salir</span>
                </a>

            </div>
        </div>
    </div>
</nav>
</header>

<script>
    // ── Gestión de tema claro / oscuro ──
    function applyThemeMode(theme) {
        const body  = document.body;
        const icon  = document.getElementById('theme-icon');
        const text  = document.getElementById('theme-text');

        if (theme === 'light') {
            body.classList.add('light-mode');
            if (icon) icon.className = 'bi bi-moon-stars-fill';
            if (icon) icon.style.color = '';
            if (text) text.textContent = 'Oscuro';
        } else {
            body.classList.remove('light-mode');
            if (icon) icon.className = 'bi bi-sun-fill text-amber';
            if (icon) icon.style.color = '';
            if (text) text.textContent = 'Claro';
        }
    }

    function toggleThemeMode() {
        const current  = localStorage.getItem('radio_theme') || 'dark';
        const newTheme = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('radio_theme', newTheme);
        applyThemeMode(newTheme);
    }

    // Aplicar al cargar
    document.addEventListener('DOMContentLoaded', () => {
        const saved = localStorage.getItem('radio_theme') || 'dark';
        applyThemeMode(saved);
    });
</script>
