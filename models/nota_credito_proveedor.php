<?php
require_once __DIR__ . '/../config/conexion.php';

class NotaCreditoProveedor
{
    /**
     * Guardar o actualizar nota de crédito (lógica en PHP)
     * @return array ['success' => bool, 'nota_id' => int, 'error' => string]
     */
    public static function guardar($datos)
    {
        $pdo = Conexion::conectar();
        
        try {
            $pdo->beginTransaction();
            
            $notaId = $datos['id'] ?? null;
            
            if ($notaId) {
                // ACTUALIZAR nota existente
                $sql = "UPDATE abono_proveedor_cab SET
                        proveedor_id = ?,
                        numero = ?,
                        fecha = ?,
                        subtotal = ?,
                        gravada = ?,
                        iva = ?,
                        total = ?,
                        forma_pago = ?,
                        referencia = ?,
                        banco = ?,
                        sta = ?,
                        observaciones = ?
                        WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['proveedor_id'],
                    $datos['numero'],
                    $datos['fecha'],
                    $datos['subtotal'],
                    $datos['gravada'],
                    $datos['iva'],
                    $datos['total'],
                    $datos['forma_pago'] ?? null,
                    $datos['referencia'] ?? null,
                    $datos['banco'] ?? null,
                    $datos['sta'] ?? 0,
                    $datos['observaciones'] ?? null,
                    $notaId
                ]);
                
                // Eliminar detalles anteriores
                $stmt = $pdo->prepare("DELETE FROM abono_proveedor_det WHERE nota_credito_cab_id = ?");
                $stmt->execute([$notaId]);
                
            } else {
                // INSERTAR nueva nota
                $sql = "INSERT INTO abono_proveedor_cab 
                        (sucursal_id, proveedor_id, numero, fecha, subtotal, gravada, iva, total, 
                         forma_pago, referencia, banco, sta, observaciones)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['sucursal_id'],
                    $datos['proveedor_id'],
                    $datos['numero'],
                    $datos['fecha'],
                    $datos['subtotal'],
                    $datos['gravada'],
                    $datos['iva'],
                    $datos['total'],
                    $datos['forma_pago'] ?? null,
                    $datos['referencia'] ?? null,
                    $datos['banco'] ?? null,
                    $datos['sta'] ?? 0,
                    $datos['observaciones'] ?? null
                ]);
                
                $notaId = $pdo->lastInsertId();
            }
            
            // Insertar detalles (compras aplicadas)
            if (!empty($datos['detalles'])) {
                $sqlDet = "INSERT INTO abono_proveedor_det 
                           (nota_credito_cab_id, sucursal_id, ingreso_cab_id, monto_abono)
                           VALUES (?, ?, ?, ?)";
                $stmtDet = $pdo->prepare($sqlDet);
                
                foreach ($datos['detalles'] as $detalle) {
                    $stmtDet->execute([
                        $notaId,
                        $datos['sucursal_id'],
                        $detalle['ingreso_cab_id'],
                        $detalle['monto_abono']
                    ]);
                }
            }
            
            // Si se está emitiendo directamente (sta=1), no hay que hacer nada adicional
            // Los saldos se calculan dinámicamente al consultar
            
            $pdo->commit();
            
            return [
                'success' => true,
                'nota_id' => $notaId,
                'error' => null
            ];
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en NotaCreditoProveedor::guardar: " . $e->getMessage());
            return [
                'success' => false,
                'nota_id' => -1,
                'error' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Emitir nota de crédito (cambiar de borrador a emitido)
     */
    public static function emitir($notaId)
    {
        $pdo = Conexion::conectar();
        
        try {
            $pdo->beginTransaction();
            
            // Verificar que la nota existe y está en borrador
            $stmt = $pdo->prepare("SELECT sta FROM abono_proveedor_cab WHERE id = ?");
            $stmt->execute([$notaId]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$nota) {
                throw new Exception("Nota de crédito no encontrada");
            }
            
            if ($nota['sta'] != 0) {
                throw new Exception("La nota ya está emitida");
            }
            
            // Actualizar estado a emitido
            $stmt = $pdo->prepare("UPDATE abono_proveedor_cab SET sta = 1 WHERE id = ?");
            $stmt->execute([$notaId]);
            
            // Los saldos se calculan dinámicamente, no hay que actualizar nada más
            
            $pdo->commit();
            
            return [
                'success' => true,
                'error' => null
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en NotaCreditoProveedor::emitir: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en NotaCreditoProveedor::emitir: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todas las notas de crédito por sucursal
     */
    public static function obtenerPorSucursal($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("
            SELECT 
                npc.id,
                npc.numero,
                DATE_FORMAT(npc.fecha, '%d-%m-%Y') AS fecha,
                npc.total,
                npc.sta,
                p.nombre AS proveedor,
                npc.forma_pago,
                npc.referencia,
                npc.banco
            FROM abono_proveedor_cab npc
            INNER JOIN proveedor p ON npc.proveedor_id = p.id
            WHERE npc.sucursal_id = ?
            ORDER BY npc.fecha DESC, npc.id DESC
        ");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener nota de crédito con detalles completos
     */
    public static function obtenerPorIdConDetalles($id)
    {
        $pdo = Conexion::conectar();
        
        try {
            // Cabecera
            $stmt = $pdo->prepare("
                SELECT 
                    npc.*,
                    p.nombre AS proveedor
                FROM abono_proveedor_cab npc
                INNER JOIN proveedor p ON npc.proveedor_id = p.id
                WHERE npc.id = ?
            ");
            $stmt->execute([$id]);
            $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cabecera) {
                return null;
            }
            
            // Detalles
            $stmt = $pdo->prepare("
                SELECT 
                    npd.*,
                    i.numero AS compra_numero,
                    i.total AS compra_total,
                    DATE_FORMAT(i.fecha, '%d-%m-%Y') AS compra_fecha
                FROM abono_proveedor_det npd
                INNER JOIN ingreso_cab i ON npd.ingreso_cab_id = i.id
                WHERE npd.nota_credito_cab_id = ?
            ");
            $stmt->execute([$id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cabecera['detalles'] = $detalles;
            
            return $cabecera;
        } catch (PDOException $e) {
            error_log("Error en NotaCreditoProveedor::obtenerPorIdConDetalles: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener compras pendientes de un proveedor (para aplicar notas)
     */
    public static function obtenerComprasPendientes($proveedorId, $sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("
            SELECT 
                i.id AS compra_id,
                i.numero AS compra_numero,
                DATE_FORMAT(i.fecha, '%d-%m-%Y') AS compra_fecha,
                i.total AS compra_total,
                COALESCE(SUM(npd.monto_abono), 0) AS total_abonos,
                (i.total - COALESCE(SUM(npd.monto_abono), 0)) AS saldo_pendiente
            FROM ingreso_cab i
            LEFT JOIN abono_proveedor_det npd ON i.id = npd.ingreso_cab_id
            LEFT JOIN abono_proveedor_cab npc ON npd.nota_credito_cab_id = npc.id AND npc.sta = 1
            WHERE i.proveedor_id = ? 
              AND i.sucursal_id = ?
              AND i.opcionpago = 1 
              AND i.sta = 1
            GROUP BY i.id, i.numero, i.fecha, i.total
            HAVING saldo_pendiente > 0
            ORDER BY i.fecha ASC
        ");
        $stmt->execute([$proveedorId, $sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estado de cuenta de un proveedor
     */
    public static function obtenerEstadoCuenta($proveedorId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("
            SELECT * FROM v_estado_cuenta_proveedores 
            WHERE proveedor_id = ?
            ORDER BY compra_fecha ASC
        ");
        $stmt->execute([$proveedorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Eliminar nota de crédito (solo si está en borrador)
     */
    public static function eliminar($id)
    {
        try {
            $pdo = Conexion::conectar();
            
            // Verificar que esté en borrador
            $stmt = $pdo->prepare("SELECT sta FROM abono_proveedor_cab WHERE id = ?");
            $stmt->execute([$id]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$nota) {
                return ['success' => false, 'error' => 'La nota de crédito no existe'];
            }
            
            if ($nota['sta'] == 1) {
                return ['success' => false, 'error' => 'No se puede eliminar una nota emitida'];
            }
            
            // Eliminar (los detalles se eliminan en cascada)
            $stmt = $pdo->prepare("DELETE FROM abono_proveedor_cab WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Error en NotaCreditoProveedor::eliminar: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error de base de datos'];
        }
    }

    /**
     * Generar número de nota de crédito automático
     */
    public static function generarNumero($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("
            SELECT COALESCE(MAX(CAST(SUBSTRING(numero, 4) AS UNSIGNED)), 0) + 1 AS siguiente
            FROM abono_proveedor_cab
            WHERE sucursal_id = ? AND numero LIKE 'NC-%'
        ");
        $stmt->execute([$sucursalId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return 'NC-' . str_pad($resultado['siguiente'], 6, '0', STR_PAD_LEFT);
    }
}
