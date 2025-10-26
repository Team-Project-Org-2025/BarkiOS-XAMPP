-- Script SQL para actualizar la estructura de compras existente
-- Adaptado para funcionar con la estructura actual de proveedores

USE barkios_db;

-- Verificar si la tabla compras ya existe con la estructura antigua
-- Si existe, vamos a modificarla para que tenga la estructura que necesitamos

-- Agregar campos faltantes a la tabla compras si no existen
ALTER TABLE compras
ADD COLUMN IF NOT EXISTS factura_numero VARCHAR(50) COMMENT 'Número de factura del proveedor',
ADD COLUMN IF NOT EXISTS tracking VARCHAR(100) COMMENT 'Número de seguimiento del envío',
ADD COLUMN IF NOT EXISTS pdf_generado TINYINT(1) DEFAULT 0 COMMENT 'Si ya se generó el PDF',
ADD COLUMN IF NOT EXISTS activo TINYINT(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Modificar el campo proveedor_rif si es necesario (ya debería existir)
-- Agregar índice único para factura_numero
ALTER TABLE compras
ADD UNIQUE KEY IF NOT EXISTS unique_factura (factura_numero, activo);

-- Agregar índices adicionales para optimización
ALTER TABLE compras
ADD KEY IF NOT EXISTS idx_proveedor (proveedor_rif),
ADD KEY IF NOT EXISTS idx_fecha (fecha_compra),
ADD KEY IF NOT EXISTS idx_factura (factura_numero);

-- Crear tabla de prendas compradas si no existe
CREATE TABLE IF NOT EXISTS prendas_compradas (
  prenda_comprada_id INT(11) NOT NULL AUTO_INCREMENT,
  compra_id INT(11) NOT NULL,
  producto_nombre VARCHAR(200) NOT NULL COMMENT 'Nombre/descripción de la prenda',
  categoria VARCHAR(100) NOT NULL COMMENT 'Categoría de la prenda',
  precio_costo DECIMAL(10,2) NOT NULL COMMENT 'Precio de costo de esta prenda única',
  PRIMARY KEY (prenda_comprada_id),
  KEY idx_compra (compra_id),
  KEY idx_categoria (categoria),
  CONSTRAINT fk_prendas_compradas
    FOREIGN KEY (compra_id)
    REFERENCES compras (compra_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Cada prenda comprada es única con su precio individual';

-- Actualizar datos existentes para que sean compatibles
-- Si hay datos en la tabla compras antigua, necesitamos migrarlos

-- Crear vista para reportes de compras
CREATE OR REPLACE VIEW vista_compras AS
SELECT
    c.compra_id,
    c.factura_numero,
    c.fecha_compra,
    c.tracking,
    p.nombre_empresa AS proveedor,
    p.nombre_contacto AS contacto_proveedor,
    c.monto_total,
    COUNT(pc.prenda_comprada_id) AS total_prendas,
    c.pdf_generado,
    c.created_at,
    c.updated_at
FROM compras c
INNER JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
LEFT JOIN prendas_compradas pc ON c.compra_id = pc.compra_id
WHERE c.activo = 1
GROUP BY c.compra_id
ORDER BY c.fecha_compra DESC;

-- Procedimiento para obtener estadísticas de compras
DROP PROCEDURE IF EXISTS sp_estadisticas_compras;

DELIMITER $$

CREATE PROCEDURE sp_estadisticas_compras()
BEGIN
    SELECT
        COUNT(*) as total_compras,
        SUM(monto_total) as monto_total_general,
        AVG(monto_total) as promedio_compra,
        (
            SELECT COUNT(*)
            FROM prendas_compradas pc
            INNER JOIN compras c ON pc.compra_id = c.compra_id
            WHERE c.activo = 1
        ) as total_prendas_compradas
    FROM compras
    WHERE activo = 1;
END$$

DELIMITER ;

-- Procedimiento para obtener detalle de una compra
DROP PROCEDURE IF EXISTS sp_detalle_compra;

DELIMITER $$

CREATE PROCEDURE sp_detalle_compra(IN p_compra_id INT)
BEGIN
    -- Información de la compra
    SELECT
        c.*,
        p.nombre_empresa,
        p.nombre_contacto,
        p.direccion AS direccion_proveedor
    FROM compras c
    INNER JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
    WHERE c.compra_id = p_compra_id AND c.activo = 1;

    -- Prendas de la compra
    SELECT * FROM prendas_compradas WHERE compra_id = p_compra_id ORDER BY categoria, producto_nombre;
END$$

DELIMITER ;

-- Verificación final
SELECT 'Estructura de tablas actualizada correctamente' as mensaje;
DESCRIBE compras;
DESCRIBE prendas_compradas;
