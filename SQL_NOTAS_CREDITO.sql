-- ============================================================
-- SISTEMA DE NOTAS DE CRÉDITO/ABONO PARA CLIENTES Y PROVEEDORES
-- Patrón: Cabecera-Detalle (cab/det)
-- ============================================================

USE `farmacia`;

-- ============================================================
-- TABLA: abono_cliente_cab (ENCABEZADO)
-- Descripción: Encabezado de notas de crédito para clientes
-- ============================================================
CREATE TABLE IF NOT EXISTS `abono_cliente_cab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `numero` varchar(50) DEFAULT NULL COMMENT 'Número de nota de crédito',
  `fecha` datetime DEFAULT NULL,
  `tipo` enum('abono','anticipo') NOT NULL DEFAULT 'abono' COMMENT 'abono=rebaja facturas, anticipo=saldo a favor',
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `gravada` decimal(12,2) DEFAULT 0.00,
  `iva` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `forma_pago` int(11) DEFAULT NULL COMMENT 'Transferencia, Cheque, Depósito',
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de cheque/transferencia/depósito',
  `banco` varchar(100) DEFAULT NULL COMMENT 'Banco emisor',
  `sta` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=borrador, 1=emitido',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nota_cliente_cab_sucursal_idx` (`sucursal_id`),
  KEY `fk_nota_cliente_cab_cliente_idx` (`cliente_id`),
  KEY `fk_nota_cliente_cab_forma_pago_idx` (`forma_pago`),
  CONSTRAINT `fk_nota_cliente_cab_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_cliente_cab_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_cliente_cab_forma_pago` FOREIGN KEY (`forma_pago`) REFERENCES `forma_pago` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci 
COMMENT='Encabezado de notas de crédito para clientes';

-- ============================================================
-- TABLA: abono_cliente_det (DETALLE)
-- Descripción: Detalle de facturas incluidas en la nota de crédito
-- ============================================================
CREATE TABLE IF NOT EXISTS `abono_cliente_det` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal_id` int(11) NOT NULL,
  `nota_credito_cab_id` int(11) NOT NULL,
  `egreso_cab_id` int(11) NOT NULL COMMENT 'Factura a la que se aplica el abono',
  `monto_abono` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto que se abona a esta factura',
  `observaciones` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nota_cliente_det_sucursal_idx` (`sucursal_id`),
  KEY `fk_nota_cliente_det_cab_idx` (`nota_credito_cab_id`),
  KEY `fk_nota_cliente_det_egreso_idx` (`egreso_cab_id`),
  CONSTRAINT `fk_nota_cliente_det_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_cliente_det_cab` FOREIGN KEY (`nota_credito_cab_id`) REFERENCES `abono_cliente_cab` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_cliente_det_egreso` FOREIGN KEY (`egreso_cab_id`) REFERENCES `egreso_cab` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci 
COMMENT='Detalle de facturas en notas de crédito de clientes';

-- ============================================================
-- TABLA: abono_proveedor_cab (ENCABEZADO)
-- Descripción: Encabezado de notas de crédito para proveedores
-- ============================================================
CREATE TABLE IF NOT EXISTS `abono_proveedor_cab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `numero` varchar(50) DEFAULT NULL COMMENT 'Número de nota de crédito',
  `fecha` datetime DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `gravada` decimal(12,2) DEFAULT 0.00,
  `iva` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `forma_pago` int(11) DEFAULT NULL COMMENT 'Transferencia, Cheque, Depósito',
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de cheque/transferencia/depósito',
  `banco` varchar(100) DEFAULT NULL COMMENT 'Banco emisor',
  `sta` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=borrador, 1=emitido',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nota_proveedor_cab_sucursal_idx` (`sucursal_id`),
  KEY `fk_nota_proveedor_cab_proveedor_idx` (`proveedor_id`),
  KEY `fk_nota_proveedor_cab_forma_pago_idx` (`forma_pago`),
  CONSTRAINT `fk_nota_proveedor_cab_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_proveedor_cab_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_proveedor_cab_forma_pago` FOREIGN KEY (`forma_pago`) REFERENCES `forma_pago` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci 
COMMENT='Encabezado de notas de crédito para proveedores';

-- ============================================================
-- TABLA: abono_proveedor_det (DETALLE)
-- Descripción: Detalle de compras incluidas en la nota de crédito
-- ============================================================
CREATE TABLE IF NOT EXISTS `abono_proveedor_det` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal_id` int(11) NOT NULL,
  `nota_credito_cab_id` int(11) NOT NULL,
  `ingreso_cab_id` int(11) NOT NULL COMMENT 'Compra a la que se aplica el abono',
  `monto_abono` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto que se abona a esta compra',
  `observaciones` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nota_proveedor_det_sucursal_idx` (`sucursal_id`),
  KEY `fk_nota_proveedor_det_cab_idx` (`nota_credito_cab_id`),
  KEY `fk_nota_proveedor_det_ingreso_idx` (`ingreso_cab_id`),
  CONSTRAINT `fk_nota_proveedor_det_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_proveedor_det_cab` FOREIGN KEY (`nota_credito_cab_id`) REFERENCES `abono_proveedor_cab` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_nota_proveedor_det_ingreso` FOREIGN KEY (`ingreso_cab_id`) REFERENCES `ingreso_cab` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci 
COMMENT='Detalle de compras en notas de crédito de proveedores';

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

-- SP: Guardar nota de crédito de cliente (cabecera + detalles)
DROP PROCEDURE IF EXISTS sp_guardar_nota_credito_cliente$$
DELIMITER $$
CREATE PROCEDURE sp_guardar_nota_credito_cliente(
    IN p_sucursal_id INT,
    IN p_cliente_id INT,
    IN p_numero VARCHAR(50),
    IN p_fecha DATETIME,
    IN p_tipo ENUM('abono','anticipo'),
    IN p_subtotal DECIMAL(12,2),
    IN p_gravada DECIMAL(12,2),
    IN p_iva DECIMAL(12,2),
    IN p_total DECIMAL(12,2),
    IN p_forma_pago INT,
    IN p_referencia VARCHAR(100),
    IN p_banco VARCHAR(100),
    IN p_sta TINYINT,
    IN p_observaciones TEXT,
    IN p_detalles JSON,  -- [{"egreso_cab_id":1,"monto_abono":100.50,"observaciones":"..."}]
    OUT p_nota_id INT,
    OUT p_error VARCHAR(255)
)
BEGIN
    DECLARE v_count INT DEFAULT 0;
    DECLARE v_suma_abonos DECIMAL(12,2) DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_nota_id = -1;
        SET p_error = 'Error al guardar la nota de crédito';
    END;
    
    SET p_error = NULL;
    START TRANSACTION;
    
    -- Validar que el cliente existe
    SELECT COUNT(*) INTO v_count FROM clientes WHERE id = p_cliente_id;
    IF v_count = 0 THEN
        SET p_error = 'El cliente no existe';
        SET p_nota_id = -1;
        ROLLBACK;
    ELSE
        -- Insertar cabecera
        INSERT INTO abono_cliente_cab 
        (sucursal_id, cliente_id, numero, fecha, tipo, subtotal, gravada, iva, total, 
         forma_pago, referencia, banco, sta, observaciones)
        VALUES 
        (p_sucursal_id, p_cliente_id, p_numero, p_fecha, p_tipo, p_subtotal, p_gravada, 
         p_iva, p_total, p_forma_pago, p_referencia, p_banco, p_sta, p_observaciones);
        
        SET p_nota_id = LAST_INSERT_ID();
        
        -- Insertar detalles desde JSON (si existen)
        IF p_detalles IS NOT NULL AND JSON_LENGTH(p_detalles) > 0 THEN
            INSERT INTO abono_cliente_det (sucursal_id, nota_credito_cab_id, egreso_cab_id, monto_abono, observaciones)
            SELECT 
                p_sucursal_id,
                p_nota_id,
                JSON_UNQUOTE(JSON_EXTRACT(value, '$.egreso_cab_id')),
                JSON_UNQUOTE(JSON_EXTRACT(value, '$.monto_abono')),
                JSON_UNQUOTE(JSON_EXTRACT(value, '$.observaciones'))
            FROM JSON_TABLE(p_detalles, '$[*]' COLUMNS (value JSON PATH '$')) AS jt;
            
            -- Validar que la suma de abonos coincida con el total
            SELECT SUM(monto_abono) INTO v_suma_abonos 
            FROM abono_cliente_det 
            WHERE nota_credito_cab_id = p_nota_id;
            
            IF v_suma_abonos != p_total THEN
                SET p_error = CONCAT('La suma de abonos (', v_suma_abonos, ') no coincide con el total (', p_total, ')');
                SET p_nota_id = -1;
                ROLLBACK;
            ELSE
                COMMIT;
            END IF;
        ELSE
            COMMIT;
        END IF;
    END IF;
END$$
DELIMITER ;

-- SP: Guardar nota de crédito de proveedor (cabecera + detalles)
DROP PROCEDURE IF EXISTS sp_guardar_nota_credito_proveedor$$
DELIMITER $$
CREATE PROCEDURE sp_guardar_nota_credito_proveedor(
    IN p_sucursal_id INT,
    IN p_proveedor_id INT,
    IN p_numero VARCHAR(50),
    IN p_fecha DATETIME,
    IN p_subtotal DECIMAL(12,2),
    IN p_gravada DECIMAL(12,2),
    IN p_iva DECIMAL(12,2),
    IN p_total DECIMAL(12,2),
    IN p_forma_pago INT,
    IN p_referencia VARCHAR(100),
    IN p_banco VARCHAR(100),
    IN p_sta TINYINT,
    IN p_observaciones TEXT,
    IN p_detalles JSON,  -- [{"ingreso_cab_id":1,"monto_abono":100.50,"observaciones":"..."}]
    OUT p_nota_id INT,
    OUT p_error VARCHAR(255)
)
BEGIN
    DECLARE v_count INT DEFAULT 0;
    DECLARE v_suma_abonos DECIMAL(12,2) DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_nota_id = -1;
        SET p_error = 'Error al guardar la nota de crédito';
    END;
    
    SET p_error = NULL;
    START TRANSACTION;
    
    -- Validar que el proveedor existe
    SELECT COUNT(*) INTO v_count FROM proveedor WHERE id = p_proveedor_id;
    IF v_count = 0 THEN
        SET p_error = 'El proveedor no existe';
        SET p_nota_id = -1;
        ROLLBACK;
    ELSE
        -- Insertar cabecera
        INSERT INTO abono_proveedor_cab 
        (sucursal_id, proveedor_id, numero, fecha, subtotal, gravada, iva, total, 
         forma_pago, referencia, banco, sta, observaciones)
        VALUES 
        (p_sucursal_id, p_proveedor_id, p_numero, p_fecha, p_subtotal, p_gravada, 
         p_iva, p_total, p_forma_pago, p_referencia, p_banco, p_sta, p_observaciones);
        
        SET p_nota_id = LAST_INSERT_ID();
        
        -- Insertar detalles desde JSON (si existen)
        IF p_detalles IS NOT NULL AND JSON_LENGTH(p_detalles) > 0 THEN
            INSERT INTO abono_proveedor_det (sucursal_id, nota_credito_cab_id, ingreso_cab_id, monto_abono, observaciones)
            SELECT 
                p_sucursal_id,
                p_nota_id,
                JSON_UNQUOTE(JSON_EXTRACT(value, '$.ingreso_cab_id')),
                JSON_UNQUOTE(JSON_EXTRACT(value, '$.monto_abono')),
                JSON_UNQUOTE(JSON_EXTRACT(value, '$.observaciones'))
            FROM JSON_TABLE(p_detalles, '$[*]' COLUMNS (value JSON PATH '$')) AS jt;
            
            -- Validar que la suma de abonos coincida con el total
            SELECT SUM(monto_abono) INTO v_suma_abonos 
            FROM abono_proveedor_det 
            WHERE nota_credito_cab_id = p_nota_id;
            
            IF v_suma_abonos != p_total THEN
                SET p_error = CONCAT('La suma de abonos (', v_suma_abonos, ') no coincide con el total (', p_total, ')');
                SET p_nota_id = -1;
                ROLLBACK;
            ELSE
                COMMIT;
            END IF;
        ELSE
            COMMIT;
        END IF;
    END IF;
END$$
DELIMITER ;

-- SP: Actualizar estado de nota de crédito cliente (borrador a emitido)
DROP PROCEDURE IF EXISTS sp_emitir_nota_credito_cliente$$
DELIMITER $$
CREATE PROCEDURE sp_emitir_nota_credito_cliente(
    IN p_nota_id INT,
    OUT p_success TINYINT,
    OUT p_error VARCHAR(255)
)
BEGIN
    DECLARE v_sta TINYINT;
    
    SET p_error = NULL;
    SET p_success = 0;
    
    -- Verificar que existe y está en borrador
    SELECT sta INTO v_sta FROM abono_cliente_cab WHERE id = p_nota_id;
    
    IF v_sta IS NULL THEN
        SET p_error = 'La nota de crédito no existe';
    ELSEIF v_sta = 1 THEN
        SET p_error = 'La nota de crédito ya está emitida';
    ELSE
        UPDATE abono_cliente_cab SET sta = 1 WHERE id = p_nota_id;
        SET p_success = 1;
    END IF;
END$$
DELIMITER ;

-- SP: Actualizar estado de nota de crédito proveedor (borrador a emitido)
DROP PROCEDURE IF EXISTS sp_emitir_nota_credito_proveedor$$
DELIMITER $$
CREATE PROCEDURE sp_emitir_nota_credito_proveedor(
    IN p_nota_id INT,
    OUT p_success TINYINT,
    OUT p_error VARCHAR(255)
)
BEGIN
    DECLARE v_sta TINYINT;
    
    SET p_error = NULL;
    SET p_success = 0;
    
    -- Verificar que existe y está en borrador
    SELECT sta INTO v_sta FROM abono_proveedor_cab WHERE id = p_nota_id;
    
    IF v_sta IS NULL THEN
        SET p_error = 'La nota de crédito no existe';
    ELSEIF v_sta = 1 THEN
        SET p_error = 'La nota de crédito ya está emitida';
    ELSE
        UPDATE abono_proveedor_cab SET sta = 1 WHERE id = p_nota_id;
        SET p_success = 1;
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- VISTAS PARA REPORTES DE ESTADO DE CUENTA
-- ============================================================

-- Vista: Estado de cuenta de clientes con abonos
CREATE OR REPLACE VIEW `v_estado_cuenta_clientes` AS
SELECT 
    c.id AS cliente_id,
    c.nombre AS cliente,
    e.id AS factura_id,
    e.numero AS factura_numero,
    e.fecha AS factura_fecha,
    e.total AS factura_total,
    COALESCE(SUM(ncd.monto_abono), 0) AS total_abonos,
    (e.total - COALESCE(SUM(ncd.monto_abono), 0)) AS saldo_pendiente
FROM clientes c
INNER JOIN egreso_cab e ON c.id = e.cliente_id
LEFT JOIN abono_cliente_det ncd ON e.id = ncd.egreso_cab_id
LEFT JOIN abono_cliente_cab ncc ON ncd.nota_credito_cab_id = ncc.id AND ncc.sta = 1
WHERE e.opcionpago = 1 AND e.sta = 1  -- Solo facturas al crédito emitidas
GROUP BY c.id, c.nombre, e.id, e.numero, e.fecha, e.total
HAVING saldo_pendiente > 0
ORDER BY c.nombre, e.fecha;

-- Vista: Estado de cuenta de proveedores con abonos
CREATE OR REPLACE VIEW `v_estado_cuenta_proveedores` AS
SELECT 
    p.id AS proveedor_id,
    p.nombre AS proveedor,
    i.id AS compra_id,
    i.numero AS compra_numero,
    i.fecha AS compra_fecha,
    i.total AS compra_total,
    COALESCE(SUM(npd.monto_abono), 0) AS total_abonos,
    (i.total - COALESCE(SUM(npd.monto_abono), 0)) AS saldo_pendiente
FROM proveedor p
INNER JOIN ingreso_cab i ON p.id = i.proveedor_id
LEFT JOIN abono_proveedor_det npd ON i.id = npd.ingreso_cab_id
LEFT JOIN abono_proveedor_cab npc ON npd.nota_credito_cab_id = npc.id AND npc.sta = 1
WHERE i.opcionpago = 1 AND i.sta = 1  -- Solo compras al crédito emitidas
GROUP BY p.id, p.nombre, i.id, i.numero, i.fecha, i.total
HAVING saldo_pendiente > 0
ORDER BY p.nombre, i.fecha;

-- Vista: Anticipos de clientes (notas sin aplicar a facturas)
CREATE OR REPLACE VIEW `v_anticipos_clientes` AS
SELECT 
    c.id AS cliente_id,
    c.nombre AS cliente,
    nc.id AS nota_id,
    nc.numero AS nota_numero,
    nc.fecha,
    nc.total AS monto_total,
    COALESCE((nc.total - SUM(ncd.monto_abono)), nc.total) AS anticipo_disponible
FROM clientes c
INNER JOIN abono_cliente_cab nc ON c.id = nc.cliente_id
LEFT JOIN abono_cliente_det ncd ON nc.id = ncd.nota_credito_cab_id
WHERE nc.tipo = 'anticipo' AND nc.sta = 1
GROUP BY c.id, c.nombre, nc.id, nc.numero, nc.fecha, nc.total
HAVING anticipo_disponible > 0
ORDER BY c.nombre, nc.fecha;

-- ============================================================
-- SCRIPT COMPLETADO
-- ============================================================

/*
FLUJO DE USO:

1. CREAR NOTA DE CRÉDITO PARA CLIENTE CON SP:
   CALL sp_guardar_nota_credito_cliente(
       1,                    -- sucursal_id
       5,                    -- cliente_id
       'NC-001',             -- numero
       NOW(),                -- fecha
       'abono',              -- tipo (abono o anticipo)
       100.00,               -- subtotal
       100.00,               -- gravada
       12.00,                -- iva
       112.00,               -- total
       1,                    -- forma_pago (FK a forma_pago)
       'TRX123456',          -- referencia
       'Banco Industrial',   -- banco
       0,                    -- sta (0=borrador, 1=emitido)
       'Observaciones...',   -- observaciones
       '[{"egreso_cab_id":10,"monto_abono":50.00},{"egreso_cab_id":15,"monto_abono":62.00}]', -- detalles JSON
       @nota_id,             -- OUT nota_id
       @error                -- OUT error
   );
   SELECT @nota_id, @error;

2. EMITIR LA NOTA (cambiar de borrador a emitido):
   CALL sp_emitir_nota_credito_cliente(1, @success, @error);
   SELECT @success, @error;

3. CONSULTAR ESTADO DE CUENTA:
   SELECT * FROM v_estado_cuenta_clientes WHERE cliente_id = 5;
   
4. CONSULTAR ANTICIPOS DISPONIBLES:
   SELECT * FROM v_anticipos_clientes WHERE cliente_id = 5;

EJEMPLO IVA:
- Si es EXENTA: gravada=0, iva=0, total=subtotal
- Si tiene IVA: gravada=subtotal, iva=subtotal*0.12, total=subtotal+iva

EJEMPLO JSON DETALLES:
[
  {"egreso_cab_id": 10, "monto_abono": 50.00, "observaciones": "Abono parcial factura #10"},
  {"egreso_cab_id": 15, "monto_abono": 62.00, "observaciones": "Abono total factura #15"}
]
*/

SELECT 'Tablas de notas de crédito (cab/det) creadas exitosamente' AS Resultado;
SELECT 'Stored Procedures creados: sp_guardar_nota_credito_cliente, sp_guardar_nota_credito_proveedor' AS Resultado;
SELECT 'Vistas para reportes de estado de cuenta creadas' AS Resultado;
SELECT 'Sistema listo para manejar múltiples facturas por nota' AS Resultado;
