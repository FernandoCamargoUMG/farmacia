-- Script para agregar funcionalidad de compras al crédito/contado
-- Ejecutar este script en tu base de datos existente

USE `farmacia`;

-- Agregar campo forma_pago a la tabla ingreso_cab
ALTER TABLE `ingreso_cab` 
ADD COLUMN `forma_pago` int(11) DEFAULT NULL 
COMMENT 'Efectivo\nCheque\nDeposito\nTarjeta de Crédito\nTarjeta de Débito\nTransferencia Bancaria\n'
AFTER `proveedor_id`;

-- Agregar índice para forma_pago
ALTER TABLE `ingreso_cab` 
ADD KEY `fk_forma_pago_ingreso_idx` (`forma_pago`);

-- Agregar foreign key para forma_pago
ALTER TABLE `ingreso_cab` 
ADD CONSTRAINT `fk_forma_pago_ingreso` 
FOREIGN KEY (`forma_pago`) REFERENCES `forma_pago` (`id`) 
ON DELETE NO ACTION 
ON UPDATE NO ACTION;

-- Agregar campo opcionpago a la tabla ingreso_cab
ALTER TABLE `ingreso_cab` 
ADD COLUMN `opcionpago` tinyint(4) DEFAULT 0 
COMMENT '0=contado/1=credito'
AFTER `observaciones`;

-- Actualización exitosa
SELECT 'Script ejecutado correctamente. Tabla ingreso_cab actualizada con campos forma_pago y opcionpago' AS Resultado;
