<?php
/**
 * Controlador de Perfil de Usuario
 * Responsabilidad: Gestionar la información del perfil del usuario
 */

namespace Controllers;

use Utils\GestorAutenticacion;

class ControladorPerfil 
{
    private GestorAutenticacion $gestorAuth;
    private $conexion;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        
        // Verificar que el usuario esté autenticado
        if (!$this->gestorAuth->estaAutenticado()) {
            header('Location: /artesanoDigital/login');
            exit;
        }

        // Generar token CSRF si no existe
        if (empty($_SESSION['csrf_token'])) {
            $this->gestorAuth->generarTokenCSRF();
        }

        // Obtener conexión a la base de datos
        require_once __DIR__ . '/../config/Database.php';
        $this->conexion = \Config\Database::obtenerInstancia()->obtenerConexion();
    }

    /**
     * Muestra el perfil del usuario
     */
    public function mostrarPerfil(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarPerfil($usuario['id_usuario']);
        }

        // Obtener información completa del usuario de la base de datos
        $datosUsuario = $this->obtenerDatosCompletos($usuario['id_usuario']);

        $datos = [
            'titulo' => 'Mi Perfil',
            'usuario' => $datosUsuario
        ];

        $this->cargarVista('perfil/perfil', $datos);
    }

    /**
     * Actualiza la información del perfil
     */
    private function actualizarPerfil(int $idUsuario): void 
    {
        try {
            // Validar token CSRF
            if (!$this->gestorAuth->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Token de seguridad inválido');
            }

            // Validar datos obligatorios
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            
            if (empty($nombre) || empty($correo)) {
                throw new \Exception('El nombre y correo son obligatorios');
            }

            // Verificar si el correo ya existe (excepto el propio)
            $sql = "SELECT id_usuario FROM usuarios WHERE correo = :correo AND id_usuario != :id_usuario";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['correo' => $correo, 'id_usuario' => $idUsuario]);
            
            if ($stmt->fetch()) {
                throw new \Exception('Este correo ya está registrado por otro usuario');
            }

            // Actualizar información básica
            $sql = "UPDATE usuarios SET 
                        nombre = :nombre, 
                        correo = :correo, 
                        telefono = :telefono, 
                        direccion = :direccion
                    WHERE id_usuario = :id_usuario";
            
            $stmt = $this->conexion->prepare($sql);
            $resultado = $stmt->execute([
                'nombre' => $nombre,
                'correo' => $correo,
                'telefono' => $telefono,
                'direccion' => trim($_POST['direccion'] ?? ''),
                'id_usuario' => $idUsuario
            ]);

            if ($resultado) {
                // Actualizar la sesión con los nuevos datos
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_correo'] = $correo;
                
                $_SESSION['mensaje_exito'] = 'Perfil actualizado correctamente';
            } else {
                throw new \Exception('Error al actualizar el perfil');
            }

            // Manejar cambio de contraseña si se proporcionó
            $passwordActual = $_POST['password_actual'] ?? '';
            $passwordNuevo = $_POST['password_nuevo'] ?? '';
            $passwordConfirmar = $_POST['password_confirmar'] ?? '';

            if (!empty($passwordNuevo)) {
                $this->cambiarPassword($idUsuario, $passwordActual, $passwordNuevo, $passwordConfirmar);
            }

        } catch (\Exception $e) {
            $_SESSION['mensaje_error'] = $e->getMessage();
        }

        // Redireccionar para evitar reenvío del formulario
        header('Location: /artesanoDigital/perfil');
        exit;
    }

    /**
     * Cambia la contraseña del usuario
     */
    private function cambiarPassword(int $idUsuario, string $passwordActual, string $passwordNuevo, string $passwordConfirmar): void 
    {
        // Validar que las contraseñas coincidan
        if ($passwordNuevo !== $passwordConfirmar) {
            throw new \Exception('Las contraseñas nuevas no coinciden');
        }

        // Validar longitud mínima
        if (strlen($passwordNuevo) < 6) {
            throw new \Exception('La nueva contraseña debe tener al menos 6 caracteres');
        }

        // Verificar contraseña actual
        $sql = "SELECT contrasena FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['id_usuario' => $idUsuario]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($passwordActual, $usuario['contrasena'])) {
            throw new \Exception('La contraseña actual es incorrecta');
        }

        // Actualizar contraseña
        $sql = "UPDATE usuarios SET contrasena = :contrasena WHERE id_usuario = :id_usuario";
        $stmt = $this->conexion->prepare($sql);
        $resultado = $stmt->execute([
            'contrasena' => password_hash($passwordNuevo, PASSWORD_DEFAULT),
            'id_usuario' => $idUsuario
        ]);

        if (!$resultado) {
            throw new \Exception('Error al cambiar la contraseña');
        }
    }

    /**
     * Obtiene los datos completos del usuario
     */
    private function obtenerDatosCompletos(int $idUsuario): array 
    {
        $sql = "SELECT 
                    id_usuario, 
                    nombre, 
                    correo, 
                    telefono, 
                    direccion, 
                    tipo_usuario, 
                    fecha_registro,
                    activo
                FROM usuarios 
                WHERE id_usuario = :id_usuario";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute(['id_usuario' => $idUsuario]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Carga una vista
     */
    private function cargarVista(string $vista, array $datos = []): void 
    {
        extract($datos);
        include "views/{$vista}.php";
    }
}
