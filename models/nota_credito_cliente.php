<?php
require_once __DIR__ . '/../config/conexion.php';

class NotaCreditoCliente
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
                $sql = "UPDATE abono_cliente_cab SET
                        cliente_id = ?,
                        numero = ?,
                        fecha = ?,
                        tipo = ?,
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
                    $datos['cliente_id'],
                    $datos['numero'],
                    $datos['fecha'],
                    $datos['tipo'] ?? 'abono',
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
                $stmt = $pdo->prepare("DELETE FROM abono_cliente_det WHERE nota_credito_cab_id = ?");
                $stmt->execute([$notaId]);
                
            } else {
                // INSERTAR nueva nota
                $sql = "INSERT INTO abono_cliente_cab 
                        (sucursal_id, cliente_id, numero, fecha, tipo, subtotal, gravada, iva, total, 
                            forma_pago, referencia, banco, sta, observaciones)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $datos['sucursal_id'],
                    $datos['cliente_id'],
                    $datos['numero'],
                    $datos['fecha'],
                    $datos['tipo'] ?? 'abono',
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
            
            // Insertar detalles (facturas aplicadas)
            if (!empty($datos['detalles'])) {
                $sqlDet = "INSERT INTO abono_cliente_det 
                           (nota_credito_cab_id, sucursal_id, egreso_cab_id, monto_abono)
                           VALUES (?, ?, ?, ?)";
                $stmtDet = $pdo->prepare($sqlDet);
                
                foreach ($datos['detalles'] as $detalle) {
                    $stmtDet->execute([
                        $notaId,
                        $datos['sucursal_id'],
                        $detalle['egreso_cab_id'],
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
            error_log("Error en NotaCreditoCliente::guardar: " . $e->getMessage());
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
            $stmt = $pdo->prepare("SELECT sta FROM abono_cliente_cab WHERE id = ?");
            $stmt->execute([$notaId]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$nota) {
                throw new Exception("Nota de crédito no encontrada");
            }
            
            if ($nota['sta'] != 0) {
                throw new Exception("La nota ya está emitida");
            }
            
            // Actualizar estado a emitido
            $stmt = $pdo->prepare("UPDATE abono_cliente_cab SET sta = 1 WHERE id = ?");
            $stmt->execute([$notaId]);
            
            // Los saldos se calculan dinámicamente, no hay que actualizar nada más
            
            $pdo->commit();
            
            return [
                'success' => true,
                'error' => null
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en NotaCreditoCliente::emitir: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error en NotaCreditoCliente::emitir: " . $e->getMessage());
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
                ncc.id,
                ncc.numero,
                DATE_FORMAT(ncc.fecha, '%d-%m-%Y') AS fecha,
                ncc.tipo,
                ncc.total,
                ncc.sta,
                CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                ncc.forma_pago,
                ncc.referencia,
                ncc.banco
            FROM abono_cliente_cab ncc
            INNER JOIN clientes c ON ncc.cliente_id = c.id
            WHERE ncc.sucursal_id = ?
            ORDER BY ncc.fecha DESC, ncc.id DESC
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
                    ncc.*,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente
                FROM abono_cliente_cab ncc
                INNER JOIN clientes c ON ncc.cliente_id = c.id
                WHERE ncc.id = ?
            ");
            $stmt->execute([$id]);
            $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cabecera) {
                return null;
            }
            
            // Detalles
            $stmt = $pdo->prepare("
                SELECT 
                    ncd.*,
                    e.numero AS factura_numero,
                    e.total AS factura_total,
                    DATE_FORMAT(e.fecha, '%d-%m-%Y') AS factura_fecha
                FROM abono_cliente_det ncd
                INNER JOIN egreso_cab e ON ncd.egreso_cab_id = e.id
                WHERE ncd.nota_credito_cab_id = ?
            ");
            $stmt->execute([$id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cabecera['detalles'] = $detalles;
            
            return $cabecera;
        } catch (PDOException $e) {
            error_log("Error en NotaCreditoCliente::obtenerPorIdConDetalles: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener facturas pendientes de un cliente (para aplicar notas)
     */
    public static function obtenerFacturasPendientes($clienteId, $sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("
            SELECT 
                e.id AS factura_id,
                e.numero AS factura_numero,
                DATE_FORMAT(e.fecha, '%d-%m-%Y') AS factura_fecha,
                e.total AS factura_total,
                COALESCE(SUM(ncd.monto_abono), 0) AS total_abonos,
                (e.total - COALESCE(SUM(ncd.monto_abono), 0)) AS saldo_pendiente
            FROM egreso_cab e
            LEFT JOIN abono_cliente_det ncd ON e.id = ncd.egreso_cab_id
            LEFT JOIN abono_cliente_cab ncc ON ncd.nota_credito_cab_id = ncc.id AND ncc.sta = 1
            WHERE e.cliente_id = ? 
              AND e.sucursal_id = ?
              AND e.opcionpago = 1 
              AND e.sta = 1
            GROUP BY e.id, e.numero, e.fecha, e.total
            HAVING saldo_pendiente > 0
            ORDER BY e.fecha ASC
        ");
        $stmt->execute([$clienteId, $sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estado de cuenta de un cliente
     */
    public static function obtenerEstadoCuenta($clienteId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("
            SELECT * FROM v_estado_cuenta_clientes 
            WHERE cliente_id = ?
            ORDER BY factura_fecha ASC
        ");
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener anticipos disponibles de un cliente
     */
    public static function obtenerAnticiposDisponibles($clienteId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("
            SELECT * FROM v_anticipos_clientes 
            WHERE cliente_id = ?
            ORDER BY fecha ASC
        ");
        $stmt->execute([$clienteId]);
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
            $stmt = $pdo->prepare("SELECT sta FROM abono_cliente_cab WHERE id = ?");
            $stmt->execute([$id]);
            $nota = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$nota) {
                return ['success' => false, 'error' => 'La nota de crédito no existe'];
            }
            
            if ($nota['sta'] == 1) {
                return ['success' => false, 'error' => 'No se puede eliminar una nota emitida'];
            }
            
            // Eliminar (los detalles se eliminan en cascada)
            $stmt = $pdo->prepare("DELETE FROM abono_cliente_cab WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Error en NotaCreditoCliente::eliminar: " . $e->getMessage());
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
            FROM abono_cliente_cab
            WHERE sucursal_id = ? AND numero LIKE 'NC-%'
        ");
        $stmt->execute([$sucursalId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return 'NC-' . str_pad($resultado['siguiente'], 6, '0', STR_PAD_LEFT);
    }
}
