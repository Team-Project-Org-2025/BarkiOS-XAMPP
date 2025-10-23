-- =========================================================
-- Script SQL para crear la tabla cuentas_pagar
-- Módulo: Cuentas por Pagar
-- Descripción: Gestiona los créditos que los distribuidores/proveedores
--              otorgan a la empresa para pagar las compras realizadas
-- =========================================================

CREATE TABLE IF NOT EXISTS `cuentas_pagar` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` VARCHAR(12) NOT NULL COMMENT 'RIF del proveedor',
  `factura_numero` VARCHAR(50) NOT NULL COMMENT 'Número de factura del proveedor',
  `fecha_emision` DATE NOT NULL COMMENT 'Fecha de emisión de la factura',
  `fecha_vencimiento` DATE NOT NULL COMMENT 'Fecha de vencimiento del pago',
  `fecha_pago` DATETIME NULL DEFAULT NULL COMMENT 'Fecha en que se realizó el pago',
  `monto_total` DECIMAL(10,2) NOT NULL COMMENT 'Monto total a pagar',
  `estado` ENUM('Pendiente', 'Pagada', 'Vencida', 'Parcial') DEFAULT 'Pendiente' COMMENT 'Estado del pago',
  `activo` TINYINT(1) DEFAULT 1 COMMENT 'Registro activo (1) o eliminado lógicamente (0)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_factura` (`factura_numero`, `activo`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_vencimiento` (`fecha_vencimiento`),
  CONSTRAINT `fk_cuentas_pagar_proveedor` 
    FOREIGN KEY (`proveedor_id`) 
    REFERENCES `proveedores` (`id`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Tabla para gestionar las cuentas por pagar a proveedores/distribuidores';

-- =========================================================
-- Datos de prueba (opcional)
-- =========================================================

-- Insertar algunas cuentas por pagar de ejemplo
-- Nota: Asegúrate de que existan proveedores con estos IDs en la tabla proveedores

-- INSERT INTO `cuentas_pagar` 
--   (`proveedor_id`, `factura_numero`, `fecha_emision`, `fecha_vencimiento`, `monto_total`, `estado`) 
-- VALUES
--   ('181818188', 'FACT-2025-001', '2025-10-01', '2025-11-01', 5000.00, 'Pendiente'),
--   ('181818188', 'FACT-2025-002', '2025-10-10', '2025-11-10', 7500.50, 'Pendiente');

-- =========================================================
-- Índices adicionales para optimización (opcional)
-- =========================================================

-- Índice compuesto para consultas frecuentes por proveedor y estado
CREATE INDEX `idx_proveedor_estado` ON `cuentas_pagar` (`proveedor_id`, `estado`);

-- Índice para búsquedas por rango de fechas
CREATE INDEX `idx_fechas` ON `cuentas_pagar` (`fecha_emision`, `fecha_vencimiento`);

-- =========================================================
-- Vista para reportes (opcional pero recomendada)
-- =========================================================

CREATE OR REPLACE VIEW `vista_cuentas_pagar` AS
SELECT 
    cp.id,
    cp.factura_numero,
    cp.proveedor_id,
    p.nombre_empresa AS proveedor,
    p.nombre_contacto,
    cp.fecha_emision,
    cp.fecha_vencimiento,
    cp.fecha_pago,
    cp.monto_total,
    cp.estado,
    DATEDIFF(cp.fecha_vencimiento, CURDATE()) AS dias_para_vencimiento,
    CASE 
        WHEN cp.estado = 'Pagada' THEN 'Al día'
        WHEN DATEDIFF(cp.fecha_vencimiento, CURDATE()) < 0 THEN 'Vencida'
        WHEN DATEDIFF(cp.fecha_vencimiento, CURDATE()) <= 7 THEN 'Por vencer'
        ELSE 'Normal'
    END AS clasificacion_urgencia
FROM cuentas_pagar cp
INNER JOIN proveedores p ON cp.proveedor_id = p.id
WHERE cp.activo = 1
ORDER BY cp.fecha_vencimiento ASC;

-- =========================================================
-- Trigger para actualizar automáticamente estado a 'Vencida'
-- cuando se consulta una cuenta cuya fecha de vencimiento ya pasó
-- =========================================================

DELIMITER $$

CREATE TRIGGER `trg_actualizar_estado_vencido`
BEFORE UPDATE ON `cuentas_pagar`
FOR EACH ROW
BEGIN
    -- Si la fecha de vencimiento ya pasó y el estado sigue como 'Pendiente'
    IF NEW.fecha_vencimiento < CURDATE() 
       AND NEW.estado = 'Pendiente' 
       AND OLD.estado = 'Pendiente' THEN
        SET NEW.estado = 'Vencida';
    END IF;
END$$

DELIMITER ;

-- =========================================================
-- Procedimiento almacenado para obtener estadísticas
-- =========================================================

DELIMITER $$

CREATE PROCEDURE `sp_estadisticas_cuentas_pagar`()
BEGIN
    SELECT 
        COUNT(*) as total_cuentas,
        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'Pagada' THEN 1 ELSE 0 END) as pagadas,
        SUM(CASE WHEN estado = 'Vencida' THEN 1 ELSE 0 END) as vencidas,
        SUM(CASE WHEN estado = 'Parcial' THEN 1 ELSE 0 END) as parciales,
        SUM(CASE WHEN estado IN ('Pendiente', 'Vencida') THEN monto_total ELSE 0 END) as total_pendiente,
        SUM(CASE WHEN estado = 'Pagada' THEN monto_total ELSE 0 END) as total_pagado,
        SUM(monto_total) as monto_total_general
    FROM cuentas_pagar
    WHERE activo = 1;
END$$

DELIMITER ;

-- =========================================================
-- Comentarios finales
-- =========================================================

-- Para ejecutar este script:
-- 1. Abre phpMyAdmin o tu cliente MySQL preferido
-- 2. Selecciona la base de datos 'barkios_db'
-- 3. Ejecuta este script completo
-- 4. Verifica que la tabla se haya creado correctamente con: SHOW TABLES LIKE 'cuentas_pagar';
-- 5. Para ver las estadísticas: CALL sp_estadisticas_cuentas_pagar();
-- 6. Para ver la vista de reportes: SELECT * FROM vista_cuentas_pagar;
