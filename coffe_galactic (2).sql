-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-06-2025 a las 23:29:20
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
-- Base de datos: `coffe_galactic`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
(1, 9, 15, 1, 12000.00),
(2, 10, 14, 1, 20000.00),
(3, 11, 16, 1, 30000.00),
(4, 12, 9, 4, 20000.00),
(5, 13, 15, 1, 12000.00),
(6, 14, 16, 1, 30000.00),
(7, 15, 15, 1, 12000.00),
(8, 16, 16, 1, 30000.00),
(9, 17, 15, 1, 12000.00),
(10, 18, 16, 1, 30000.00),
(11, 19, 15, 1, 12000.00),
(12, 20, 15, 1, 12000.00),
(13, 21, 16, 1, 30000.00),
(14, 22, 16, 1, 30000.00),
(15, 23, 16, 1, 30000.00),
(16, 24, 16, 1, 30000.00),
(17, 25, 16, 1, 30000.00),
(18, 26, 17, 1, 22900.00),
(19, 27, 15, 1, 12000.00),
(20, 28, 15, 3, 12000.00),
(21, 29, 15, 1, 12000.00),
(22, 30, 16, 3, 30000.00),
(23, 31, 16, 1, 30000.00),
(24, 32, 17, 1, 22900.00),
(25, 33, 11, 2, 15000.00),
(26, 34, 16, 1, 30000.00),
(27, 35, 15, 3, 12000.00),
(28, 36, 16, 1, 30000.00),
(29, 37, 15, 1, 12000.00),
(30, 38, 16, 1, 30000.00),
(31, 39, 16, 1, 30000.00),
(32, 40, 17, 1, 22900.00),
(33, 41, 17, 1, 22900.00),
(34, 42, 15, 1, 12000.00),
(35, 43, 16, 1, 30000.00),
(36, 44, 15, 1, 12000.00),
(37, 45, 16, 1, 30000.00),
(38, 46, 15, 1, 12000.00),
(39, 47, 14, 1, 20000.00),
(40, 48, 15, 1, 12000.00),
(41, 49, 15, 1, 12000.00),
(42, 49, 14, 1, 20000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `numero_factura` varchar(20) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','completado','anulado') NOT NULL DEFAULT 'pendiente',
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','enviado','entregado') DEFAULT 'pendiente',
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `total`, `fecha_pedido`, `estado`, `fecha`) VALUES
(9, 24, 12000.00, '2025-06-17 22:13:51', 'pendiente', '2025-06-19 15:23:26'),
(10, 24, 20000.00, '2025-06-17 22:17:32', 'pendiente', '2025-06-19 15:23:26'),
(11, 10, 30000.00, '2025-06-19 20:27:15', 'pendiente', '2025-06-19 15:27:15'),
(12, 10, 80000.00, '2025-06-19 20:28:46', 'pendiente', '2025-06-19 15:28:46'),
(13, 10, 12000.00, '2025-06-19 21:06:22', 'pendiente', '2025-06-19 16:06:22'),
(14, 10, 30000.00, '2025-06-19 21:12:27', 'pendiente', '2025-06-19 16:12:27'),
(15, 10, 12000.00, '2025-06-19 21:20:01', '', '2025-06-19 16:20:01'),
(16, 10, 30000.00, '2025-06-19 21:27:02', '', '2025-06-19 16:27:02'),
(17, 10, 12000.00, '2025-06-19 21:28:40', '', '2025-06-19 16:28:40'),
(18, 10, 30000.00, '2025-06-19 21:44:34', '', '2025-06-19 16:44:34'),
(19, 10, 12000.00, '2025-06-19 21:50:05', '', '2025-06-19 16:50:05'),
(20, 10, 12000.00, '2025-06-19 22:00:37', '', '2025-06-19 17:00:37'),
(21, 10, 30000.00, '2025-06-19 22:02:02', '', '2025-06-19 17:02:02'),
(22, 10, 30000.00, '2025-06-19 22:04:12', '', '2025-06-19 17:04:12'),
(23, 10, 30000.00, '2025-06-20 19:25:51', '', '2025-06-20 14:25:51'),
(24, 10, 30000.00, '2025-06-20 19:30:34', '', '2025-06-20 14:30:34'),
(25, 10, 30000.00, '2025-06-20 19:40:41', '', '2025-06-20 14:40:41'),
(26, 10, 22900.00, '2025-06-20 19:46:09', '', '2025-06-20 14:46:09'),
(27, 10, 12000.00, '2025-06-20 19:48:55', '', '2025-06-20 14:48:55'),
(28, 10, 36000.00, '2025-06-20 19:49:32', '', '2025-06-20 14:49:32'),
(29, 10, 12000.00, '2025-06-20 19:52:19', '', '2025-06-20 14:52:19'),
(30, 10, 90000.00, '2025-06-20 19:55:34', '', '2025-06-20 14:55:34'),
(31, 10, 30000.00, '2025-06-20 20:03:03', '', '2025-06-20 15:03:03'),
(32, 10, 22900.00, '2025-06-20 20:10:41', '', '2025-06-20 15:10:41'),
(33, 10, 30000.00, '2025-06-20 20:17:13', '', '2025-06-20 15:17:13'),
(34, 10, 30000.00, '2025-06-20 20:18:25', '', '2025-06-20 15:18:25'),
(35, 10, 36000.00, '2025-06-20 20:25:22', '', '2025-06-20 15:25:22'),
(36, 10, 30000.00, '2025-06-20 20:35:31', '', '2025-06-20 15:35:31'),
(37, 10, 12000.00, '2025-06-20 20:42:24', '', '2025-06-20 15:42:24'),
(38, 10, 30000.00, '2025-06-20 20:52:39', '', '2025-06-20 15:52:39'),
(39, 10, 30000.00, '2025-06-20 20:53:15', '', '2025-06-20 15:53:15'),
(40, 10, 22900.00, '2025-06-20 20:56:27', '', '2025-06-20 15:56:27'),
(41, 10, 22900.00, '2025-06-20 21:00:26', '', '2025-06-20 16:00:26'),
(42, 10, 12000.00, '2025-06-20 21:02:12', '', '2025-06-20 16:02:12'),
(43, 10, 30000.00, '2025-06-20 21:04:16', '', '2025-06-20 16:04:16'),
(44, 10, 12000.00, '2025-06-20 21:06:38', '', '2025-06-20 16:06:38'),
(45, 10, 30000.00, '2025-06-20 21:09:04', '', '2025-06-20 16:09:04'),
(46, 10, 12000.00, '2025-06-20 21:15:43', '', '2025-06-20 16:15:43'),
(47, 10, 20000.00, '2025-06-20 21:16:50', '', '2025-06-20 16:16:50'),
(48, 10, 12000.00, '2025-06-20 21:23:01', '', '2025-06-20 16:23:01'),
(49, 10, 32000.00, '2025-06-20 21:24:27', '', '2025-06-20 16:24:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  `categoria_id` int(10) UNSIGNED DEFAULT NULL,
  `categoria` varchar(255) NOT NULL DEFAULT '',
  `descuento` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `stock`, `fecha_agregado`, `categoria_id`, `categoria`, `descuento`) VALUES
(9, 'cafe ingles', 'chevere', 20000.00, 'uploads/30e8cf490f3c64167ecf453685f8be19.jpg', 0, '2025-05-23 22:49:23', NULL, 'Estilo Ciudad', 2),
(10, 'caffe viena', 'El punto de encuentro.', 4888.00, 'uploads/df3142793c0043d03594d90c883d1f9f.jpg', 0, '2025-05-23 23:15:05', NULL, 'Estilo Ciudad', 6),
(11, 'caffe aurora', 'Frescura en cada mezcla.', 15000.00, 'uploads/37d5f5a385ee719ce3a15bb616912cfa.png', 0, '2025-05-23 23:39:25', NULL, 'Naturales', 6),
(12, 'caffe liqueurs', 'Café directo del origen.', 20000.00, 'uploads/99f0ebf67137b8b342e4802012461c8d.jpg', 0, '2025-05-24 00:01:18', NULL, 'Naturales', 8),
(13, 'caffe lungo', 'Conexión natural.', 23000.00, 'uploads/3d0d32b107b3be3778436d8327e36b0a.jpg', 0, '2025-05-24 16:37:36', NULL, 'Naturales', 1),
(14, 'cafe latte machchiato', 'Donde las ideas fluyen.', 20000.00, 'uploads/aee6678ae294299bc32ffcc0b6b807be.jpg', 0, '2025-05-27 16:08:55', NULL, 'Creativos', 5),
(15, 'caffe helado', 'Energía al instante.', 12000.00, 'uploads/05fc1e7837aa3e84f275ea297707439b.jpg', 0, '2025-05-27 16:09:38', NULL, 'Creativos', 3),
(16, 'caffe irish', 'Café con actitud.', 30000.00, 'uploads/5ccdaca84a15255bffcc0a1058df6960.jpg', 0, '2025-05-27 16:10:43', NULL, 'Creativos', 4),
(17, 'caffe ristretto', 'Sabor premium en cada sorbo.', 22900.00, 'uploads/6e5456d835b2314bbac29526dd721025.jpg', 0, '2025-05-27 16:11:28', NULL, 'Clásicos', 9),
(19, 'Caffe galao', 'Un amanecer en cada taza.', 14000.00, 'uploads/75f76191441f7cacdd1953a65c7a0563.jpg', 0, '2025-06-16 14:27:54', NULL, 'Clásicos', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `fecha_registro`, `rol`) VALUES
(1, 'Prueba', 'correo@ejemplo.com', '$2y$10$KIX/HZ4QoM9HVn7mEy/zheC6m7fHqJDm.r9cFJ5Xy1VEXCpNOZyUm', '2025-03-24 20:58:38', 'user'),
(2, 'Prueba', 'prueba@example.com', '123456', '2025-03-24 21:34:26', 'user'),
(3, 'esneyder', 'esneyder@gmail.com', '$2y$10$9o.HdZvH22tfS1SETn.F/.dq40mm1dwgQfaRXQjOm3taG5eBYkGte', '2025-03-26 16:07:37', 'admin'),
(4, 'esneyder', 'corderoesneyder6@gmail.com', '$2y$10$4us7tQhsGwC4sC8BRE5S6OeYoMHwFdBc4H2YEvkW9GwpZt1kSBLZC', '2025-03-28 17:01:27', 'user'),
(5, 'ingrid', 'ingrid@gmail.com', '$2y$10$iBJsMz7KsL2fLSiD5JVu6euDeeQMiCpMQaTu55UaqbW/Pu5P6TuS6', '2025-03-28 17:05:54', 'user'),
(6, 'keiner', 'keiner1@gmail.com', '$2y$10$cAA6tjE/RxEn.C0Z2bTfHuw9YuwIKJZ5zScTy0owNipNKnHBinUYy', '2025-03-28 17:42:17', 'user'),
(7, 'ever', 'ever@gmail.com', '$2y$10$VjK5nTteTPSHEsCoMruKB.sHUL78A6CnfevMKm62LaeMmMiiYWpea', '2025-03-31 15:32:54', 'user'),
(8, 'jhon', 'jhon@gmail.com', '$2y$10$UGzLn6nTZSD.IjD0fn7iweI6r8WCFOYQRWBOuhcvSb/KOhbUGiJT6', '2025-04-01 15:34:44', 'user'),
(9, 'arnulfo', 'arnulfo@gmail.com', '$2y$10$6pVcDhBgSO7j8akNsn8U0e6OhNgGMVC9wVxY5nHmpXPbg8cWNSl0W', '2025-04-03 15:12:17', 'user'),
(10, 'andres', 'andres@example.com', '$2y$10$sf8e7PVvEvvpuNvmjSsPgetjIVyaf4KVPAPQbw4JkzfY9lltAvlV6', '2025-04-03 18:36:21', 'admin'),
(11, 'mateo', 'wendyvanesa004@gmail.com', '$2y$10$1DWv0XhB3EQJ4CKeayMBRumUUfgsEbdWgeE3YWuLCFBSFHAjfHGbm', '2025-04-11 20:36:33', 'user'),
(12, 'mateo', 'cbatistacortina@gmail.com', '$2y$10$TRPDiur9kJR/Y/HixK1og.7IT2FVNyvRW3EqxX9VmSMpgJF/juJKO', '2025-04-11 20:37:51', 'user'),
(22, 'oscarito', 'oscarito@gmail.com', '$2y$10$qV5eUXYyUkhyWQ1adPylSObLJrT0l0dQa9/mkcBIV.KqO/9URghni', '2025-06-16 15:16:57', 'user'),
(23, 'cesar', 'cesar@example.com', '$2y$10$t8fgMs3y55ptF4z1vbj5ZOH5ed/8kzBQWpZi4.Un3TtNA/UYpifp.', '2025-06-17 19:07:51', 'admin'),
(24, 'elkin', 'elkin@example.com', '$2y$10$JyK1Ui6AieZezQQf793H/O9jG17HLFeCeoksofzgORgo7dYuTOsPK', '2025-06-17 21:49:31', 'user'),
(25, 'jorge', 'jorgetorres@mail.com', '$2y$10$apMBKkzDwrQKLapxGLFVcu55txpT4ocuwAPm7y4agW3xcMiKJtcam', '2025-06-19 19:18:12', 'user');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detalle_pedido` (`pedido_id`),
  ADD KEY `fk_detalle_producto` (`producto_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pedido_usuario` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `facturas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
