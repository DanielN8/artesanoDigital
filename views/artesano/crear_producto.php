<?php
/**
 * Controlador para crear nuevos productos
 * Este script maneja la lógica de creación de productos desde el modal del dashboard de artesanos
 */

require_once dirname(__FILE__) . '/../../config/Database.php';
require_once dirname(__FILE__) . '/../../utils/GestorAutenticacion.php';
require_once dirname(__FILE__) . '/../../patrones/ProductoDecorador.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exitoso' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Verificar autenticación
$gestorAuth = Utils\GestorAutenticacion::obtenerInstancia();
if (!$gestorAuth->estaAutenticado() || $gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'artesano') {
    echo json_encode(['exitoso' => false, 'mensaje' => 'No autorizado']);
    exit;
}

// Obtener datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$activo = isset($_POST['activo']) ? 1 : 0;

// Validación básica
if (empty($nombre) || $precio <= 0 || $stock < 0) {
    echo json_encode(['exitoso' => false, 'mensaje' => 'Por favor completa todos los campos obligatorios']);
    exit;
}

try {
    $conexion = Config\Database::obtenerInstancia()->obtenerConexion();
    
    // 1. Obtener el ID de la tienda del artesano
    $idUsuario = $gestorAuth->obtenerUsuarioActual()['id_usuario'];
    $stmt = $conexion->prepare("SELECT id_tienda FROM tiendas WHERE id_usuario = ? LIMIT 1");
    $stmt->execute([$idUsuario]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resultado) {
        echo json_encode(['exitoso' => false, 'mensaje' => 'No tienes una tienda registrada']);
        exit;
    }
    
    $idTienda = $resultado['id_tienda'];
    
    // 2. Manejar la carga de imagen si existe
    $nombreImagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['imagen'];
        $nombreTemporal = $archivo['tmp_name'];
        $nombreOriginal = $archivo['name'];
        
        // Generar nombre único para la imagen
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        $nombreImagen = uniqid('prod_') . '.' . $extension;
        
        // Directorio de carga
        $directorioDestino = dirname(__FILE__) . '/../../uploads/';
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }
        
        // Mover archivo
        if (!move_uploaded_file($nombreTemporal, $directorioDestino . $nombreImagen)) {
            $nombreImagen = null; // Si falla, no guardar imagen
        }
    }
    
    // 3. Insertar el producto base
    $conexion->beginTransaction();
    
    $stmt = $conexion->prepare("
        INSERT INTO productos (id_tienda, nombre, descripcion, precio, stock, imagen, activo) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $idTienda,
        $nombre,
        $descripcion,
        $precio,
        $stock,
        $nombreImagen,
        $activo
    ]);
    
    $idProducto = $conexion->lastInsertId();
    
    // 4. Manejar descuentos si están marcados
    $aplicarDescuento = isset($_POST['aplicar_descuento']);
    
    if ($aplicarDescuento) {
        $tipoDescuento = $_POST['tipo_descuento'] ?? '';
        $razonDescuento = trim($_POST['razon_descuento'] ?? 'Oferta especial');
        $fechaFinDescuento = !empty($_POST['fecha_fin_descuento']) ? $_POST['fecha_fin_descuento'] : null;
        
        // Crear un producto base para aplicar el decorador
        $productoBase = new Patrones\ProductoBase([
            'id_producto' => $idProducto,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'imagen' => $nombreImagen,
            'id_tienda' => $idTienda,
            'activo' => $activo
        ]);
        
        if ($tipoDescuento === 'porcentaje' && isset($_POST['descuento_porcentaje'])) {
            $porcentajeDescuento = floatval($_POST['descuento_porcentaje']);
            if ($porcentajeDescuento > 0 && $porcentajeDescuento < 100) {
                // Aplicar descuento por porcentaje
                $productoConDescuento = new Patrones\DecoradorDescuentoPorcentaje(
                    $productoBase, 
                    $porcentajeDescuento,
                    $razonDescuento
                );
                
                // Guardar el descuento en la base de datos (en una tabla de descuentos si la tienes)
                // Como no tienes una tabla específica para descuentos, puedes manejarlo a través de tu
                // patrón decorador al mostrar los productos
                
                // Para propósitos de demostración, podrías guardar esta información en la descripción
                $nuevaDescripcion = $descripcion . "\n[DESCUENTO_PORCENTAJE: $porcentajeDescuento%]";
                if (!empty($razonDescuento)) {
                    $nuevaDescripcion .= "\n[RAZON_DESCUENTO: $razonDescuento]";
                }
                if ($fechaFinDescuento) {
                    $nuevaDescripcion .= "\n[FECHA_FIN_DESCUENTO: $fechaFinDescuento]";
                }
                
                $stmt = $conexion->prepare("UPDATE productos SET descripcion = ? WHERE id_producto = ?");
                $stmt->execute([$nuevaDescripcion, $idProducto]);
            }
        } elseif ($tipoDescuento === 'monto' && isset($_POST['descuento_monto'])) {
            $montoDescuento = floatval($_POST['descuento_monto']);
            if ($montoDescuento > 0 && $montoDescuento < $precio) {
                // Aplicar descuento por monto
                $productoConDescuento = new Patrones\DecoradorDescuentoMonto(
                    $productoBase, 
                    $montoDescuento,
                    $razonDescuento
                );
                
                // Guardar el descuento en la descripción para fines de demostración
                $nuevaDescripcion = $descripcion . "\n[DESCUENTO_MONTO: $montoDescuento]";
                if (!empty($razonDescuento)) {
                    $nuevaDescripcion .= "\n[RAZON_DESCUENTO: $razonDescuento]";
                }
                if ($fechaFinDescuento) {
                    $nuevaDescripcion .= "\n[FECHA_FIN_DESCUENTO: $fechaFinDescuento]";
                }
                
                $stmt = $conexion->prepare("UPDATE productos SET descripcion = ? WHERE id_producto = ?");
                $stmt->execute([$nuevaDescripcion, $idProducto]);
            }
        }
    }
    
    $conexion->commit();
    
    echo json_encode([
        'exitoso' => true, 
        'mensaje' => 'Producto creado exitosamente',
        'id_producto' => $idProducto
    ]);
    
} catch (Exception $e) {
    if (isset($conexion)) {
        $conexion->rollBack();
    }
    error_log("Error al crear producto: " . $e->getMessage());
    echo json_encode([
        'exitoso' => false, 
        'mensaje' => 'Error al crear el producto: ' . $e->getMessage()
    ]);
}
