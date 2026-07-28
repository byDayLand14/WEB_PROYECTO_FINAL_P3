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
    <title>Sintonizador FM Stereo en Vivo - Radio Wave FM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="frontend/css/estilos_fm.css">
</head>
<body>
    <?php include __DIR__ . '/backend/includes/header.php'; ?>

    <main class="container-fluid px-md-5">
        
        <!-- ── Sintonizador FM ── -->
        <section class="tuner-wheel-container mb-5 text-center position-relative bg-glassmorphism rounded-4 p-4 shadow-lg">
            <div class="row align-items-center mb-4">
                <div class="col-lg-4 text-lg-start mb-3 mb-lg-0">
                    <span class="badge badge-cyan px-3 py-2 font-monospace" style="font-size: 0.8rem;">
                        <i class="bi bi-reception-4 text-green me-2"></i> FM STEREO HIGH-FIDELITY
                    </span>
                    <h5 class="fw-bold mt-2 mb-0" style="color: var(--text-primary);">STEREO WAVE — ESTACIÓN MATRIZ</h5>
                </div>

                <div class="col-lg-4 mb-3 mb-lg-0">
                    <!-- Display LCD Digital -->
                    <div class="lcd-freq-box shadow-lg">
                        <span id="freq-num">98.1</span> <small style="font-size: 1.8rem;" class="text-amber">MHz</small>
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <button id="btn-play-radio"
                        class="btn fw-bold px-5 py-3 rounded-pill radio-card-effect d-inline-flex align-items-center gap-2"
                        style="background: linear-gradient(135deg, var(--accent-green), #34d399); border: none; color: #fff; box-shadow: 0 6px 20px rgba(16,185,129,0.4); font-size: 0.95rem;">
                        <i class="bi bi-play-fill fs-4"></i>
                        <span>Encender Radio FM</span>
                    </button>
                </div>
            </div>

            <!-- Slider Dial de Frecuencia FM -->
            <div class="my-4 px-md-4">
                <label for="freq-slider" class="form-label text-cyan fw-bold d-flex justify-content-between">
                    <span class="font-monospace">87.5 MHz</span>
                    <span class="text-uppercase"><i class="bi bi-sliders me-1"></i> DIAL DE FRECUENCIA STEREO (87.5 - 108.0 MHz)</span>
                    <span class="font-monospace">108.0 MHz</span>
                </label>
                <input type="range" class="form-range" id="freq-slider" min="87.5" max="108.0" step="0.1" value="98.1" required>
            </div>

            <!-- Botones Presets Memory Buttons P1 a P6 -->
            <div class="d-flex justify-content-center flex-wrap gap-2 my-3">
                <button class="preset-btn" data-freq="88.5">P1 (88.5 FM)</button>
                <button class="preset-btn" data-freq="92.3">P2 (92.3 FM)</button>
                <button class="preset-btn" data-freq="98.1">P3 (98.1 FM)</button>
                <button class="preset-btn" data-freq="102.5">P4 (102.5 FM)</button>
                <button class="preset-btn" data-freq="104.9">P5 (104.9 FM)</button>
                <button class="preset-btn" data-freq="107.3">P6 (107.3 FM)</button>
            </div>

            <!-- Ecualizador de Espectro Audio Visual (CSS + JS limpio) -->
            <div class="mt-4">
                <div class="equalizer-container d-flex justify-content-center align-items-end gap-2" id="visualizer-equalizer">
                    <div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div>
                    <div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div>
                    <div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div>
                    <div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div><div class="eq-bar"></div>
                </div>
            </div>
        </section>

        <!-- Secciones Informativas y Parrilla de Emisoras -->
        <div class="row g-4 mb-5">
            <section class="col-lg-7">
                <article class="glass-card p-4 h-100 radio-card-effect">
                    <h4 class="text-cyan fw-bold mb-3"><i class="bi bi-broadcast text-magenta me-2"></i> CANCIÓN AL AIRE EN ESTA FRECUENCIA</h4>
                    
                    <div class="p-4 bg-dark bg-opacity-80 rounded-3 border border-secondary d-flex align-items-center mb-4">
                        <div class="vinyl-record spinning me-4" style="width: 75px; height: 75px;">
                            <div class="vinyl-center"></div>
                        </div>
                        <div>
                            <h3 class="text-white fw-bold mb-1" id="current-song-title">Transmitiendo De Música Ligera</h3>
                            <h5 class="text-warning fw-bold mb-1" id="current-artist-name">Soda Stereo (DJ: Alex Wave)</h5>
                            <span class="badge bg-danger text-uppercase" id="current-station-genre">Rock Latino • Frecuencia Matriz 98.1 MHz</span>
                        </div>
                    </div>

                    <h5 class="text-white fw-bold mb-3"><i class="bi bi-chat-quote-fill text-amber me-2"></i> Peticiones en Vivo de Oyentes</h5>
                    
                    <!-- Formulario de Peticiones Funcional con Validación -->
                    <form class="needs-validation mb-4 p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary" novalidate id="form-peticion-oyente" onsubmit="enviarPeticionOyente(event)">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" id="input-oyente-nombre" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Tu Nombre u Oyente" required minlength="2" maxlength="50">
                                <div class="invalid-feedback">Ingresa tu nombre u apodo.</div>
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="input-oyente-cancion" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Canción / Artista deseado" required minlength="2" maxlength="80">
                                <div class="invalid-feedback">Ingresa la canción o mensaje.</div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-cyan btn-sm w-100 fw-bold"><i class="bi bi-send-fill"></i> Enviar</button>
                            </div>
                        </div>
                    </form>

                    <!-- Lista Dinámica de Peticiones en Vivo -->
                    <div class="list-group list-group-flush bg-transparent" id="lista-peticiones-oyentes">
                        <div class="list-group-item bg-transparent text-light border-secondary px-0">
                            <strong class="text-cyan">María G.:</strong> "¡Excelente sintonía y sonido en esta estación FM!"
                        </div>
                        <div class="list-group-item bg-transparent text-light border-secondary px-0">
                            <strong class="text-cyan">Carlos P.:</strong> "Por favor pongan Bohemian Rhapsody de Queen en 98.1 FM!"
                        </div>
                    </div>
                </article>
            </section>

            <aside class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <h4 class="section-title mb-4">
                        <i class="bi bi-grid-3x3-gap-fill text-amber"></i>
                        Frecuencias destacadas
                    </h4>
                    <div class="d-grid gap-3">
                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center radio-card-effect"
                             style="background: var(--bg-base); border: 1px solid var(--border-subtle); cursor: pointer;"
                             onclick="sintonizarEstacion(98.1)"
                             title="Sintonizar 98.1 MHz FM">
                            <div>
                                <strong class="d-block" style="color: var(--text-primary);">98.1 MHz FM</strong>
                                <small style="color: var(--text-muted);">Estación Matriz Rock &amp; Pop</small>
                            </div>
                            <span class="badge badge-green"><i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>ON-AIR</span>
                        </div>

                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center radio-card-effect"
                             style="background: var(--bg-base); border: 1px solid var(--border-subtle); cursor: pointer;"
                             onclick="sintonizarEstacion(88.5)"
                             title="Sintonizar 88.5 MHz FM">
                            <div>
                                <strong class="d-block" style="color: var(--text-primary);">88.5 MHz FM</strong>
                                <small style="color: var(--text-muted);">Stereo Clásicos de Siempre</small>
                            </div>
                            <span class="badge" style="background: var(--bg-elevated); border: 1px solid var(--border-normal); color: var(--text-muted); font-size: 0.72rem;">DISPONIBLE</span>
                        </div>

                        <div class="p-3 rounded-3 d-flex justify-content-between align-items-center radio-card-effect"
                             style="background: var(--bg-base); border: 1px solid var(--border-subtle); cursor: pointer;"
                             onclick="sintonizarEstacion(102.5)"
                             title="Sintonizar 102.5 MHz FM">
                            <div>
                                <strong class="d-block" style="color: var(--text-primary);">102.5 MHz FM</strong>
                                <small style="color: var(--text-muted);">Ritmos Latinos &amp; Salsa Stereo</small>
                            </div>
                            <span class="badge" style="background: var(--bg-elevated); border: 1px solid var(--border-normal); color: var(--text-muted); font-size: 0.72rem;">DISPONIBLE</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

    </main>

    <!-- Barra Flotante de Audio Inferior -->
    <?php include __DIR__ . '/backend/includes/player_bar.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/validaciones.js"></script>
    <script src="frontend/js/radio_tuner.js"></script>
    <script>
        // Cargar peticiones guardadas al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            cargarPeticionesGuardadas();
        });

        function enviarPeticionOyente(e) {
            e.preventDefault();
            const form = document.getElementById('form-peticion-oyente');
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            const inputNombre = document.getElementById('input-oyente-nombre');
            const inputCancion = document.getElementById('input-oyente-cancion');
            const nombre = inputNombre.value.trim();
            const cancion = inputCancion.value.trim();

            if (!nombre || !cancion) return;

            const lista = document.getElementById('lista-peticiones-oyentes');
            const nuevoDiv = document.createElement('div');
            nuevoDiv.className = 'list-group-item bg-transparent text-light border-secondary px-0 animate-fade-in-up';
            nuevoDiv.innerHTML = `<strong class="text-cyan">${escapeHTML(nombre)}:</strong> "${escapeHTML(cancion)}" <span class="badge bg-danger ms-2" style="font-size:0.65rem;">AHORA</span>`;
            
            lista.prepend(nuevoDiv);

            // Guardar en localStorage para persistencia
            const guardadas = JSON.parse(localStorage.getItem('peticiones_oyentes') || '[]');
            guardadas.unshift({ nombre, cancion, fecha: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) });
            localStorage.setItem('peticiones_oyentes', JSON.stringify(guardadas.slice(0, 10)));

            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion('¡Tu petición fue enviada en vivo a la cabina del DJ!', 'exito');
            }

            form.reset();
            form.classList.remove('was-validated');
        }

        function cargarPeticionesGuardadas() {
            const guardadas = JSON.parse(localStorage.getItem('peticiones_oyentes') || '[]');
            if (guardadas.length === 0) return;

            const lista = document.getElementById('lista-peticiones-oyentes');
            guardadas.forEach(p => {
                const item = document.createElement('div');
                item.className = 'list-group-item bg-transparent text-light border-secondary px-0';
                item.innerHTML = `<strong class="text-cyan">${escapeHTML(p.nombre)}:</strong> "${escapeHTML(p.cancion)}" <small class="text-muted ms-2">(${p.fecha || 'Reciente'})</small>`;
                lista.appendChild(item);
            });
        }

        function sintonizarEstacion(freq) {
            const slider = document.getElementById('freq-slider');
            if (slider) slider.value = freq;

            if (window.radioFM && typeof window.radioFM.setFrequency === 'function') {
                window.radioFM.setFrequency(freq);
            }
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion(`Sintonizando frecuencia ${freq.toFixed(1)} MHz FM...`, 'info');
            }
        }
    </script>
</body>
</html>
