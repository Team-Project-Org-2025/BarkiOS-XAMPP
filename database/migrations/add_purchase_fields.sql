-- ============================================================
-- MIGRACIÓN: Agregar campos adicionales a tabla compras
-- Fecha: 2025-01-22
-- Descripción: Agregar campos para información completa de factura
-- ============================================================

USE barkios;

-- Verificar y agregar columna fecha_compra
ALTER TABLE compras
ADD COLUMN IF NOT EXISTS fecha_compra DATE NOT NULL DEFAULT (CURRENT_DATE)
AFTER factura_numero;

-- Verificar y agregar columna referencia
ALTER TABLE compras
ADD COLUMN IF NOT EXISTS referencia VARCHAR(100) NULL
AFTER tracking;

-- Verificar y agregar columna telefono
ALTER TABLE compras
ADD COLUMN IF NOT EXISTS telefono VARCHAR(20) NULL
AFTER referencia;

-- Verificar y agregar columna metodo_pago
ALTER TABLE compras
ADD COLUMN IF NOT EXISTS metodo_pago VARCHAR(50) NULL
AFTER telefono;

-- Verificar y agregar columna direccion
ALTER TABLE compras
ADD COLUMN IF NOT EXISTS direccion TEXT NULL
AFTER metodo_pago;

-- Verificar estructura actualizada
DESCRIBE compras;

-- Actualizar vista si existe
DROP VIEW IF EXISTS vista_compras;

CREATE VIEW vista_compras AS
SELECT 
    c.compra_id,
    c.proveedor_id,
    c.factura_numero,
    c.fecha_compra,
    c.tracking,
    c.referencia,
    c.telefono,
    c.metodo_pago,
    c.direccion,
    c.monto_total,
    c.pdf_generado,
    c.created_at,
    c.activo,
    p.nombre_empresa AS proveedor,
    p.nombre_contacto,
    (SELECT COUNT(*) FROM prendas_compradas pc WHERE pc.compra_id = c.compra_id) AS total_prendas
FROM compras c
LEFT JOIN proveedores p ON c.proveedor_id = p.id
WHERE c.activo = 1
ORDER BY c.fecha_compra DESC, c.created_at DESC;

SELECT 'Migración completada exitosamente' AS resultado;
