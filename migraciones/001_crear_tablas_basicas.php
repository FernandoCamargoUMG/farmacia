<?php

try {
    $conn = Conexion::conectar();

    $sql = "
        CREATE TABLE IF NOT EXISTS `farmacia`.`sucursal` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nombre_sucursal` VARCHAR(255) NULL,
            `direccion_sucursal` VARCHAR(255) NULL,
            `departamento` VARCHAR(255) NULL,
            `telefono` VARCHAR(50) NULL,
            `porc_iva` FLOAT NOT NULL DEFAULT 12,
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB;

        CREATE TABLE IF NOT EXISTS `farmacia`.`producto` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nombre` VARCHAR(255) NULL,
            `descripcion` VARCHAR(255) NULL,
            `precio` DECIMAL(10,2) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB;

        CREATE TABLE IF NOT EXISTS `farmacia`.`inventario` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `producto_id` INT NOT NULL,
            `sucursal_id` INT NOT NULL,
            `cantidad` INT NULL DEFAULT 0,
            PRIMARY KEY (`id`, `producto_id`, `sucursal_id`),
            INDEX `index_producto` (`producto_id`),
            INDEX `index_sucursal` (`sucursal_id`),
            CONSTRAINT `fk_producto_inventario`
                FOREIGN KEY (`producto_id`)
                REFERENCES `farmacia`.`producto` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `fk_sucursal_inventario`
                FOREIGN KEY (`sucursal_id`)
                REFERENCES `farmacia`.`sucursal` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE = InnoDB;

        CREATE TABLE IF NOT EXISTS `farmacia`.`clientes` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `sucursal_id` INT NOT NULL,
            `nombre` VARCHAR(50) NULL,
            `apellido` VARCHAR(50) NULL,
            `dpi` VARCHAR(13) NULL,
            `email` VARCHAR(100) NULL,
            `direccion` VARCHAR(255) NULL,
            `telefono` VARCHAR(50) NULL,
            `nit` VARCHAR(100) NULL,
            PRIMARY KEY (`id`, `sucursal_id`),
            INDEX `index_sucursal_clientes` (`sucursal_id`),
            CONSTRAINT `fk_sucursal_clientes`
                FOREIGN KEY (`sucursal_id`)
                REFERENCES `farmacia`.`sucursal` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE = InnoDB;

        CREATE TABLE IF NOT EXISTS `farmacia`.`egreso_cab` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `sucursal_id` INT NOT NULL,
            `cliente_id` INT NOT NULL,
            `fecha` DATETIME NULL,
            `numero` VARCHAR(50) NULL,
            `subtotal` DECIMAL(10,2) NULL DEFAULT 0.00,
            `total` DECIMAL(10,2) NULL DEFAULT 0.00,
            `iva` DECIMAL(10,2) NULL DEFAULT 0.00,
            `sta` TINYINT NOT NULL,
            `observaciones` VARCHAR(255) NULL,
            PRIMARY KEY (`id`, `sucursal_id`, `cliente_id`),
            INDEX `index_sucursal_egreso` (`sucursal_id`),
            INDEX `index_cliente_egreso` (`cliente_id`),
            CONSTRAINT `fk_sucursal_egreso`
                FOREIGN KEY (`sucursal_id`)
                REFERENCES `farmacia`.`sucursal` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `fk_cliente_egreso`
                FOREIGN KEY (`cliente_id`)
                REFERENCES `farmacia`.`clientes` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE = InnoDB;

        CREATE TABLE IF NOT EXISTS `farmacia`.`bodega` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `sucursal_id` INT NOT NULL,
            `nombre` VARCHAR(255) NULL,
            `ubicacion` VARCHAR(100) NULL,
            PRIMARY KEY (`id`, `sucursal_id`),
            INDEX `fk_sucursal_bodega` (`sucursal_id`),
            CONSTRAINT `fk_sucursal_bodega_constraint`
                FOREIGN KEY (`sucursal_id`)
                REFERENCES `farmacia`.`sucursal` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE = InnoDB;

        CREATE TABLE IF NOT EXISTS `farmacia`.`egreso_det` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `sucursal_id` INT NOT NULL,
            `egreso_cab_id` INT NOT NULL,
            `producto_id` INT NOT NULL,
            `bodega_id` INT NOT NULL,
            `cantidad` VARCHAR(45) NULL,
            `precio` VARCHAR(45) NULL,
            PRIMARY KEY (`id`, `sucursal_id`, `egreso_cab_id`, `producto_id`, `bodega_id`),
            INDEX `fk_sucursal_det` (`sucursal_id`),
            INDEX `fk_producto_det` (`producto_id`),
            INDEX `fk_egreso_cab_det` (`egreso_cab_id`),
            INDEX `fk_bodega_det` (`bodega_id`),
            CONSTRAINT `fk_sucursal_egreso_det`
                FOREIGN KEY (`sucursal_id`)
                REFERENCES `farmacia`.`sucursal` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `fk_producto_egreso_det`
                FOREIGN KEY (`producto_id`)
                REFERENCES `farmacia`.`producto` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `fk_egreso_cab_egreso_det`
                FOREIGN KEY (`egreso_cab_id`)
                REFERENCES `farmacia`.`egreso_cab` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `fk_bodega_egreso_det`
                FOREIGN KEY (`bodega_id`)
                REFERENCES `farmacia`.`bodega` (`id`)
                ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE = InnoDB;
    ";

    $conn->exec($sql);
    echo "Migración 001 ejecutada correctamente.\n";
} catch (PDOException $e) {
    echo "Error al ejecutar la migración 001: " . $e->getMessage() . "\n";
}
