-- =========================================================
-- SISTEMA DE VENTAS Y DISQUERA RADIO FM - ESTRUCTURA BD
-- Base de datos: bdventas / bdradio_fm
-- Para importar desde phpMyAdmin
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- Tabla: usuarios (Administradores y Vendedores)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) DEFAULT 'Administrador FM',
    rol ENUM('admin', 'vendedor', 'locutor', 'operador') NOT NULL DEFAULT 'admin',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: productos (Catálogo general de la tienda POS)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_barras VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: clientes (Consumidores e historial)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: discjockey (Locutores / DJs)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS discjockey (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NULL,
    nombre VARCHAR(150) NOT NULL,
    apodo_dj VARCHAR(100) NOT NULL,
    experiencia_anos INT DEFAULT 3,
    estilo_musical VARCHAR(100) DEFAULT 'Variado',
    turno ENUM('Mañana', 'Tarde', 'Noche', 'Madrugada') DEFAULT 'Tarde',
    foto VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: grupo (Bandas / Artistas musicales)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS grupo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    genero_musical VARCHAR(100) NOT NULL,
    pais_origen VARCHAR(100) DEFAULT 'Ecuador',
    anio_formacion INT DEFAULT 2000,
    imagen_url VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: disco (Álbumes / Producciones)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS disco (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    anio_lanzamiento INT DEFAULT 2023,
    discografica VARCHAR(100) NULL,
    formato ENUM('CD', 'Vinilo', 'Digital', 'Casete') NOT NULL DEFAULT 'Digital',
    precio DECIMAL(10,2) NOT NULL DEFAULT 10.00,
    stock INT NOT NULL DEFAULT 10,
    imagen_url VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_disco_grupo FOREIGN KEY (grupo_id) REFERENCES grupo(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: cancion (Pistas musicales)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS cancion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disco_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    genero VARCHAR(100) DEFAULT 'Rock',
    duracion_segundos INT NOT NULL DEFAULT 180,
    audio_url VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cancion_disco FOREIGN KEY (disco_id) REFERENCES disco(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: ventas (Cabecera de facturas POS)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    usuario_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    iva DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    cambio DECIMAL(10,2) NOT NULL,
    estado ENUM('pagada', 'anulada') NOT NULL DEFAULT 'pagada',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_venta_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_venta_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: detalle_venta (Items de factura)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS detalle_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Tabla: reproduccion (Parrilla FM Stereo)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS reproduccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    frecuencia_fm DECIMAL(5,2) NOT NULL DEFAULT 98.10,
    discjockey_id INT NOT NULL,
    cancion_id INT NOT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    duracion_emision INT DEFAULT 180,
    nivel_audiencia INT DEFAULT 85,
    notas VARCHAR(255) NULL,
    estado ENUM('programada', 'al_aire', 'finalizada') DEFAULT 'al_aire',
    CONSTRAINT fk_rep_dj FOREIGN KEY (discjockey_id) REFERENCES discjockey(id) ON DELETE CASCADE,
    CONSTRAINT fk_rep_cancion FOREIGN KEY (cancion_id) REFERENCES cancion(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
