-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-10-2025 a las 02:10:00
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

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`cliente_ced`, `nombre_cliente`, `telefono`, `correo`, `direccion`, `fec_creacion`, `fec_actualizacion`, `tipo`, `activo`, `limite_credito`) VALUES
(12321312, 'Cristopher', '04267239855', NULL, 'sadasd', '2025-10-22 19:12:45', '2025-10-22 19:12:51', 'vip', 0, 0.00),
(30303030, 'Ana Torres', '0414-5556677', 'ana.torres@gmail.com', 'Av. Bolívar, Mérida', '2025-10-22 14:39:12', NULL, 'regular', 1, 0.00),
(40404040, 'Carlos Gómez', '0416-8899001', 'carlos.gomez@hotmail.com', 'Calle 23, Ejido', '2025-10-22 14:39:12', NULL, 'vip', 1, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `compra_id` int(11) NOT NULL,
  `proveedor_rif` int(11) DEFAULT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagado','vencido') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `fec_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fec_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`compra_id`, `proveedor_rif`, `fecha_compra`, `monto_total`, `estado`, `observaciones`, `fec_creacion`, `fec_actualizacion`) VALUES
(10000004, 50100100, '2025-10-22 14:39:12', 300.00, 'pagado', NULL, '2025-10-22 14:39:12', NULL),
(10000005, 50100200, '2025-10-22 14:39:12', 600.00, 'pendiente', NULL, '2025-10-22 14:39:12', NULL);

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

--
-- Volcado de datos para la tabla `credito`
--

INSERT INTO `credito` (`credito_id`, `referencia_credito`, `venta_id`, `cuenta_cobrar_id`, `fecha_inicio`) VALUES
(25, 'CRE-121321231132', 60, NULL, '2025-10-23 00:02:45'),
(26, 'CRE-121321231', 61, NULL, '2025-10-23 00:19:33'),
(27, 'CRE-121321231123', 62, NULL, '2025-10-23 00:22:43'),
(28, 'CRE-1231321231', 63, NULL, '2025-10-23 00:26:24'),
(29, 'CRE-1231231321', 64, NULL, '2025-10-23 00:27:23'),
(30, 'CRE-1213212313212', 65, NULL, '2025-10-23 00:28:01'),
(31, 'CRE-123123121312', 66, NULL, '2025-10-23 01:18:45'),
(32, 'CRE-12313212', 67, NULL, '2025-10-23 01:21:07'),
(33, 'CRE-1213121231321', 68, NULL, '2025-10-23 01:30:56'),
(34, 'CRE-121321231213', 69, NULL, '2025-10-23 01:31:31'),
(35, 'CRE-VEF-88685798', 72, NULL, '2025-10-23 01:40:28'),
(36, 'CRE-1213212315555', 74, NULL, '2025-10-25 03:05:54'),
(37, 'CRE-12312312744455', 75, NULL, '2025-10-25 03:12:17'),
(38, 'CRE-12313121231', 76, NULL, '2025-10-25 03:13:07');

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

--
-- Volcado de datos para la tabla `cuentas_cobrar`
--

INSERT INTO `cuentas_cobrar` (`cuenta_cobrar_id`, `credito_id`, `emision`, `vencimiento`, `estado`, `fec_creacion`, `fec_actualizacion`) VALUES
(31, 31, '2025-10-23 01:18:45', '2025-10-26 00:00:00', 'vencido', '2025-10-23 01:18:45', NULL),
(32, 32, '2025-10-23 01:21:07', '2025-10-31 00:00:00', 'vencido', '2025-10-23 01:21:07', NULL),
(33, 33, '2025-10-23 01:30:56', '2025-10-26 00:00:00', 'vencido', '2025-10-23 01:30:56', NULL),
(34, 34, '2025-10-23 01:31:31', '2025-10-29 00:00:00', 'vencido', '2025-10-23 01:31:31', '2025-10-23 01:37:59'),
(35, 35, '2025-10-23 01:40:28', '2025-10-31 00:00:00', 'vencido', '2025-10-23 01:40:28', NULL),
(36, 36, '2025-10-25 03:05:54', '2025-10-28 00:00:00', 'vencido', '2025-10-25 03:05:54', NULL),
(37, 37, '2025-10-25 03:12:17', '2025-10-29 00:00:00', 'vencido', '2025-10-25 03:12:17', NULL),
(38, 38, '2025-10-25 03:13:07', '2025-10-27 00:00:00', 'pendiente', '2025-10-25 03:13:07', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_pagar`
--

CREATE TABLE `cuentas_pagar` (
  `cuenta_pagar_id` int(11) NOT NULL,
  `proveedor_rif` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
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

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`detalle_venta_id`, `venta_id`, `prenda_id`, `codigo_prenda`, `precio_unitario`) VALUES
(52, 60, 213123449, '213123125', 12.00),
(53, 61, 213123449, '213123125', 12.00),
(54, 62, 213123449, '213123125', 12.00),
(55, 63, 213123449, '213123125', 12.00),
(56, 64, 213123449, '213123125', 12.00),
(57, 65, 213123449, '213123125', 12.00),
(58, 66, 213123449, '213123125', 12.00),
(59, 67, 213123450, '121321212', 12.00),
(60, 68, 213123450, '121321212', 12.00),
(61, 68, 213123449, '213123125', 12.00),
(62, 69, 213123450, '121321212', 12.00),
(63, 70, 213123449, '213123125', 12.00),
(64, 71, 213123450, '121321212', 12.00),
(65, 72, 213123449, '213123125', 12.00),
(66, 74, 213123450, '121321212', 12.00),
(67, 75, 213123449, '213123125', 12.00),
(68, 76, 213123451, '213123123', 65.00);

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

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`empleado_ced`, `nombre`, `telefono`, `correo`, `direccion`, `cargo`, `fecha_ingreso`, `fec_creacion`, `fec_actualizacion`, `activo`) VALUES
(10101010, 'María López', '0414-1112233', NULL, NULL, 'Vendedora', '2023-08-01', '2025-10-22 14:39:12', NULL, 1),
(20202020, 'José Ramírez', '0412-9988776', NULL, NULL, 'Supervisor', '2022-03-15', '2025-10-22 14:39:12', NULL, 1);

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

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`pago_id`, `venta_id`, `fecha_pago`, `monto`, `tipo_pago`, `moneda_pago`, `referencia_bancaria`, `banco`, `estado_pago`, `credito_id`, `observaciones`) VALUES
(4, 35, '2025-10-22 21:01:50', 13.00, 'EFECTIVO', 'USD', '', '', 'CONFIRMADO', 9, ''),
(5, 35, '2025-10-22 21:10:59', 13.68, 'EFECTIVO', 'USD', '', '', 'CONFIRMADO', 9, ''),
(6, 36, '2025-10-22 21:23:07', 45.00, 'EFECTIVO', 'USD', '', '', 'CONFIRMADO', 10, ''),
(7, 36, '2025-10-22 21:23:40', 20.00, 'TRANSFERENCIA', 'USD', '123123', 'Banesco', 'CONFIRMADO', 10, 'Cristopher'),
(8, 74, '2025-10-25 03:06:28', 10.00, 'TRANSFERENCIA', 'USD', '123123', 'Banesco', 'CONFIRMADO', 36, ''),
(9, 76, '2025-10-25 22:19:02', 12.00, 'TRANSFERENCIA', 'BS', '123123', 'Banesco', 'CONFIRMADO', 38, ''),
(10, 76, '2025-10-25 22:19:18', 5.65, 'PAGO_MOVIL', 'BS', '123123', 'Banesco', 'CONFIRMADO', 38, '');

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

--
-- Volcado de datos para la tabla `prendas`
--

INSERT INTO `prendas` (`prenda_id`, `codigo_prenda`, `compra_id`, `nombre`, `categoria`, `tipo`, `precio`, `precio_compra`, `imagen`, `descripcion`, `fecha_creacion`, `fec_actualizacion`, `activo`, `estado`) VALUES
(213123449, '213123125', NULL, 'Cristopher', 'Casual', 'Blusa', 12.00, NULL, NULL, NULL, '2025-10-22 23:56:01', '2025-10-25 03:12:30', 1, 'DISPONIBLE'),
(213123450, '121321212', NULL, 'Cristopher', 'Formal', 'Camisa', 12.00, NULL, NULL, NULL, '2025-10-23 01:20:36', '2025-10-25 03:12:31', 1, 'DISPONIBLE'),
(213123451, '213123123', NULL, 'hola', 'Casual', 'Pantalon', 65.00, NULL, NULL, NULL, '2025-10-25 03:12:47', '2025-10-25 03:13:07', 1, 'VENDIDA'),
(213123452, '213123122', NULL, 'Cristopher', 'Formal', 'Vestido', 12.00, NULL, NULL, NULL, '2025-10-25 21:08:05', NULL, 1, 'DISPONIBLE');

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

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`proveedor_rif`, `nombre_contacto`, `nombre_empresa`, `direccion`, `telefono`, `correo`, `tipo_rif`, `activo`, `fec_creacion`, `fec_actualizacion`) VALUES
(50100100, 'Luis Fernández', 'Moda Mérida', 'Av. Urdaneta, Mérida', '0424-3344556', NULL, 'J', 1, '2025-10-22 14:39:12', NULL),
(50100200, 'Carmen Díaz', 'Textiles Andinos', 'Calle 12, Ejido', '0426-8899445', NULL, 'J', 1, '2025-10-22 14:39:12', NULL);

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
(1, 'admin@ejemplo.com', '$2y$10$QWzJ9bH7R/lFhS8qT5Cj5u.x.2P9yL0s0k7d0g3I2xX8j1Y5rM4y2', 'Administrador Garage', '2025-10-18 01:23:45'),
(2, 'usuario.peligroso@ejemplo.com', 'Prueba123@?', 'Usuario NO Seguro', '2025-10-19 20:34:43'),
(4, 'deadtaraprogresive@gmail.com', 'holacomoestas123j*A', 'Cristopher', '0000-00-00 00:00:00');

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
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`venta_id`, `referencia`, `referencia_bancaria`, `banco`, `fecha`, `empleado_ced`, `cliente_ced`, `tipo_venta`, `estado_venta`, `monto_total`, `iva_porcentaje`, `monto_iva`, `monto_subtotal`, `saldo_pendiente`, `observaciones`, `fec_creacion`, `fec_actualizacion`) VALUES
(60, '121321231132', NULL, NULL, '2025-10-23 00:02:45', 10101010, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 00:02:45', '2025-10-23 00:09:51'),
(61, '121321231', NULL, NULL, '2025-10-23 00:19:32', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 00:19:32', '2025-10-23 00:22:24'),
(62, '121321231123', NULL, NULL, '2025-10-23 00:22:43', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 00:22:43', '2025-10-23 00:23:09'),
(63, '1231321231', NULL, NULL, '2025-10-23 00:26:24', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 00:26:24', '2025-10-23 00:26:43'),
(64, '1231231321', NULL, NULL, '2025-10-23 00:27:23', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 00:27:23', '2025-10-23 00:27:31'),
(65, '1213212313212', NULL, NULL, '2025-10-23 00:28:01', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 00:28:01', '2025-10-23 00:43:09'),
(66, '123123121312', NULL, NULL, '2025-10-23 01:18:45', 10101010, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 01:18:45', '2025-10-23 01:19:05'),
(67, '12313212', NULL, NULL, '2025-10-23 01:21:07', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 01:21:07', '2025-10-23 01:21:26'),
(68, '1213121231321', NULL, NULL, '2025-10-23 01:30:56', 20202020, 40404040, 'credito', 'cancelada', 27.84, 16.00, 3.84, 24.00, 0.00, '', '2025-10-23 01:30:56', '2025-10-23 01:31:05'),
(69, '121321231213', NULL, NULL, '2025-10-23 01:31:31', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 01:31:31', '2025-10-23 01:39:27'),
(70, '121321211', NULL, NULL, '2025-10-23 01:38:44', 20202020, 40404040, 'contado', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 01:38:44', '2025-10-23 01:39:25'),
(71, 'VEF-88685869', NULL, NULL, '2025-10-23 01:39:54', 20202020, 30303030, 'contado', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, 'Pe causa', '2025-10-23 01:39:54', '2025-10-25 02:53:05'),
(72, 'VEF-88685798', NULL, NULL, '2025-10-23 01:40:28', 10101010, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-23 01:40:28', '2025-10-25 03:11:55'),
(74, '1213212315555', NULL, NULL, '2025-10-25 03:05:54', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-25 03:05:54', '2025-10-25 03:12:31'),
(75, '12312312744455', NULL, NULL, '2025-10-25 03:12:17', 20202020, 40404040, 'credito', 'cancelada', 13.92, 16.00, 1.92, 12.00, 0.00, '', '2025-10-25 03:12:17', '2025-10-25 03:12:30'),
(76, '12313121231', NULL, NULL, '2025-10-25 03:13:07', 20202020, 40404040, 'credito', 'pendiente', 75.40, 16.00, 10.40, 65.00, 57.75, '', '2025-10-25 03:13:07', '2025-10-25 22:19:18');

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
  ADD KEY `proveedor_rif_idx` (`proveedor_rif`);

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
  MODIFY `compra_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10000006;

--
-- AUTO_INCREMENT de la tabla `credito`
--
ALTER TABLE `credito`
  MODIFY `credito_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `cuentas_cobrar`
--
ALTER TABLE `cuentas_cobrar`
  MODIFY `cuenta_cobrar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `cuentas_pagar`
--
ALTER TABLE `cuentas_pagar`
  MODIFY `cuenta_pagar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalles_pago`
--
ALTER TABLE `detalles_pago`
  MODIFY `detalle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `detalle_compra_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `detalle_venta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `pago_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `prendas`
--
ALTER TABLE `prendas`
  MODIFY `prenda_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213123453;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `venta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

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
  ADD CONSTRAINT `cuentas_pagar_ibfk_1` FOREIGN KEY (`proveedor_rif`) REFERENCES `proveedores` (`proveedor_rif`);

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
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`empleado_ced`) REFERENCES `empleados` (`empleado_ced`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`cliente_ced`) REFERENCES `clientes` (`cliente_ced`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
