<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/producto.php';

class ProductoTest extends TestCase
{
    private $producto;

    protected function setUp(): void
    {
        $this->producto = new Producto();
    }

    public function testCrearProducto()
    {
        $datos = [
            'nombre' => 'Paracetamol',
            'descripcion' => 'Analgésico',
            'precio' => 10.50,
            'categoria_id' => 1,
            'stock_minimo' => 10
        ];
        
        $resultado = $this->producto->crear($datos);
        $this->assertTrue($resultado > 0);
    }

    public function testBuscarProducto()
    {
        $id = 1;
        $producto = $this->producto->buscar($id);
        $this->assertIsArray($producto);
        $this->assertArrayHasKey('nombre', $producto);
    }

    public function testActualizarProducto()
    {
        $datos = [
            'id' => 1,
            'nombre' => 'Paracetamol 500mg',
            'descripcion' => 'Analgésico y antipirético',
            'precio' => 12.50,
            'categoria_id' => 1,
            'stock_minimo' => 15
        ];
        
        $resultado = $this->producto->actualizar($datos);
        $this->assertTrue($resultado);
    }
}