SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `tecnostock`
--

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'Periféricos', 'Dispositivos de entrada y salida para computadores.', 'A'),
(2, 'Almacenamiento', 'Dispositivos para guardar y transportar información.', 'A'),
(3, 'Audio', 'Productos relacionados con sonido y comunicación.', 'A'),
(4, 'Conectividad', 'Accesorios y dispositivos para conexiones de red y datos.', 'A'),
(5, 'Energía', 'Cargadores, adaptadores y accesorios de alimentación.', 'A');

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `correo`, `clave_hash`, `estado`) VALUES
(1, 'Pedro', 'pedro@tecnostock.cl', '$2y$10$x4uvprdoCNXi5/dgXm52LeeQxKrkZI8hQxvfIGLiaOulACOQBp4ka', 'A'),
(2, 'Ana', 'ana@tecnostock.cl', '$2y$10$y6/iYJYbhlcL1xkV8OFf3O7HrhgpX73.cU.DUYeL444oyXWQPpR2e', 'A'),
(3, 'Carlos', 'carlos@tecnostock.cl', '$2y$10$cq/1LpRK3A.YsLfjCf/KB.sAJBfxBN.WZvo.v.5SC0t/EyykkvZAu', 'A');
COMMIT;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `codigo`, `nombre`, `descripcion`, `precio`, `stock_actual`, `stock_minimo`, `estado`) VALUES
(1, 'TEC-MOU-001', 'Mouse inalámbrico', 'Mouse óptico inalámbrico USB, color negro.', 8990.00, 25, 5, 'A'),
(2, 'TEC-TEC-001', 'Teclado mecánico', 'Teclado mecánico USB con distribución español.', 29990.00, 12, 3, 'A'),
(3, 'TEC-AUD-001', 'Audífonos USB', 'Audífonos con micrófono y conexión USB.', 15990.00, 4, 5, 'A'),
(4, 'TEC-USB-001', 'Memoria USB 64 GB', 'Memoria USB 3.0 de 64 GB.', 7990.00, 32, 10, 'A'),
(5, 'TEC-WEB-001', 'Webcam HD', 'Cámara web HD con micrófono integrado.', 18990.00, 7, 3, 'A'),
(6, 'TEC-CAR-001', 'Cargador USB-C', 'Cargador de pared USB-C de 30 W.', 12990.00, 2, 5, 'A');

--
-- Volcado de datos para la tabla `producto_categoria`
--

INSERT INTO `producto_categoria` (`id_producto`, `id_categoria`) VALUES
(1, 1),
(1, 4),
(1, 5),
(2, 1),
(3, 3),
(4, 2),
(4, 4),
(5, 1),
(5, 4),
(6, 5);

--
-- Volcado de datos para la tabla `movimiento`
--

INSERT INTO `movimiento` (`id_movimiento`, `fecha_hora`, `tipo`, `cantidad`, `observacion`, `id_usuario`, `id_producto`) VALUES
(1, '2026-11-01 12:00:00', 'ENTRADA', 30, 'Ingreso inicial de mouse inalámbricos.', 2, 1),
(2, '2026-11-03 14:30:00', 'SALIDA', 5, 'Salida por venta de unidades.', 3, 1),
(3, '2026-11-01 12:15:00', 'ENTRADA', 15, 'Ingreso inicial de teclados mecánicos.', 1, 2),
(4, '2026-11-04 15:00:00', 'SALIDA', 3, 'Salida por venta de unidades.', 1, 2),
(5, '2026-11-02 13:00:00', 'ENTRADA', 8, 'Ingreso inicial de audífonos USB.', 3, 3),
(6, '2026-11-05 18:00:00', 'SALIDA', 4, 'Salida por venta de unidades.', 2, 3),
(7, '2026-11-02 13:30:00', 'ENTRADA', 40, 'Ingreso inicial de memorias USB.', 2, 4),
(8, '2026-11-06 19:00:00', 'SALIDA', 8, 'Salida por venta de unidades.', 3, 4),
(9, '2026-11-03 11:45:00', 'ENTRADA', 10, 'Ingreso inicial de webcams.', 2, 5),
(10, '2026-11-07 17:20:00', 'SALIDA', 3, 'Salida por venta de unidades.', 1, 5),
(11, '2026-11-03 12:10:00', 'ENTRADA', 6, 'Ingreso inicial de cargadores USB-C.', 1, 6),
(12, '2026-11-08 20:00:00', 'SALIDA', 4, 'Salida por venta de unidades.', 3, 6);