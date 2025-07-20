<?php
/**
 * Controlador de Productos
 */

// Incluir el modelo de manera simple y directa
require_once dirname(__FILE__) . '/../models/Producto.php';

class ControladorProductos
{
    /** @var Producto */
    private $modeloProducto;

    public function __construct()
    {
        $this->modeloProducto = new Producto();
    }

    public function mostrarCatalogo()
    {
        $productos = $this->modeloProducto->obtenerTodos();
        $titulo = 'Catálogo de Productos';
        $descripcion = 'Descubre los mejores productos artesanales de Panamá Oeste';
        
        include 'views/productos/catalogo.php';
    }

    public function mostrarDetalle($id)
    {
        $producto = $this->modeloProducto->obtenerPorId($id);
        
        if (!$producto) {
            http_response_code(404);
            include 'views/errors/404.php';
            return;
        }

        $titulo = $producto['nombre'];
        $descripcion = substr($producto['descripcion'], 0, 150) . '...';
        include 'views/productos/detalle.php';
    }
}
