-- =========================================================
-- Script de migración para agregar campo 'activo' a proveedores
-- =========================================================

-- Verificar si la columna ya existe antes de agregarla
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'barkios_db' 
      AND TABLE_NAME = 'proveedores' 
      AND COLUMN_NAME = 'activo'
);

-- Agregar la columna 'activo' si no existe
SET @query = IF(
    @column_exists = 0,
    'ALTER TABLE `proveedores` ADD COLUMN `activo` TINYINT(1) DEFAULT 1 COMMENT "Proveedor activo (1) o eliminado lógicamente (0)" AFTER `tipo_rif`',
    'SELECT "La columna activo ya existe" AS mensaje'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar todos los proveedores existentes como activos
UPDATE `proveedores` SET `activo` = 1 WHERE `activo` IS NULL OR `activo` = 0;

-- Verificación
SELECT 'Migración completada exitosamente' AS resultado;
SELECT * FROM `proveedores` LIMIT 5;
