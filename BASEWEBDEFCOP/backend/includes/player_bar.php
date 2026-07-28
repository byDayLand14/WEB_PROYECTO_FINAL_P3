<?php if (!isset($_SESSION['usuario_activo'])) return; ?>
<div class="radio-player-bar" role="complementary" aria-label="Reproductor de audio">
    <div class="container-fluid d-flex align-items-center justify-content-between p-0">

        <!-- ── Info de la canción ── -->
        <div class="d-flex align-items-center gap-3" style="min-width: 0; flex: 1; max-width: 280px;">
            <div class="vinyl-record spinning flex-shrink-0" id="player-vinyl" style="width: 52px; height: 52px;" aria-hidden="true">
                <div class="vinyl-center"></div>
            </div>
            <div class="text-truncate">
                <span class="d-block fw-bold text-truncate" id="bar-song-title" style="font-size: 0.88rem; color: var(--text-primary); max-width: 200px;">
                    Soda Stereo — De Música Ligera
                </span>
                <small class="d-flex align-items-center gap-1 font-monospace" id="bar-station-freq" style="color: var(--accent-cyan); font-size: 0.75rem;">
                    <span class="pulse-red" style="width: 7px; height: 7px;"></span>
                    98.1 MHz · FM STEREO
                </small>
            </div>
        </div>

        <!-- ── Controles centrales ── -->
        <div class="d-flex align-items-center gap-3 mx-auto">
            <button class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                id="bar-btn-prev"
                title="Frecuencia anterior"
                aria-label="Frecuencia anterior"
                style="width: 36px; height: 36px; background: var(--bg-elevated); border: 1px solid var(--border-normal); color: var(--text-secondary);">
                <i class="bi bi-skip-start-fill" style="font-size: 1.1rem;"></i>
            </button>

            <button class="btn rounded-circle d-flex align-items-center justify-content-center"
                id="bar-btn-play"
                title="Reproducir / Pausar"
                aria-label="Reproducir o pausar"
                style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); border: none; box-shadow: 0 4px 16px rgba(59,130,246,0.4);">
                <i class="bi bi-play-fill fs-4" id="bar-play-icon"></i>
            </button>

            <button class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                id="bar-btn-next"
                title="Siguiente frecuencia"
                aria-label="Siguiente frecuencia"
                style="width: 36px; height: 36px; background: var(--bg-elevated); border: 1px solid var(--border-normal); color: var(--text-secondary);">
                <i class="bi bi-skip-end-fill" style="font-size: 1.1rem;"></i>
            </button>
        </div>

        <!-- ── Volumen y estado ── -->
        <div class="d-none d-md-flex align-items-center gap-3" style="min-width: 0; flex: 1; max-width: 240px; justify-content: flex-end;">
            <i class="bi bi-volume-up-fill flex-shrink-0" style="color: var(--accent-cyan); font-size: 1rem;" aria-hidden="true"></i>
            <input type="range" class="form-range flex-grow-1" id="bar-volume" min="0" max="100" value="80"
                aria-label="Control de volumen" title="Volumen" style="max-width: 110px;">
            <span class="badge flex-shrink-0 font-monospace"
                id="bar-equalizer-badge"
                style="background: var(--bg-elevated); border: 1px solid var(--border-normal); color: var(--accent-amber); font-size: 0.7rem; letter-spacing: 0.06em;">
                HQ
            </span>
        </div>

    </div>
</div>
