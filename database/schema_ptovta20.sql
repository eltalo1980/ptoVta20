-- ========================================================================
-- SISTEMA PTOVTA20: SCRIPT DE CREACIÓN DE BASE DE DATOS Y TABLAS (MYSQL)
-- Compatible con MySQL 5.7+, MySQL 8.0+ y MariaDB 10.3+
-- Arquitectura: Multi-tenant (Múltiples usuarios y locales aislados por id_local)
-- Prefijo de Tablas: tbl_ptovta20_
-- ========================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------
-- 1. TABLA DE LOCALES / SUCURSALES (TENANTS)
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_locales`;
CREATE TABLE `tbl_ptovta20_locales` (
    `id_local` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre_local` VARCHAR(150) NOT NULL COMMENT 'Nombre de la sucursal o negocio',
    `rut_empresa` VARCHAR(20) NULL COMMENT 'RUT/NIT tributario del local',
    `direccion` VARCHAR(255) NULL,
    `telefono` VARCHAR(50) NULL,
    `email_contacto` VARCHAR(150) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activo, 0: Inactivo/Suspendido',
    `fecha_expiracion` DATE NULL COMMENT 'Fecha límite del plan contratado',
    `valor_plan` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Valor mensual del servicio',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 2. TABLA DE USUARIOS DEL SISTEMA
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_usuarios`;
CREATE TABLE `tbl_ptovta20_usuarios` (
    `id_usuario` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL COMMENT 'Local al que pertenece el usuario',
    `nombres` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NULL,
    `rut` VARCHAR(20) NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(50) NULL,
    `nivel` INT NOT NULL DEFAULT 1 COMMENT '1: Vendedor/Operador, 10: Cajero/Admin Local, 100: Superadmin Global',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_expiracion` DATE NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_usuario_local` (`id_local`),
    INDEX `idx_usuario_nivel` (`nivel`),
    CONSTRAINT `fk_usuarios_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 3. TABLA DE CONFIGURACIÓN Y PARÁMETROS POR LOCAL
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_configuracion`;
CREATE TABLE `tbl_ptovta20_configuracion` (
    `idConfiguracion` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `idLocal` INT UNSIGNED NOT NULL,
    `categoria` VARCHAR(100) NOT NULL COMMENT 'Clave de configuración (ej: valorIVA, cajaPagadora)',
    `valor` TEXT NULL COMMENT 'Valor configurado',
    `descripcion` VARCHAR(255) NULL,
    `tipoValores` VARCHAR(50) NOT NULL DEFAULT 'texto' COMMENT 'texto, numero, combo',
    `nivel` INT NOT NULL DEFAULT 10 COMMENT 'Nivel mínimo requerido para editar',
    INDEX `idx_config_local_cat` (`idLocal`, `categoria`),
    CONSTRAINT `fk_config_local` FOREIGN KEY (`idLocal`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 4. MAESTRO DE INVENTARIO Y PRODUCTOS POR LOCAL
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_stock`;
CREATE TABLE `tbl_ptovta20_stock` (
    `id_producto` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(50) NOT NULL COMMENT 'Código de barra o identificador del producto',
    `descripcion` VARCHAR(255) NOT NULL,
    `empresa` VARCHAR(100) NULL COMMENT 'Proveedor / Marca',
    `precio_neto` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `precio_costo` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000 COMMENT 'Stock disponible (unidades o kilos)',
    `cantidad_minima` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000 COMMENT 'Alerta de stock crítico',
    `cantidad_venta_mayor` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000 COMMENT 'Umbral para precio por mayor',
    `precio_venta_mayor` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Precio unitario aplicado por mayor',
    `venta_por_unidad` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Por unidad, 0: Por peso/balanza',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_stock_local_codigo` (`id_local`, `codigo`),
    INDEX `idx_stock_local_empresa` (`id_local`, `empresa`),
    INDEX `idx_stock_activo` (`activo`),
    CONSTRAINT `fk_stock_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 5. COMBOS Y PACKS PROMOCIONALES POR LOCAL
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_stock_pack`;
CREATE TABLE `tbl_ptovta20_stock_pack` (
    `id_pack` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `codigo_pack` VARCHAR(50) NOT NULL,
    `codigo` VARCHAR(50) NOT NULL DEFAULT '0' COMMENT '0: Cabecera del pack, !=0: Producto que lo compone',
    `descripcion` VARCHAR(255) NOT NULL,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `cantidad_minima` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_pack_local_cod` (`id_local`, `codigo_pack`),
    CONSTRAINT `fk_pack_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 6. LISTA DE REPOSICIÓN Y COMPRAS PENDIENTES POR LOCAL
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_stock_pendiente`;
CREATE TABLE `tbl_ptovta20_stock_pendiente` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_producto` INT UNSIGNED NULL,
    `id_local` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `empresa` VARCHAR(100) NULL,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `precio_costo` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pendiente_local_emp` (`id_local`, `empresa`),
    CONSTRAINT `fk_pendiente_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 7. CABECERA DE VENTAS CERRADAS (HISTÓRICO)
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_ventas_total`;
CREATE TABLE `tbl_ptovta20_ventas_total` (
    `id_ventas` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL COMMENT 'Vendedor o cajero que cerró la venta',
    `monto_sencillo` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Monto entregado por el cliente en efectivo',
    `total_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Monto neto final cobrado',
    `forma_pago` INT NOT NULL COMMENT '1: Tarjeta, 2: Efectivo, 3: Transferencia, 4: Venta Interna',
    `vuleto` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Vuelto entregado al cliente',
    `fecha_venta` DATETIME NOT NULL,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_vtastotal_local_fecha` (`id_local`, `fecha_venta`),
    INDEX `idx_vtastotal_usuario` (`id_usuario`),
    CONSTRAINT `fk_vtastotal_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE,
    CONSTRAINT `fk_vtastotal_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_ptovta20_usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 8. DETALLE DE ÍTEMS VENDIDOS
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_ventas`;
CREATE TABLE `tbl_ptovta20_ventas` (
    `id_ventas` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_ventas_total` INT UNSIGNED NOT NULL,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(50) NOT NULL,
    `empresa` VARCHAR(100) NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `fecha_venta` DATETIME NOT NULL,
    `forma_pago` INT NOT NULL,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_ventas_total_ref` (`id_ventas_total`),
    INDEX `idx_ventas_local_fecha` (`id_local`, `fecha_venta`),
    INDEX `idx_ventas_codigo` (`codigo`),
    CONSTRAINT `fk_ventas_cabecera` FOREIGN KEY (`id_ventas_total`) REFERENCES `tbl_ptovta20_ventas_total` (`id_ventas`) ON DELETE CASCADE,
    CONSTRAINT `fk_ventas_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 9. CARRITO DE VENTA TEMPORAL (EN CURSO / APARCADAS)
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_ventas_detalle_tmp`;
CREATE TABLE `tbl_ptovta20_ventas_detalle_tmp` (
    `id_ventas` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `id_producto` INT UNSIGNED NULL,
    `codigo` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `empresa` VARCHAR(100) NULL,
    `fecha_venta` DATETIME NOT NULL,
    `forma_pago` INT NOT NULL DEFAULT 2,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `stock` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
    `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `estado_caja` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Activa vendedor, 1: Aparcada en Caja',
    `folio_caja` VARCHAR(50) NULL COMMENT 'Folio único de venta aparcada (ej: F1-1723456789)',
    `tipo_pago` VARCHAR(50) NULL COMMENT 'codBarraEfectivo, codBarraTarjeta',
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_tmppos_local_usr` (`id_local`, `id_usuario`),
    INDEX `idx_tmppos_folio` (`folio_caja`),
    CONSTRAINT `fk_tmppos_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 10. AUDITORÍA DE VENTAS TEMPORALES BORRADAS / CANCELADAS
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_ventas_detalle_tmp_borrada`;
CREATE TABLE `tbl_ptovta20_ventas_detalle_tmp_borrada` (
    `id_ventas` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `id_producto` INT UNSIGNED NULL,
    `codigo` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `empresa` VARCHAR(100) NULL,
    `fecha_venta` DATETIME NOT NULL,
    `forma_pago` INT NOT NULL DEFAULT 2,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `stock` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
    `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_tmpborrada_local_fecha` (`id_local`, `fecha_venta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 11. CARRITO DE DEVOLUCIONES TEMPORAL
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_devolucion_detalle_tmp`;
CREATE TABLE `tbl_ptovta20_devolucion_detalle_tmp` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(50) NOT NULL,
    `empresa` VARCHAR(100) NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `fecha_venta` DATETIME NOT NULL,
    `forma_pago` INT NOT NULL DEFAULT 2,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `stock` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
    `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    INDEX `idx_devtmp_local_usr` (`id_local`, `id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 12. HISTÓRICO DE DEVOLUCIONES PROCESADAS
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_devolucion_detalle_final`;
CREATE TABLE `tbl_ptovta20_devolucion_detalle_final` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(50) NOT NULL,
    `empresa` VARCHAR(100) NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `fecha_venta` DATETIME NOT NULL,
    `forma_pago` INT NOT NULL DEFAULT 2,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `stock` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
    `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    INDEX `idx_devfinal_local_usr` (`id_local`, `id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 13. BALANCE DIARIO Y ARQUEO DE CAJA
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_caja`;
CREATE TABLE `tbl_ptovta20_caja` (
    `id_caja` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `fecha_movimiento` DATE NOT NULL,
    `monto` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `fecha_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_caja_local_fecha` (`id_local`, `fecha_movimiento`),
    CONSTRAINT `fk_caja_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 14. REGISTRO DE PAGOS DE FACTURAS / COMPRAS A PROVEEDORES
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_facturas`;
CREATE TABLE `tbl_ptovta20_facturas` (
    `id_factura` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `empresa` VARCHAR(100) NOT NULL,
    `factura_monto` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `fecha_pago` DATE NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_facturas_local_fecha` (`id_local`, `fecha_pago`),
    CONSTRAINT `fk_facturas_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 15. KIOSCO / VISOR DE CONSULTA DE PRECIOS
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_consulta_precio`;
CREATE TABLE `tbl_ptovta20_consulta_precio` (
    `id_ventas` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `precio_venta` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `empresa` VARCHAR(100) NULL,
    `stock` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
    `fecha_venta` DATETIME NOT NULL,
    `cantidad` DECIMAL(12, 4) NOT NULL DEFAULT 1.0000,
    `sub_total` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'CONSULTA',
    INDEX `idx_consulta_local_usr` (`id_local`, `id_usuario`),
    CONSTRAINT `fk_consulta_local` FOREIGN KEY (`id_local`) REFERENCES `tbl_ptovta20_locales` (`id_local`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 16. LOG DE AUDITORÍA Y COLA DE SINCRONIZACIÓN
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_ptovta20_log`;
CREATE TABLE `tbl_ptovta20_log` (
    `id_log` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_local` INT UNSIGNED NOT NULL,
    `id_usuario` INT UNSIGNED NULL,
    `fecha` DATETIME NOT NULL,
    `descripcion` TEXT NOT NULL,
    `ultima_actualizacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `actualizacion_estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    INDEX `idx_log_local_fecha` (`id_local`, `fecha`),
    INDEX `idx_log_estado` (`actualizacion_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 17. CATÁLOGO GLOBAL COMPARTIDO DE PRODUCTOS (EAN MASTER)
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_master_productos`;
CREATE TABLE `tbl_master_productos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(50) NOT NULL UNIQUE,
    `descripcion` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 18. CATÁLOGO DE FORMAS DE PAGO
-- ------------------------------------------------------------------------
DROP TABLE IF EXISTS `tbl_master_pagos`;
CREATE TABLE `tbl_master_pagos` (
    `id_pago` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- DATOS SEMILLA INICIALES (SEED DATA)
-- ========================================================================

-- 1. Insertar Formas de Pago
INSERT INTO `tbl_master_pagos` (`id_pago`, `nombre`, `activo`) VALUES
(1, 'Tarjeta', 1),
(2, 'Efectivo', 1),
(3, 'Transferencia', 1),
(4, 'Venta Interna', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- 2. Insertar Local Inicial de Prueba (Local ID = 10)
INSERT INTO `tbl_ptovta20_locales` (`id_local`, `nombre_local`, `rut_empresa`, `direccion`, `telefono`, `email_contacto`, `activo`, `fecha_expiracion`, `valor_plan`)
VALUES (10, 'Local Principal Demo (Local 10)', '76.123.456-7', 'Av. Central #1234', '+56912345678', 'admin@ptovta20.com', 1, '2099-12-31', 0.00)
ON DUPLICATE KEY UPDATE `nombre_local` = VALUES(`nombre_local`);

-- 3. Insertar Usuario Superadministrador Inicial asignado al Local 10 (Password: 123456)
-- Hash bcrypt para '123456': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `tbl_ptovta20_usuarios` (`id_usuario`, `id_local`, `nombres`, `apellidos`, `rut`, `email`, `password`, `telefono`, `nivel`, `activo`, `fecha_expiracion`)
VALUES (1, 10, 'Super', 'Admin', '11.111.111-1', 'admin@ptovta20.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+56912345678', 100, 1, '2099-12-31')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

-- 4. Insertar Configuraciones Base para el Local 10
INSERT INTO `tbl_ptovta20_configuracion` (`idLocal`, `categoria`, `valor`, `descripcion`, `tipoValores`, `nivel`) VALUES
(10, 'valorIVA', '19', 'Porcentaje IVA aplicado', 'numero', 10),
(10, 'porcentajeGanacia', '30', 'Porcentaje de Ganancia Sugerido', 'numero', 10),
(10, 'cajaPagadora', '0', 'Habilitar separación de Vendedor y Caja Pagadora (0=No, 1=Sí)', 'combo', 10),
(10, 'dineroInicialCaja', '0', 'Monto inicial diario de caja', 'numero', 10),
(10, 'codBarraEfectivo', '9999001', 'Código de barras de acción rápida para Pago Efectivo', 'texto', 10),
(10, 'codBarraTarjeta', '9999002', 'Código de barras de acción rápida para Pago Tarjeta', 'texto', 10),
(10, 'divisorPeso', '1', 'Divisor de balanza pesable (1 = Kilos, 1000 = Gramos)', 'numero', 10),
(10, 'consultaPrecio', '1', 'Habilitar módulo Kiosco Consulta Precios', 'combo', 10),
(10, 'tiempoVerConsultaPrecio', '3', 'Segundos de visualización en pantalla de consulta', 'numero', 10),
(10, 'verResumenCaja', '1', 'Mostrar saldo en caja tras procesar pago', 'combo', 10),
(10, 'buttonSize', 'grande', 'Tamaño de botones de venta táctil', 'texto', 10),
(10, 'textSize', 'h1', 'Tamaño de tipografía de venta táctil', 'texto', 10);

-- 5. Inicializar Registro de Caja para el Local 10
INSERT INTO `tbl_ptovta20_caja` (`id_local`, `fecha_movimiento`, `monto`, `ultima_actualizacion`, `actualizacion_estado`)
VALUES (10, CURDATE(), 0.00, NOW(), 'pendiente');

SET FOREIGN_KEY_CHECKS = 1;
