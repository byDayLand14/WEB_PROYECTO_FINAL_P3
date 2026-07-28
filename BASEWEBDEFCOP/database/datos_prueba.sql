-- =========================================================
-- DATOS DE PRUEBA INICIALES - bdventas / bdradio_fm
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Usuario Inicial (admin / admin123)
INSERT INTO usuarios (usuario, password_hash, nombre, rol, estado) VALUES
('admin', '$2b$12$nFOIdUEYQwhzKktCEq5KveqnD6KA7ijuSyZ.piBoPskBWU.DTfWfG', 'Administrador FM', 'admin', 1)
ON DUPLICATE KEY UPDATE usuario=usuario;

-- Productos Iniciales POS
INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock) VALUES
('7501234567890', 'Coca Cola 500ml', 'Bebida gaseosa', 0.80, 100),
('7501234567891', 'Pan de molde', 'Pan tajado grande', 1.50, 50),
('7501234567892', 'Leche entera 1L', 'Leche pasteurizada', 1.20, 80),
('7501234567893', 'Arroz 1lb', 'Arroz extra', 0.65, 120),
('7501234567894', 'Cuaderno universitario', '100 hojas', 2.30, 40)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Clientes Iniciales
INSERT INTO clientes (id, cedula, nombre, telefono, email) VALUES
(1, '9999999999', 'Consumidor Final', '0990000000', 'consumidor@final.com')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

INSERT INTO clientes (cedula, nombre, telefono, email) VALUES
('0102030405', 'Juan Pérez', '0991234567', 'juan@email.com'),
('0102030406', 'María Gómez', '0987654321', 'maria@email.com')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- DJs Iniciales
INSERT INTO discjockey (cedula, nombre, apodo_dj, experiencia_anos, turno, estado) VALUES
('1726549821', 'Alejandro Vega Morales', 'DJ Alex Wave', 5, 'Tarde', 1),
('1712345678', 'Carlos Eduardo Proaño', 'DJ Max Stereo', 8, 'Mañana', 1),
('1798765432', 'Andrea Sofia Morales', 'DJ Andy Mix', 4, 'Noche', 1)
ON DUPLICATE KEY UPDATE apodo_dj=VALUES(apodo_dj);

-- Grupos Iniciales
INSERT INTO grupo (nombre, genero_musical, pais_origen, anio_formacion, estado) VALUES
('Soda Stereo', 'Rock Latino', 'Argentina', 1982, 1),
('Daft Punk', 'Electronic', 'Francia', 1993, 1),
('Queen', 'Classic Rock', 'Reino Unido', 1970, 1)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Discos Iniciales
INSERT INTO disco (grupo_id, titulo, anio_lanzamiento, discografica, formato, precio, stock, estado) VALUES
(1, 'Canción Animal', 1990, 'CBS Records', 'Vinilo', 15.00, 20, 1),
(2, 'Random Access Memories', 2013, 'Columbia Records', 'Digital', 18.50, 15, 1),
(3, 'A Night at the Opera', 1975, 'EMI Records', 'Vinilo', 22.00, 10, 1)
ON DUPLICATE KEY UPDATE titulo=VALUES(titulo);

-- Canciones Iniciales
INSERT INTO cancion (disco_id, titulo, genero, duracion_segundos, audio_url, estado) VALUES
(1, 'De Música Ligera', 'Rock Latino', 212, 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 1),
(1, 'Un Misil En Mi Placard', 'Rock Latino', 187, 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 1),
(2, 'Get Lucky', 'Electronic', 248, 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', 1),
(3, 'Bohemian Rhapsody', 'Classic Rock', 354, 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', 1)
ON DUPLICATE KEY UPDATE titulo=VALUES(titulo);

-- Reproducciones Iniciales
INSERT INTO reproduccion (frecuencia_fm, discjockey_id, cancion_id, fecha_hora, duracion_emision, nivel_audiencia, notas, estado) VALUES
(98.10, 1, 1, NOW(), 212, 92, 'Emisión Estelar FM Matriz', 'al_aire')
ON DUPLICATE KEY UPDATE frecuencia_fm=VALUES(frecuencia_fm);

SET FOREIGN_KEY_CHECKS = 1;
