<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once dirname(__FILE__) . '/../config/Database.php';
require_once dirname(__FILE__) . '/../models/Pedido.php';

use Config\Database;
use Models\Pedido;

try {
    $database = Database::obtenerInstancia();
    $db = $database->obtenerConexion();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    $pathParts = explode('/', trim($path, '/'));
    
    switch ($method) {
        case 'GET':
            if (isset($pathParts[0]) && is_numeric($pathParts[0])) {
                $pedidoId = $pathParts[0];
                
                if (isset($pathParts[1]) && $pathParts[1] === 'detalles') {
                    // Obtener detalles del pedido
                    obtenerDetallesPedido($db, $pedidoId);
                } else {
                    // Obtener pedido individual
                    obtenerPedido($db, $pedidoId);
                }
            } else {
                // Listar pedidos del artesano
                listarPedidosArtesano($db);
            }
            break;
            
        case 'POST':
            if (isset($pathParts[0]) && is_numeric($pathParts[0])) {
                $pedidoId = $pathParts[0];
                
                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'estado':
                            actualizarEstadoPedido($db, $pedidoId);
                            break;
                        case 'envio':
                            actualizarInfoEnvio($db, $pedidoId);
                            break;
                        case 'direccion':
                            actualizarDireccionEnvio($db, $pedidoId);
                            break;
                        case 'evento':
                            agregarEventoPedido($db, $pedidoId);
                            break;
                        default:
                            http_response_code(404);
                            echo json_encode(['error' => 'Endpoint no encontrado']);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Acción no especificada']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID de pedido requerido']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }

} catch (Exception $e) {
    error_log("Error en API pedidos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

function listarPedidosArtesano($db) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Consulta para obtener pedidos donde el artesano tenga productos
        // Calculando solo el total de SUS productos en cada pedido
        $query = "
            SELECT 
                p.id_pedido,
                p.fecha_pedido,
                SUM(pp.cantidad * pp.precio_unitario) as total,
                p.estado,
                p.metodo_pago,
                p.direccion_envio,
                p.empresa_envio,
                p.numero_seguimiento,
                p.fecha_estimada_entrega,
                p.notas_entrega,
                u.nombre as cliente_nombre,
                u.correo as cliente_email,
                u.telefono as cliente_telefono,
                u.direccion as cliente_direccion
            FROM pedidos p
            INNER JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE t.id_usuario = ?
            GROUP BY p.id_pedido, p.fecha_pedido, p.estado, p.metodo_pago, p.direccion_envio, 
                     p.empresa_envio, p.numero_seguimiento, p.fecha_estimada_entrega, p.notas_entrega,
                     u.nombre, u.correo, u.telefono, u.direccion
            ORDER BY p.fecha_pedido DESC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$artesanoId]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $pedidos
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener pedidos: ' . $e->getMessage()]);
    }
}

function obtenerPedido($db, $pedidoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Obtener información del pedido con total calculado solo para productos del artesano
        $query = "
            SELECT 
                p.id_pedido,
                p.fecha_pedido,
                SUM(pp.cantidad * pp.precio_unitario) as total,
                p.estado,
                p.metodo_pago,
                p.direccion_envio,
                p.empresa_envio,
                p.numero_seguimiento,
                p.fecha_estimada_entrega,
                p.notas_entrega,
                u.nombre as cliente_nombre,
                u.correo as cliente_email,
                u.telefono as cliente_telefono,
                u.direccion as cliente_direccion,
                u.fecha_registro as cliente_registro
            FROM pedidos p
            INNER JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE p.id_pedido = ? AND t.id_usuario = ?
            GROUP BY p.id_pedido, p.fecha_pedido, p.estado, p.metodo_pago, p.direccion_envio,
                     p.empresa_envio, p.numero_seguimiento, p.fecha_estimada_entrega, p.notas_entrega,
                     u.nombre, u.correo, u.telefono, u.direccion, u.fecha_registro
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$pedidoId, $artesanoId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $pedido
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener pedido: ' . $e->getMessage()]);
    }
}

function obtenerDetallesPedido($db, $pedidoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Primero obtener información básica del pedido
        $pedidoQuery = "
            SELECT 
                p.id_pedido,
                p.fecha_pedido,
                p.estado,
                p.metodo_pago,
                p.direccion_envio,
                p.empresa_envio,
                p.numero_seguimiento,
                p.fecha_estimada_entrega,
                p.notas_entrega,
                NULL as fecha_envio,
                u.nombre as cliente_nombre,
                u.correo as cliente_email,
                u.telefono as cliente_telefono,
                u.direccion as cliente_direccion
            FROM pedidos p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            WHERE p.id_pedido = ? AND t.id_usuario = ?
            GROUP BY p.id_pedido
        ";
        
        $stmt = $db->prepare($pedidoQuery);
        $stmt->execute([$pedidoId, $artesanoId]);
        $pedidoInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedidoInfo) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        // Obtener productos del pedido que pertenezcan al artesano
        $productosQuery = "
            SELECT 
                pp.id_producto,
                pr.nombre,
                pr.descripcion,
                pr.imagen,
                pp.cantidad,
                pp.precio_unitario,
                (pp.cantidad * pp.precio_unitario) as subtotal
            FROM pedido_productos pp
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            WHERE pp.id_pedido = ? AND t.id_usuario = ?
            ORDER BY pr.nombre
        ";
        
        $stmt = $db->prepare($productosQuery);
        $stmt->execute([$pedidoId, $artesanoId]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($productos)) {
            http_response_code(404);
            echo json_encode(['error' => 'No hay productos del artesano en este pedido']);
            return;
        }
        
        // Calcular totales solo de los productos del artesano
        $subtotal = array_sum(array_column($productos, 'subtotal'));
        $costoEnvio = 0.00; // Envío gratis
        $descuentos = 0.00; // Por ahora sin descuentos
        $total = $subtotal + $costoEnvio - $descuentos;
        $total = $subtotal + $costoEnvio - $descuentos;
        
        // Estructurar respuesta JSON coherente
        $respuesta = [
            'id_pedido' => $pedidoInfo['id_pedido'],
            'numero_pedido' => '#' . str_pad($pedidoInfo['id_pedido'], 5, '0', STR_PAD_LEFT),
            'fecha_pedido' => $pedidoInfo['fecha_pedido'],
            'estado' => $pedidoInfo['estado'],
            'metodo_pago' => $pedidoInfo['metodo_pago'],
            'total' => number_format($total, 2),
            'cliente' => [
                'nombre' => $pedidoInfo['cliente_nombre'],
                'correo' => $pedidoInfo['cliente_email'],
                'telefono' => $pedidoInfo['cliente_telefono'],
                'direccion' => $pedidoInfo['direccion_envio'] ?: $pedidoInfo['cliente_direccion']
            ],
            'productos' => array_map(function($producto) {
                return [
                    'id_producto' => $producto['id_producto'],
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'imagen' => $producto['imagen'] ?: '/artesanoDigital/public/placeholder.jpg',
                    'cantidad' => intval($producto['cantidad']),
                    'precio_unitario' => number_format($producto['precio_unitario'], 2),
                    'subtotal' => number_format($producto['subtotal'], 2)
                ];
            }, $productos),
            'resumen_financiero' => [
                'subtotal' => number_format($subtotal, 2),
                'descuentos' => number_format($descuentos, 2),
                'costo_envio' => number_format($costoEnvio, 2),
                'total' => number_format($total, 2)
            ],
            'envio' => [
                'empresa' => $pedidoInfo['empresa_envio'],
                'numero_seguimiento' => $pedidoInfo['numero_seguimiento'],
                'fecha_envio' => $pedidoInfo['fecha_envio'],
                'fecha_estimada_entrega' => $pedidoInfo['fecha_estimada_entrega'],
                'notas' => $pedidoInfo['notas_entrega']
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $respuesta
        ]);
        
    } catch (Exception $e) {
        error_log("Error en obtenerDetallesPedido: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles: ' . $e->getMessage()]);
    }
}

function actualizarEstadoPedido($db, $pedidoId) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['estado'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Estado requerido']);
            return;
        }
        
        $nuevoEstado = $input['estado'];
        $estadosValidos = ['pendiente', 'en_proceso', 'en_camino', 'entregado', 'cancelado'];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            http_response_code(400);
            echo json_encode(['error' => 'Estado no válido']);
            return;
        }
        
        $artesanoId = $_SESSION['usuario_id'];
        
        // Verificar que el pedido pertenezca al artesano
        $verificarQuery = "
            SELECT COUNT(*) 
            FROM pedidos p
            INNER JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            WHERE p.id_pedido = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($verificarQuery);
        $stmt->execute([$pedidoId, $artesanoId]);
        
        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        // Actualizar estado
        $updateQuery = "UPDATE pedidos SET estado = ?, fecha_actualizacion = NOW() WHERE id_pedido = ?";
        $stmt = $db->prepare($updateQuery);
        $resultado = $stmt->execute([$nuevoEstado, $pedidoId]);
        
        if ($resultado) {
            // Registrar evento en historial (si existe tabla de eventos)
            registrarEventoPedido($db, $pedidoId, 'estado_cambio', "Estado cambiado a: $nuevoEstado");
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
        } else {
            throw new Exception('Error al actualizar el estado');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar estado: ' . $e->getMessage()]);
    }
}

function actualizarInfoEnvio($db, $pedidoId) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $artesanoId = $_SESSION['usuario_id'];
        
        // Verificar que el pedido pertenezca al artesano
        $verificarQuery = "
            SELECT COUNT(*) 
            FROM pedidos p
            INNER JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            WHERE p.id_pedido = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($verificarQuery);
        $stmt->execute([$pedidoId, $artesanoId]);
        
        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        // Construir query de actualización dinámicamente
        $setClauses = [];
        $valores = [];
        
        if (isset($input['empresa_envio'])) {
            $setClauses[] = "empresa_envio = ?";
            $valores[] = $input['empresa_envio'];
        }
        
        if (isset($input['numero_seguimiento'])) {
            $setClauses[] = "numero_seguimiento = ?";
            $valores[] = $input['numero_seguimiento'];
        }
        
        if (isset($input['fecha_envio'])) {
            $setClauses[] = "fecha_envio = ?";
            $valores[] = $input['fecha_envio'];
        }
        
        if (isset($input['fecha_estimada_entrega'])) {
            $setClauses[] = "fecha_estimada_entrega = ?";
            $valores[] = $input['fecha_estimada_entrega'];
        }
        
        if (empty($setClauses)) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay campos para actualizar']);
            return;
        }
        
        $setClauses[] = "fecha_actualizacion = NOW()";
        $valores[] = $pedidoId;
        
        $updateQuery = "UPDATE pedidos SET " . implode(', ', $setClauses) . " WHERE id_pedido = ?";
        $stmt = $db->prepare($updateQuery);
        $resultado = $stmt->execute($valores);
        
        if ($resultado) {
            // Registrar evento
            $descripcion = "Información de envío actualizada";
            if (isset($input['numero_seguimiento'])) {
                $descripcion .= " - Número de seguimiento: " . $input['numero_seguimiento'];
            }
            registrarEventoPedido($db, $pedidoId, 'envio', $descripcion);
            
            echo json_encode([
                'success' => true,
                'message' => 'Información de envío actualizada'
            ]);
        } else {
            throw new Exception('Error al actualizar información de envío');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar envío: ' . $e->getMessage()]);
    }
}

function actualizarDireccionEnvio($db, $pedidoId) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $artesanoId = $_SESSION['usuario_id'];
        
        if (!isset($input['direccion_envio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dirección de envío requerida']);
            return;
        }
        
        // Verificar que el pedido pertenezca al artesano
        $verificarQuery = "
            SELECT COUNT(*) 
            FROM pedidos p
            INNER JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            WHERE p.id_pedido = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($verificarQuery);
        $stmt->execute([$pedidoId, $artesanoId]);
        
        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        // Actualizar dirección
        $updateQuery = "
            UPDATE pedidos SET 
                direccion_envio = ?,
                notas_entrega = ?,
                fecha_actualizacion = NOW()
            WHERE id_pedido = ?
        ";
        
        $stmt = $db->prepare($updateQuery);
        $resultado = $stmt->execute([
            $input['direccion_envio'],
            $input['notas_entrega'] ?? '',
            $pedidoId
        ]);
        
        if ($resultado) {
            registrarEventoPedido($db, $pedidoId, 'envio', 'Dirección de envío actualizada');
            
            echo json_encode([
                'success' => true,
                'message' => 'Dirección actualizada correctamente'
            ]);
        } else {
            throw new Exception('Error al actualizar la dirección');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar dirección: ' . $e->getMessage()]);
    }
}

function agregarEventoPedido($db, $pedidoId) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['tipo']) || !isset($input['descripcion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo y descripción requeridos']);
            return;
        }
        
        $artesanoId = $_SESSION['usuario_id'];
        
        // Verificar que el pedido pertenezca al artesano
        $verificarQuery = "
            SELECT COUNT(*) 
            FROM pedidos p
            INNER JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
            INNER JOIN productos pr ON pp.id_producto = pr.id_producto
            INNER JOIN tiendas t ON pr.id_tienda = t.id_tienda
            WHERE p.id_pedido = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($verificarQuery);
        $stmt->execute([$pedidoId, $artesanoId]);
        
        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado']);
            return;
        }
        
        // Registrar evento
        registrarEventoPedido($db, $pedidoId, $input['tipo'], $input['descripcion']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Evento agregado correctamente'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al agregar evento: ' . $e->getMessage()]);
    }
}

function registrarEventoPedido($db, $pedidoId, $tipo, $descripcion) {
    try {
        // Verificar si existe tabla de eventos de pedidos
        $checkTableQuery = "SHOW TABLES LIKE 'pedido_eventos'";
        $stmt = $db->prepare($checkTableQuery);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // Crear tabla de eventos si no existe
            $createTableQuery = "
                CREATE TABLE pedido_eventos (
                    id_evento INT AUTO_INCREMENT PRIMARY KEY,
                    id_pedido INT NOT NULL,
                    tipo ENUM('estado_cambio', 'envio', 'comunicacion', 'problema', 'nota') NOT NULL,
                    descripcion TEXT NOT NULL,
                    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    id_usuario INT,
                    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
                    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
                )
            ";
            $db->exec($createTableQuery);
        }
        
        // Insertar evento
        $insertQuery = "
            INSERT INTO pedido_eventos (id_pedido, tipo, descripcion, id_usuario)
            VALUES (?, ?, ?, ?)
        ";
        
        $stmt = $db->prepare($insertQuery);
        $stmt->execute([$pedidoId, $tipo, $descripcion, $_SESSION['usuario_id']]);
        
    } catch (Exception $e) {
        error_log("Error registrando evento: " . $e->getMessage());
        // No lanzar error para no interrumpir la operación principal
    }
}
?>
