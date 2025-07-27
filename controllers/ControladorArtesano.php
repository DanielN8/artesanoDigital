<?php
/**
 * Controlador de Artesano
 * Responsabilidad: Gestionar el panel de artesanos
 */

namespace Controllers;

use Models\Usuario;
use Models\Pedido;
use Models\Tienda;
use Utils\GestorAutenticacion;
use Config\Database;
use PDO;
use Exception;

// Incluir el modelo Producto (que no tiene namespace)
require_once dirname(__FILE__) . '/../models/Producto.php';

class ControladorArtesano 
{
    private GestorAutenticacion $gestorAuth;
    private Usuario $modeloUsuario;
    private \Producto $modeloProducto;
    private Pedido $modeloPedido;
    private Tienda $modeloTienda;
    private PDO $conexion;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloUsuario = new Usuario();
        $this->modeloProducto = new \Producto();
        $this->modeloPedido = new Pedido();
        $this->modeloTienda = new Tienda();
        $this->conexion = Database::obtenerInstancia()->obtenerConexion();
        
        // Verificar que el usuario esté autenticado y sea artesano
        if (!$this->gestorAuth->estaAutenticado() || 
            $this->gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'artesano') {
            header('Location: /artesanoDigital/login');
            exit;
        }
    }

    /**
     * Muestra el dashboard del artesano
     */
    public function mostrarDashboard(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $idUsuario = $usuario['id_usuario'] ?? 0;
        
        // Obtener pedidos recientes de sus productos (como vendedor)
        $pedidosRecientes = $this->obtenerPedidosRecibidos($idUsuario);
        
        // Obtener pedidos personales (como cliente)
        $pedidosPersonales = $this->obtenerPedidosPersonales($idUsuario);
        
        // Obtener productos del artesano
        $productos = $this->obtenerProductosPorArtesano($idUsuario);
        
        $datos = [
            'titulo' => 'Panel de Artesano',
            'usuario' => $usuario,
            'estadisticas' => $this->obtenerEstadisticas($idUsuario),
            'pedidos_recientes' => $pedidosRecientes,
            'pedidos_personales' => $pedidosPersonales,
            'productos' => $productos
        ];

        $this->cargarVista('artesano/dashboard', $datos);
    }

    /**
     * Gestiona los productos del artesano
     */
    public function gestionarProductos(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $productos = []; // Implementar obtención de productos del artesano
        
        $datos = [
            'titulo' => 'Mis Productos',
            'productos' => $productos
        ];

        $this->cargarVista('artesano/productos', $datos);
    }

    /**
     * Muestra formulario para crear producto
     */
    public function mostrarCrearProducto(): void 
    {
        $datos = [
            'titulo' => 'Crear Producto'
        ];

        $this->cargarVista('artesano/crear-producto', $datos);
    }
    
    /**
     * Muestra formulario para crear/editar tienda
     */
    public function mostrarTienda(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $idUsuario = $usuario['id_usuario'];
        
        // Verificar si ya tiene una tienda
        $tienda = $this->modeloTienda->obtenerPorUsuario($idUsuario);
        
        $datos = [
            'titulo' => $tienda ? 'Editar mi Tienda' : 'Crear mi Tienda',
            'tienda' => $tienda
        ];

        $this->cargarVista('artesano/tienda', $datos);
    }
    
    /**
     * Procesa la creación/actualización de tienda
     */
    public function procesarTienda(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $idUsuario = $usuario['id_usuario'];
        
        try {
            // Validar datos obligatorios
            if (empty($_POST['nombre_tienda'])) {
                throw new Exception("El nombre de la tienda es obligatorio");
            }
            
            $nombreTienda = trim($_POST['nombre_tienda']);
            $descripcion = trim($_POST['descripcion'] ?? '');
            $imagenLogo = null;
            
            // Procesar imagen del logo si se ha subido
            if (isset($_FILES['imagen_logo']) && $_FILES['imagen_logo']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['imagen_logo'];
                $nombreTemporal = $archivo['tmp_name'];
                $nombreOriginal = $archivo['name'];
                
                // Validar que sea una imagen
                $tipoArchivo = $archivo['type'];
                if (!in_array($tipoArchivo, ['image/jpeg', 'image/png', 'image/gif'])) {
                    throw new Exception("El archivo debe ser una imagen (JPEG, PNG o GIF)");
                }
                
                // Generar nombre único para la imagen
                $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                $imagenLogo = 'logos/' . uniqid('tienda_') . '.' . $extension;
                
                // Directorio de carga
                $directorioDestino = dirname(__FILE__) . '/../uploads/logos/';
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0755, true);
                }
                
                // Mover archivo
                if (!move_uploaded_file($nombreTemporal, dirname(__FILE__) . '/../uploads/' . $imagenLogo)) {
                    throw new Exception("Error al cargar la imagen del logo");
                }
            }
            
            // Verificar si ya tiene una tienda
            $tiendaExistente = $this->modeloTienda->obtenerPorUsuario($idUsuario);
            
            // Si ya existe la tienda, actualizar
            if ($tiendaExistente) {
                $datosActualizar = [
                    'nombre_tienda' => $nombreTienda,
                    'descripcion' => $descripcion
                ];
                
                // Solo actualizar la imagen si se subió una nueva
                if ($imagenLogo) {
                    $datosActualizar['imagen_logo'] = $imagenLogo;
                    
                    // Eliminar logo anterior si existe
                    if (!empty($tiendaExistente['imagen_logo'])) {
                        $rutaLogoAnterior = dirname(__FILE__) . '/../uploads/' . $tiendaExistente['imagen_logo'];
                        if (file_exists($rutaLogoAnterior)) {
                            unlink($rutaLogoAnterior);
                        }
                    }
                }
                
                $resultado = $this->modeloTienda->actualizar($tiendaExistente['id_tienda'], $datosActualizar);
                if (!$resultado['exitoso']) {
                    throw new Exception($resultado['mensaje']);
                }
                
                $_SESSION['mensaje'] = "Tienda actualizada correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                // Crear nueva tienda
                $datosTienda = [
                    'id_usuario' => $idUsuario,
                    'nombre_tienda' => $nombreTienda,
                    'descripcion' => $descripcion,
                    'imagen_logo' => $imagenLogo
                ];
                
                $resultado = $this->modeloTienda->crear($datosTienda);
                if (!$resultado['exitoso']) {
                    throw new Exception($resultado['mensaje']);
                }
                
                $_SESSION['mensaje'] = "Tienda creada correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            }
            
            // Redireccionar para evitar reenvío del formulario
            header('Location: /artesanoDigital/artesano/tienda');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = "error";
            header('Location: /artesanoDigital/artesano/tienda');
            exit;
        }
    }

    /**
     * Procesa la creación de un producto
     */
    public function crearProducto(): void 
    {
        try {
            // Implementar creación de producto
            $this->responderJSON([
                'exitoso' => true,
                'mensaje' => 'Producto creado exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("Error al crear producto: " . $e->getMessage());
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Error al crear producto'
            ]);
        }
    }

    /**
     * Actualiza un producto
     */
    public function actualizarProducto(): void 
    {
        try {
            // Implementar actualización de producto
            $this->responderJSON([
                'exitoso' => true,
                'mensaje' => 'Producto actualizado exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("Error al actualizar producto: " . $e->getMessage());
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Error al actualizar producto'
            ]);
        }
    }

    /**
     * Gestiona los pedidos del artesano
     */
    public function gestionarPedidos(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $pedidos = []; // Implementar obtención de pedidos
        
        $datos = [
            'titulo' => 'Gestión de Pedidos',
            'pedidos' => $pedidos
        ];

        $this->cargarVista('artesano/pedidos', $datos);
    }

    /**
     * Actualiza el estado de un pedido
     */
    public function actualizarEstadoPedido(): void 
    {
        try {
            $idPedido = (int)($_POST['id_pedido'] ?? 0);
            $estado = $_POST['estado'] ?? '';

            if ($idPedido <= 0 || empty($estado)) {
                $this->responderJSON([
                    'exitoso' => false,
                    'mensaje' => 'Datos inválidos'
                ]);
                return;
            }

            $resultado = $this->modeloPedido->actualizarEstado($idPedido, $estado);

            $this->responderJSON([
                'exitoso' => $resultado,
                'mensaje' => $resultado ? 'Estado actualizado' : 'Error al actualizar estado'
            ]);

        } catch (Exception $e) {
            error_log("Error al actualizar estado de pedido: " . $e->getMessage());
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Obtiene estadísticas del artesano desde la base de datos
     * @param int $idUsuario
     * @return array
     */
    private function obtenerEstadisticas(int $idUsuario): array 
    {
        // Inicializar estadísticas
        $estadisticas = [
            'productos_activos' => 0,
            'ventas_totales' => 0,
            'ingresos_totales' => 0.00,
            'pedidos_pendientes' => 0
        ];
        
        try {
            // Usar la conexión ya establecida en el constructor
            $conexion = $this->conexion;
            
            // 1. Obtener ID de la tienda
            $stmt = $conexion->prepare("SELECT id_tienda FROM tiendas WHERE id_usuario = ?");
            $stmt->execute([$idUsuario]);
            $tienda = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tienda) {
                error_log("Artesano ID {$idUsuario} no tiene tienda registrada");
                return $estadisticas; // Artesano sin tienda
            }
            
            $idTienda = $tienda['id_tienda'];
            error_log("Obteniendo estadísticas para tienda ID: {$idTienda} del usuario: {$idUsuario}");
            
            // 2. Contar productos activos
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM productos WHERE id_tienda = ? AND activo = 1");
            $stmt->execute([$idTienda]);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            $estadisticas['productos_activos'] = (int)($resultado['total'] ?? 0);
            
            // 3. Contar ventas totales (productos vendidos)
            $stmt = $conexion->prepare("
                SELECT COUNT(*) as total_ventas, SUM(pp.cantidad * pp.precio_unitario) as ingresos
                FROM pedido_productos pp
                JOIN pedidos p ON pp.id_pedido = p.id_pedido
                JOIN productos prod ON pp.id_producto = prod.id_producto
                WHERE prod.id_tienda = ? AND p.estado != 'cancelado'
            ");
            $stmt->execute([$idTienda]);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            $estadisticas['ventas_totales'] = (int)($resultado['total_ventas'] ?? 0);
            $estadisticas['ingresos_totales'] = floatval($resultado['ingresos'] ?? 0);
            
            // 4. Contar pedidos pendientes
            $stmt = $conexion->prepare("
                SELECT COUNT(DISTINCT p.id_pedido) as pendientes
                FROM pedidos p
                JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
                JOIN productos prod ON pp.id_producto = prod.id_producto
                WHERE prod.id_tienda = ? AND p.estado = 'pendiente'
            ");
            $stmt->execute([$idTienda]);
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
            $estadisticas['pedidos_pendientes'] = (int)($resultado['pendientes'] ?? 0);
            
        } catch (\Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
        }
        
        return $estadisticas;
    }

    /**
     * Obtiene los pedidos recibidos (como vendedor)
     * @param int $idUsuario ID del artesano
     * @return array Lista de pedidos
     */
    private function obtenerPedidosRecibidos(int $idUsuario): array 
    {
        try {
            // Primero obtenemos el ID de la tienda del artesano
            $stmt = $this->conexion->prepare("
                SELECT id_tienda FROM tiendas WHERE id_usuario = ?
            ");
            $stmt->execute([$idUsuario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $idTienda = $resultado ? $resultado['id_tienda'] : 0;
            
            if (!$idTienda) {
                error_log("Usuario {$idUsuario} no tiene tienda para obtener pedidos recibidos");
                return [];
            }
            
            error_log("Obteniendo pedidos recibidos para tienda ID: {$idTienda}");
            
            // Ahora obtenemos los pedidos que contengan productos de esa tienda
            // Calculamos solo el total de los productos de este artesano en cada pedido
            $stmt = $this->conexion->prepare("
                SELECT 
                    p.id_pedido, 
                    p.fecha_pedido, 
                    SUM(pp.cantidad * pp.precio_unitario) as total, 
                    p.estado, 
                    u.nombre as cliente_nombre,
                    u.correo as cliente_correo
                FROM pedidos p
                JOIN pedido_productos pp ON p.id_pedido = pp.id_pedido
                JOIN productos prod ON pp.id_producto = prod.id_producto
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE prod.id_tienda = ?
                GROUP BY p.id_pedido, p.fecha_pedido, p.estado, u.nombre, u.correo
                ORDER BY p.fecha_pedido DESC
                LIMIT 10
            ");
            $stmt->execute([$idTienda]);
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Encontrados " . count($pedidos) . " pedidos recibidos para la tienda {$idTienda}");
            return $pedidos;
            
        } catch (\Exception $e) {
            error_log("Error al obtener pedidos recibidos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los pedidos personales del artesano (como cliente)
     * @param int $idUsuario ID del artesano
     * @return array Lista de pedidos
     */
    private function obtenerPedidosPersonales(int $idUsuario): array 
    {
        try {
            error_log("Obteniendo pedidos personales para usuario ID: {$idUsuario}");
            
            $stmt = $this->conexion->prepare("
                SELECT id_pedido, fecha_pedido, total, estado, metodo_pago
                FROM pedidos 
                WHERE id_usuario = ?
                ORDER BY fecha_pedido DESC
                LIMIT 10
            ");
            $stmt->execute([$idUsuario]);
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Encontrados " . count($pedidos) . " pedidos personales para usuario {$idUsuario}");
            return $pedidos;
            
        } catch (\Exception $e) {
            error_log("Error al obtener pedidos personales: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los productos del artesano
     * @param int $idUsuario ID del artesano
     * @return array Lista de productos
     */
    private function obtenerProductosPorArtesano(int $idUsuario): array 
    {
        try {
            // Primero obtenemos el ID de la tienda del artesano
            $stmt = $this->conexion->prepare("
                SELECT id_tienda, nombre_tienda FROM tiendas WHERE id_usuario = ?
            ");
            $stmt->execute([$idUsuario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $idTienda = $resultado ? $resultado['id_tienda'] : 0;
            $nombreTienda = $resultado ? $resultado['nombre_tienda'] : '';
            
            if (!$idTienda) {
                error_log("Usuario {$idUsuario} no tiene tienda para obtener productos");
                return [];
            }
            
            error_log("Obteniendo productos para tienda ID: {$idTienda}");
            
            // Ahora obtenemos los productos de esa tienda
            $stmt = $this->conexion->prepare("
                SELECT p.id_producto, p.nombre, p.precio, p.descripcion, p.stock, p.imagen, p.activo, p.descuento
                FROM productos p
                WHERE p.id_tienda = ?
                ORDER BY p.fecha_creacion DESC
                LIMIT 20
            ");
            $stmt->execute([$idTienda]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar el nombre de la tienda a cada producto
            foreach ($productos as &$producto) {
                $producto['nombre_tienda'] = $nombreTienda;
            }
            
            error_log("Encontrados " . count($productos) . " productos para la tienda {$idTienda}");
            return $productos;
            
        } catch (\Exception $e) {
            error_log("Error al obtener productos del artesano: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Carga una vista
     * @param string $vista
     * @param array $datos
     */
    private function cargarVista(string $vista, array $datos = []): void 
    {
        // Añadir mensaje de sesión si existe
        if (isset($_SESSION['mensaje'])) {
            $datos['mensaje'] = $_SESSION['mensaje'];
            $datos['tipo_mensaje'] = $_SESSION['tipo_mensaje'] ?? 'info';
            
            // Limpiar mensajes de sesión después de usarlos
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        }
        
        extract($datos);
        include "views/{$vista}.php";
    }

    /**
     * Responde con JSON
     * @param array $datos
     */
    private function responderJSON(array $datos): void 
    {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}
