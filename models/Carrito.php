<?php
/**
 * Modelo Carrito - Gestión del carrito de compras
 * Responsabilidad: CRUD del carrito y operaciones de compra
 */

namespace Models;

use Config\Database;
use PDO;
use Exception;

// Incluir el modelo Producto (que no tiene namespace)
require_once dirname(__FILE__) . '/Producto.php';

class Carrito 
{
    private PDO $conexion;

    public function __construct() 
    {
        $db = Database::obtenerInstancia();
        $this->conexion = $db->obtenerConexion();
    }

    /**
     * Obtiene o crea el carrito del usuario
     * @param int $idUsuario
     * @return int ID del carrito
     */
    public function obtenerOCrearCarrito(int $idUsuario): int 
    {
        try {
            // Buscar carrito existente
            $sql = "SELECT id_carrito FROM carritos WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);
            
            $carrito = $stmt->fetch();
            
            if ($carrito) {
                return (int)$carrito['id_carrito'];
            }

            // Crear nuevo carrito
            $sql = "INSERT INTO carritos (id_usuario) VALUES (:id_usuario)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);

            return (int)$this->conexion->lastInsertId();

        } catch (Exception $e) {
            error_log("Error al obtener/crear carrito: " . $e->getMessage());
            throw new Exception("Error al gestionar carrito");
        }
    }

    /**
     * Añade un producto al carrito
     * @param int $idUsuario
     * @param int $idProducto
     * @param int $cantidad
     * @return array [exitoso => bool, mensaje => string]
     */
    public function agregarProducto(int $idUsuario, int $idProducto, int $cantidad = 1): array 
    {
        try {
            // Verificar que el producto existe y tiene stock
            $modeloProducto = new \Producto();
            $producto = $modeloProducto->obtenerPorId($idProducto);
            
            if (!$producto) {
                return ['exitoso' => false, 'mensaje' => 'Producto no encontrado'];
            }

            if ($producto['stock'] < $cantidad) {
                return ['exitoso' => false, 'mensaje' => 'Stock insuficiente'];
            }

            $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

            // Verificar si el producto ya está en el carrito
            $sql = "SELECT cantidad FROM carrito_productos 
                    WHERE id_carrito = :id_carrito AND id_producto = :id_producto";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                'id_carrito' => $idCarrito,
                'id_producto' => $idProducto
            ]);

            $productoEnCarrito = $stmt->fetch();

            if ($productoEnCarrito) {
                // Actualizar cantidad
                $nuevaCantidad = $productoEnCarrito['cantidad'] + $cantidad;
                
                if ($producto['stock'] < $nuevaCantidad) {
                    return ['exitoso' => false, 'mensaje' => 'Stock insuficiente para la cantidad solicitada'];
                }

                $sql = "UPDATE carrito_productos 
                        SET cantidad = :cantidad 
                        WHERE id_carrito = :id_carrito AND id_producto = :id_producto";
                $stmt = $this->conexion->prepare($sql);
                $resultado = $stmt->execute([
                    'cantidad' => $nuevaCantidad,
                    'id_carrito' => $idCarrito,
                    'id_producto' => $idProducto
                ]);
            } else {
                // Insertar nuevo producto
                $sql = "INSERT INTO carrito_productos (id_carrito, id_producto, cantidad) 
                        VALUES (:id_carrito, :id_producto, :cantidad)";
                $stmt = $this->conexion->prepare($sql);
                $resultado = $stmt->execute([
                    'id_carrito' => $idCarrito,
                    'id_producto' => $idProducto,
                    'cantidad' => $cantidad
                ]);
            }

            return [
                'exitoso' => $resultado,
                'mensaje' => $resultado ? 'Producto agregado al carrito' : 'Error al agregar producto'
            ];

        } catch (Exception $e) {
            error_log("Error al agregar producto al carrito: " . $e->getMessage());
            return ['exitoso' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Obtiene los productos del carrito del usuario
     * @param int $idUsuario
     * @return array
     */
    public function obtenerProductos(int $idUsuario): array 
    {
        try {
            $sql = "SELECT cp.id_carrito_producto, cp.id_producto, cp.cantidad,
                           p.nombre, p.descripcion, p.precio, p.imagen, p.stock,
                           t.nombre_tienda, u.nombre as nombre_artesano,
                           (cp.cantidad * p.precio) as subtotal
                    FROM carrito_productos cp
                    INNER JOIN carritos c ON cp.id_carrito = c.id_carrito
                    INNER JOIN productos p ON cp.id_producto = p.id_producto
                    LEFT JOIN tiendas t ON p.id_tienda = t.id_tienda
                    LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
                    WHERE c.id_usuario = :id_usuario AND p.activo = 1
                    ORDER BY cp.fecha_agregado DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener productos del carrito: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza la cantidad de un producto en el carrito
     * @param int $idUsuario
     * @param int $idProducto
     * @param int $cantidad
     * @return array [exitoso => bool, mensaje => string]
     */
    public function actualizarCantidad(int $idUsuario, int $idProducto, int $cantidad): array 
    {
        try {
            if ($cantidad <= 0) {
                return $this->eliminarProducto($idUsuario, $idProducto);
            }

            // Verificar stock disponible
            $modeloProducto = new \Producto();
            if (!$modeloProducto->verificarStock($idProducto, $cantidad)) {
                return ['exitoso' => false, 'mensaje' => 'Stock insuficiente'];
            }

            $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

            $sql = "UPDATE carrito_productos 
                    SET cantidad = :cantidad 
                    WHERE id_carrito = :id_carrito AND id_producto = :id_producto";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                'cantidad' => $cantidad,
                'id_carrito' => $idCarrito,
                'id_producto' => $idProducto
            ]);

            return [
                'exitoso' => $resultado,
                'mensaje' => $resultado ? 'Cantidad actualizada' : 'Error al actualizar cantidad'
            ];

        } catch (Exception $e) {
            error_log("Error al actualizar cantidad en carrito: " . $e->getMessage());
            return ['exitoso' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Elimina un producto del carrito
     * @param int $idUsuario
     * @param int $idProducto
     * @return array [exitoso => bool, mensaje => string]
     */
    public function eliminarProducto(int $idUsuario, int $idProducto): array 
    {
        try {
            $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

            $sql = "DELETE FROM carrito_productos 
                    WHERE id_carrito = :id_carrito AND id_producto = :id_producto";

            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                'id_carrito' => $idCarrito,
                'id_producto' => $idProducto
            ]);

            return [
                'exitoso' => $resultado,
                'mensaje' => $resultado ? 'Producto eliminado del carrito' : 'Error al eliminar producto'
            ];

        } catch (Exception $e) {
            error_log("Error al eliminar producto del carrito: " . $e->getMessage());
            return ['exitoso' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Vacía completamente el carrito del usuario
     * @param int $idUsuario
     * @return bool
     */
    public function vaciarCarrito(int $idUsuario): bool 
    {
        try {
            $this->conexion->beginTransaction();
            
            // Obtenemos el ID del carrito
            $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

            // Eliminamos todos los productos del carrito
            $sql = "DELETE FROM carrito_productos WHERE id_carrito = :id_carrito";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute(['id_carrito' => $idCarrito]);
            
            if (!$resultado) {
                throw new Exception("No se pudo vaciar el carrito con ID: $idCarrito");
            }
            
            // Verificar que se hayan eliminado todos los productos
            $sqlVerificar = "SELECT COUNT(*) FROM carrito_productos WHERE id_carrito = :id_carrito";
            $stmtVerificar = $this->conexion->prepare($sqlVerificar);
            $stmtVerificar->execute(['id_carrito' => $idCarrito]);
            
            if ($stmtVerificar->fetchColumn() > 0) {
                throw new Exception("No se eliminaron todos los productos del carrito: $idCarrito");
            }
            
            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error al vaciar carrito para usuario $idUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alias de vaciarCarrito() para mantener compatibilidad
     * @deprecated Use vaciarCarrito() instead
     * @param int $idUsuario
     * @return bool
     */
    public function limpiar(int $idUsuario): bool 
    {
        return $this->vaciarCarrito($idUsuario);
    }

    /**
     * Calcula el total del carrito
     * @param int $idUsuario
     * @return float
     */
    public function calcularTotal(int $idUsuario): float 
    {
        try {
            // Verificar que el usuario sea válido
            if ($idUsuario <= 0) {
                error_log("ID de usuario inválido para calcular total: " . $idUsuario);
                return 0.0;
            }
            
            $sql = "SELECT SUM(cp.cantidad * p.precio) as total
                    FROM carrito_productos cp
                    INNER JOIN carritos c ON cp.id_carrito = c.id_carrito
                    INNER JOIN productos p ON cp.id_producto = p.id_producto
                    WHERE c.id_usuario = :id_usuario AND p.activo = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (float)($resultado['total'] ?? 0);
            
            // Validar que el total sea un número positivo
            if (is_nan($total) || $total < 0) {
                error_log("Total inválido calculado para usuario {$idUsuario}: {$total}");
                return 0.0;
            }
            
            error_log("Total calculado para usuario {$idUsuario}: {$total}");
            return $total;

        } catch (Exception $e) {
            error_log("Error al calcular total del carrito: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Cuenta los productos en el carrito
     * @param int $idUsuario
     * @return int
     */
    public function contarProductos(int $idUsuario): int 
    {
        try {
            $sql = "SELECT SUM(cp.cantidad) as total_productos
                    FROM carrito_productos cp
                    INNER JOIN carritos c ON cp.id_carrito = c.id_carrito
                    INNER JOIN productos p ON cp.id_producto = p.id_producto
                    WHERE c.id_usuario = :id_usuario AND p.activo = 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);

            $resultado = $stmt->fetch();
            return (int)($resultado['total_productos'] ?? 0);

        } catch (Exception $e) {
            error_log("Error al contar productos del carrito: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Valida el carrito antes del checkout
     * @param int $idUsuario
     * @return array [valido => bool, errores => array]
     */
    public function validarCarrito(int $idUsuario): array 
    {
        try {
            $productos = $this->obtenerProductos($idUsuario);
            $errores = [];

            if (empty($productos)) {
                $errores[] = 'El carrito está vacío';
                return ['valido' => false, 'errores' => $errores];
            }

            foreach ($productos as $producto) {
                // El campo 'activo' ya está filtrado en obtenerProductos() con WHERE p.activo = 1
                // pero agregamos una verificación adicional por seguridad
                if (!isset($producto['stock']) || !isset($producto['cantidad'])) {
                    $nombreProducto = isset($producto['nombre']) ? $producto['nombre'] : 'desconocido';
                    $errores[] = "Datos del producto '{$nombreProducto}' incompletos";
                    continue;
                }

                // Verificar stock
                if ((int)$producto['stock'] < (int)$producto['cantidad']) {
                    $errores[] = "Stock insuficiente para '{$producto['nombre']}'. Disponible: {$producto['stock']}, solicitado: {$producto['cantidad']}";
                }
            }

            return [
                'valido' => empty($errores),
                'errores' => $errores
            ];

        } catch (Exception $e) {
            error_log("Error al validar carrito: " . $e->getMessage());
            return ['valido' => false, 'errores' => ['Error interno del servidor']];
        }
    }

    /**
     * Obtiene el resumen total del carrito (cantidad y monto)
     * @param int $idUsuario
     * @return array [cantidad => int, total => float]
     */
    public function obtenerResumen(int $idUsuario): array 
    {
        try {
            $sql = "SELECT 
                        COUNT(cp.id_producto) as cantidad_productos,
                        SUM(cp.cantidad) as cantidad_items,
                        SUM(cp.cantidad * p.precio) as total
                    FROM carrito_productos cp
                    INNER JOIN carritos c ON cp.id_carrito = c.id_carrito
                    INNER JOIN productos p ON cp.id_producto = p.id_producto
                    WHERE c.id_usuario = :id_usuario AND p.activo = 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);

            $resultado = $stmt->fetch();

            return [
                'cantidad_productos' => (int)($resultado['cantidad_productos'] ?? 0),
                'cantidad_items' => (int)($resultado['cantidad_items'] ?? 0),
                'total' => (float)($resultado['total'] ?? 0.0)
            ];

        } catch (Exception $e) {
            error_log("Error al obtener resumen del carrito: " . $e->getMessage());
            return [
                'cantidad_productos' => 0,
                'cantidad_items' => 0,
                'total' => 0.0
            ];
        }
    }
}
