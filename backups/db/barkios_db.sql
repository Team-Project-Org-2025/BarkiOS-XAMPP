-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-11-2025 a las 02:05:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `barkios_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `cliente_ced` int(11) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `tipo` enum('regular','vip') DEFAULT 'regular',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `limite_credito` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `compra_id` int(11) NOT NULL,
  `factura_numero` varchar(8) NOT NULL,
  `proveedor_rif` int(11) DEFAULT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `tracking` varchar(8) DEFAULT NULL,
  `pdf_generado` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credito`
--

CREATE TABLE `credito` (
  `credito_id` int(11) NOT NULL,
  `referencia_credito` varchar(50) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `cuenta_cobrar_id` int(11) DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_cobrar`
--

CREATE TABLE `cuentas_cobrar` (
  `cuenta_cobrar_id` int(11) NOT NULL,
  `credito_id` int(11) DEFAULT NULL,
  `emision` timestamp NOT NULL DEFAULT current_timestamp(),
  `vencimiento` datetime NOT NULL,
  `estado` enum('pendiente','pagado','vencido') DEFAULT 'pendiente',
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_pagar`
--

CREATE TABLE `cuentas_pagar` (
  `cuenta_pagar_id` int(11) NOT NULL,
  `compra_id` int(11) DEFAULT NULL,
  `proveedor_rif` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('pendiente','pagado','vencido') DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL,
  `pago_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pago`
--

CREATE TABLE `detalles_pago` (
  `detalle_id` int(11) NOT NULL,
  `tipo_pago` enum('efectivo','tarjeta','transferencia','otros') NOT NULL,
  `banco` enum('banesco','provincial','mercantil','venezuela','otro') DEFAULT NULL,
  `pagos_id` int(11) DEFAULT NULL,
  `referencia_bancaria` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra`
--

CREATE TABLE `detalle_compra` (
  `detalle_compra_id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `codigo_prenda` varchar(20) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `detalle_venta_id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `prenda_id` int(11) NOT NULL,
  `codigo_prenda` varchar(20) DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `empleado_ced` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `pago_id` int(11) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto` decimal(10,2) NOT NULL,
  `tipo_pago` enum('EFECTIVO','TRANSFERENCIA','PAGO_MOVIL','ZELLE','PUNTO','CHEQUE','OTRO') DEFAULT 'EFECTIVO',
  `moneda_pago` enum('USD','BS') NOT NULL DEFAULT 'BS',
  `referencia_bancaria` varchar(50) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `estado_pago` enum('PENDIENTE','CONFIRMADO','ANULADO') DEFAULT 'CONFIRMADO',
  `credito_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_compras`
--

CREATE TABLE `pagos_compras` (
  `pago_compra_id` int(11) NOT NULL,
  `cuenta_pagar_id` int(11) NOT NULL,
  `compra_id` int(11) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto` decimal(10,2) NOT NULL,
  `tipo_pago` enum('EFECTIVO','TRANSFERENCIA','PAGO_MOVIL','ZELLE','CHEQUE','OTRO') DEFAULT 'EFECTIVO',
  `moneda_pago` enum('USD','BS') NOT NULL DEFAULT 'USD',
  `referencia_bancaria` varchar(50) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `estado_pago` enum('PENDIENTE','CONFIRMADO','ANULADO') DEFAULT 'CONFIRMADO',
  `observaciones` text DEFAULT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prendas`
--

CREATE TABLE `prendas` (
  `prenda_id` int(11) NOT NULL,
  `codigo_prenda` varchar(20) NOT NULL,
  `compra_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` enum('Formal','Casual','Deportivo','Invierno','Verano','Fiesta') NOT NULL,
  `tipo` enum('Vestido','Camisa','Pantalon','Chaqueta','Blusa','Short','Falda','Enterizo') NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `estado` enum('DISPONIBLE','VENDIDA','ELIMINADA') NOT NULL DEFAULT 'DISPONIBLE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `proveedor_rif` int(11) NOT NULL,
  `nombre_contacto` varchar(100) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `tipo_rif` char(1) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos`
--

CREATE TABLE `recibos` (
  `recibo_id` varchar(20) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `venta_id` int(11) DEFAULT NULL,
  `prenda_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `nombre`, `created_at`) VALUES
(1, 'admin@ejemplo.com', '$2y$10$U0dsB7JAeyTnN7TH.H6V1OT7OoHhpdgcR7/u6pn2b/eqWr2cF2CbC', 'Administrador Garage', '2025-10-18 01:23:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `venta_id` int(11) NOT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `referencia_bancaria` varchar(50) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `empleado_ced` int(11) DEFAULT NULL,
  `cliente_ced` int(11) DEFAULT NULL,
  `tipo_venta` enum('contado','credito') NOT NULL DEFAULT 'contado',
  `estado_venta` enum('pendiente','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `monto_total` decimal(10,2) DEFAULT 0.00,
  `iva_porcentaje` decimal(5,2) NOT NULL DEFAULT 16.00,
  `monto_iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_pendiente` decimal(10,2) DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cliente_ced`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`compra_id`),
  ADD UNIQUE KEY `factura_numero_unique` (`factura_numero`),
  ADD KEY `proveedor_rif_idx` (`proveedor_rif`);

--
-- Indices de la tabla `credito`
--
ALTER TABLE `credito`
  ADD PRIMARY KEY (`credito_id`),
  ADD UNIQUE KEY `referencia_credito` (`referencia_credito`),
  ADD UNIQUE KEY `referencia_credito_2` (`referencia_credito`),
  ADD KEY `venta_id_idx` (`venta_id`),
  ADD KEY `cuenta_cobrar_id_idx` (`cuenta_cobrar_id`);

--
-- Indices de la tabla `cuentas_cobrar`
--
ALTER TABLE `cuentas_cobrar`
  ADD PRIMARY KEY (`cuenta_cobrar_id`),
  ADD KEY `credito_id_idx` (`credito_id`);

--
-- Indices de la tabla `cuentas_pagar`
--
ALTER TABLE `cuentas_pagar`
  ADD PRIMARY KEY (`cuenta_pagar_id`),
  ADD KEY `proveedor_rif_idx` (`proveedor_rif`),
  ADD KEY `idx_compra` (`compra_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `detalles_pago`
--
ALTER TABLE `detalles_pago`
  ADD PRIMARY KEY (`detalle_id`),
  ADD KEY `pagos_id_idx` (`pagos_id`);

--
-- Indices de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`detalle_compra_id`),
  ADD KEY `compra_id_idx` (`compra_id`),
  ADD KEY `codigo_prenda_idx` (`codigo_prenda`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`detalle_venta_id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `prenda_id` (`prenda_id`),
  ADD KEY `codigo_prenda_idx` (`codigo_prenda`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`empleado_ced`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`pago_id`),
  ADD KEY `venta_id_idx` (`venta_id`),
  ADD KEY `credito_id_idx` (`credito_id`);

--
-- Indices de la tabla `pagos_compras`
--
ALTER TABLE `pagos_compras`
  ADD PRIMARY KEY (`pago_compra_id`),
  ADD KEY `cuenta_pagar_id_idx` (`cuenta_pagar_id`),
  ADD KEY `compra_id_idx` (`compra_id`),
  ADD KEY `idx_cuenta_pagar` (`cuenta_pagar_id`);

--
-- Indices de la tabla `prendas`
--
ALTER TABLE `prendas`
  ADD PRIMARY KEY (`prenda_id`),
  ADD UNIQUE KEY `codigo_prenda` (`codigo_prenda`),
  ADD KEY `compra_id_idx` (`compra_id`),
  ADD KEY `codigo_prenda_idx` (`codigo_prenda`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`proveedor_rif`);

--
-- Indices de la tabla `recibos`
--
ALTER TABLE `recibos`
  ADD PRIMARY KEY (`recibo_id`),
  ADD KEY `venta_id_idx` (`venta_id`),
  ADD KEY `prenda_id_idx` (`prenda_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`venta_id`),
  ADD UNIQUE KEY `referencia` (`referencia`),
  ADD KEY `empleado_ced` (`empleado_ced`),
  ADD KEY `cliente_ced` (`cliente_ced`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `compra_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `credito`
--
ALTER TABLE `credito`
  MODIFY `credito_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas_cobrar`
--
ALTER TABLE `cuentas_cobrar`
  MODIFY `cuenta_cobrar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas_pagar`
--
ALTER TABLE `cuentas_pagar`
  MODIFY `cuenta_pagar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_pago`
--
ALTER TABLE `detalles_pago`
  MODIFY `detalle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `detalle_compra_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `detalle_venta_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `pago_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_compras`
--
ALTER TABLE `pagos_compras`
  MODIFY `pago_compra_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prendas`
--
ALTER TABLE `prendas`
  MODIFY `prenda_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `venta_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`proveedor_rif`) REFERENCES `proveedores` (`proveedor_rif`);

--
-- Filtros para la tabla `credito`
--
ALTER TABLE `credito`
  ADD CONSTRAINT `credito_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`venta_id`);

--
-- Filtros para la tabla `cuentas_cobrar`
--
ALTER TABLE `cuentas_cobrar`
  ADD CONSTRAINT `cuentas_cobrar_ibfk_1` FOREIGN KEY (`credito_id`) REFERENCES `credito` (`credito_id`);

--
-- Filtros para la tabla `cuentas_pagar`
--
ALTER TABLE `cuentas_pagar`
  ADD CONSTRAINT `cuentas_pagar_ibfk_1` FOREIGN KEY (`proveedor_rif`) REFERENCES `proveedores` (`proveedor_rif`),
  ADD CONSTRAINT `fk_cuentas_pagar_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`compra_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `detalle_compra_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`compra_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_compra_ibfk_2` FOREIGN KEY (`codigo_prenda`) REFERENCES `prendas` (`codigo_prenda`);

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`venta_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`prenda_id`) REFERENCES `prendas` (`prenda_id`);

--
-- Filtros para la tabla `pagos_compras`
--
ALTER TABLE `pagos_compras`
  ADD CONSTRAINT `fk_pagos_compras_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`compra_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pagos_compras_cuenta_pagar` FOREIGN KEY (`cuenta_pagar_id`) REFERENCES `cuentas_pagar` (`cuenta_pagar_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`empleado_ced`) REFERENCES `empleados` (`empleado_ced`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`cliente_ced`) REFERENCES `clientes` (`cliente_ced`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
