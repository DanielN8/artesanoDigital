<?php
/**
 * Modelo Pedido - Gestión de pedidos de compra
 * Responsabilidad: CRUD de pedidos y operaciones relacionadas
 */

namespace Models;

use Config\Database;
use PDO;
use Exception;

class Pedido 
{
    private PDO $conexion;

    public function __construct() 
    {
        $db = Database::obtenerInstancia();
        $this->conexion = $db->obtenerConexion();
    }

    /**
     * Crea un nuevo pedido
     * @param array $datos
     * @return array
     */
    public function crear(array $datos): array 
    {
        try {
            $this->conexion->beginTransaction();

            // Verificar que el total sea un número válido
            $total = floatval($datos['total']);
            if (is_nan($total) || $total <= 0) {
                error_log("Error: Total inválido en Pedido::crear(): " . var_export($datos['total'], true));
                throw new Exception("El total del pedido no es válido");
            }
            
            // Crear el pedido principal
            $sql = "INSERT INTO pedidos (id_usuario, estado, metodo_pago, total, direccion_envio) 
                    VALUES (:id_usuario, 'pendiente', :metodo_pago, :total, :direccion_envio)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                'id_usuario' => $datos['id_usuario'],
                'metodo_pago' => $datos['metodo_pago'],
                'total' => $total, // Usar el valor ya validado
                'direccion_envio' => $datos['direccion_envio']
            ]);

            $idPedido = $this->conexion->lastInsertId();

            // Agregar productos al pedido
            foreach ($datos['productos'] as $producto) {
                // Verificar que el producto tenga un ID válido
                if (!isset($producto['id_producto']) || empty($producto['id_producto'])) {
                    error_log("Error: Se intentó guardar un producto sin ID: " . print_r($producto, true));
                    continue; // Saltar este producto
                }
                
                $sqlProducto = "INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio_unitario) 
                               VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";

                $stmtProducto = $this->conexion->prepare($sqlProducto);
                $stmtProducto->execute([
                    'id_pedido' => $idPedido,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio']
                ]);
            }

            $this->conexion->commit();

            return [
                'exitoso' => true,
                'mensaje' => 'Pedido creado exitosamente',
                'id_pedido' => (int)$idPedido
            ];

        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error al crear pedido: " . $e->getMessage());
            return [
                'exitoso' => false,
                'mensaje' => 'Error al procesar el pedido'
            ];
        }
    }

    /**
     * Obtiene pedidos de un usuario
     * @param int $idUsuario
     * @return array
     */
    public function obtenerPorUsuario(int $idUsuario): array 
    {
        try {
            $sql = "SELECT * FROM pedidos WHERE id_usuario = :id_usuario ORDER BY fecha_pedido DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);
            
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error al obtener pedidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza el estado de un pedido
     * @param int $idPedido
     * @param string $estado
     * @return bool
     */
    public function actualizarEstado(int $idPedido, string $estado): bool 
    {
        try {
            $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([
                'estado' => $estado,
                'id_pedido' => $idPedido
            ]);

        } catch (Exception $e) {
            error_log("Error al actualizar estado del pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los productos de un pedido específico
     * @param int $idPedido
     * @return array
     */
    public function obtenerProductosPedido(int $idPedido): array 
    {
        try {
            $sql = "SELECT pp.id_pedido_producto, pp.id_producto, pp.cantidad, pp.precio_unitario,
                           p.nombre, p.descripcion, p.imagen
                    FROM pedido_productos pp
                    INNER JOIN productos p ON pp.id_producto = p.id_producto
                    WHERE pp.id_pedido = :id_pedido
                    ORDER BY pp.id_pedido_producto ASC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_pedido' => $idPedido]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener productos del pedido: " . $e->getMessage());
            return [];
        }
    }
}
