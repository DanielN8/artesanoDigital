<?php
/**
 * Modelo Usuario - Gestión de usuarios (clientes y artesanos)
 * Responsabilidad: CRUD de usuarios y operaciones relacionadas
 */

namespace Models;

use Config\Database;
use Utils\Validador;
use PDO;
use Exception;

class Usuario 
{
    private PDO $conexion;

    public function __construct() 
    {
        $db = Database::obtenerInstancia();
        $this->conexion = $db->obtenerConexion();
    }

    /**
     * Crea un nuevo usuario
     * @param array $datos Datos del usuario
     * @return array [exitoso => bool, mensaje => string, id => int|null]
     */
    public function crear(array $datos): array 
    {
        try {
            // Validar datos
            $validacion = Validador::validarRegistroUsuario($datos);
            if (!$validacion['valido']) {
                return [
                    'exitoso' => false,
                    'mensaje' => implode(', ', $validacion['errores']),
                    'id' => null
                ];
            }

            // Verificar si el correo ya existe
            if ($this->existeCorreo($datos['correo'])) {
                return [
                    'exitoso' => false,
                    'mensaje' => 'El correo electrónico ya está registrado',
                    'id' => null
                ];
            }

            // Sanitizar datos
            $datosLimpios = [
                'nombre' => Validador::sanitizarTexto($datos['nombre']),
                'correo' => Validador::sanitizarCorreo($datos['correo']),
                'telefono' => isset($datos['telefono']) ? Validador::sanitizarTexto($datos['telefono']) : null,
                'direccion' => isset($datos['direccion']) ? Validador::sanitizarTexto($datos['direccion']) : null,
                'contrasena' => password_hash($datos['contrasena'], PASSWORD_DEFAULT),
                'tipo_usuario' => $datos['tipo_usuario']
            ];

            $sql = "INSERT INTO usuarios (nombre, correo, telefono, direccion, contrasena, tipo_usuario) 
                    VALUES (:nombre, :correo, :telefono, :direccion, :contrasena, :tipo_usuario)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($datosLimpios);

            $idUsuario = $this->conexion->lastInsertId();

            return [
                'exitoso' => true,
                'mensaje' => 'Usuario creado exitosamente',
                'id' => (int)$idUsuario
            ];

        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return [
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor',
                'id' => null
            ];
        }
    }

    /**
     * Obtiene un usuario por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId(int $id): ?array 
    {
        try {
            $sql = "SELECT id_usuario, nombre, correo, telefono, direccion, tipo_usuario, 
                           fecha_registro, activo 
                    FROM usuarios 
                    WHERE id_usuario = :id AND activo = 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id' => $id]);

            $resultado = $stmt->fetch();
            return $resultado ?: null;

        } catch (Exception $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un usuario por correo electrónico
     * @param string $correo
     * @return array|null
     */
    public function obtenerPorCorreo(string $correo): ?array 
    {
        try {
            $sql = "SELECT id_usuario, nombre, correo, telefono, direccion, contrasena, 
                           tipo_usuario, fecha_registro, activo 
                    FROM usuarios 
                    WHERE correo = :correo";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['correo' => $correo]);

            $resultado = $stmt->fetch();
            return $resultado ?: null;

        } catch (Exception $e) {
            error_log("Error al obtener usuario por correo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza los datos de un usuario
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar(int $id, array $datos): bool 
    {
        try {
            $camposPermitidos = ['nombre', 'telefono', 'direccion'];
            $camposActualizar = [];
            $valores = ['id' => $id];

            foreach ($camposPermitidos as $campo) {
                if (isset($datos[$campo])) {
                    $camposActualizar[] = "{$campo} = :{$campo}";
                    $valores[$campo] = Validador::sanitizarTexto($datos[$campo]);
                }
            }

            if (empty($camposActualizar)) {
                return false;
            }

            $sql = "UPDATE usuarios SET " . implode(', ', $camposActualizar) . " 
                    WHERE id_usuario = :id AND activo = 1";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute($valores);

        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia la contraseña de un usuario
     * @param int $id
     * @param string $contrasenaActual
     * @param string $contrasenaNueva
     * @return array [exitoso => bool, mensaje => string]
     */
    public function cambiarContrasena(int $id, string $contrasenaActual, string $contrasenaNueva): array 
    {
        try {
            // Obtener usuario actual
            $usuario = $this->obtenerPorId($id);
            if (!$usuario) {
                return ['exitoso' => false, 'mensaje' => 'Usuario no encontrado'];
            }

            // Verificar contraseña actual
            $usuarioCompleto = $this->obtenerPorCorreo($usuario['correo']);
            if (!password_verify($contrasenaActual, $usuarioCompleto['contrasena'])) {
                return ['exitoso' => false, 'mensaje' => 'Contraseña actual incorrecta'];
            }

            // Validar nueva contraseña
            if (!Validador::validarContrasena($contrasenaNueva)) {
                return ['exitoso' => false, 'mensaje' => 'La nueva contraseña no cumple los requisitos'];
            }

            // Actualizar contraseña
            $sql = "UPDATE usuarios SET contrasena = :contrasena WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                'contrasena' => password_hash($contrasenaNueva, PASSWORD_DEFAULT),
                'id' => $id
            ]);

            return [
                'exitoso' => $resultado,
                'mensaje' => $resultado ? 'Contraseña actualizada exitosamente' : 'Error al actualizar contraseña'
            ];

        } catch (Exception $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return ['exitoso' => false, 'mensaje' => 'Error interno del servidor'];
        }
    }

    /**
     * Desactiva un usuario (soft delete)
     * @param int $id
     * @return bool
     */
    public function desactivar(int $id): bool 
    {
        try {
            $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute(['id' => $id]);

        } catch (Exception $e) {
            error_log("Error al desactivar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe un correo en la base de datos
     * @param string $correo
     * @return bool
     */
    private function existeCorreo(string $correo): bool 
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE correo = :correo";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['correo' => $correo]);
            
            return $stmt->fetchColumn() > 0;

        } catch (Exception $e) {
            error_log("Error al verificar correo existente: " . $e->getMessage());
            return true; // Por seguridad, asumir que existe
        }
    }

    /**
     * Verifica si existe un usuario con el correo dado
     * @param string $correo El correo a verificar
     * @return bool
     */
    public function existePorCorreo(string $correo): bool 
    {
        return $this->existeCorreo($correo);
    }

    /**
     * Obtiene estadísticas de usuarios
     * @return array
     */
    public function obtenerEstadisticas(): array 
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN tipo_usuario = 'cliente' THEN 1 ELSE 0 END) as clientes,
                        SUM(CASE WHEN tipo_usuario = 'artesano' THEN 1 ELSE 0 END) as artesanos,
                        SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos
                    FROM usuarios";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetch() ?: [
                'total' => 0,
                'clientes' => 0,
                'artesanos' => 0,
                'activos' => 0
            ];

        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de usuarios: " . $e->getMessage());
            return ['total' => 0, 'clientes' => 0, 'artesanos' => 0, 'activos' => 0];
        }
    }

    /**
     * Guarda un token de recuperación para un usuario (método temporal para logging)
     * @param string $correo
     * @param string $token
     * @return bool
     */
    public function guardarTokenRecuperacion(string $correo, string $token): bool 
    {
        // Por ahora solo logging, implementar almacenamiento real después
        error_log("Token de recuperación generado para $correo: $token");
        return true;
    }
}
