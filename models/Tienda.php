<?php
/**
 * Modelo Tienda - Gestión de tiendas de artesanos
 * Responsabilidad: CRUD de tiendas y operaciones relacionadas
 */

namespace Models;

use Config\Database;
use PDO;
use Exception;

class Tienda 
{
    private PDO $conexion;

    public function __construct() 
    {
        $db = Database::obtenerInstancia();
        $this->conexion = $db->obtenerConexion();
    }

    /**
     * Crea una nueva tienda
     * @param array $datos Datos de la tienda
     * @return array [exitoso => bool, mensaje => string, id => int|null]
     */
    public function crear(array $datos): array 
    {
        try {
            // Validar datos requeridos
            if (empty($datos['id_usuario'])) {
                return [
                    'exitoso' => false,
                    'mensaje' => 'ID de usuario es requerido',
                    'id' => null
                ];
            }

            if (empty($datos['nombre_tienda'])) {
                return [
                    'exitoso' => false,
                    'mensaje' => 'Nombre de tienda es requerido',
                    'id' => null
                ];
            }

            // Verificar si el usuario ya tiene una tienda
            if ($this->usuarioTieneTienda($datos['id_usuario'])) {
                return [
                    'exitoso' => false,
                    'mensaje' => 'El usuario ya tiene una tienda registrada',
                    'id' => null
                ];
            }

            $sql = "INSERT INTO tiendas (
                        id_usuario, 
                        nombre_tienda, 
                        descripcion,
                        imagen_logo,
                        fecha_creacion
                    ) VALUES (
                        :id_usuario, 
                        :nombre_tienda, 
                        :descripcion,
                        :imagen_logo,
                        NOW()
                    )";

            $stmt = $this->conexion->prepare($sql);
            $idUsuario = $datos['id_usuario'];
            $nombreTienda = $datos['nombre_tienda'];
            $descripcion = $datos['descripcion'] ?? 'Tienda de productos artesanales';
            $imagenLogo = $datos['imagen_logo'] ?? null;
            
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_tienda', $nombreTienda, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':imagen_logo', $imagenLogo, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return [
                    'exitoso' => true,
                    'mensaje' => 'Tienda creada exitosamente',
                    'id' => $this->conexion->lastInsertId()
                ];
            } else {
                return [
                    'exitoso' => false,
                    'mensaje' => 'Error al crear la tienda',
                    'id' => null
                ];
            }

        } catch (Exception $e) {
            error_log("Error al crear tienda: " . $e->getMessage());
            return [
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor',
                'id' => null
            ];
        }
    }

    /**
     * Obtiene una tienda por ID de usuario
     * @param int $idUsuario
     * @return array|null
     */
    public function obtenerPorUsuario(int $idUsuario): ?array 
    {
        try {
            $sql = "SELECT * FROM tiendas WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;

        } catch (Exception $e) {
            error_log("Error al obtener tienda por usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica si un usuario ya tiene una tienda
     * @param int $idUsuario
     * @return bool
     */
    private function usuarioTieneTienda(int $idUsuario): bool 
    {
        try {
            $sql = "SELECT COUNT(*) FROM tiendas WHERE id_usuario = :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;

        } catch (Exception $e) {
            error_log("Error al verificar tienda existente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza información de una tienda
     * @param int $id
     * @param array $datos
     * @return array
     */
    public function actualizar(int $id, array $datos): array 
    {
        try {
            $camposActualizar = [];
            $parametros = [':id' => $id];

            if (isset($datos['nombre_tienda'])) {
                $camposActualizar[] = "nombre_tienda = :nombre_tienda";
                $parametros[':nombre_tienda'] = $datos['nombre_tienda'];
            }

            if (isset($datos['descripcion'])) {
                $camposActualizar[] = "descripcion = :descripcion";
                $parametros[':descripcion'] = $datos['descripcion'];
            }

            // Nota: La tabla tiendas no tiene un campo 'activa' según la estructura de BD
            
            if (isset($datos['imagen_logo'])) {
                $camposActualizar[] = "imagen_logo = :imagen_logo";
                $parametros[':imagen_logo'] = $datos['imagen_logo'];
            }

            if (empty($camposActualizar)) {
                return [
                    'exitoso' => false,
                    'mensaje' => 'No hay datos para actualizar'
                ];
            }

            $sql = "UPDATE tiendas SET " . implode(', ', $camposActualizar) . " WHERE id_tienda = :id";
            $stmt = $this->conexion->prepare($sql);

            if ($stmt->execute($parametros)) {
                return [
                    'exitoso' => true,
                    'mensaje' => 'Tienda actualizada exitosamente'
                ];
            } else {
                return [
                    'exitoso' => false,
                    'mensaje' => 'Error al actualizar la tienda'
                ];
            }

        } catch (Exception $e) {
            error_log("Error al actualizar tienda: " . $e->getMessage());
            return [
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Elimina (desactiva) una tienda
     * @param int $id
     * @return array
     */
    public function eliminar(int $id): array 
    {
        try {
            $sql = "UPDATE tiendas SET activa = 0 WHERE id_tienda = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'exitoso' => true,
                    'mensaje' => 'Tienda desactivada exitosamente'
                ];
            } else {
                return [
                    'exitoso' => false,
                    'mensaje' => 'Error al desactivar la tienda'
                ];
            }

        } catch (Exception $e) {
            error_log("Error al eliminar tienda: " . $e->getMessage());
            return [
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Lista todas las tiendas activas
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function listarActivas(int $limite = 20, int $offset = 0): array 
    {
        try {
            $sql = "SELECT t.*, u.nombre as nombre_artesano 
                    FROM tiendas t 
                    INNER JOIN usuarios u ON t.id_usuario = u.id_usuario 
                    WHERE t.activa = 1 
                    ORDER BY t.fecha_creacion DESC 
                    LIMIT :limite OFFSET :offset";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al listar tiendas: " . $e->getMessage());
            return [];
        }
    }
}
