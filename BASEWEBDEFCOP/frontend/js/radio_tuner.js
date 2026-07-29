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
        this.fallbackAudioUrl = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3';
        
        // Objeto Audio nativo de HTML5 (Estándar y fácil de explicar)
        this.audioElement = new Audio(this.fallbackAudioUrl);
        this.audioElement.preload = "auto";
        this.audioElement.loop = true; // Transmisión continua sin interrupciones tipo Emisora FM
        
        // Manejar finalización de pista
        this.audioElement.addEventListener('ended', () => {
            if (this.isPlaying) {
                this.audioElement.currentTime = 0;
                this.audioElement.play().catch(err => console.warn("Error en re-play:", err));
            }
        });

        // Manejar errores de carga o reproducción de audio
        this.audioElement.addEventListener('error', (e) => {
            console.error("Error al cargar el audio:", e);
            
            // Si falló la URL actual de la base de datos, intentar URL de respaldo
            if (this.audioElement.src !== this.fallbackAudioUrl && !this.audioElement.src.endsWith('SoundHelix-Song-1.mp3')) {
                console.warn("Cambiando a audio de respaldo (fallback)...");
                this.audioElement.src = this.fallbackAudioUrl;
                if (this.isPlaying) {
                    this.audioElement.play().catch(() => {});
                }
            } else {
                this.isPlaying = false;
                this.updateUIState();
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion('No se pudo reproducir el archivo de audio. Verifique su conexión.', 'error');
                }
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
     * Normaliza enlaces de audio de servicios como tmpfiles.org, dropbox, google drive, etc.
     * para transformarlos automáticamente en enlaces de streaming directo de MP3.
     * @param {string} url 
     * @returns {string} URL directa de audio
     */
    normalizarUrlAudio(url) {
        if (!url || typeof url !== 'string' || url.trim() === '') {
            return this.fallbackAudioUrl;
        }
        let finalUrl = url.trim();
        
        // 1. Convertir enlaces de tmpfiles.org (insertar /dl/ para stream directo de audio)
        // Ejemplo: https://tmpfiles.org/12345/song.mp3 -> https://tmpfiles.org/dl/12345/song.mp3
        if (finalUrl.includes('tmpfiles.org/') && !finalUrl.includes('tmpfiles.org/dl/')) {
            finalUrl = finalUrl.replace('tmpfiles.org/', 'tmpfiles.org/dl/');
        }

        // 2. Convertir enlaces de Dropbox a descarga directa
        if (finalUrl.includes('dropbox.com/')) {
            finalUrl = finalUrl.replace('www.dropbox.com', 'dl.dropboxusercontent.com')
                               .replace('?dl=0', '?dl=1');
        }

        // 3. Convertir enlaces de Google Drive
        if (finalUrl.includes('drive.google.com/file/d/')) {
            const match = finalUrl.match(/\/file\/d\/([^\/]+)/);
            if (match && match[1]) {
                finalUrl = `https://docs.google.com/uc?export=download&id=${match[1]}`;
            }
        }

        return finalUrl;
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
        
        const audioUrlFinal = this.normalizarUrlAudio(url || this.audioElement.src);
        const urlAbsoluta = new URL(audioUrlFinal, window.location.href).href;
        
        if (this.audioElement.src !== urlAbsoluta && this.audioElement.src !== audioUrlFinal) {
            this.audioElement.src = audioUrlFinal;
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
                    mostrarNotificacion('El navegador bloqueó la reproducción automática o la URL de audio no es válida. Presione Play.', 'info');
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
                let targetAudioUrl = this.fallbackAudioUrl;

                if (data.estado === 'exito' && data.datos && data.datos.length > 0) {
                    const actual = data.datos[0];
                    if (infoTitle) infoTitle.innerText = actual.cancion_titulo;
                    if (infoArtist) infoArtist.innerText = actual.grupo_nombre + " (DJ: " + actual.apodo_dj + ")";
                    if (infoGenre) infoGenre.innerText = actual.genero + " • " + actual.disco_titulo;
                    
                    if (this.barSongTitle) {
                        this.barSongTitle.innerText = actual.grupo_nombre + " - " + actual.cancion_titulo;
                    }
                    if (actual.audio_url && actual.audio_url.trim() !== '') {
                        targetAudioUrl = this.normalizarUrlAudio(actual.audio_url);
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

                // Evitar reiniciar el audio si la URL no ha cambiado
                const urlAbsoluta = new URL(targetAudioUrl, window.location.href).href;
                if (this.audioElement.src !== urlAbsoluta && this.audioElement.src !== targetAudioUrl) {
                    this.audioElement.src = targetAudioUrl;
                    if (this.isPlaying) {
                        this.audioElement.play().catch(err => console.warn("Error reanudando audio tras cambio de frecuencia:", err));
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
