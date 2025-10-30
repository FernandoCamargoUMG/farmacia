<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/ingreso.php';

class IngresoTest extends TestCase
{
    private $ingreso;

    protected function setUp(): void
    {
        $this->ingreso = new Ingreso();
    }

    public function testCrearIngreso()
    {
        $datos = [
            'fecha' => date('Y-m-d'),
            'cliente_id' => 1,
            'total' => 100.50,
            'forma_pago_id' => 1
        ];
        
        $resultado = $this->ingreso->crear($datos);
        $this->assertTrue($resultado > 0);
    }

    public function testBuscarIngreso()
    {
        $id = 1;
        $ingreso = $this->ingreso->buscar($id);
        $this->assertIsArray($ingreso);
        $this->assertArrayHasKey('total', $ingreso);
    }

    public function testObtenerIngresosPorFecha()
    {
        $fecha_inicio = date('Y-m-d', strtotime('-7 days'));
        $fecha_fin = date('Y-m-d');
        
        $ingresos = $this->ingreso->obtenerPorFecha($fecha_inicio, $fecha_fin);
        $this->assertIsArray($ingresos);
    }
}