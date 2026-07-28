/**
 * =========================================================
 * CONTROLADOR DEL SINTONIZADOR DE RADIO FM Y REPRODUCTOR
 * Explicación para Sustentación / Defensa de Proyecto:
 * - Este script maneja los eventos de usuario para cambiar la frecuencia FM.
 * - Utiliza la API nativa Audio() de JavaScript para reproducir el stream.
 * - Realiza peticiones AJAX (fetch) al backend en PHP para consultar la canción al aire.
 * =========================================================
 */

class RadioFMTuner {
    constructor() {
        // Estado del reproductor
        this.frequency = 98.10;
        this.isPlaying = false;
        
        // Objeto Audio nativo de HTML5 (Estándar y fácil de explicar)
        this.audioElement = new Audio('https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3');
        this.audioElement.preload = "auto";
        
        // Manejar errores de carga o reproducción de audio
        this.audioElement.addEventListener('error', (e) => {
            console.error("Error al cargar el audio:", e);
            this.isPlaying = false;
            this.updateUIState();
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion('No se pudo reproducir el archivo de audio. Verifique la URL de la canción (.mp3).', 'error');
            }
        });

        // Inicializar listeners de la interfaz
        this.initUI();
    }

    /**
     * Vincula los elementos HTML (botones, sliders) con sus eventos JS
     */
    initUI() {
        // Elementos del DOM
        this.freqDisplay = document.getElementById('freq-num');
        this.slider = document.getElementById('freq-slider');
        this.playBtn = document.getElementById('btn-play-radio');
        this.barPlayBtn = document.getElementById('bar-btn-play');
        this.barPlayIcon = document.getElementById('bar-play-icon');
        this.barVinyl = document.getElementById('player-vinyl');
        this.barSongTitle = document.getElementById('bar-song-title');
        this.barStationFreq = document.getElementById('bar-station-freq');
        this.volumeSlider = document.getElementById('bar-volume');
        this.equalizer = document.getElementById('visualizer-equalizer');

        // Evento del slider manual de frecuencia FM
        if (this.slider) {
            this.slider.addEventListener('input', (e) => {
                this.setFrequency(parseFloat(e.target.value));
            });
        }

        // Eventos de los botones de reproducción
        if (this.playBtn) {
            this.playBtn.addEventListener('click', () => this.togglePlay());
        }

        if (this.barPlayBtn) {
            this.barPlayBtn.addEventListener('click', () => this.togglePlay());
        }

        // Evento de control de volumen
        if (this.volumeSlider) {
            this.volumeSlider.addEventListener('input', (e) => {
                const volumeValue = parseFloat(e.target.value) / 100;
                this.audioElement.volume = volumeValue;
            });
        }

        // Botones de cambio gradual de frecuencia (Anterior / Siguiente)
        const prevBtn = document.getElementById('bar-btn-prev');
        const nextBtn = document.getElementById('bar-btn-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                const nuevaFreq = Math.max(87.5, this.frequency - 0.4);
                if (this.slider) this.slider.value = nuevaFreq;
                this.setFrequency(nuevaFreq);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const nuevaFreq = Math.min(108.0, this.frequency + 0.4);
                if (this.slider) this.slider.value = nuevaFreq;
                this.setFrequency(nuevaFreq);
            });
        }

        // Botones de memorias o presets P1 a P6
        document.querySelectorAll('.preset-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const freqPreset = parseFloat(btn.dataset.freq);
                if (freqPreset) {
                    if (this.slider) this.slider.value = freqPreset;
                    this.setFrequency(freqPreset);
                }
            });
        });
    }

    /**
     * Establece la frecuencia FM actual y consulta la canción programada
     * @param {number} freq - Frecuencia en MHz (ej: 98.1)
     */
    setFrequency(freq) {
        this.frequency = parseFloat(freq.toFixed(2));
        
        // Actualizar textos en la interfaz
        if (this.freqDisplay) {
            this.freqDisplay.innerText = this.frequency.toFixed(1);
        }
        if (this.barStationFreq) {
            this.barStationFreq.innerHTML = `<span class="pulse-red me-1"></span> ${this.frequency.toFixed(1)} MHz STEREO FM`;
        }
        
        // Consultar al backend PHP los datos de la canción en esta frecuencia
        this.actualizarInformacionEmisora(this.frequency);
    }

    /**
     * Sincroniza la interfaz visual (botones, vinilo, ecualizador) con el estado del reproductor
     */
    updateUIState() {
        if (this.isPlaying) {
            if (this.playBtn) {
                this.playBtn.innerHTML = '<i class="bi bi-pause-fill"></i> DETENER EMISIÓN';
                this.playBtn.classList.replace('btn-success', 'btn-danger');
            }
            if (this.barPlayIcon) {
                this.barPlayIcon.classList.replace('bi-play-fill', 'bi-pause-fill');
            }
            if (this.barVinyl) {
                this.barVinyl.classList.add('spinning');
            }
            if (this.equalizer) {
                this.equalizer.classList.add('playing');
            }
        } else {
            if (this.playBtn) {
                this.playBtn.innerHTML = '<i class="bi bi-play-fill"></i> ENCENDER RADIO FM';
                this.playBtn.classList.replace('btn-danger', 'btn-success');
            }
            if (this.barPlayIcon) {
                this.barPlayIcon.classList.replace('bi-pause-fill', 'bi-play-fill');
            }
            if (this.barVinyl) {
                this.barVinyl.classList.remove('spinning');
            }
            if (this.equalizer) {
                this.equalizer.classList.remove('playing');
            }
        }
    }

    /**
     * Reproduce un archivo de audio de forma directa
     * @param {string} url - URL del archivo MP3
     * @param {string} title - Título para la barra flotante (opcional)
     */
    playAudio(url = null, title = null) {
        if (title && this.barSongTitle) {
            this.barSongTitle.innerText = title;
        }
        if (url && url.trim() !== '') {
            this.audioElement.src = url;
        }

        const playPromise = this.audioElement.play();
        if (playPromise !== undefined) {
            playPromise.then(() => {
                this.isPlaying = true;
                this.updateUIState();
            }).catch(err => {
                console.warn('Reproducción bloqueada o errónea:', err);
                this.isPlaying = false;
                this.updateUIState();
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion('El navegador bloqueó la reproducción automática. Presione Play para escuchar.', 'info');
                }
            });
        }
    }

    /**
     * Pausa la reproducción de audio
     */
    pauseAudio() {
        this.audioElement.pause();
        this.isPlaying = false;
        this.updateUIState();
    }

    /**
     * Alterna entre reproducir y pausar
     */
    togglePlay() {
        if (this.isPlaying) {
            this.pauseAudio();
        } else {
            this.playAudio();
        }
    }

    /**
     * Consulta al servidor PHP la canción transmitida actualmente en la frecuencia seleccionada
     * @param {number} freq 
     */
    actualizarInformacionEmisora(freq) {
        const infoTitle = document.getElementById('current-song-title');
        const infoArtist = document.getElementById('current-artist-name');
        const infoGenre = document.getElementById('current-station-genre');

        fetch(`backend/api_reproducciones.php?accion=listar&frecuencia=${freq}`)
            .then(res => res.json())
            .then(data => {
                if (data.estado === 'exito' && data.datos && data.datos.length > 0) {
                    const actual = data.datos[0];
                    if (infoTitle) infoTitle.innerText = actual.cancion_titulo;
                    if (infoArtist) infoArtist.innerText = actual.grupo_nombre + " (DJ: " + actual.apodo_dj + ")";
                    if (infoGenre) infoGenre.innerText = actual.genero + " • " + actual.disco_titulo;
                    
                    if (this.barSongTitle) {
                        this.barSongTitle.innerText = actual.grupo_nombre + " - " + actual.cancion_titulo;
                    }
                    if (actual.audio_url) {
                        this.audioElement.src = actual.audio_url;
                        if (this.isPlaying) this.audioElement.play().catch(() => {});
                    }
                } else {
                    // Datos por defecto si no hay canción programada en esa frecuencia exacta
                    if (infoTitle) infoTitle.innerText = "Transmisión FM Stereo " + freq.toFixed(1) + " MHz";
                    if (infoArtist) infoArtist.innerText = "Emisora Matriz en Vivo";
                    if (infoGenre) infoGenre.innerText = "Señal de Música Continuada";

                    if (this.barSongTitle) {
                        this.barSongTitle.innerText = "FM Radio - " + freq.toFixed(1) + " MHz Stereo";
                    }
                }
            })
            .catch(error => {
                console.error("Error consultando información de la emisora:", error);
            });
    }
}

// Inicialización automática cuando el documento HTML está listo
document.addEventListener('DOMContentLoaded', () => {
    window.radioFM = new RadioFMTuner();
});
