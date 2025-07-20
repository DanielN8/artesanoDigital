<?php
/**
 * Controlador de Carrito
 * Responsabilidad: Gestionar el carrito de compras
 */

namespace Controllers;

use Models\Carrito;
use Utils\GestorAutenticacion;
use Exception;

// Incluir el modelo Producto (que no tiene namespace)
require_once dirname(__FILE__) . '/../models/Producto.php';

class ControladorCarrito 
{
    private GestorAutenticacion $gestorAuth;
    private Carrito $modeloCarrito;
    private \Producto $modeloProducto;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloCarrito = new Carrito();
        $this->modeloProducto = new \Producto();
        
        // Procesar acciones AJAX
        if (isset($_POST['accion']) && $this->esAjax()) {
            $this->procesarAccionAjax($_POST['accion']);
        }
    }
    
    /**
     * Procesa solicitudes AJAX para el carrito
     * @param string $accion
     */
    private function procesarAccionAjax($accion) 
    {
        switch ($accion) {
            case 'obtener_carrito':
                $this->responderCarritoJson();
                break;
                
            case 'agregar_producto':
                $idProducto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
                $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
                $this->agregarProductoAjax($idProducto, $cantidad);
                break;
                
            case 'actualizar_cantidad':
                $idProducto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
                $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
                $this->actualizarCantidadAjax($idProducto, $cantidad);
                break;
                
            case 'eliminar_producto':
                $idProducto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
                $this->eliminarProductoAjax($idProducto);
                break;
                
            case 'vaciar_carrito':
                $this->vaciarCarritoAjax();
                break;
                
            default:
                $this->responderJson(['exitoso' => false, 'mensaje' => 'Acción no reconocida']);
        }
    }
    
    /**
     * Verifica si la solicitud es AJAX
     * @return bool
     */
    private function esAjax() 
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Envía respuesta JSON y termina la ejecución
     * @param array $datos
     */
    private function responderJson($datos) 
    {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
    
    /**
     * Devuelve el carrito actual en formato JSON
     */
    private function responderCarritoJson() 
    {
        $idUsuario = $this->gestorAuth->obtenerUsuarioActual() ? 
                     $this->gestorAuth->obtenerUsuarioActual()['id_usuario'] : 0;
        
        $carrito = $this->modeloCarrito->obtenerProductos($idUsuario);
        $totalProductos = 0;
        
        foreach ($carrito as $item) {
            $totalProductos += $item['cantidad'];
        }
        
        $this->responderJson([
            'exitoso' => true,
            'carrito' => $carrito,
            'total_productos' => $totalProductos
        ]);
    }
    
    /**
     * Agrega un producto al carrito (AJAX)
     * @param int $idProducto
     * @param int $cantidad
     */
    private function agregarProductoAjax($idProducto, $cantidad) 
    {
        // Validar datos
        if ($idProducto <= 0 || $cantidad <= 0) {
            $this->responderJson(['exitoso' => false, 'mensaje' => 'Datos inválidos']);
            return;
        }
        
        try {
            // Verificar disponibilidad
            $producto = $this->modeloProducto->obtenerPorId($idProducto);
            
            if (!$producto) {
                $this->responderJson(['exitoso' => false, 'mensaje' => 'El producto no existe']);
                return;
            }
            
            if ($producto['stock'] < $cantidad) {
                $this->responderJson([
                    'exitoso' => false, 
                    'mensaje' => "Solo hay {$producto['stock']} unidades disponibles"
                ]);
                return;
            }
            
            // Obtener ID de usuario o null para carrito de invitado
            $idUsuario = $this->gestorAuth->obtenerUsuarioActual() ? 
                         $this->gestorAuth->obtenerUsuarioActual()['id_usuario'] : null;
            
            // Agregar al carrito
            $this->modeloCarrito->agregarProducto($idProducto, $cantidad, $idUsuario);
            
            // Obtener carrito actualizado
            $carritoActual = $this->modeloCarrito->obtenerProductos($idUsuario);
            $totalProductos = 0;
            
            foreach ($carritoActual as $item) {
                $totalProductos += $item['cantidad'];
            }
            
            // Incluir datos del producto para respuesta
            $infoProducto = [
                'id' => $producto['id_producto'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'imagen' => $producto['imagen'],
                'stock' => $producto['stock']
            ];
            
            // Si hay tienda asociada, obtener nombre del artesano
            if (isset($producto['id_tienda'])) {
                $tienda = $this->modeloProducto->obtenerTienda($producto['id_tienda']);
                $infoProducto['artesano'] = $tienda ? $tienda['nombre'] : '';
            }
            
            $this->responderJson([
                'exitoso' => true,
                'mensaje' => 'Producto agregado al carrito',
                'carrito' => $carritoActual,
                'total_productos' => $totalProductos,
                'producto' => $infoProducto
            ]);
            
        } catch (Exception $e) {
            $this->responderJson(['exitoso' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Actualiza la cantidad de un producto en el carrito (AJAX)
     * @param int $idProducto
     * @param int $cantidad
     */
    private function actualizarCantidadAjax($idProducto, $cantidad) 
    {
        if ($idProducto <= 0) {
            $this->responderJson(['exitoso' => false, 'mensaje' => 'ID de producto inválido']);
            return;
        }
        
        try {
            $idUsuario = $this->gestorAuth->obtenerUsuarioActual() ? 
                         $this->gestorAuth->obtenerUsuarioActual()['id_usuario'] : null;
                         
            // Si la cantidad es 0 o negativa, eliminar el producto
            if ($cantidad <= 0) {
                $this->modeloCarrito->eliminarProducto($idProducto, $idUsuario);
            } else {
                // Verificar stock antes de actualizar
                $producto = $this->modeloProducto->obtenerPorId($idProducto);
                
                if ($producto && $producto['stock'] < $cantidad) {
                    $this->responderJson([
                        'exitoso' => false, 
                        'mensaje' => "Solo hay {$producto['stock']} unidades disponibles"
                    ]);
                    return;
                }
                
                // Actualizar cantidad
                $this->modeloCarrito->actualizarCantidad($idProducto, $cantidad, $idUsuario);
            }
            
            // Obtener carrito actualizado
            $carritoActual = $this->modeloCarrito->obtenerProductos($idUsuario);
            $totalProductos = 0;
            
            foreach ($carritoActual as $item) {
                $totalProductos += $item['cantidad'];
            }
            
            $this->responderJson([
                'exitoso' => true,
                'mensaje' => 'Cantidad actualizada',
                'carrito' => $carritoActual,
                'total_productos' => $totalProductos
            ]);
            
        } catch (Exception $e) {
            $this->responderJson(['exitoso' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Elimina un producto del carrito (AJAX)
     * @param int $idProducto
     */
    private function eliminarProductoAjax($idProducto) 
    {
        if ($idProducto <= 0) {
            $this->responderJson(['exitoso' => false, 'mensaje' => 'ID de producto inválido']);
            return;
        }
        
        try {
            $idUsuario = $this->gestorAuth->obtenerUsuarioActual() ? 
                         $this->gestorAuth->obtenerUsuarioActual()['id_usuario'] : null;
                         
            $this->modeloCarrito->eliminarProducto($idProducto, $idUsuario);
            
            // Obtener carrito actualizado
            $carritoActual = $this->modeloCarrito->obtenerProductos($idUsuario);
            $totalProductos = 0;
            
            foreach ($carritoActual as $item) {
                $totalProductos += $item['cantidad'];
            }
            
            $this->responderJson([
                'exitoso' => true,
                'mensaje' => 'Producto eliminado del carrito',
                'carrito' => $carritoActual,
                'total_productos' => $totalProductos
            ]);
            
        } catch (Exception $e) {
            $this->responderJson(['exitoso' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Vacía el carrito completo (AJAX)
     */
    private function vaciarCarritoAjax() 
    {
        try {
            $idUsuario = $this->gestorAuth->obtenerUsuarioActual() ? 
                         $this->gestorAuth->obtenerUsuarioActual()['id_usuario'] : null;
                         
            $this->modeloCarrito->vaciarCarrito($idUsuario);
            
            $this->responderJson([
                'exitoso' => true,
                'mensaje' => 'Carrito vaciado',
                'carrito' => [],
                'total_productos' => 0
            ]);
            
        } catch (Exception $e) {
            $this->responderJson(['exitoso' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el carrito de compras
     */
    public function mostrarCarrito(): void 
    {
        $productos = [];
        $total = 0;

        if ($this->gestorAuth->estaAutenticado()) {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $productos = $this->modeloCarrito->obtenerProductos($usuario['id_usuario']);
            $total = $this->modeloCarrito->calcularTotal($usuario['id_usuario']);
        }

        $datos = [
            'titulo' => 'Carrito de Compras',
            'productos' => $productos,
            'total' => $total
        ];

        $this->cargarVista('carrito/mostrar', $datos);
    }

    /**
     * Agrega un producto al carrito
     */
    public function agregarProducto(): void 
    {
        if (!$this->gestorAuth->estaAutenticado()) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Debes iniciar sesión para agregar productos al carrito'
            ]);
            return;
        }

        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        
        if ($idProducto <= 0 || $cantidad <= 0) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Datos inválidos'
            ]);
            return;
        }

        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $resultado = $this->modeloCarrito->agregarProducto(
            $usuario['id_usuario'], 
            $idProducto, 
            $cantidad
        );

        $this->responderJSON($resultado);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito
     */
    public function actualizarCantidad(): void 
    {
        if (!$this->gestorAuth->estaAutenticado()) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'No autorizado'
            ]);
            return;
        }

        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $resultado = $this->modeloCarrito->actualizarCantidad(
            $usuario['id_usuario'], 
            $idProducto, 
            $cantidad
        );

        $this->responderJSON($resultado);
    }

    /**
     * Elimina un producto del carrito
     */
    public function eliminarProducto(): void 
    {
        if (!$this->gestorAuth->estaAutenticado()) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'No autorizado'
            ]);
            return;
        }

        $idProducto = (int)($_POST['id_producto'] ?? 0);
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $resultado = $this->modeloCarrito->eliminarProducto(
            $usuario['id_usuario'], 
            $idProducto
        );

        $this->responderJSON($resultado);
    }

    /**
     * Carga una vista
     * @param string $vista
     * @param array $datos
     */
    private function cargarVista(string $vista, array $datos = []): void 
    {
        extract($datos);
        include "views/{$vista}.php";
    }

    /**
     * Responde con JSON
     * @param array $datos
     */
    // Este método ya existe arriba como responderJson
}
