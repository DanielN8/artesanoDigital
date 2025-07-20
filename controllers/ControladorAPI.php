<?php
/**
 * Controlador API
 * Responsabilidad: Gestionar endpoints API para AJAX
 */

namespace Controllers;

use Models\Usuario;
use Models\Pedido;
use Config\Database;
use Patrones\DecoradorNotificacion;
use Utils\GestorAutenticacion;
use Exception;
use PDO;

class ControladorAPI 
{
    private GestorAutenticacion $gestorAuth;
    private PDO $conexion;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->conexion = Database::obtenerInstancia()->obtenerConexion();
        
        // Configurar headers para API
        header('Content-Type: application/json');
        
        // Endpoints públicos que no requieren autenticación
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $publicEndpoints = [
            '/artesanoDigital/api/notificaciones'
        ];
        
        // Solo verificar autenticación para endpoints protegidos
        if (!in_array($requestUri, $publicEndpoints) && !$this->gestorAuth->estaAutenticado()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
    }

    /**
     * Obtiene notificaciones del usuario actual
     */
    public function obtenerNotificaciones(): void 
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            
            // Por ahora retornamos datos de prueba
            $notificaciones = [
                [
                    'id' => 1,
                    'tipo' => 'nuevo_pedido',
                    'mensaje' => 'Nuevo pedido recibido por $85.00',
                    'leida' => false,
                    'fecha' => '2024-12-01 10:30:00'
                ],
                [
                    'id' => 2,
                    'tipo' => 'stock_bajo',
                    'mensaje' => 'Stock bajo en Mola Tradicional (2 unidades)',
                    'leida' => false,
                    'fecha' => '2024-12-01 09:15:00'
                ]
            ];

            echo json_encode([
                'exitoso' => true,
                'notificaciones' => $notificaciones
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Marca una notificación como leída
     */
    public function marcarNotificacionLeida(): void 
    {
        try {
            $idNotificacion = (int)($_POST['id'] ?? 0);
            
            if ($idNotificacion <= 0) {
                http_response_code(400);
                echo json_encode([
                    'exitoso' => false,
                    'mensaje' => 'ID de notificación inválido'
                ]);
                return;
            }

            // Implementar marcado como leída
            // Por ahora simulamos éxito
            echo json_encode([
                'exitoso' => true,
                'mensaje' => 'Notificación marcada como leída'
            ]);

        } catch (Exception $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Obtiene información del carrito (contador de productos)
     */
    public function obtenerInfoCarrito(): void 
    {
        try {
            header('Content-Type: application/json');
            $cantidad = 0;
            $total = 0.00;
            
            // Verificamos autenticación
            if ($this->gestorAuth->estaAutenticado()) {
                $usuario = $this->gestorAuth->obtenerUsuarioActual();
                
                // Creamos instancia del modelo carrito
                require_once dirname(__FILE__) . '/../models/Carrito.php';
                $modeloCarrito = new \Models\Carrito();
                
                // Obtenemos datos reales
                $cantidad = $modeloCarrito->contarProductos($usuario['id_usuario']);
                $total = $modeloCarrito->calcularTotal($usuario['id_usuario']);
                
                // Actualizamos el contador en sesión para el header
                $_SESSION['carrito_total'] = $cantidad;
            } else {
                // Carrito de sesión para usuarios no autenticados
                if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
                    foreach ($_SESSION['carrito'] as $item) {
                        $cantidad += (int)$item['cantidad'];
                        $total += (float)$item['precio'] * (int)$item['cantidad'];
                    }
                }
                $_SESSION['carrito_total'] = $cantidad;
            }
            
            echo json_encode([
                'exitoso' => true,
                'cantidad_productos' => $cantidad,
                'total' => $total
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener info del carrito: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error al obtener información del carrito'
            ]);
        }
    }

    /**
     * Búsqueda de productos para autocompletado
     */
    public function buscarProductos(): void 
    {
        try {
            $termino = $_GET['q'] ?? '';
            
            if (strlen($termino) < 2) {
                echo json_encode([
                    'exitoso' => true,
                    'productos' => []
                ]);
                return;
            }

            // Por ahora retornamos datos de prueba
            $productos = [
                ['id' => 1, 'nombre' => 'Mola Tradicional', 'precio' => 85.00],
                ['id' => 2, 'nombre' => 'Vasija de Cerámica', 'precio' => 45.00]
            ];

            echo json_encode([
                'exitoso' => true,
                'productos' => $productos
            ]);

        } catch (Exception $e) {
            error_log("Error en búsqueda de productos: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error en búsqueda'
            ]);
        }
    }
    
    /**
     * Obtiene los detalles de un pedido (para artesanos)
     * Usado para ver los pedidos recibidos en sus productos
     */
    public function obtenerDetallesPedido($idPedido): void
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idUsuario = $usuario['id_usuario'];
            
            // Verificar que el usuario sea un artesano
            if ($usuario['tipo_usuario'] !== 'artesano') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            // Obtener el ID de la tienda del artesano
            $stmt = $this->conexion->prepare("
                SELECT id_tienda FROM tiendas WHERE id_usuario = ?
            ");
            $stmt->execute([$idUsuario]);
            $tienda = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tienda) {
                http_response_code(404);
                echo json_encode(['error' => 'Tienda no encontrada']);
                return;
            }
            
            $idTienda = $tienda['id_tienda'];
            
            // Obtener información del pedido
            $stmt = $this->conexion->prepare("
                SELECT p.id_pedido, p.fecha_pedido, p.estado, p.metodo_pago, p.total, p.direccion_envio,
                       u.id_usuario, u.nombre, u.correo, u.telefono
                FROM pedidos p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
                JOIN productos prod ON pp.id_producto = prod.id_producto
                WHERE p.id_pedido = ? AND prod.id_tienda = ?
                LIMIT 1
            ");
            $stmt->execute([$idPedido, $idTienda]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pedido) {
                http_response_code(404);
                echo json_encode(['error' => 'Pedido no encontrado o no pertenece a tu tienda']);
                return;
            }
            
            // Obtener productos del pedido que pertenezcan a la tienda del artesano
            $stmt = $this->conexion->prepare("
                SELECT pp.id_producto, pp.cantidad, pp.precio_unitario,
                       p.nombre, p.imagen
                FROM pedido_productos pp
                JOIN productos p ON pp.id_producto = p.id_producto
                WHERE pp.id_pedido = ? AND p.id_tienda = ?
            ");
            $stmt->execute([$idPedido, $idTienda]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear respuesta
            $respuesta = [
                'id_pedido' => $pedido['id_pedido'],
                'fecha_pedido' => $pedido['fecha_pedido'],
                'estado' => $pedido['estado'],
                'metodo_pago' => $pedido['metodo_pago'],
                'total' => $pedido['total'],
                'direccion_envio' => $pedido['direccion_envio'],
                'cliente' => [
                    'id_usuario' => $pedido['id_usuario'],
                    'nombre' => $pedido['nombre'],
                    'email' => $pedido['correo'],
                    'telefono' => $pedido['telefono']
                ],
                'productos' => $productos
            ];
            
            echo json_encode($respuesta);
            
        } catch (Exception $e) {
            error_log("Error al obtener detalles del pedido: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al obtener detalles del pedido',
                'mensaje' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene los detalles de un pedido personal (artesano como cliente)
     */
    public function obtenerDetallesPedidoPersonal($idPedido): void
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idUsuario = $usuario['id_usuario'];
            
            // Obtener información del pedido
            $stmt = $this->conexion->prepare("
                SELECT p.id_pedido, p.fecha_pedido, p.estado, p.metodo_pago, p.total, p.direccion_envio
                FROM pedidos p
                WHERE p.id_pedido = ? AND p.id_usuario = ?
            ");
            $stmt->execute([$idPedido, $idUsuario]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pedido) {
                http_response_code(404);
                echo json_encode(['error' => 'Pedido no encontrado o no te pertenece']);
                return;
            }
            
            // Obtener productos del pedido
            $stmt = $this->conexion->prepare("
                SELECT pp.id_producto, pp.cantidad, pp.precio_unitario,
                       p.nombre, p.imagen,
                       t.nombre_tienda as vendedor
                FROM pedido_productos pp
                JOIN productos p ON pp.id_producto = p.id_producto
                JOIN tiendas t ON p.id_tienda = t.id_tienda
                WHERE pp.id_pedido = ?
            ");
            $stmt->execute([$idPedido]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear respuesta
            $respuesta = [
                'id_pedido' => $pedido['id_pedido'],
                'fecha_pedido' => $pedido['fecha_pedido'],
                'estado' => $pedido['estado'],
                'metodo_pago' => $pedido['metodo_pago'],
                'total' => $pedido['total'],
                'direccion_envio' => $pedido['direccion_envio'],
                'productos' => $productos
            ];
            
            echo json_encode($respuesta);
            
        } catch (Exception $e) {
            error_log("Error al obtener detalles del pedido personal: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al obtener detalles del pedido',
                'mensaje' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Actualiza el estado de un pedido
     */
    public function actualizarEstadoPedido($idPedido): void
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idUsuario = $usuario['id_usuario'];
            
            // Verificar que el usuario sea un artesano
            if ($usuario['tipo_usuario'] !== 'artesano') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            // Verificar método PUT
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                throw new Exception("Método no permitido");
            }
            
            // Obtener datos enviados
            $json = file_get_contents('php://input');
            $datos = json_decode($json, true);
            
            if (!$datos || !isset($datos['estado']) || !in_array($datos['estado'], ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'])) {
                throw new Exception("Datos inválidos");
            }
            
            $estado = $datos['estado'];
            $comentario = $datos['comentario'] ?? '';
            
            // Obtener el ID de la tienda del artesano
            $stmt = $this->conexion->prepare("
                SELECT id_tienda FROM tiendas WHERE id_usuario = ?
            ");
            $stmt->execute([$idUsuario]);
            $tienda = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tienda) {
                http_response_code(404);
                echo json_encode(['error' => 'Tienda no encontrada']);
                return;
            }
            
            $idTienda = $tienda['id_tienda'];
            
            // Verificar que el pedido contenga productos de la tienda del artesano
            $stmt = $this->conexion->prepare("
                SELECT DISTINCT p.id_pedido, p.id_usuario
                FROM pedidos p
                JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
                JOIN productos prod ON pp.id_producto = prod.id_producto
                WHERE p.id_pedido = ? AND prod.id_tienda = ?
                LIMIT 1
            ");
            $stmt->execute([$idPedido, $idTienda]);
            $pedidoInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pedidoInfo) {
                http_response_code(404);
                echo json_encode(['error' => 'Pedido no encontrado o no contiene productos de tu tienda']);
                return;
            }
            
            // Actualizar estado del pedido
            $stmt = $this->conexion->prepare("
                UPDATE pedidos SET estado = ? WHERE id_pedido = ?
            ");
            $resultado = $stmt->execute([$estado, $idPedido]);
            
            if (!$resultado) {
                throw new Exception("Error al actualizar el estado del pedido");
            }
            
            // Crear notificación para el cliente
            $mensaje = "El estado de tu pedido #" . str_pad($idPedido, 5, '0', STR_PAD_LEFT) . " ha sido actualizado a " . ucfirst($estado);
            if (!empty($comentario)) {
                $mensaje .= ". Mensaje del vendedor: " . $comentario;
            }
            
            $stmt = $this->conexion->prepare("
                INSERT INTO notificaciones (id_usuario, tipo, mensaje)
                VALUES (?, 'estado_actualizado', ?)
            ");
            $stmt->execute([$pedidoInfo['id_usuario'], $mensaje]);
            
            echo json_encode([
                'exitoso' => true,
                'mensaje' => 'Estado del pedido actualizado correctamente'
            ]);
            
        } catch (Exception $e) {
            error_log("Error al actualizar estado del pedido: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error al actualizar estado del pedido: ' . $e->getMessage()
            ]);
        }
    }
}
