-- =========================================================
--  SISTEMA RADIO FM - BASE DE DATOS bdradio_fm
--  Asignatura: Desarrollo Web - Radio FM System
-- =========================================================

CREATE DATABASE IF NOT EXISTS bdradio_fm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bdradio_fm;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- Tabla: usuarios (Control de Acceso al Sistema)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'locutor', 'operador') NOT NULL DEFAULT 'locutor',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario Admin por defecto (admin / admin123)
INSERT INTO usuarios (usuario, password_hash, nombre, rol, estado) VALUES
('admin', '$2b$12$nFOIdUEYQwhzKktCEq5KveqnD6KA7ijuSyZ.piBoPskBWU.DTfWfG', 'Administrador FM', 'admin', 1),
('locutor1', '$2b$12$nFOIdUEYQwhzKktCEq5KveqnD6KA7ijuSyZ.piBoPskBWU.DTfWfG', 'Carlos "DJ Max" Proaño', 'locutor', 1);

-- Actualizar usuarios existentes si la BD ya fue creada previamente en MySQL
UPDATE usuarios SET nombre = 'Administrador FM' WHERE usuario = 'admin';

-- ---------------------------------------------------------
-- Tabla: discjockey (ENTIDAD 1 DEL DIAGRAMA PDF)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS discjockey (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    apodo_dj VARCHAR(100) NOT NULL,
    experiencia_anos INT NOT NULL DEFAULT 1,
    turno ENUM('Mañana', 'Tarde', 'Noche', 'Madrugada') NOT NULL DEFAULT 'Mañana',
    foto VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO discjockey (cedula, nombre, apodo_dj, experiencia_anos, turno, estado) VALUES
('1726549821', 'Alejandro Vega Morales', 'DJ Alex Wave', 5, 'Tarde', 1),
('1712345678', 'Carlos Eduardo Proaño', 'DJ Max Stereo', 8, 'Mañana', 1),
('1798765432', 'Andrea Sofia Morales', 'DJ Andy Mix', 4, 'Noche', 1),
('1755443322', 'Roberto Alejandro Silva', 'DJ Bob Retro', 10, 'Madrugada', 1);

UPDATE discjockey SET nombre = 'Alejandro Vega Morales', apodo_dj = 'DJ Alex Wave' WHERE cedula = '1726549821';

-- ---------------------------------------------------------
-- Tabla: grupo (ENTIDAD 2 DEL DIAGRAMA PDF)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS grupo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    genero_musical VARCHAR(100) NOT NULL,
    pais_origen VARCHAR(100) NOT NULL,
    anio_formacion INT NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO grupo (nombre, genero_musical, pais_origen, anio_formacion, estado) VALUES
('Soda Stereo', 'Rock Latino', 'Argentina', 1982, 1),
('Daft Punk', 'Electronic / Synthwave', 'Francia', 1993, 1),
('Queen', 'Classic Rock', 'Reino Unido', 1970, 1),
('Coldplay', 'Alternative Rock', 'Reino Unido', 1996, 1),
('Grupo Niche', 'Salsa', 'Colombia', 1978, 1);

-- ---------------------------------------------------------
-- Tabla: disco (ENTIDAD 3 DEL DIAGRAMA PDF)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS disco (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    anio_lanzamiento INT NOT NULL,
    discografica VARCHAR(100) NULL,
    formato ENUM('CD', 'Vinilo', 'Digital', 'Casete') NOT NULL DEFAULT 'Digital',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_disco_grupo FOREIGN KEY (grupo_id) REFERENCES grupo(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO disco (grupo_id, titulo, anio_lanzamiento, discografica, formato, estado) VALUES
(1, 'Canción Animal', 1990, 'CBS Records', 'Vinilo', 1),
(2, 'Random Access Memories', 2013, 'Columbia Records', 'Digital', 1),
(3, 'A Night at the Opera', 1975, 'EMI Records', 'Vinilo', 1),
(4, 'A Head Full of Dreams', 2015, 'Parlophone', 'CD', 1),
(5, 'Cielo de Tambores', 1990, 'Codiscos', 'CD', 1);

-- ---------------------------------------------------------
-- Tabla: cancion (ENTIDAD 4 DEL DIAGRAMA PDF)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS cancion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disco_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    duracion_segundos INT NOT NULL,
    numero_pista INT NOT NULL DEFAULT 1,
    genero VARCHAR(100) NOT NULL,
    audio_url VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cancion_disco FOREIGN KEY (disco_id) REFERENCES disco(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cancion (disco_id, titulo, duracion_segundos, numero_pista, genero, audio_url, estado) VALUES
(1, 'De Música Ligera', 212, 1, 'Rock Latino', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 1),
(1, 'Persiana Americana', 292, 2, 'Rock Latino', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 1),
(2, 'Get Lucky', 248, 1, 'Disco / Synth', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', 1),
(2, 'Instant Crush', 337, 2, 'Synthpop', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', 1),
(3, 'Bohemian Rhapsody', 354, 1, 'Rock', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3', 1),
(4, 'Adventure of a Lifetime', 263, 1, 'Pop Rock', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3', 1),
(5, 'Una Aventura', 320, 1, 'Salsa', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3', 1);

-- ---------------------------------------------------------
-- Tabla: reproduccion (ENTIDAD 5 DEL DIAGRAMA PDF)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS reproduccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discjockey_id INT NOT NULL,
    cancion_id INT NOT NULL,
    frecuencia_fm DECIMAL(5,2) NOT NULL DEFAULT 98.10,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    duracion_emision INT NOT NULL,
    nivel_audiencia INT NOT NULL DEFAULT 85,
    notas VARCHAR(255) NULL,
    CONSTRAINT fk_repro_dj FOREIGN KEY (discjockey_id) REFERENCES discjockey(id) ON DELETE CASCADE,
    CONSTRAINT fk_repro_cancion FOREIGN KEY (cancion_id) REFERENCES cancion(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO reproduccion (discjockey_id, cancion_id, frecuencia_fm, fecha_hora, duracion_emision, nivel_audiencia, notas) VALUES
(1, 1, 98.10, NOW() - INTERVAL 2 HOUR, 212, 92, 'Emisión al aire en especial de Rock Latino'),
(1, 3, 98.10, NOW() - INTERVAL 1 HOUR, 248, 98, 'Petición directa de los oyentes vía Whatsapp FM'),
(2, 5, 102.50, NOW() - INTERVAL 30 MINUTE, 354, 88, 'Programa matutino de clasicos'),
(3, 4, 104.90, NOW() - INTERVAL 10 MINUTE, 263, 79, 'Bloque Pop/Electronic de la tarde');

SET FOREIGN_KEY_CHECKS = 1;
