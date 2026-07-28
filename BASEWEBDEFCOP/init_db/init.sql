-- =========================================================
--  SISTEMA POS - bdventas
--  Importar este archivo desde phpMyAdmin (pestaña "Importar")
--  con la base de datos "bdventas" ya creada y seleccionada.
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- Tabla: usuarios
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'vendedor') NOT NULL DEFAULT 'vendedor',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario de prueba -> usuario: admin / contraseña: admin123
INSERT INTO usuarios (usuario, password_hash, rol, estado) VALUES
('admin', '$2b$12$nFOIdUEYQwhzKktCEq5KveqnD6KA7ijuSyZ.piBoPskBWU.DTfWfG', 'admin', 1);

-- ---------------------------------------------------------
-- Tabla: productos
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

INSERT INTO productos (codigo_barras, nombre, descripcion, precio, stock) VALUES
('7501234567890', 'Coca Cola 500ml', 'Bebida gaseosa', 0.80, 100),
('7501234567891', 'Pan de molde', 'Pan tajado grande', 1.50, 50),
('7501234567892', 'Leche entera 1L', 'Leche pasteurizada', 1.20, 80),
('7501234567893', 'Arroz 1lb', 'Arroz extra', 0.65, 120),
('7501234567894', 'Cuaderno universitario', '100 hojas', 2.30, 40);

-- ---------------------------------------------------------
-- Tabla: clientes
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

-- Cliente genérico usado como "Consumidor Final"
INSERT INTO clientes (id, cedula, nombre, telefono, email) VALUES
(1, '9999999999', 'Consumidor Final', NULL, NULL);

INSERT INTO clientes (cedula, nombre, telefono, email) VALUES
('0102030405', 'Juan Pérez', '0991234567', '[email protected]'),
('0102030406', 'María Gómez', '0987654321', '[email protected]');

-- ---------------------------------------------------------
-- Tabla: ventas (cabecera de cada venta)
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
-- Tabla: detalle_venta (líneas / productos de cada venta)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS detalle_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_venta FOREIGN KEY (venta_id) REFERENCES ventas(id),
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
