-- =========================================================
--  MIGRACIÓN: agrega la columna "estado" a la tabla ventas
--  Ejecuta esto SOLO SI ya habías importado el init.sql antes
--  (si tu tabla "ventas" ya tiene la columna "estado", no ejecutes esto)
-- =========================================================
ALTER TABLE ventas
    ADD COLUMN estado ENUM('pagada', 'anulada') NOT NULL DEFAULT 'pagada' AFTER cambio;
