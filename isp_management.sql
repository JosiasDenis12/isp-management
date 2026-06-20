-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 22-12-2025 a las 19:28:21
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `isp_management`
--

-- --------------------------------------------------------
CREATE DATABASE /*!32312 IF NOT EXISTS*/ `isp_management` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */;
USE `isp_management`;

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `direccion` text,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `estado` enum('activo','suspendido','pendiente') DEFAULT 'activo',
  `tipo_conexion` enum('fibra_optica','inalambrica','cable_coaxial') DEFAULT NULL,
  `fecha_contratacion` date DEFAULT (curdate()),
  `dia_corte` tinyint unsigned NOT NULL DEFAULT '5',
  `plan_mensual` decimal(10,2) DEFAULT NULL,
  `megas_contratados` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clientes_estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
 

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `direccion`, `telefono`, `email`, `estado`, `tipo_conexion`, `fecha_contratacion`, `plan_mensual`, `megas_contratados`, `created_at`, `updated_at`) VALUES
(1, 'PRUEBA 5', 'JSOIAS', '68+9609', 'ronaldocabelelstino@gmail.com', 'activo', 'fibra_optica', '2025-11-21', 400.00, 100, '2025-11-22 04:45:19', '2025-11-22 04:45:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

DROP TABLE IF EXISTS `equipos`;
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `tipo_equipo` varchar(100) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `estado_tecnico` enum('operativo','necesita_revision','dañado','fuera_de_servicio','en_mantenimiento') DEFAULT 'operativo',
  `fecha_instalacion` date DEFAULT NULL,
  `observaciones_tecnico` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_equipos_cliente_id` (`cliente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `cliente_id`, `tipo_equipo`, `marca`, `modelo`, `numero_serie`, `estado_tecnico`, `fecha_instalacion`, `observaciones_tecnico`, `created_at`, `updated_at`) VALUES
(1, 1, 'Router Fibra', 'TP-Link', 'Archer AX73', 'SN001234', 'operativo', '2024-01-01', 'Instalación exitosa, señal excelente', '2025-07-03 22:30:47', '2025-07-03 22:30:47'),
(2, 2, 'Antena Inalámbrica', 'Ubiquiti', 'NanoStation M5', 'SN002345', 'operativo', '2024-01-05', 'Buena recepción de señal', '2025-07-03 22:30:47', '2025-07-03 22:30:47'),
(3, 3, 'Módem Cable', 'Motorola', 'MB7621', 'SN003456', 'necesita_revision', '2024-01-03', 'Intermitencias en la conexión', '2025-07-03 22:30:47', '2025-07-03 22:30:47'),
(4, 4, 'ONU Fibra', 'Huawei', 'HG8245H', 'SN004567', 'operativo', '2024-01-20', 'Configuración óptima', '2025-07-03 22:30:47', '2025-07-03 22:30:47'),
(5, 5, 'Router Inalámbrico', 'Netgear', 'R6700', 'SN005678', 'operativo', '2024-01-25', 'Pendiente optimización', '2025-07-03 22:30:47', '2025-07-03 22:30:47'),
(6, 8, 'switch', 'Mikrotik', '54154', '354135', 'operativo', '2025-07-04', 'execlenet', '2025-07-05 03:26:16', '2025-07-05 03:26:16'),
(7, 8, 'switch', 'Mikrotik', '54154', '354135', 'operativo', '2025-07-04', 'execlenet', '2025-07-05 03:26:26', '2025-07-05 03:26:26'),
(8, 8, 'switch', 'Mikrotik', '54154', '354135', 'operativo', '2025-07-04', 'execlenet', '2025-07-05 03:27:09', '2025-07-05 03:27:09'),
(9, 8, 'switch', 'Mikrotik', '54154', '354135', '', '2025-07-04', '', '2025-07-05 03:33:35', '2025-07-05 05:33:32'),
(10, 2, 'modem', 'srhesth', '241', '24154', 'operativo', '2025-07-11', '', '2025-07-05 04:00:26', '2025-07-05 04:00:26'),
(11, 4, 'router', 'mjjj', '533', '5353', 'operativo', '2025-07-05', '', '2025-07-05 04:01:24', '2025-07-05 04:01:24'),
(12, 2, 'switch', 'ghjdfgyjhdfhjdfhj', '53154', '6541654', 'necesita_revision', '2025-07-12', '', '2025-07-05 04:02:13', '2025-07-06 23:17:41'),
(13, 14, 'router', 'Mikrotik', '54154', '354135', 'operativo', '2025-10-27', '', '2025-10-27 21:36:01', '2025-10-27 21:36:01'),
(14, 17, 'modem', 'dsd', 'ascas', 'asc', 'operativo', '2025-11-10', 'scs', '2025-11-10 00:16:11', '2025-11-10 00:16:11'),
(15, 1, 'otro', 'evfefv', 'efv', '', 'necesita_revision', '0000-00-00', '', '2025-11-22 04:55:58', '2025-11-22 04:58:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitas_tecnicas`
--

DROP TABLE IF EXISTS `visitas_tecnicas`;
CREATE TABLE IF NOT EXISTS `visitas_tecnicas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `equipo_id` int DEFAULT NULL,
  `fecha_visita` datetime NOT NULL,
  `tipo_visita` enum('instalacion','mantenimiento','reparacion','revision') DEFAULT NULL,
  `tecnico_nombre` varchar(255) DEFAULT NULL,
  `observaciones` text,
  `estado` enum('programada','pendiente','completada','cancelada','reprogramada') DEFAULT 'programada',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visitas_cliente_id` (`cliente_id`),
  KEY `idx_visitas_equipo_id` (`equipo_id`),
  KEY `idx_visitas_estado` (`estado`),
  KEY `idx_visitas_fecha` (`fecha_visita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `metodo_pago` enum('transferencia','efectivo','paypal','tarjeta') DEFAULT NULL,
  `estado` enum('pagado','pendiente','vencido') DEFAULT 'pendiente',
  `numero_factura` varchar(100) DEFAULT NULL,
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pagos_cliente_id` (`cliente_id`),
  KEY `idx_pagos_fecha_vencimiento` (`fecha_vencimiento`),
  KEY `idx_pagos_estado` (`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `cliente_id`, `monto`, `fecha_pago`, `fecha_vencimiento`, `metodo_pago`, `estado`, `numero_factura`, `observaciones`, `created_at`) VALUES
(1, 1, 500.00, '2025-11-21', '2025-12-21', 'efectivo', 'pendiente', 'FAC-001', '', '2025-11-22 04:46:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `nombre`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Ing. Tomas', NULL, '3123aa', 'admin', '2025-11-10 01:21:55', '2025-11-10 01:23:13');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
