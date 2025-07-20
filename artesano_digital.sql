-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2025 at 04:50 AM
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
(2, 5, '2025-07-17 10:43:51');

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
(4, 2, 4, 1, '2025-07-17 10:43:51');

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
(4, 1, 'stock_bajo', 'El producto \"Sombrero Pintao\" tiene stock bajo (8 unidades)', 0, '2025-07-17 10:43:51');

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` enum('pendiente','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `fecha_pedido` datetime DEFAULT current_timestamp(),
  `direccion_envio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `estado`, `metodo_pago`, `total`, `descuento`, `fecha_pedido`, `direccion_envio`) VALUES
(1, 4, 'enviado', 'tarjeta_credito', 102.00, 0.00, '2025-07-17 10:43:51', 'Calle 50, Ciudad de Panamá'),
(2, 5, 'pendiente', 'yappy', 57.00, 0.00, '2025-07-17 10:43:51', 'Vía España, San Miguelito'),
(3, 6, 'pendiente', 'yappy', 0.00, 0.00, '2025-07-17 11:42:40', '{\"nombre\":\"Daniel\",\"direccion\":\"dasdasd\",\"ciudad\":\"La Chorrera\",\"telefono\":\"+507 6000-0000\"}');

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
(4, 2, 4, 1, 22.00, 0.00);

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
(1, 1, 'Mola Tradicional', 'Mola auténtica hecha por artesanas gunas con diseños tradicionales', 45.00, 12.00, 'productos/mola1.jpg', 15, 1, '2025-07-17 10:43:51'),
(2, 1, 'Sombrero Pintao', 'Sombrero pintao tejido a mano con fibras naturales', 35.00, 3.50, 'productos/sombrero1.jpg', 8, 1, '2025-07-17 10:43:51'),
(3, 1, 'Pulsera de Tagua', 'Pulsera elaborada con semillas de tagua, diseño único', 12.00, 1.00, 'productos/pulsera1.jpg', 25, 1, '2025-07-17 10:43:51'),
(4, 2, 'Vasija de Barro', 'Vasija decorativa de barro cocido con motivos precolombinos', 28.00, 4.00, 'productos/vasija1.jpg', 12, 1, '2025-07-17 10:43:51'),
(5, 2, 'Plato Decorativo', 'Plato de cerámica pintado a mano con diseños florales', 22.00, 2.50, 'productos/plato1.jpg', 18, 1, '2025-07-17 10:43:51'),
(6, 3, 'Huipil Bordado', 'Huipil tradicional con bordados coloridos hechos a mano', 65.00, 10.00, 'productos/huipil1.jpg', 6, 1, '2025-07-17 10:43:51'),
(7, 3, 'Bolso Tejido', 'Bolso artesanal tejido con fibras naturales y diseños étnicos', 38.00, 0.00, 'productos/bolso1.jpg', 10, 1, '2025-07-17 10:43:51');

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
(3, 3, 'Textiles Ana', 'Textiles únicos con diseños autóctonos de Panamá', 'logos/ana_logo.jpg', '2025-07-17 10:43:51');

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
(7, 'Daniel', 'danielartesano@gmail.com', '+507 6000-0000', '', '$2y$10$2ZJZLG0sv7MK6WK/lOo1bO3PHJyzYHcUp8piEPjit11Ha4igYdPEu', 'artesano', '2025-07-18 12:39:21', 1);

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
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `carrito_productos`
--
ALTER TABLE `carrito_productos`
  MODIFY `id_carrito_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pedido_productos`
--
ALTER TABLE `pedido_productos`
  MODIFY `id_pedido_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `recuperaciones_contrasena`
--
ALTER TABLE `recuperaciones_contrasena`
  MODIFY `id_recuperacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiendas`
--
ALTER TABLE `tiendas`
  MODIFY `id_tienda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
