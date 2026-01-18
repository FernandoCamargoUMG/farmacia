-- ============================================================
-- SISTEMA DE CATEGORÍAS DE PRECIO
-- Permite definir categorías con precios predefinidos opcionales
-- ============================================================

USE `farmacia`;

-- ============================================================
-- TABLA: categoria_precio
-- Descripción: Categorías de precio para productos
-- ============================================================
CREATE TABLE IF NOT EXISTS `categoria_precio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre de la categoría de precio',
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción de la categoría',
  `precio_base` decimal(10,2) DEFAULT NULL COMMENT 'Precio base de la categoría (opcional)',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nombre_categoria_precio` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci 
COMMENT='Categorías de precio para productos';

-- ============================================================
-- Modificar tabla producto para agregar categoria_precio_id
-- ============================================================
ALTER TABLE `producto` 
ADD COLUMN `categoria_precio_id` int(11) DEFAULT NULL COMMENT 'FK a categoria_precio' AFTER `categoria_id`;

-- Agregar índice
ALTER TABLE `producto` 
ADD KEY `fk_producto_categoria_precio` (`categoria_precio_id`);

-- Agregar constraint (opcional, si quieres integridad referencial)
ALTER TABLE `producto` 
ADD CONSTRAINT `fk_producto_categoria_precio` 
FOREIGN KEY (`categoria_precio_id`) 
REFERENCES `categoria_precio` (`id`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- ============================================================
-- DATOS DE EJEMPLO
-- ============================================================
INSERT INTO `categoria_precio` (`nombre`, `descripcion`, `precio_base`, `activo`) VALUES
('Premium', 'Productos de alta calidad', 150.00, 1),
('Estándar', 'Productos de calidad media', 75.00, 1),
('Económico', 'Productos de bajo costo', 35.00, 1),
('Personalizado', 'Precio definido por el usuario', NULL, 1);

-- Script ejecutado correctamente
SELECT 'Tabla categoria_precio creada y producto actualizado correctamente' AS Resultado;
