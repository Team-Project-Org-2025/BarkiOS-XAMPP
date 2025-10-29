-- Script simplificado para verificar y actualizar la estructura de compras
-- Este script verifica la estructura actual y la actualiza si es necesario

USE barkios_db;

-- Verificar estructura actual de la tabla compras
DESCRIBE compras;

-- Agregar campos faltantes si no existen
ALTER TABLE compras
ADD COLUMN IF NOT EXISTS factura_numero VARCHAR(50) COMMENT 'Número de factura del proveedor',
ADD COLUMN IF NOT EXISTS tracking VARCHAR(100) COMMENT 'Número de seguimiento del envío',
ADD COLUMN IF NOT EXISTS pdf_generado TINYINT(1) DEFAULT 0 COMMENT 'Si ya se generó el PDF',
ADD COLUMN IF NOT EXISTS activo TINYINT(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

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

-- Agregar índice único para factura_numero si no existe
ALTER TABLE compras
ADD UNIQUE KEY IF NOT EXISTS unique_factura (factura_numero, activo);

-- Verificar que todo esté correcto
SELECT 'Estructura actualizada correctamente' as mensaje;
DESCRIBE compras;
DESCRIBE prendas_compradas;
