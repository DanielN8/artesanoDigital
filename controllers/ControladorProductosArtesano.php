<?php
/**
 * Controlador de productos para artesanos
 * Responsabilidad: Gestionar operaciones CRUD para productos de artesanos
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar dependencias
require_once dirname(__FILE__) . '/../models/Producto.php';
require_once dirname(__FILE__) . '/../utils/GestorAutenticacion.php';
require_once dirname(__FILE__) . '/../utils/GestorUploads.php';
require_once dirname(__FILE__) . '/../models/Tienda.php';
require_once dirname(__FILE__) . '/../config/Database.php';

use Utils\GestorAutenticacion;
use Utils\GestorUploads;
use Models\Tienda;

class ControladorProductosArtesano 
{
    private $gestorAuth;
    private $modeloProducto;
    private $gestorUploads;
    private $modeloTienda;
    private $conexion;

    public function __construct() 
    {
        // Iniciar el sistema de logs
        error_log("Iniciando ControladorProductosArtesano");
        
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloProducto = new \Producto();
        $this->gestorUploads = new GestorUploads();
        $this->modeloTienda = new Tienda();
        $this->conexion = \Config\Database::obtenerInstancia()->obtenerConexion();
        
        // Verificar que el usuario esté autenticado y sea artesano
        if (!$this->gestorAuth->estaAutenticado()) {
            error_log("Usuario no autenticado, redirigiendo a login");
            header('Location: /artesanoDigital/auth/login?redirect=artesano/dashboard');
            exit;
        }
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        if (!isset($usuario['tipo_usuario']) || $usuario['tipo_usuario'] !== 'artesano') {
            error_log("Usuario no es artesano, redirigiendo a dashboard de cliente");
            header('Location: /artesanoDigital/cliente/dashboard');
            exit;
        }
        
        error_log("ControladorProductosArtesano inicializado correctamente para artesano ID: " . $usuario['id_usuario']);
    }

    /**
     * Crea un nuevo producto con posibilidad de descuento
     */
    public function crear(array $datos, array $archivo = null) 
    {
        try {
            // Validar datos básicos
            if (empty($datos['nombre']) || empty($datos['precio'])) {
                return [
                    'error' => true,
                    'mensaje' => 'El nombre y precio son obligatorios'
                ];
            }
            
            // Verificar si se proporcionó un ID de tienda
            $idTienda = isset($datos['id_tienda']) ? (int)$datos['id_tienda'] : null;
            
            error_log("ID de tienda recibido: " . ($idTienda ?: 'ninguno'));
            
            // Si no se proporcionó, intentar obtener la tienda del artesano
            if (!$idTienda) {
                $usuario = $this->gestorAuth->obtenerUsuarioActual();
                if (isset($usuario['id_usuario'])) {
                    error_log("Buscando tienda para el usuario ID: " . $usuario['id_usuario']);
                    $tienda = $this->modeloTienda->obtenerPorUsuario($usuario['id_usuario']);
                    if ($tienda) {
                        $idTienda = $tienda['id_tienda'];
                        error_log("Tienda encontrada con ID: " . $idTienda);
                    } else {
                        error_log("No se encontró tienda para el usuario");
                    }
                } else {
                    error_log("No se pudo obtener el ID del usuario actual");
                }
            }
            
            if (!$idTienda) {
                error_log("El artesano no tiene tienda asociada. Debe crear una tienda primero.");
                return [
                    'error' => true,
                    'mensaje' => 'No se encontró una tienda asociada a tu cuenta. Debes crear una tienda primero usando el botón "Crear mi tienda ahora" en tu dashboard.'
                ];
            }
            
            // Procesar imagen si existe
            $rutaImagen = '';
            if ($archivo && isset($archivo['imagen']) && !empty($archivo['imagen']['name'])) {
                try {
                    // Verificar que la carpeta de uploads existe
                    $directorioDestino = dirname(dirname(__FILE__)) . '/public/productos/';
                    if (!file_exists($directorioDestino)) {
                        if (!mkdir($directorioDestino, 0777, true) && !is_dir($directorioDestino)) {
                            error_log("Error: No se pudo crear el directorio $directorioDestino");
                            return [
                                'error' => true,
                                'mensaje' => 'Error al crear el directorio para subir imágenes'
                            ];
                        }
                    }
                    
                    // Generar un nombre único para la imagen
                    $nombreArchivo = uniqid('prod_') . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', basename($archivo['imagen']['name']));
                    $rutaDestino = $directorioDestino . $nombreArchivo;
                    
                    // Verificar que sea una imagen válida
                    $tipoArchivo = mime_content_type($archivo['imagen']['tmp_name']);
                    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    
                    if (!in_array($tipoArchivo, $tiposPermitidos)) {
                        return [
                            'error' => true,
                            'mensaje' => 'El archivo subido no es una imagen válida. Solo se permiten JPG, PNG y GIF.'
                        ];
                    }
                    
                    // Mover el archivo subido
                    if (move_uploaded_file($archivo['imagen']['tmp_name'], $rutaDestino)) {
                        $rutaImagen = 'public/productos/' . $nombreArchivo;
                        error_log("Imagen subida correctamente a: $rutaDestino");
                    } else {
                        $errorUpload = error_get_last();
                        error_log("Error al subir imagen: " . ($errorUpload ? $errorUpload['message'] : 'Desconocido'));
                        return [
                            'error' => true,
                            'mensaje' => 'Error al subir la imagen del producto. Verifica los permisos de la carpeta.'
                        ];
                    }
                } catch (\Exception $e) {
                    error_log("Error al procesar imagen: " . $e->getMessage());
                    return [
                        'error' => true,
                        'mensaje' => 'Error al procesar la imagen: ' . $e->getMessage()
                    ];
                }
            }
            
            // Establecer valores para el producto
            $nombre = $datos['nombre'];
            $descripcion = $datos['descripcion'] ?? '';
            $precio = floatval($datos['precio']);
            $stock = isset($datos['stock']) ? intval($datos['stock']) : 0;
            $descuento = isset($datos['descuento']) ? floatval($datos['descuento']) : 0;
            $activo = 1; // Los productos nuevos están activos por defecto
            
            // Preparar la consulta SQL para insertar producto
            $sql = "INSERT INTO productos (
                id_tienda, nombre, descripcion, precio, 
                imagen, stock, descuento, activo, fecha_creacion
            ) VALUES (
                :id_tienda, :nombre, :descripcion, :precio,
                :imagen, :stock, :descuento, :activo, NOW()
            )";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_tienda', $idTienda, \PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre, \PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, \PDO::PARAM_STR);
            $stmt->bindParam(':precio', $precio, \PDO::PARAM_STR);
            $stmt->bindParam(':imagen', $rutaImagen, \PDO::PARAM_STR);
            $stmt->bindParam(':stock', $stock, \PDO::PARAM_INT);
            $stmt->bindParam(':descuento', $descuento, \PDO::PARAM_STR);
            $stmt->bindParam(':activo', $activo, \PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $idProducto = $this->conexion->lastInsertId();
                return [
                    'error' => false,
                    'id_producto' => $idProducto,
                    'mensaje' => 'Producto creado con éxito'
                ];
            } else {
                $error = $stmt->errorInfo();
                error_log("Error SQL al crear producto: " . print_r($error, true));
                return [
                    'error' => true,
                    'mensaje' => 'Error al crear el producto en la base de datos: ' . $error[2]
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error al crear producto: " . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => 'Error al crear el producto: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualiza un producto existente
     */
    public function actualizar(int $idProducto, array $datos, array $archivo = null) 
    {
        try {
            // Verificar propiedad del producto
            $producto = $this->obtenerPorId($idProducto);
            if (!$producto) {
                return [
                    'error' => true,
                    'mensaje' => 'El producto no existe'
                ];
            }
            
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $tienda = $this->modeloTienda->obtenerPorUsuario($usuario['id_usuario']);
            
            // Verificar que el producto pertenece a la tienda del artesano
            if (!$tienda || $producto['id_tienda'] != $tienda['id_tienda']) {
                return [
                    'error' => true,
                    'mensaje' => 'No tienes permiso para modificar este producto'
                ];
            }
            
            // Validar datos básicos
            if (empty($datos['nombre']) || empty($datos['precio'])) {
                return [
                    'error' => true,
                    'mensaje' => 'El nombre y precio son obligatorios'
                ];
            }
            
            // Procesar imagen si existe
            $rutaImagen = $producto['imagen']; // Mantener la imagen actual por defecto
            if ($archivo && isset($archivo['imagen']) && !empty($archivo['imagen']['name'])) {
                try {
                    // Verificar que la carpeta de uploads existe
                    $directorioDestino = dirname(dirname(__FILE__)) . '/public/productos/';
                    if (!file_exists($directorioDestino)) {
                        if (!mkdir($directorioDestino, 0777, true) && !is_dir($directorioDestino)) {
                            error_log("Error: No se pudo crear el directorio $directorioDestino");
                            return [
                                'error' => true,
                                'mensaje' => 'Error al crear el directorio para subir imágenes'
                            ];
                        }
                    }
                    
                    // Generar un nombre único para la imagen
                    $nombreArchivo = uniqid('prod_') . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', basename($archivo['imagen']['name']));
                    $rutaDestino = $directorioDestino . $nombreArchivo;
                    
                    // Verificar que sea una imagen válida
                    $tipoArchivo = mime_content_type($archivo['imagen']['tmp_name']);
                    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    
                    if (!in_array($tipoArchivo, $tiposPermitidos)) {
                        return [
                            'error' => true,
                            'mensaje' => 'El archivo subido no es una imagen válida. Solo se permiten JPG, PNG y GIF.'
                        ];
                    }
                    
                    // Mover el archivo subido
                    if (move_uploaded_file($archivo['imagen']['tmp_name'], $rutaDestino)) {
                        // Si hay una imagen anterior, eliminarla
                        if (!empty($producto['imagen'])) {
                            $rutaAnterior = dirname(dirname(__FILE__)) . '/' . $producto['imagen'];
                            if (file_exists($rutaAnterior)) {
                                unlink($rutaAnterior);
                            }
                        }
                        $rutaImagen = 'public/productos/' . $nombreArchivo;
                        error_log("Imagen actualizada correctamente a: $rutaDestino");
                    } else {
                        $errorUpload = error_get_last();
                        error_log("Error al subir imagen: " . ($errorUpload ? $errorUpload['message'] : 'Desconocido'));
                        return [
                            'error' => true,
                            'mensaje' => 'Error al subir la imagen del producto. Verifica los permisos de la carpeta.'
                        ];
                    }
                } catch (\Exception $e) {
                    error_log("Error al procesar imagen: " . $e->getMessage());
                    return [
                        'error' => true,
                        'mensaje' => 'Error al procesar la imagen: ' . $e->getMessage()
                    ];
                }
            }
            
            // Establecer valores para el producto
            $nombre = $datos['nombre'];
            $descripcion = $datos['descripcion'] ?? '';
            $precio = floatval($datos['precio']);
            $stock = isset($datos['stock']) ? intval($datos['stock']) : 0;
            $descuento = isset($datos['descuento']) ? floatval($datos['descuento']) : 0;
            $activo = isset($datos['activo']) ? 1 : 0;
            
            // Preparar la consulta SQL para actualizar producto
            $sql = "UPDATE productos SET 
                nombre = :nombre, 
                descripcion = :descripcion, 
                precio = :precio,
                imagen = :imagen, 
                stock = :stock, 
                descuento = :descuento, 
                activo = :activo
            WHERE id_producto = :id_producto";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_producto', $idProducto, \PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre, \PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, \PDO::PARAM_STR);
            $stmt->bindParam(':precio', $precio, \PDO::PARAM_STR);
            $stmt->bindParam(':imagen', $rutaImagen, \PDO::PARAM_STR);
            $stmt->bindParam(':stock', $stock, \PDO::PARAM_INT);
            $stmt->bindParam(':descuento', $descuento, \PDO::PARAM_STR);
            $stmt->bindParam(':activo', $activo, \PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'error' => false,
                    'id_producto' => $idProducto,
                    'mensaje' => 'Producto actualizado con éxito'
                ];
            } else {
                $error = $stmt->errorInfo();
                error_log("Error SQL al actualizar producto: " . print_r($error, true));
                return [
                    'error' => true,
                    'mensaje' => 'Error al actualizar el producto en la base de datos: ' . $error[2]
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error al actualizar producto: " . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => 'Error al actualizar el producto: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Elimina un producto
     */
    public function eliminar(int $idProducto) 
    {
        try {
            // Verificar propiedad del producto
            $producto = $this->obtenerPorId($idProducto);
            if (!$producto) {
                return [
                    'error' => true,
                    'mensaje' => 'El producto no existe'
                ];
            }
            
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $tienda = $this->modeloTienda->obtenerPorUsuario($usuario['id_usuario']);
            
            // Verificar que el producto pertenece a la tienda del artesano
            if (!$tienda || $producto['id_tienda'] != $tienda['id_tienda']) {
                return [
                    'error' => true,
                    'mensaje' => 'No tienes permiso para eliminar este producto'
                ];
            }
            
            // Preparar la consulta SQL para eliminar producto
            $sql = "DELETE FROM productos WHERE id_producto = :id_producto";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_producto', $idProducto, \PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Si hay una imagen, eliminarla
                if (!empty($producto['imagen'])) {
                    $rutaImagen = dirname(dirname(__FILE__)) . '/' . $producto['imagen'];
                    if (file_exists($rutaImagen)) {
                        unlink($rutaImagen);
                    }
                }
                
                return [
                    'error' => false,
                    'mensaje' => 'Producto eliminado con éxito'
                ];
            } else {
                $error = $stmt->errorInfo();
                error_log("Error SQL al eliminar producto: " . print_r($error, true));
                return [
                    'error' => true,
                    'mensaje' => 'Error al eliminar el producto de la base de datos: ' . $error[2]
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error al eliminar producto: " . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => 'Error al eliminar el producto: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtiene un producto por su ID
     */
    public function obtenerPorId(int $idProducto) 
    {
        try {
            $sql = "SELECT p.*, t.nombre as nombre_tienda 
                    FROM productos p 
                    LEFT JOIN tiendas t ON p.id_tienda = t.id_tienda 
                    WHERE p.id_producto = :id_producto";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_producto', $idProducto, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error al obtener producto por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Procesa las solicitudes POST y redirige según el resultado
     */
    public function procesarSolicitud() 
    {
        // Manejar solicitudes GET para obtener datos del producto
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $action = $_GET['action'] ?? '';
            $id = $_GET['id'] ?? '';
            
            if ($action === 'obtener' && !empty($id)) {
                header('Content-Type: application/json');
                try {
                    $producto = $this->obtenerPorId((int)$id);
                    if ($producto) {
                        // Verificar que el producto pertenece al artesano actual
                        $usuario = $this->gestorAuth->obtenerUsuarioActual();
                        $tienda = $this->modeloTienda->obtenerPorUsuario($usuario['id_usuario']);
                        
                        if ($tienda && $producto['id_tienda'] == $tienda['id_tienda']) {
                            echo json_encode([
                                'success' => true,
                                'producto' => $producto
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'No tienes permiso para ver este producto'
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Producto no encontrado'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al obtener el producto: ' . $e->getMessage()
                    ]);
                }
                exit;
            }
            
            // Para otras solicitudes GET, redirigir al dashboard
            header('Location: /artesanoDigital/artesano/dashboard');
            exit;
        }
        
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Se accedió a ControladorProductosArtesano sin método POST");
            // Para solicitudes no POST, simplemente mostrar la página del dashboard
            header('Location: /artesanoDigital/artesano/dashboard');
            exit;
        }
        
        // Verificar la acción solicitada
        $accion = $_POST['accion'] ?? '';
        error_log("=== PROCESANDO SOLICITUD ===");
        error_log("Acción: $accion");
        error_log("Usuario ID: " . ($this->gestorAuth->obtenerUsuarioActual()['id_usuario'] ?? 'No definido'));
        error_log("Datos POST: " . json_encode(array_keys($_POST)));
        error_log("Archivos FILES: " . (isset($_FILES['imagen']) ? $_FILES['imagen']['name'] : 'Ninguno'));
        
        if ($accion === 'crear_producto') {
            try {
                // Registro de datos recibidos
                error_log("Datos recibidos para crear_producto: " . json_encode(array_keys($_POST)));
                if (isset($_FILES['imagen'])) {
                    error_log("Imagen recibida: " . $_FILES['imagen']['name']);
                } else {
                    error_log("No se recibió imagen");
                }
                
                // Procesar la solicitud para crear un producto
                $resultado = $this->crear($_POST, $_FILES);
                
                // Verificar si es una petición AJAX
                $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                
                if (!$resultado['error']) {
                    error_log("Producto creado correctamente con ID: " . ($resultado['id_producto'] ?? 'desconocido'));
                    
                    if ($esAjax) {
                        // Respuesta JSON para AJAX
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'exito' => true,
                            'message' => 'Producto creado correctamente',
                            'mensaje' => 'Producto creado correctamente',
                            'id_producto' => $resultado['id_producto'] ?? null
                        ]);
                        exit;
                    } else {
                        // Guardar mensaje en sesión para mostrar toast
                        $_SESSION['toast_mensaje'] = 'Producto creado correctamente';
                        $_SESSION['toast_tipo'] = 'exito';
                        // Redirigir a dashboard
                        header('Location: /artesanoDigital/artesano/dashboard');
                        exit;
                    }
                } else {
                    error_log("Error al crear producto: " . ($resultado['mensaje'] ?? 'Error desconocido'));
                    
                    if ($esAjax) {
                        // Respuesta JSON para AJAX
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'exito' => false,
                            'message' => $resultado['mensaje'] ?? 'Error al crear el producto',
                            'mensaje' => $resultado['mensaje'] ?? 'Error al crear el producto'
                        ]);
                        exit;
                    } else {
                        // Guardar mensaje de error en sesión para mostrar toast
                        $_SESSION['toast_mensaje'] = $resultado['mensaje'] ?? 'Error al crear el producto';
                        $_SESSION['toast_tipo'] = 'error';
                        // Redirigir a dashboard
                        header('Location: /artesanoDigital/artesano/dashboard');
                        exit;
                    }
                }
            } catch (\Exception $e) {
                // Registrar el error
                error_log("Excepción al crear producto: " . $e->getMessage());
                
                // Verificar si es una petición AJAX
                $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                
                if ($esAjax) {
                    // Respuesta JSON para AJAX
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'exito' => false,
                        'message' => 'Error al crear el producto: ' . $e->getMessage(),
                        'mensaje' => 'Error al crear el producto: ' . $e->getMessage()
                    ]);
                    exit;
                } else {
                    // Guardar mensaje de error en sesión para mostrar toast
                    $_SESSION['toast_mensaje'] = 'Error al crear el producto: ' . $e->getMessage();
                    $_SESSION['toast_tipo'] = 'error';
                    // Redirigir a dashboard
                    header('Location: /artesanoDigital/artesano/dashboard');
                    exit;
                }
            }
        } elseif ($accion === 'actualizar_producto') {
            try {
                // Validar que el ID del producto existe
                $producto_id = $_POST['producto_id'] ?? $_POST['id_producto'] ?? '';
                if (empty($producto_id)) {
                    // Verificar si es una petición AJAX
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'message' => 'ID del producto no especificado'
                        ]);
                        exit;
                    }
                    header('Location: /artesanoDigital/artesano/dashboard?mensaje=ID del producto no especificado&tipo=error');
                    exit;
                }
                
                // Procesar la solicitud para actualizar un producto
                $resultado = $this->actualizar((int)$producto_id, $_POST, $_FILES);
                
                // Verificar si es una petición AJAX
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    if (!$resultado['error']) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Producto actualizado correctamente'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => $resultado['mensaje'] ?? 'Error al actualizar el producto'
                        ]);
                    }
                    exit;
                }
                
                // Respuesta tradicional para formularios
                if (!$resultado['error']) {
                    $_SESSION['toast_mensaje'] = 'Producto actualizado correctamente';
                    $_SESSION['toast_tipo'] = 'exito';
                    header('Location: /artesanoDigital/artesano/dashboard');
                    exit;
                } else {
                    $_SESSION['toast_mensaje'] = $resultado['mensaje'] ?? 'Error al actualizar el producto';
                    $_SESSION['toast_tipo'] = 'error';
                    header('Location: /artesanoDigital/artesano/dashboard');
                    exit;
                }
            } catch (\Exception $e) {
                error_log("Error al actualizar producto: " . $e->getMessage());
                
                // Verificar si es una petición AJAX
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al actualizar el producto: ' . $e->getMessage()
                    ]);
                    exit;
                }
                
                header('Location: /artesanoDigital/artesano/dashboard?mensaje=' . urlencode('Error al actualizar el producto: ' . $e->getMessage()) . '&tipo=error');
                exit;
            }
        } elseif ($accion === 'eliminar_producto') {
            try {
                // Validar que el ID del producto existe
                $producto_id = $_POST['producto_id'] ?? $_POST['id_producto'] ?? '';
                if (empty($producto_id)) {
                    // Verificar si es una petición AJAX
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'message' => 'ID del producto no especificado'
                        ]);
                        exit;
                    }
                    header('Location: /artesanoDigital/artesano/dashboard?mensaje=ID del producto no especificado&tipo=error');
                    exit;
                }
                
                // Procesar la solicitud para eliminar un producto
                $resultado = $this->eliminar((int)$producto_id);
                
                // Verificar si es una petición AJAX
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    if (!$resultado['error']) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Producto eliminado correctamente'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => $resultado['mensaje'] ?? 'Error al eliminar el producto'
                        ]);
                    }
                    exit;
                }
                
                // Respuesta tradicional para formularios
                if (!$resultado['error']) {
                    header('Location: /artesanoDigital/artesano/dashboard?mensaje=Producto eliminado correctamente&tipo=success');
                    exit;
                } else {
                    header('Location: /artesanoDigital/artesano/dashboard?mensaje=' . urlencode($resultado['mensaje'] ?? 'Error al eliminar el producto') . '&tipo=error');
                    exit;
                }
            } catch (\Exception $e) {
                error_log("Error al eliminar producto: " . $e->getMessage());
                
                // Verificar si es una petición AJAX
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al eliminar el producto: ' . $e->getMessage()
                    ]);
                    exit;
                }
                
                header('Location: /artesanoDigital/artesano/dashboard?mensaje=' . urlencode('Error al eliminar el producto: ' . $e->getMessage()) . '&tipo=error');
                exit;
            }
        } else {
            // Acción no reconocida
            header('Location: /artesanoDigital/artesano/dashboard?mensaje=Acción no válida&tipo=error');
            exit;
        }
    }
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Procesar directamente la solicitud siempre que se acceda a este archivo 
// Esto es importante para que funcione cuando se llama desde el formulario
$controlador = new ControladorProductosArtesano();
$controlador->procesarSolicitud();
