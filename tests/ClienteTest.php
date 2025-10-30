<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../models/clientes.php';

class ClienteTest extends TestCase
{
    private $cliente;

    protected function setUp(): void
    {
        $this->cliente = new Cliente();
    }

    public function testCrearCliente()
    {
        $datos = [
            'nombre' => 'Juan Pérez',
            'nit' => '12345678',
            'direccion' => 'Calle Principal 123',
            'telefono' => '55555555'
        ];
        
        $resultado = $this->cliente->crear($datos);
        $this->assertTrue($resultado > 0);
    }

    public function testBuscarCliente()
    {
        $id = 1;
        $cliente = $this->cliente->buscar($id);
        $this->assertIsArray($cliente);
        $this->assertArrayHasKey('nombre', $cliente);
    }

    public function testActualizarCliente()
    {
        $datos = [
            'id' => 1,
            'nombre' => 'Juan Antonio Pérez',
            'nit' => '12345678',
            'direccion' => 'Avenida Principal 456',
            'telefono' => '66666666'
        ];
        
        $resultado = $this->cliente->actualizar($datos);
        $this->assertTrue($resultado);
    }
}