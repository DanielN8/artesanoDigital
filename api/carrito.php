<?php
/**
 * API del Carrito de Compras
 * Endpoint para operaciones AJAX del carrito
 */

session_start();

// Headers para API JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir el modelo del carrito
require_once dirname(__FILE__) . '/../models/Carrito.php';

// Función para enviar respuesta JSON
function enviarRespuesta($datos, $codigoHttp = 200) {
    http_response_code($codigoHttp);
    echo json_encode($datos);
    exit;
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    enviarRespuesta([
        'exitoso' => false,
        'mensaje' => 'Usuario no autenticado'
    ], 401);
}

try {
    $carrito = new Models\Carrito();
    
    // Obtener la acción solicitada
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener datos JSON del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $accion = $input['accion'] ?? $accion;
        }
    }
    
    $idUsuario = $_SESSION['usuario_id'];
    
    switch ($accion) {
        case 'agregar':
            $idProducto = $input['id_producto'] ?? $_POST['id_producto'] ?? null;
            $cantidad = $input['cantidad'] ?? $_POST['cantidad'] ?? 1;
            
            if (!$idProducto) {
                enviarRespuesta([
                    'exitoso' => false,
                    'mensaje' => 'ID del producto requerido'
                ], 400);
            }
            
            $resultado = $carrito->agregarProducto($idProducto, $cantidad, $idUsuario);
            
            if ($resultado) {
                // Obtener información actualizada del carrito
                $productosCarrito = $carrito->obtenerProductos($idUsuario);
                $total = $carrito->calcularTotal($idUsuario);
                $cantidadTotal = $carrito->contarProductos($idUsuario);
                
                enviarRespuesta([
                    'exitoso' => true,
                    'mensaje' => 'Producto agregado al carrito exitosamente',
                    'carrito' => [
                        'productos' => $productosCarrito,
                        'total' => $total,
                        'cantidad_total' => $cantidadTotal
                    ]
                ]);
            } else {
                enviarRespuesta([
                    'exitoso' => false,
                    'mensaje' => 'Error al agregar producto al carrito'
                ], 500);
            }
            break;
            
        case 'obtener':
            $productos = $carrito->obtenerProductos($idUsuario);
            $total = $carrito->calcularTotal($idUsuario);
            $cantidadTotal = $carrito->contarProductos($idUsuario);
            
            enviarRespuesta([
                'exitoso' => true,
                'carrito' => [
                    'productos' => $productos,
                    'total' => $total,
                    'cantidad_total' => $cantidadTotal
                ]
            ]);
            break;
            
        case 'actualizar':
            $idProducto = $input['id_producto'] ?? $_POST['id_producto'] ?? null;
            $cantidad = $input['cantidad'] ?? $_POST['cantidad'] ?? null;
            
            if (!$idProducto || !$cantidad) {
                enviarRespuesta([
                    'exitoso' => false,
                    'mensaje' => 'ID del producto y cantidad requeridos'
                ], 400);
            }
            
            $resultado = $carrito->actualizarCantidad($idProducto, $cantidad, $idUsuario);
            
            if ($resultado) {
                $total = $carrito->calcularTotal($idUsuario);
                $cantidadTotal = $carrito->contarProductos($idUsuario);
                
                enviarRespuesta([
                    'exitoso' => true,
                    'mensaje' => 'Cantidad actualizada',
                    'carrito' => [
                        'total' => $total,
                        'cantidad_total' => $cantidadTotal
                    ]
                ]);
            } else {
                enviarRespuesta([
                    'exitoso' => false,
                    'mensaje' => 'Error al actualizar cantidad'
                ], 500);
            }
            break;
            
        case 'eliminar':
            $idProducto = $input['id_producto'] ?? $_POST['id_producto'] ?? $_GET['id_producto'] ?? null;
            
            if (!$idProducto) {
                enviarRespuesta([
                    'exitoso' => false,
                    'mensaje' => 'ID del producto requerido'
                ], 400);
            }
            
            $resultado = $carrito->eliminarProducto($idProducto, $idUsuario);
            
            if ($resultado) {
                $total = $carrito->calcularTotal($idUsuario);
                $cantidadTotal = $carrito->contarProductos($idUsuario);
                
                enviarRespuesta([
                    'exitoso' => true,
                    'mensaje' => 'Producto eliminado del carrito',
                    'carrito' => [
                        'total' => $total,
                        'cantidad_total' => $cantidadTotal
                    ]
                ]);
            } else {
                enviarRespuesta([
                    'exitoso' => false,
                    'mensaje' => 'Error al eliminar producto'
                ], 500);
            }
            break;
            
        case 'vaciar':
            $resultado = $carrito->vaciarCarrito($idUsuario);
            
            if ($resultado) {
                enviarRespuesta([
                    'exitoso' => true,
                    'mensaje' => 'Carrito vaciado exitosamente',
                    'carrito' => [
                        'productos' => [],
                        'total' => 0,
                        'cantidad_total' => 0
                    ]
                ]);
            } else {
                enviarRespuesta([
                    'exitoso' => false,
                    'mensaje' => 'Error al vaciar el carrito'
                ], 500);
            }
            break;
            
        default:
            enviarRespuesta([
                'exitoso' => false,
                'mensaje' => 'Acción no válida'
            ], 400);
    }
    
} catch (Exception $e) {
    error_log("Error en API del carrito: " . $e->getMessage());
    enviarRespuesta([
        'exitoso' => false,
        'mensaje' => 'Error interno del servidor: ' . $e->getMessage()
    ], 500);
}
?>
