-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 10:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `artesano_digital`
--

-- --------------------------------------------------------

--
-- Table structure for table `carritos`
--

CREATE TABLE `carritos` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carritos`
--

INSERT INTO `carritos` (`id_carrito`, `id_usuario`, `fecha_creacion`) VALUES
(1, 4, '2025-07-17 10:43:51'),
(2, 5, '2025-07-17 10:43:51'),
(3, 7, '2025-07-26 19:14:18'),
(4, 3, '2025-07-26 19:14:22'),
(5, 1, '2025-07-26 19:16:48');

-- --------------------------------------------------------

--
-- Table structure for table `carrito_productos`
--

CREATE TABLE `carrito_productos` (
  `id_carrito_producto` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `fecha_agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carrito_productos`
--

INSERT INTO `carrito_productos` (`id_carrito_producto`, `id_carrito`, `id_producto`, `cantidad`, `fecha_agregado`) VALUES
(1, 1, 1, 2, '2025-07-17 10:43:51'),
(2, 1, 3, 1, '2025-07-17 10:43:51'),
(3, 2, 2, 1, '2025-07-17 10:43:51'),
(4, 2, 4, 1, '2025-07-17 10:43:51'),
(6, 4, 1, 6, '2025-07-26 19:14:22'),
(7, 2, 1, 6, '2025-07-26 19:14:27'),
(8, 5, 1, 6, '2025-07-26 19:16:48');

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('nuevo_pedido','estado_actualizado','stock_bajo','pedido_confirmado') NOT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `tipo`, `mensaje`, `leida`, `fecha_creacion`) VALUES
(1, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #1 por $102.00', 0, '2025-07-17 10:43:51'),
(2, 2, 'nuevo_pedido', 'Tienes un nuevo pedido #2 por $57.00', 0, '2025-07-17 10:43:51'),
(3, 4, 'estado_actualizado', 'Tu pedido #1 ha sido enviado', 0, '2025-07-17 10:43:51'),
(4, 1, 'stock_bajo', 'El producto \"Sombrero Pintao\" tiene stock bajo (8 unidades)', 0, '2025-07-17 10:43:51'),
(5, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #5 por B/. 80.00', 0, '2025-07-20 14:48:43'),
(6, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #6 por B/. 80.00', 0, '2025-07-20 14:50:36'),
(7, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #7 por B/. 80.00', 0, '2025-07-20 15:44:07'),
(8, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #8 por B/. 38.00', 0, '2025-07-23 21:00:42'),
(9, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #8 por B/. 11.00', 0, '2025-07-23 21:00:42'),
(10, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #8 por B/. 83.00', 0, '2025-07-23 21:00:42'),
(11, 2, 'nuevo_pedido', 'Tienes un nuevo pedido #8 por B/. 19.50', 0, '2025-07-23 21:00:42'),
(12, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #9 por B/. 83.00', 0, '2025-07-23 21:29:12'),
(13, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #10 por B/. 38.00', 0, '2025-07-23 22:06:00'),
(14, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #10 por B/. 11.00', 0, '2025-07-23 22:06:00'),
(15, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #10 por B/. 83.00', 0, '2025-07-23 22:06:00'),
(16, 2, 'nuevo_pedido', 'Tienes un nuevo pedido #10 por B/. 19.50', 0, '2025-07-23 22:06:00'),
(17, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #11 por B/. 11.00', 0, '2025-07-23 22:11:21'),
(18, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #11 por B/. 83.00', 0, '2025-07-23 22:11:21'),
(19, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #11 por B/. 55.00', 0, '2025-07-23 22:11:21'),
(20, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #12 por B/. 11.00', 0, '2025-07-23 22:23:21'),
(21, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #12 por B/. 83.00', 0, '2025-07-23 22:23:21'),
(22, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #12 por B/. 55.00', 0, '2025-07-23 22:23:21'),
(23, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #13 por B/. 11.00', 0, '2025-07-23 23:20:38'),
(24, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #13 por B/. 55.00', 0, '2025-07-23 23:20:38'),
(25, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #14 por B/. 11.00', 0, '2025-07-23 23:28:52'),
(26, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #14 por B/. 55.00', 0, '2025-07-23 23:28:52'),
(27, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #14 por B/. 79.00', 0, '2025-07-23 23:28:52'),
(28, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #15 por B/. 11.00', 0, '2025-07-23 23:37:41'),
(29, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #15 por B/. 55.00', 0, '2025-07-23 23:37:41'),
(30, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #15 por B/. 79.00', 0, '2025-07-23 23:37:41'),
(31, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #16 por B/. 11.00', 0, '2025-07-25 16:52:27'),
(32, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #16 por B/. 55.00', 0, '2025-07-25 16:52:27'),
(33, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #16 por B/. 79.00', 0, '2025-07-25 16:52:27'),
(34, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #17 por B/. 11.00', 0, '2025-07-25 18:14:52'),
(35, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #17 por B/. 55.00', 0, '2025-07-25 18:14:52'),
(36, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #17 por B/. 79.00', 0, '2025-07-25 18:14:52'),
(37, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #18 por B/. 11.00', 0, '2025-07-25 18:34:57'),
(38, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #18 por B/. 55.00', 0, '2025-07-25 18:34:57'),
(39, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #18 por B/. 79.00', 0, '2025-07-25 18:34:57'),
(40, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #22 por B/. 11.00', 0, '2025-07-25 20:01:48'),
(41, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #22 por B/. 55.00', 0, '2025-07-25 20:01:48'),
(42, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #22 por B/. 79.00', 0, '2025-07-25 20:01:48'),
(43, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #23 por B/. 79.00', 0, '2025-07-25 20:36:20'),
(44, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #23 por B/. 75.50', 0, '2025-07-25 20:36:20'),
(45, 3, 'nuevo_pedido', 'Tienes un nuevo pedido #23 por B/. 93.00', 0, '2025-07-25 20:36:20'),
(46, 2, 'nuevo_pedido', 'Tienes un nuevo pedido #23 por B/. 43.50', 0, '2025-07-25 20:36:20'),
(47, 1, 'nuevo_pedido', 'Tienes un nuevo pedido #24 por B/. 33.00', 0, '2025-07-25 20:52:36'),
(48, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #25 por B/. 281.00', 0, '2025-07-25 22:49:37'),
(49, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #26 por B/. 281.00', 0, '2025-07-25 23:55:05'),
(50, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #27 por B/. 281.00', 0, '2025-07-26 00:15:55'),
(51, 7, 'nuevo_pedido', 'Tienes un nuevo pedido #28 por B/. 281.00', 0, '2025-07-26 00:21:19'),
(52, 8, 'nuevo_pedido', 'Tienes un nuevo pedido #29 por B/. 105.00', 0, '2025-07-26 15:12:59');

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` enum('pendiente','confirmado','en_proceso','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `fecha_pedido` datetime DEFAULT current_timestamp(),
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_proceso` datetime DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `direccion_envio` text NOT NULL,
  `metodo_envio` varchar(100) DEFAULT 'Env├¡o est├índar',
  `codigo_seguimiento` varchar(100) DEFAULT NULL,
  `tiempo_estimado` varchar(50) DEFAULT '3-5 d├¡as h├íbiles',
  `empresa_envio` varchar(100) DEFAULT NULL,
  `numero_seguimiento` varchar(100) DEFAULT NULL,
  `fecha_estimada_entrega` date DEFAULT NULL,
  `notas_entrega` text DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `estado`, `metodo_pago`, `total`, `subtotal`, `descuento`, `fecha_pedido`, `fecha_confirmacion`, `fecha_proceso`, `fecha_envio`, `fecha_entrega`, `direccion_envio`, `metodo_envio`, `codigo_seguimiento`, `tiempo_estimado`, `empresa_envio`, `numero_seguimiento`, `fecha_estimada_entrega`, `notas_entrega`, `fecha_actualizacion`) VALUES
(1, 4, 'enviado', 'tarjeta_credito', 227.00, 227.00, 0.00, '2025-07-17 10:43:51', '2025-07-17 11:43:51', '2025-07-18 10:43:51', '2025-07-19 10:43:51', NULL, 'Calle 50, Ciudad de Panamá', 'Env├¡o express', 'TR00000001', '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(2, 5, 'pendiente', 'yappy', 102.00, 102.00, 0.00, '2025-07-17 10:43:51', NULL, NULL, NULL, NULL, 'Vía España, San Miguelito', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(19, 4, 'pendiente', 'tarjeta', 90.00, 90.00, 0.00, '2025-07-25 19:26:35', NULL, NULL, NULL, NULL, 'Calle 50, Edificio Plaza, Apto 15B, Ciudad de Panamß', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', 'DHL Panamß', NULL, NULL, 'Cliente solicita entrega en horario de oficina', '2025-07-26 17:13:51'),
(20, 5, 'en_proceso', 'yappy', 47.00, 47.00, 0.00, '2025-07-25 19:26:35', NULL, NULL, NULL, NULL, 'Av. Balboa, Torre Ocean, Piso 8, Oficina 802', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', 'Correos de Panamß', 'CP123456789', '2025-07-30', 'Producto personalizado seg·n especificaciones', '2025-07-26 17:13:51'),
(21, 6, 'en_proceso', 'efectivo', 50.00, 50.00, 0.00, '2025-07-25 19:26:35', NULL, NULL, NULL, NULL, 'Calle Principal, Casa 123, Las Cumbres', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', 'Mailboxes Etc.', 'MBE987654321', '2025-07-28', 'Entrega en fin de semana disponible', '2025-07-27 00:50:15'),
(22, 6, 'en_proceso', 'tarjeta', 145.00, 145.00, 0.00, '2025-07-25 20:01:48', NULL, NULL, NULL, NULL, '{\"nombre\":\"Daniel\",\"direccion\":\"adasdas\",\"ciudad\":\"Arraij\\u00e1n\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(23, 6, 'entregado', 'tarjeta', 291.00, 291.00, 0.00, '2025-07-25 20:36:20', '2025-07-25 21:36:20', '2025-07-26 20:36:20', '2025-07-27 20:36:20', '2025-07-29 20:36:20', '{\"nombre\":\"Daniel\",\"direccion\":\"adasdas\",\"ciudad\":\"Arraij\\u00e1n\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o express', 'TR00000023', '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(24, 7, 'pendiente', 'yappy', 33.00, 33.00, 0.00, '2025-07-25 20:52:36', NULL, NULL, NULL, NULL, '{\"nombre\":\"Daniel\",\"direccion\":\"asd\",\"ciudad\":\"Arraij\\u00e1n\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(25, 6, 'en_proceso', 'tarjeta', 281.00, 281.00, 0.00, '2025-07-25 22:49:37', NULL, NULL, NULL, NULL, '{\"nombre\":\"Daniel\",\"direccion\":\"sadasdas\",\"ciudad\":\"Chame\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(26, 6, 'pendiente', 'yappy', 281.00, 281.00, 0.00, '2025-07-25 23:55:05', NULL, NULL, NULL, NULL, '{\"nombre\":\"Daniel\",\"direccion\":\"asdasd\",\"ciudad\":\"Arraij\\u00e1n\",\"telefono\":\"+507 6000-1234\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(27, 6, 'pendiente', 'yappy', 281.00, 281.00, 0.00, '2025-07-26 00:15:55', NULL, NULL, NULL, NULL, '{\"nombre\":\"Daniel\",\"direccion\":\"asdasd\",\"ciudad\":\"La Chorrera\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 17:13:51'),
(28, 6, 'pendiente', 'yappy', 281.00, 281.00, 0.00, '2025-07-26 00:21:19', NULL, NULL, NULL, NULL, '{\"nombre\":\"Daniel\",\"direccion\":\"asdasds\",\"ciudad\":\"Capira\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-27 06:39:20'),
(29, 10, 'confirmado', 'yappy', 105.00, 110.00, 5.00, '2025-07-26 15:12:59', NULL, NULL, NULL, NULL, 'Bella Vista, Ciudad de Panamá', 'Envío estándar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 20:12:59'),
(30, 7, 'pendiente', 'yappy', 359.50, NULL, 0.00, '2025-07-26 17:07:50', NULL, NULL, NULL, NULL, '{\"nombre\":\"Luis Mendoza\",\"direccion\":\"Calle principal, Casa 123\",\"ciudad\":\"La Chorrera\",\"telefono\":\"6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 22:07:50'),
(31, 7, 'pendiente', 'tarjeta', 359.50, NULL, 0.00, '2025-07-26 17:48:16', NULL, NULL, NULL, NULL, '{\"nombre\":\"Luis Mendoza\",\"direccion\":\"Calle principal, Casa 123\",\"ciudad\":\"La Chorrera\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-26 22:48:16'),
(32, 7, 'pendiente', 'tarjeta', 54.00, NULL, 0.00, '2025-07-26 23:06:35', NULL, NULL, NULL, NULL, '{\"nombre\":\"Daniel\",\"direccion\":\"adasd\",\"ciudad\":\"Arraij\\u00e1n\",\"telefono\":\"+507 6000-0000\"}', 'Env├¡o est├índar', NULL, '3-5 d├¡as h├íbiles', NULL, NULL, NULL, NULL, '2025-07-27 04:06:35');

-- --------------------------------------------------------

--
-- Table structure for table `pedido_eventos`
--

CREATE TABLE `pedido_eventos` (
  `id_evento` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `tipo` enum('estado_cambio','envio','comunicacion','problema','nota') NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_evento` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pedido_eventos`
--

INSERT INTO `pedido_eventos` (`id_evento`, `id_pedido`, `tipo`, `descripcion`, `fecha_evento`, `id_usuario`) VALUES
(1, 1, 'estado_cambio', 'Pedido recibido y en procesamiento', '2025-07-26 00:27:34', 1),
(2, 2, 'estado_cambio', 'Pedido en preparaci¾n - productos en fabricaci¾n', '2025-07-26 00:27:34', 1),
(3, 2, 'envio', 'Paquete preparado para envÝo', '2025-07-26 00:27:34', 1),
(7, 1, 'estado_cambio', 'Pedido confirmado y enviado al cliente', '2025-07-26 00:30:06', 1),
(8, 1, 'envio', 'Paquete entregado a empresa de envÝo', '2025-07-26 00:30:06', 1),
(9, 2, 'estado_cambio', 'Pedido recibido - preparando productos', '2025-07-26 00:30:06', 1),
(11, 22, 'estado_cambio', 'Estado cambiado a: en_proceso', '2025-07-26 01:34:30', 7),
(12, 23, 'estado_cambio', 'Estado cambiado a: entregado', '2025-07-26 01:37:01', 7),
(13, 23, 'estado_cambio', 'Estado cambiado a: pendiente', '2025-07-26 01:37:21', 7),
(14, 25, 'estado_cambio', 'Estado cambiado a: en_proceso', '2025-07-26 03:50:00', 7),
(15, 23, 'estado_cambio', 'Estado cambiado a: entregado', '2025-07-26 03:57:36', 7),
(16, 28, 'estado_cambio', 'Estado cambiado a: en_proceso', '2025-07-26 19:11:54', 7),
(17, 28, 'estado_cambio', 'Estado cambiado a: cancelado', '2025-07-26 19:12:05', 7),
(18, 28, 'estado_cambio', 'Estado cambiado a: en_proceso', '2025-07-26 19:15:26', 7),
(19, 28, 'estado_cambio', 'Estado cambiado a: pendiente', '2025-07-27 01:17:14', 7),
(20, 28, 'estado_cambio', 'Estado cambiado a: en_proceso', '2025-07-27 04:46:01', 7),
(21, 28, 'estado_cambio', 'Estado cambiado a: cancelado', '2025-07-27 06:39:16', 7),
(22, 28, 'estado_cambio', 'Estado cambiado a: pendiente', '2025-07-27 06:39:20', 7);

-- --------------------------------------------------------

--
-- Table structure for table `pedido_productos`
--

CREATE TABLE `pedido_productos` (
  `id_pedido_producto` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pedido_productos`
--

INSERT INTO `pedido_productos` (`id_pedido_producto`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `descuento`) VALUES
(1, 1, 1, 2, 45.00, 0.00),
(2, 1, 3, 1, 12.00, 0.00),
(3, 2, 2, 1, 35.00, 0.00),
(4, 2, 4, 1, 22.00, 0.00),
(46, 1, 2, 1, 35.00, 0.00),
(47, 2, 1, 1, 45.00, 0.00),
(59, 19, 1, 2, 45.00, 0.00),
(60, 20, 2, 1, 35.00, 0.00),
(61, 20, 3, 1, 12.00, 0.00),
(62, 21, 4, 1, 28.00, 0.00),
(63, 21, 5, 1, 22.00, 0.00),
(64, 22, 3, 1, 11.00, 0.00),
(65, 22, 6, 1, 55.00, 0.00),
(66, 22, 8, 1, 79.00, 0.00),
(67, 23, 8, 1, 79.00, 0.00),
(68, 23, 1, 1, 33.00, 0.00),
(69, 23, 2, 1, 31.50, 0.00),
(70, 23, 3, 1, 11.00, 0.00),
(71, 23, 7, 1, 38.00, 0.00),
(72, 23, 6, 1, 55.00, 0.00),
(73, 23, 5, 1, 19.50, 0.00),
(74, 23, 4, 1, 24.00, 0.00),
(75, 24, 1, 1, 33.00, 0.00),
(76, 25, 11, 1, 221.00, 0.00),
(77, 25, 10, 1, 10.00, 0.00),
(78, 25, 9, 1, 50.00, 0.00),
(79, 26, 11, 1, 221.00, 0.00),
(80, 26, 10, 1, 10.00, 0.00),
(81, 26, 9, 1, 50.00, 0.00),
(82, 27, 11, 1, 221.00, 0.00),
(83, 27, 10, 1, 10.00, 0.00),
(84, 27, 9, 1, 50.00, 0.00),
(85, 28, 11, 1, 221.00, 0.00),
(86, 28, 10, 1, 10.00, 0.00),
(87, 28, 9, 1, 50.00, 0.00),
(88, 29, 13, 1, 25.00, 5.00),
(89, 29, 17, 2, 15.00, 0.00),
(90, 29, 14, 1, 30.00, 0.00),
(91, 30, 1, 2, 33.00, 0.00),
(92, 30, 16, 8, 35.00, 0.00),
(93, 30, 17, 1, 13.50, 0.00),
(94, 31, 1, 2, 33.00, 0.00),
(95, 31, 16, 8, 35.00, 0.00),
(96, 31, 17, 1, 13.50, 0.00),
(97, 32, 1, 6, 9.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `id_tienda` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `imagen` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id_producto`, `id_tienda`, `nombre`, `descripcion`, `precio`, `descuento`, `imagen`, `stock`, `activo`, `fecha_creacion`) VALUES
(1, 1, 'Mola Tradicional', 'Mola auténtica hecha por artesanas gunas con diseños tradicionales', 45.00, 12.00, 'public/productos/prod_687d53cc28804_472775.jpg', 0, 1, '2025-07-17 10:43:51'),
(2, 1, 'Sombrero Pintao', 'Sombrero pintao tejido a mano con fibras naturales', 35.00, 3.50, 'productos/sombrero1.jpg', 4, 1, '2025-07-17 10:43:51'),
(3, 1, 'Pulsera de Tagua', 'Pulsera elaborada con semillas de tagua, diseño único', 12.00, 1.00, 'productos/pulsera1.jpg', 13, 1, '2025-07-17 10:43:51'),
(4, 2, 'Vasija de Barro', 'Vasija decorativa de barro cocido con motivos precolombinos', 28.00, 4.00, 'productos/vasija1.jpg', 11, 1, '2025-07-17 10:43:51'),
(5, 2, 'Plato Decorativo', 'Plato de cerámica pintado a mano con diseños florales', 22.00, 2.50, 'productos/plato1.jpg', 15, 1, '2025-07-17 10:43:51'),
(6, 3, 'Huipil Bordado', 'Huipil tradicional con bordados coloridos hechos a mano', 65.00, 10.00, 'productos/huipil1.jpg', -4, 1, '2025-07-17 10:43:51'),
(7, 3, 'Bolso Tejido', 'Bolso artesanal tejido con fibras naturales y diseños étnicos', 38.00, 0.00, 'productos/bolso1.jpg', 7, 1, '2025-07-17 10:43:51'),
(8, 4, 'Aretes de Tagua', 'Aretes elaborados con semillas de tagua, pintados a mano con diseños naturales', 89.00, 10.00, 'public/productos/prod_687d53cc28804_472775.jpg', 77, 1, '2025-07-20 15:38:36'),
(9, 4, 'Cuadro de Mola', 'Decoración enmarcada con diseño tradicional guna elaborado en tela de mola', 100.00, 50.00, 'public/productos/prod_688431978e85f_test-image.jpg', 46, 1, '2025-07-25 20:38:31'),
(10, 4, 'Bolso Wayuu', 'Bolso tejido a mano con patrones coloridos inspirados en la cultura wayuu', 12.00, 0.00, 'public/productos/prod_688435e006900_test-image.jpg', 1, 1, '2025-07-25 20:56:48'),
(11, 4, 'Cintillo Emberá', 'Cintillo artesanal elaborado con chaquiras por mujeres Emberá', 223.00, 2.00, 'public/productos/prod_68843a3841062_test-image.jpg', 19, 1, '2025-07-25 21:15:20'),
(12, 4, 'Imán de Tagua Pintado', 'Imán decorativo hecho con tagua tallada y pintada con motivos panameños', 123.00, 3.00, 'public/productos/prod_688506510261d_test-image.jpg', 1, 1, '2025-07-26 11:46:09'),
(13, 5, 'Collar de Chaquira', 'Collar tradicional Emberá con cuentas de colores vivos', 25.00, 5.00, 'productos/collar_chaquira.jpg', 20, 1, '2025-07-26 15:12:59'),
(14, 6, 'Canasta Ngäbe', 'Canasta tejida a mano con palma natural', 30.00, 3.00, 'productos/canasta_ngabe.jpg', 12, 1, '2025-07-26 15:12:59'),
(16, 1, 'Pintura sobre Tagua', 'Semilla tallada y pintada con motivos autóctonos panameños', 40.00, 5.00, 'public/productos/tagua.png', 2, 1, '2025-07-26 15:12:59'),
(17, 2, 'Mini Tambor Artesanal', 'Mini tambor típico panameño, decorado a mano', 15.00, 1.50, 'public/productos/tambor.jpg', 28, 1, '2025-07-26 15:12:59'),
(18, 3, 'Pintura de la Pollera', 'Pintura sobre lienzo representando una pollera panameña', 80.00, 8.00, 'public/productos/pollera.jpg', 10, 1, '2025-07-26 15:12:59');

-- --------------------------------------------------------

--
-- Table structure for table `recuperaciones_contrasena`
--

CREATE TABLE `recuperaciones_contrasena` (
  `id_recuperacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiracion` datetime NOT NULL,
  `usada` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sesiones`
--

CREATE TABLE `sesiones` (
  `id_sesion` varchar(255) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `datos` text DEFAULT NULL,
  `ultima_actividad` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiendas`
--

CREATE TABLE `tiendas` (
  `id_tienda` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_tienda` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen_logo` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tiendas`
--

INSERT INTO `tiendas` (`id_tienda`, `id_usuario`, `nombre_tienda`, `descripcion`, `imagen_logo`, `fecha_creacion`) VALUES
(1, 1, 'Artesanías María', 'Hermosas artesanías tradicionales panameñas hechas a mano', 'logos/maria_logo.jpg', '2025-07-17 10:43:51'),
(2, 2, 'Cerámica Carlos', 'Cerámica artesanal de alta calidad inspirada en tradiciones locales', 'logos/carlos_logo.jpg', '2025-07-17 10:43:51'),
(3, 3, 'Textiles Ana', 'Textiles únicos con diseños autóctonos de Panamá', 'logos/ana_logo.jpg', '2025-07-17 10:43:51'),
(4, 7, 'Tienda de Daniel', 'Los mejores productos que puedes encontrar', 'logos/tienda_68858754572a7.jpg', '2025-07-19 22:41:08'),
(5, 8, 'Arte Emberá', 'Joyería y tejidos ancestrales hechos por artesanas Emberá', 'logos/arte_embera_logo.jpg', '2025-07-26 15:12:59'),
(6, 9, 'Canastas Ngäbe', 'Canastas y figuras hechas con palma natural', 'logos/ngabe_logo.jpg', '2025-07-26 15:12:59');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `tipo_usuario` enum('cliente','artesano') NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `telefono`, `direccion`, `contrasena`, `tipo_usuario`, `fecha_registro`, `activo`) VALUES
(1, 'María González', 'maria.artesana@email.com', '6001-2345', 'La Chorrera, Panamá Oeste', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artesano', '2025-07-17 10:43:51', 1),
(2, 'Carlos Mendoza', 'carlos.ceramica@email.com', '6002-3456', 'Arraiján, Panamá Oeste', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artesano', '2025-07-17 10:43:51', 1),
(3, 'Ana Rodríguez', 'ana.textiles@email.com', '6003-4567', 'Capira, Panamá Oeste', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artesano', '2025-07-17 10:43:51', 1),
(4, 'Pedro Jiménez', 'pedro.cliente@email.com', '6004-5678', 'Ciudad de Panamá', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '2025-07-17 10:43:51', 1),
(5, 'Sofía Herrera', 'sofia.cliente@email.com', '6005-6789', 'San Miguelito', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '2025-07-17 10:43:51', 1),
(6, 'Daniel', 'daniel@gmail.com', '+507 6000-0000', '', '$2y$10$vctISzZQiBVlp4OBZwA8ouR2EWw7R7/GRaya4Um6QucQj7tcDE/9q', 'cliente', '2025-07-17 10:44:47', 1),
(7, 'Daniel', 'danielartesano@gmail.com', '+507 6000-0000', '', '$2y$10$2ZJZLG0sv7MK6WK/lOo1bO3PHJyzYHcUp8piEPjit11Ha4igYdPEu', 'artesano', '2025-07-18 12:39:21', 1),
(8, 'Gloria Ortega', 'gloria.embera@email.com', '6008-1111', 'Comarca Emberá-Wounaan', '$2y$10$VYb9TAF3xqNjWuvWMsqxWeCNRkLn1jA9VSMTvq9PDn1ZrdgAWTfSm', 'artesano', '2025-07-26 15:12:59', 1),
(9, 'Luis Vega', 'luis.ngabe@email.com', '6009-2222', 'Comarca Ngäbe-Buglé', '$2y$10$VYb9TAF3xqNjWuvWMsqxWeCNRkLn1jA9VSMTvq9PDn1ZrdgAWTfSm', 'artesano', '2025-07-26 15:12:59', 1),
(10, 'Mónica Pérez', 'monica.cliente@email.com', '6010-3333', 'Bella Vista, Ciudad de Panamá', '$2y$10$VYb9TAF3xqNjWuvWMsqxWeCNRkLn1jA9VSMTvq9PDn1ZrdgAWTfSm', 'cliente', '2025-07-26 15:12:59', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carritos`
--
ALTER TABLE `carritos`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indexes for table `carrito_productos`
--
ALTER TABLE `carrito_productos`
  ADD PRIMARY KEY (`id_carrito_producto`),
  ADD UNIQUE KEY `unique_carrito_producto` (`id_carrito`,`id_producto`),
  ADD KEY `idx_carrito` (`id_carrito`),
  ADD KEY `idx_producto` (`id_producto`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_leida` (`leida`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha` (`fecha_pedido`);

--
-- Indexes for table `pedido_eventos`
--
ALTER TABLE `pedido_eventos`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `idx_pedido` (`id_pedido`),
  ADD KEY `idx_fecha` (`fecha_evento`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD PRIMARY KEY (`id_pedido_producto`),
  ADD KEY `idx_pedido` (`id_pedido`),
  ADD KEY `idx_producto` (`id_producto`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_tienda` (`id_tienda`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_precio` (`precio`);

--
-- Indexes for table `recuperaciones_contrasena`
--
ALTER TABLE `recuperaciones_contrasena`
  ADD PRIMARY KEY (`id_recuperacion`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indexes for table `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_actividad` (`ultima_actividad`);

--
-- Indexes for table `tiendas`
--
ALTER TABLE `tiendas`
  ADD PRIMARY KEY (`id_tienda`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_tipo_usuario` (`tipo_usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carritos`
--
ALTER TABLE `carritos`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `carrito_productos`
--
ALTER TABLE `carrito_productos`
  MODIFY `id_carrito_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `pedido_eventos`
--
ALTER TABLE `pedido_eventos`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pedido_productos`
--
ALTER TABLE `pedido_productos`
  MODIFY `id_pedido_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `recuperaciones_contrasena`
--
ALTER TABLE `recuperaciones_contrasena`
  MODIFY `id_recuperacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiendas`
--
ALTER TABLE `tiendas`
  MODIFY `id_tienda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carritos`
--
ALTER TABLE `carritos`
  ADD CONSTRAINT `carritos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `carrito_productos`
--
ALTER TABLE `carrito_productos`
  ADD CONSTRAINT `carrito_productos_ibfk_1` FOREIGN KEY (`id_carrito`) REFERENCES `carritos` (`id_carrito`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_productos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Constraints for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `pedido_eventos`
--
ALTER TABLE `pedido_eventos`
  ADD CONSTRAINT `pedido_eventos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_eventos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Constraints for table `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD CONSTRAINT `pedido_productos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_productos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_tienda`) REFERENCES `tiendas` (`id_tienda`) ON DELETE CASCADE;

--
-- Constraints for table `recuperaciones_contrasena`
--
ALTER TABLE `recuperaciones_contrasena`
  ADD CONSTRAINT `recuperaciones_contrasena_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Constraints for table `tiendas`
--
ALTER TABLE `tiendas`
  ADD CONSTRAINT `tiendas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
