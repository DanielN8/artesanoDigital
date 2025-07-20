<?php
/**
 * Controlador de Checkout - Versión simplificada
 * Gestiona las peticiones AJAX del proceso de checkout
 */

// Asegurarnos de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la configuración de la base de datos y modelos necesarios
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Carrito.php';
require_once __DIR__ . '/../models/Producto.php';

use Models\Carrito;
use Config\Database;

// Verificar que la solicitud sea AJAX
$esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

// Si no es AJAX, redirigir
if (!$esAjax) {
    header('Location: /artesanoDigital');
    exit;
}

// Procesar la acción solicitada
$accion = $_POST['accion'] ?? '';
$respuesta = ['exitoso' => false, 'mensaje' => 'Acción no válida'];

// Verificar si el usuario está autenticado
$usuarioAutenticado = isset($_SESSION['usuario_id']);
$idUsuario = $usuarioAutenticado ? $_SESSION['usuario_id'] : null;

// Instanciar el modelo de carrito
$modeloCarrito = new Carrito();

// Variable para debug
$debug = false;

// Si debug está activado, registrar la solicitud
if ($debug) {
    $logFile = __DIR__ . '/../debug_checkout.log';
    $logContent = date('Y-m-d H:i:s') . " - Acción: $accion\n";
    $logContent .= "POST: " . print_r($_POST, true) . "\n";
    $logContent .= "SESSION: " . print_r($_SESSION, true) . "\n";
    $logContent .= "--------------------\n";
    file_put_contents($logFile, $logContent, FILE_APPEND);
}

switch ($accion) {
    case 'agregar_producto':
        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        
        // Si el usuario está autenticado, usar el carrito de la base de datos
        if ($usuarioAutenticado) {
            $resultado = $modeloCarrito->agregarProducto($idUsuario, $idProducto, $cantidad);
            $respuesta = [
                'exitoso' => $resultado['exitoso'],
                'mensaje' => $resultado['mensaje'],
                'total_productos' => $modeloCarrito->contarProductos($idUsuario)
            ];
            
            // Actualizar el carrito en la sesión para mantener la consistencia
            $_SESSION['carrito'] = $modeloCarrito->obtenerProductos($idUsuario);
        } else {
            // Si no está autenticado, usar el carrito de sesión
            // Obtener información del producto
            $producto = new \Producto();
            $infoProducto = $producto->obtenerPorId($idProducto);
            
            if (!$infoProducto) {
                $respuesta = ['exitoso' => false, 'mensaje' => 'Producto no encontrado'];
                break;
            }
            
            // Inicializar carrito en sesión si no existe
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }
            
            // Buscar si el producto ya está en el carrito
            $encontrado = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id_producto'] == $idProducto) {
                    $item['cantidad'] += $cantidad;
                    $encontrado = true;
                    break;
                }
            }
            
            // Si no estaba en el carrito, agregarlo
            if (!$encontrado) {
                $_SESSION['carrito'][] = [
                    'id_producto' => $idProducto,
                    'nombre' => $infoProducto['nombre'],
                    'precio' => $infoProducto['precio'],
                    'imagen' => $infoProducto['imagen'],
                    'cantidad' => $cantidad,
                    'id_tienda' => $infoProducto['id_tienda'],
                    'stock' => $infoProducto['stock']
                ];
            }
            
            $respuesta = [
                'exitoso' => true,
                'mensaje' => 'Producto agregado al carrito',
                'total_productos' => array_reduce($_SESSION['carrito'], function($total, $item) {
                    return $total + $item['cantidad'];
                }, 0)
            ];
        }
        break;
        
    case 'actualizar_cantidad':
        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        
        if ($usuarioAutenticado) {
            $resultado = $modeloCarrito->actualizarCantidad($idUsuario, $idProducto, $cantidad);
            $respuesta = [
                'exitoso' => $resultado['exitoso'],
                'mensaje' => $resultado['mensaje'],
                'carrito' => $modeloCarrito->obtenerProductos($idUsuario),
                'total' => $modeloCarrito->calcularTotal($idUsuario)
            ];
            
            // Actualizar el carrito en la sesión
            $_SESSION['carrito'] = $respuesta['carrito'];
        } else {
            // Actualizar en el carrito de sesión
            if (isset($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as &$item) {
                    if ($item['id_producto'] == $idProducto) {
                        $item['cantidad'] = $cantidad;
                        break;
                    }
                }
                
                // Calcular el nuevo total
                $total = array_reduce($_SESSION['carrito'], function($total, $item) {
                    return $total + ($item['precio'] * $item['cantidad']);
                }, 0);
                
                $respuesta = [
                    'exitoso' => true,
                    'mensaje' => 'Cantidad actualizada',
                    'carrito' => $_SESSION['carrito'],
                    'total' => $total
                ];
            }
        }
        break;
        
    case 'eliminar_producto':
        $idProducto = (int)($_POST['id_producto'] ?? 0);
        
        if ($usuarioAutenticado) {
            $resultado = $modeloCarrito->eliminarProducto($idUsuario, $idProducto);
            $respuesta = [
                'exitoso' => $resultado['exitoso'],
                'mensaje' => $resultado['mensaje'],
                'carrito' => $modeloCarrito->obtenerProductos($idUsuario),
                'total' => $modeloCarrito->calcularTotal($idUsuario)
            ];
            
            // Actualizar el carrito en la sesión
            $_SESSION['carrito'] = $respuesta['carrito'];
        } else {
            // Eliminar del carrito de sesión
            if (isset($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as $key => $item) {
                    if ($item['id_producto'] == $idProducto) {
                        unset($_SESSION['carrito'][$key]);
                        break;
                    }
                }
                
                // Reindexar el array
                $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                
                // Calcular el nuevo total
                $total = array_reduce($_SESSION['carrito'], function($total, $item) {
                    return $total + ($item['precio'] * $item['cantidad']);
                }, 0);
                
                $respuesta = [
                    'exitoso' => true,
                    'mensaje' => 'Producto eliminado del carrito',
                    'carrito' => $_SESSION['carrito'],
                    'total' => $total
                ];
            }
        }
        break;
        
    case 'sincronizar_carrito':
        // Sincroniza el carrito localStorage con el carrito de sesión
        $carritoLocal = json_decode($_POST['carrito_local'] ?? '[]', true);
        
        if ($usuarioAutenticado) {
            // Si está autenticado, primero vaciar su carrito y luego agregar los productos
            $modeloCarrito->vaciarCarrito($idUsuario);
            
            foreach ($carritoLocal as $item) {
                $modeloCarrito->agregarProducto(
                    $idUsuario, 
                    (int)($item['id'] ?? 0), 
                    (int)($item['cantidad'] ?? 1)
                );
            }
            
            $respuesta = [
                'exitoso' => true,
                'mensaje' => 'Carrito sincronizado correctamente',
                'carrito' => $modeloCarrito->obtenerProductos($idUsuario),
                'total' => $modeloCarrito->calcularTotal($idUsuario)
            ];
            
            // Actualizar el carrito en la sesión
            $_SESSION['carrito'] = $respuesta['carrito'];
        } else {
            // Si no está autenticado, actualizar directamente el carrito de sesión
            $_SESSION['carrito'] = [];
            
            // Obtener información completa de cada producto
            $producto = new \Producto();
            
            foreach ($carritoLocal as $item) {
                $idProducto = (int)($item['id'] ?? 0);
                $cantidad = (int)($item['cantidad'] ?? 1);
                
                $infoProducto = $producto->obtenerPorId($idProducto);
                
                if ($infoProducto) {
                    $_SESSION['carrito'][] = [
                        'id_producto' => $idProducto,
                        'nombre' => $infoProducto['nombre'],
                        'precio' => $infoProducto['precio'],
                        'imagen' => $infoProducto['imagen'],
                        'cantidad' => $cantidad,
                        'id_tienda' => $infoProducto['id_tienda'],
                        'stock' => $infoProducto['stock']
                    ];
                }
            }
            
            $respuesta = [
                'exitoso' => true,
                'mensaje' => 'Carrito sincronizado correctamente',
                'carrito' => $_SESSION['carrito']
            ];
        }
        break;
        
    case 'obtener_carrito':
        if ($usuarioAutenticado) {
            $productos = $modeloCarrito->obtenerProductos($idUsuario);
            $total = $modeloCarrito->calcularTotal($idUsuario);
            
            $respuesta = [
                'exitoso' => true,
                'carrito' => $productos,
                'total' => $total,
                'total_productos' => count($productos)
            ];
            
            // Actualizar el carrito en la sesión
            $_SESSION['carrito'] = $productos;
        } else {
            // Devolver el carrito de sesión
            $respuesta = [
                'exitoso' => true,
                'carrito' => $_SESSION['carrito'] ?? [],
                'total' => array_reduce($_SESSION['carrito'] ?? [], function($total, $item) {
                    return $total + ($item['precio'] * $item['cantidad']);
                }, 0),
                'total_productos' => array_reduce($_SESSION['carrito'] ?? [], function($total, $item) {
                    return $total + $item['cantidad'];
                }, 0)
            ];
        }
        break;
        
    case 'vaciar_carrito':
        if ($usuarioAutenticado) {
            // Vaciar el carrito en la base de datos
            $resultado = $modeloCarrito->vaciarCarrito($idUsuario);
            
            // Vaciar también el carrito en la sesión
            $_SESSION['carrito'] = [];
            
            $respuesta = [
                'exitoso' => $resultado,
                'mensaje' => $resultado ? 'Carrito vaciado correctamente' : 'Error al vaciar el carrito',
                'carrito' => [],
                'total' => 0
            ];
        } else {
            // Vaciar el carrito de la sesión
            $_SESSION['carrito'] = [];
            
            $respuesta = [
                'exitoso' => true,
                'mensaje' => 'Carrito vaciado correctamente',
                'carrito' => [],
                'total' => 0
            ];
        }
        break;
        
    default:
        $respuesta = ['exitoso' => false, 'mensaje' => 'Acción no reconocida'];
}

// Enviar respuesta como JSON
header('Content-Type: application/json');
echo json_encode($respuesta);
exit;
