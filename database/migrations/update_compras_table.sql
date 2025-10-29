-- Modificar estructura de tabla compras para módulo de compras con N° Factura
-- Fecha: Octubre 2025

-- Crear nueva tabla compras con la estructura correcta
DROP TABLE IF EXISTS `compras_new`;
CREATE TABLE `compras_new` (
  `numero_factura` varchar(8) NOT NULL,
  `proveedor_rif` int(11) NOT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','completada','cancelada') DEFAULT 'completada',
  `observaciones` text DEFAULT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`numero_factura`),
  KEY `proveedor_rif_idx` (`proveedor_rif`),
  CONSTRAINT `compras_new_ibfk_1` FOREIGN KEY (`proveedor_rif`) REFERENCES `proveedores` (`proveedor_rif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrar datos de la tabla antigua a la nueva (si existe)
INSERT INTO `compras_new` (`numero_factura`, `proveedor_rif`, `fecha_compra`, `monto_total`, `estado`, `observaciones`, `fec_creacion`, `fec_actualizacion`, `activo`)
SELECT
  LPAD(compra_id, 8, '0') as numero_factura,
  COALESCE(proveedor_rif, 50100100) as proveedor_rif,
  COALESCE(fecha_compra, NOW()) as fecha_compra,
  COALESCE(monto_total, 0.00) as monto_total,
  CASE
    WHEN estado = 'pagado' THEN 'completada'
    WHEN estado = 'vencido' THEN 'cancelada'
    ELSE 'completada'
  END as estado,
  observaciones,
  COALESCE(fec_creacion, NOW()) as fec_creacion,
  COALESCE(fec_actualizacion, NULL) as fec_actualizacion,
  1 as activo
FROM `compras`;

-- Eliminar tabla antigua y renombrar nueva
DROP TABLE `compras`;
RENAME TABLE `compras_new` TO `compras`;

-- Actualizar AUTO_INCREMENT para que no interfiera con el nuevo esquema
ALTER TABLE `compras` MODIFY `numero_factura` varchar(8) NOT NULL;

-- Actualizar las referencias en la tabla detalle_compra para usar numero_factura en lugar de compra_id
-- Nota: Esto requiere que actualicemos también la tabla detalle_compra

DROP TABLE IF EXISTS `detalle_compra_new`;
CREATE TABLE `detalle_compra_new` (
  `detalle_compra_id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_factura` varchar(8) NOT NULL,
  `codigo_prenda` varchar(20) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`detalle_compra_id`),
  KEY `numero_factura_idx` (`numero_factura`),
  KEY `codigo_prenda_idx` (`codigo_prenda`),
  CONSTRAINT `detalle_compra_new_ibfk_1` FOREIGN KEY (`numero_factura`) REFERENCES `compras` (`numero_factura`) ON DELETE CASCADE,
  CONSTRAINT `detalle_compra_new_ibfk_2` FOREIGN KEY (`codigo_prenda`) REFERENCES `prendas` (`codigo_prenda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrar datos de detalle_compra
INSERT INTO `detalle_compra_new` (`detalle_compra_id`, `numero_factura`, `codigo_prenda`, `precio_compra`, `fecha_creacion`)
SELECT
  dc.detalle_compra_id,
  LPAD(c.compra_id, 8, '0') as numero_factura,
  dc.codigo_prenda,
  dc.precio_compra,
  dc.fecha_creacion
FROM `detalle_compra` dc
JOIN `compras` c ON dc.compra_id = c.compra_id;

-- Eliminar tabla antigua y renombrar nueva
DROP TABLE `detalle_compra`;
RENAME TABLE `detalle_compra_new` TO `detalle_compra`;

-- Actualizar AUTO_INCREMENT
ALTER TABLE `detalle_compra` MODIFY `detalle_compra_id` int(11) NOT NULL AUTO_INCREMENT;

-- Actualizar las referencias en la tabla prendas para usar numero_factura
ALTER TABLE `prendas` ADD COLUMN `numero_factura` varchar(8) NULL AFTER `compra_id`;
UPDATE `prendas` SET `numero_factura` = LPAD(`compra_id`, 8, '0') WHERE `compra_id` IS NOT NULL;
ALTER TABLE `prendas` DROP FOREIGN KEY `prendas_ibfk_1`;
ALTER TABLE `prendas` DROP COLUMN `compra_id`;
ALTER TABLE `prendas` ADD CONSTRAINT `prendas_ibfk_1` FOREIGN KEY (`numero_factura`) REFERENCES `compras` (`numero_factura`) ON DELETE SET NULL;
