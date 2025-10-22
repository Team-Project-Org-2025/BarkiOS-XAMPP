-- =========================================================
-- Script SQL para crear las tablas del módulo de Compras
-- Módulo: Compras - VERSIÓN SIMPLIFICADA
-- Descripción: Gestiona compras de ropa exclusiva (1 prenda = 1 precio único)
-- =========================================================

-- Tabla principal de compras
CREATE TABLE IF NOT EXISTS `compras` (
  `compra_id` INT(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` VARCHAR(12) NOT NULL COMMENT 'RIF del proveedor',
  `factura_numero` VARCHAR(50) NOT NULL COMMENT 'Número de factura del proveedor',
  `fecha_compra` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tracking` VARCHAR(100) NULL COMMENT 'Número de seguimiento del envío',
  `monto_total` DECIMAL(12,2) NOT NULL COMMENT 'Total pagado',
  `pdf_generado` TINYINT(1) DEFAULT 0 COMMENT 'Si ya se generó el PDF',
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`compra_id`),
  UNIQUE KEY `unique_factura` (`factura_numero`, `activo`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_fecha` (`fecha_compra`),
  KEY `idx_factura` (`factura_numero`),
  CONSTRAINT `fk_compras_proveedor` 
    FOREIGN KEY (`proveedor_id`) 
    REFERENCES `proveedores` (`id`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Tabla de compras de ropa exclusiva';

-- Tabla de prendas compradas (cada prenda es única)
CREATE TABLE IF NOT EXISTS `prendas_compradas` (
  `prenda_comprada_id` INT(11) NOT NULL AUTO_INCREMENT,
  `compra_id` INT(11) NOT NULL,
  `producto_nombre` VARCHAR(200) NOT NULL COMMENT 'Nombre/descripción de la prenda',
  `categoria` VARCHAR(100) NOT NULL COMMENT 'Categoría de la prenda',
  `precio_costo` DECIMAL(10,2) NOT NULL COMMENT 'Precio de costo de esta prenda única',
  PRIMARY KEY (`prenda_comprada_id`),
  KEY `idx_compra` (`compra_id`),
  KEY `idx_categoria` (`categoria`),
  CONSTRAINT `fk_prendas_compradas` 
    FOREIGN KEY (`compra_id`) 
    REFERENCES `compras` (`compra_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Cada prenda comprada es única con su precio individual';

-- Vista para reportes de compras
CREATE OR REPLACE VIEW `vista_compras` AS
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
INNER JOIN proveedores p ON c.proveedor_id = p.id
LEFT JOIN prendas_compradas pc ON c.compra_id = pc.compra_id
WHERE c.activo = 1
GROUP BY c.compra_id
ORDER BY c.fecha_compra DESC;

-- Procedimiento para obtener estadísticas de compras
DELIMITER $$

CREATE PROCEDURE `sp_estadisticas_compras`()
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
DELIMITER $$

CREATE PROCEDURE `sp_detalle_compra`(IN p_compra_id INT)
BEGIN
    -- Información de la compra
    SELECT 
        c.*,
        p.nombre_empresa,
        p.nombre_contacto,
        p.direccion AS direccion_proveedor
    FROM compras c
    INNER JOIN proveedores p ON c.proveedor_id = p.id
    WHERE c.compra_id = p_compra_id AND c.activo = 1;
    
    -- Prendas de la compra
    SELECT * FROM prendas_compradas WHERE compra_id = p_compra_id ORDER BY categoria, producto_nombre;
END$$

DELIMITER ;

-- No se necesitan triggers ya que cada prenda tiene su precio único

-- Índices adicionales para optimización
CREATE INDEX `idx_compra_proveedor_fecha` ON `compras` (`proveedor_id`, `fecha_compra`);

-- =========================================================
-- Datos de prueba (opcional - comentado)
-- =========================================================

-- Ejemplo de inserción de compra
-- INSERT INTO compras (proveedor_id, factura_numero, tracking, monto_total)
-- VALUES ('J123456789', 'FACT-USA-001', 'TRACK123456', 5950.00);

-- Ejemplo de prendas (20 pantalones únicos)
-- SET @compra_id = LAST_INSERT_ID();
-- INSERT INTO prendas_compradas (compra_id, producto_nombre, categoria, precio_costo) VALUES
-- (@compra_id, 'Pantalón Levi''s 501 Talla 32 Azul', 'Pantalones', 45.00),
-- (@compra_id, 'Pantalón Levi''s 501 Talla 34 Negro', 'Pantalones', 47.50),
-- (@compra_id, 'Camisa Ralph Lauren Blanca M', 'Camisas', 55.00);

-- =========================================================
-- Verificación
-- =========================================================

-- Ver tablas creadas
SHOW TABLES LIKE '%compra%';

-- Ver estructura
DESCRIBE compras;
DESCRIBE prendas_compradas;

-- Probar vista
SELECT * FROM vista_compras LIMIT 5;

-- Probar estadísticas
CALL sp_estadisticas_compras();
