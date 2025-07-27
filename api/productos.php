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
require_once dirname(__FILE__) . '/../models/Producto.php';

use Config\Database;

try {
    $database = Database::obtenerInstancia();
    $db = $database->obtenerConexion();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    $pathParts = explode('/', trim($path, '/'));
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['accion']) && $_GET['accion'] === 'obtener_tienda') {
                obtenerTiendaArtesano($db);
            } elseif (isset($pathParts[0]) && is_numeric($pathParts[0])) {
                $productoId = $pathParts[0];
                obtenerProducto($db, $productoId);
            } elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $productoId = $_GET['id'];
                obtenerProducto($db, $productoId);
            } else {
                listarProductosArtesano($db);
            }
            break;
            
        case 'POST':
            // Verificar si hay una acción específica en los datos POST
            $accion = $_POST['accion'] ?? null;
            
            if ($accion === 'crear_producto') {
                crearProductoFormData($db);
            } elseif ($accion === 'actualizar_producto' && isset($_POST['id_producto'])) {
                $productoId = $_POST['id_producto'];
                actualizarProductoFormData($db, $productoId);
            } elseif ($accion === 'eliminar_producto' && isset($_POST['id_producto'])) {
                $productoId = $_POST['id_producto'];
                eliminarProducto($db, $productoId);
            } elseif (isset($pathParts[0]) && is_numeric($pathParts[0])) {
                $productoId = $pathParts[0];
                
                if (isset($pathParts[1])) {
                    switch ($pathParts[1]) {
                        case 'estado':
                            actualizarEstadoProducto($db, $productoId);
                            break;
                        case 'stock':
                            actualizarStockProducto($db, $productoId);
                            break;
                        case 'precio':
                            actualizarPrecioProducto($db, $productoId);
                            break;
                        case 'duplicar':
                            duplicarProducto($db, $productoId);
                            break;
                        default:
                            http_response_code(404);
                            echo json_encode(['error' => 'Endpoint no encontrado']);
                    }
                } else {
                    actualizarProducto($db, $productoId);
                }
            } else {
                // Si no hay acción específica ni ID, intentar crear producto con JSON
                crearProducto($db);
            }
            break;
            
        case 'DELETE':
            // Parsear datos del cuerpo para DELETE requests
            $deleteData = [];
            if (!empty(file_get_contents('php://input'))) {
                parse_str(file_get_contents('php://input'), $deleteData);
            }
            
            // Manejar DELETE con datos del cuerpo o POST para compatibilidad
            if ((isset($_POST['accion']) && $_POST['accion'] === 'eliminar_producto' && isset($_POST['id_producto'])) ||
                (isset($deleteData['accion']) && $deleteData['accion'] === 'eliminar_producto' && isset($deleteData['id_producto']))) {
                $productoId = $_POST['id_producto'] ?? $deleteData['id_producto'];
                eliminarProducto($db, $productoId);
            } elseif (isset($pathParts[0]) && is_numeric($pathParts[0])) {
                $productoId = $pathParts[0];
                eliminarProducto($db, $productoId);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID de producto requerido']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }

} catch (Exception $e) {
    error_log("Error en API productos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

function listarProductosArtesano($db) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Obtener productos del artesano con información de ventas
        $query = "
            SELECT 
                p.id_producto,
                p.nombre,
                p.descripcion,
                p.precio,
                p.descuento,
                p.imagen,
                p.stock,
                p.activo,
                p.fecha_creacion,
                t.nombre_tienda,
                COALESCE(ventas.total_vendido, 0) as total_vendido,
                COALESCE(ventas.ingresos_totales, 0) as ingresos_totales
            FROM productos p
            LEFT JOIN tiendas t ON p.id_tienda = t.id_tienda
            LEFT JOIN (
                SELECT 
                    pp.id_producto,
                    SUM(pp.cantidad) as total_vendido,
                    SUM(pp.cantidad * pp.precio_unitario) as ingresos_totales
                FROM pedido_productos pp
                JOIN pedidos ped ON pp.id_pedido = ped.id_pedido
                WHERE ped.estado IN ('entregado', 'en_camino')
                GROUP BY pp.id_producto
            ) ventas ON p.id_producto = ventas.id_producto
            WHERE t.id_usuario = ?
            ORDER BY p.fecha_creacion DESC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$artesanoId]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $productos
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener productos: ' . $e->getMessage()]);
    }
}

function obtenerProducto($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Verificar que el producto pertenezca al artesano
        $query = "
            SELECT 
                p.*,
                t.nombre_tienda,
                COALESCE(ventas.total_vendido, 0) as total_vendido,
                COALESCE(ventas.ingresos_totales, 0) as ingresos_totales,
                COALESCE(estadisticas.visualizaciones, 0) as visualizaciones,
                COALESCE(estadisticas.favoritos, 0) as favoritos
            FROM productos p
            LEFT JOIN tiendas t ON p.id_tienda = t.id_tienda
            LEFT JOIN (
                SELECT 
                    pp.id_producto,
                    SUM(pp.cantidad) as total_vendido,
                    SUM(pp.cantidad * pp.precio_unitario) as ingresos_totales
                FROM pedido_productos pp
                JOIN pedidos ped ON pp.id_pedido = ped.id_pedido
                WHERE ped.estado IN ('entregado', 'en_camino')
                GROUP BY pp.id_producto
            ) ventas ON p.id_producto = ventas.id_producto
            LEFT JOIN (
                SELECT 
                    id_producto,
                    0 as visualizaciones,
                    0 as favoritos
                FROM productos
            ) estadisticas ON p.id_producto = estadisticas.id_producto
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$productoId, $artesanoId]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'producto' => $producto
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener producto: ' . $e->getMessage()]);
    }
}

function crearProductoFormData($db) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Validar datos requeridos
        if (empty($_POST['nombre']) || empty($_POST['descripcion']) || !isset($_POST['precio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos. Nombre, descripción y precio son requeridos.']);
            return;
        }
        
        // Obtener ID de tienda del artesano
        $stmtTienda = $db->prepare("SELECT id_tienda FROM tiendas WHERE id_usuario = ?");
        $stmtTienda->execute([$artesanoId]);
        $tienda = $stmtTienda->fetch(PDO::FETCH_ASSOC);
        
        if (!$tienda) {
            http_response_code(400);
            echo json_encode(['error' => 'El artesano no tiene una tienda configurada']);
            return;
        }
        
        // Procesar imagen si existe
        $rutaImagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['imagen'];
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'prod_' . uniqid() . '.' . $extension;
            $rutaDestino = dirname(__FILE__) . '/../uploads/productos/' . $nombreArchivo;
            
            // Crear directorio si no existe
            $directorioDestino = dirname($rutaDestino);
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0755, true);
            }
            
            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $rutaImagen = 'uploads/productos/' . $nombreArchivo;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al subir la imagen']);
                return;
            }
        }
        
        // Insertar producto
        $query = "
            INSERT INTO productos (
                id_tienda, nombre, descripcion, precio, descuento, 
                imagen, stock, activo, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute([
            $tienda['id_tienda'],
            $_POST['nombre'],
            $_POST['descripcion'],
            floatval($_POST['precio']),
            floatval($_POST['descuento'] ?? 0),
            $rutaImagen,
            intval($_POST['stock'] ?? 0),
            isset($_POST['activo']) ? 1 : 0
        ]);
        
        if ($resultado) {
            $nuevoId = $db->lastInsertId();
            echo json_encode([
                'success' => true,
                'exito' => true,
                'data' => ['id_producto' => $nuevoId],
                'message' => 'Producto creado exitosamente',
                'mensaje' => 'Producto creado exitosamente'
            ]);
        } else {
            throw new Exception('Error al insertar producto en la base de datos');
        }
        
    } catch (Exception $e) {
        error_log("Error en crearProductoFormData: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'exito' => false,
            'error' => 'Error al crear producto: ' . $e->getMessage(),
            'message' => 'Error al crear producto: ' . $e->getMessage(),
            'mensaje' => 'Error al crear producto: ' . $e->getMessage()
        ]);
    }
}

function crearProducto($db) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        if (empty($input['nombre']) || empty($input['descripcion']) || !isset($input['precio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }
        
        // Obtener ID de tienda del artesano
        $stmtTienda = $db->prepare("SELECT id_tienda FROM tiendas WHERE id_usuario = ?");
        $stmtTienda->execute([$artesanoId]);
        $tienda = $stmtTienda->fetch(PDO::FETCH_ASSOC);
        
        if (!$tienda) {
            http_response_code(400);
            echo json_encode(['error' => 'No tienes una tienda creada']);
            return;
        }
        
        // Insertar producto
        $query = "
            INSERT INTO productos (
                id_tienda, nombre, descripcion, precio, descuento, 
                imagen, stock, activo, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute([
            $tienda['id_tienda'],
            $input['nombre'],
            $input['descripcion'],
            $input['precio'],
            $input['descuento'] ?? 0,
            $input['imagen'] ?? null,
            $input['stock'] ?? 0,
            $input['activo'] ?? 1
        ]);
        
        if ($resultado) {
            $nuevoId = $db->lastInsertId();
            echo json_encode([
                'success' => true,
                'data' => ['id_producto' => $nuevoId],
                'message' => 'Producto creado exitosamente'
            ]);
        } else {
            throw new Exception('Error al insertar producto');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear producto: ' . $e->getMessage()]);
    }
}

function actualizarProducto($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Verificar que el producto pertenezca al artesano
        $verificacion = "
            SELECT p.id_producto 
            FROM productos p
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($verificacion);
        $stmt->execute([$productoId, $artesanoId]);
        
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado para modificar este producto']);
            return;
        }
        
        // Construir query de actualización dinámicamente
        $campos = [];
        $valores = [];
        
        $camposPermitidos = ['nombre', 'descripcion', 'precio', 'descuento', 'imagen', 'stock', 'activo'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($input[$campo])) {
                $campos[] = "$campo = ?";
                $valores[] = $input[$campo];
            }
        }
        
        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay datos para actualizar']);
            return;
        }
        
        $valores[] = $productoId;
        
        $query = "UPDATE productos SET " . implode(', ', $campos) . " WHERE id_producto = ?";
        
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute($valores);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ]);
        } else {
            throw new Exception('Error al actualizar producto');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar producto: ' . $e->getMessage()]);
    }
}

function actualizarProductoFormData($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Verificar que el producto pertenezca al artesano
        $verificacion = "
            SELECT p.id_producto 
            FROM productos p
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($verificacion);
        $stmt->execute([$productoId, $artesanoId]);
        
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado para modificar este producto']);
            return;
        }
        
        // Construir query de actualización dinámicamente
        $campos = [];
        $valores = [];
        
        $camposPermitidos = ['nombre', 'descripcion', 'precio', 'descuento', 'stock', 'activo'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($_POST[$campo])) {
                $campos[] = "$campo = ?";
                $valores[] = $_POST[$campo];
            }
        }
        
        // Manejar upload de imagen si existe
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__FILE__) . '/../uploads/productos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $fileName = 'prod_' . uniqid() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filePath)) {
                $campos[] = "imagen = ?";
                $valores[] = $fileName;
            }
        }
        
        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay datos para actualizar']);
            return;
        }
        
        $valores[] = $productoId;
        
        $query = "UPDATE productos SET " . implode(', ', $campos) . " WHERE id_producto = ?";
        
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute($valores);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ]);
        } else {
            throw new Exception('Error al actualizar producto');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar producto: ' . $e->getMessage()]);
    }
}

function actualizarEstadoProducto($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['activo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Estado requerido']);
            return;
        }
        
        // Verificar y actualizar
        $query = "
            UPDATE productos p
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            SET p.activo = ?
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute([$input['activo'], $productoId, $artesanoId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar estado: ' . $e->getMessage()]);
    }
}

function eliminarProducto($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Verificar que no tenga pedidos asociados
        $checkPedidos = "
            SELECT COUNT(*) as total
            FROM pedido_productos pp
            JOIN productos p ON pp.id_producto = p.id_producto
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($checkPedidos);
        $stmt->execute([$productoId, $artesanoId]);
        $pedidos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedidos['total'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'No se puede eliminar un producto con pedidos asociados']);
            return;
        }
        
        // Eliminar producto
        $query = "
            DELETE p FROM productos p
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute([$productoId, $artesanoId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar producto: ' . $e->getMessage()]);
    }
}

function actualizarStockProducto($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['stock']) || !is_numeric($input['stock'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Stock válido requerido']);
            return;
        }
        
        // Verificar y actualizar
        $query = "
            UPDATE productos p
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            SET p.stock = ?
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute([$input['stock'], $productoId, $artesanoId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Stock actualizado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar stock: ' . $e->getMessage()]);
    }
}

function actualizarPrecioProducto($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['precio']) || !is_numeric($input['precio']) || $input['precio'] < 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Precio válido requerido']);
            return;
        }
        
        // Verificar y actualizar
        $query = "
            UPDATE productos p
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            SET p.precio = ?, p.descuento = ?
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $descuento = isset($input['descuento']) ? $input['descuento'] : 0;
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute([$input['precio'], $descuento, $productoId, $artesanoId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Precio actualizado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar precio: ' . $e->getMessage()]);
    }
}

function duplicarProducto($db, $productoId) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        // Obtener datos del producto original
        $query = "
            SELECT p.*
            FROM productos p
            JOIN tiendas t ON p.id_tienda = t.id_tienda
            WHERE p.id_producto = ? AND t.id_usuario = ?
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$productoId, $artesanoId]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            return;
        }
        
        // Crear copia del producto
        $insertQuery = "
            INSERT INTO productos (
                id_tienda, nombre, descripcion, precio, descuento, 
                imagen, stock, activo, fecha_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $db->prepare($insertQuery);
        $resultado = $stmt->execute([
            $producto['id_tienda'],
            $producto['nombre'] . ' (Copia)',
            $producto['descripcion'],
            $producto['precio'],
            $producto['descuento'],
            $producto['imagen'],
            0, // Stock en 0 para la copia
            0  // Inactivo por defecto
        ]);
        
        if ($resultado) {
            $nuevoId = $db->lastInsertId();
            echo json_encode([
                'success' => true,
                'data' => ['id_producto' => $nuevoId],
                'message' => 'Producto duplicado exitosamente'
            ]);
        } else {
            throw new Exception('Error al duplicar producto');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al duplicar producto: ' . $e->getMessage()]);
    }
}

function obtenerTiendaArtesano($db) {
    try {
        $artesanoId = $_SESSION['usuario_id'];
        
        $query = "SELECT id_tienda, nombre_tienda FROM tiendas WHERE id_usuario = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$artesanoId]);
        $tienda = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tienda) {
            echo json_encode([
                'success' => true,
                'tienda' => $tienda
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Tienda no encontrada']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener tienda: ' . $e->getMessage()]);
    }
}
?>
