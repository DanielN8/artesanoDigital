<?php
/**
 * Clase GestorAutenticacion - Patrón Singleton para manejo de autenticación
 * Responsabilidad: Gestionar sesiones, login, logout y verificación de permisos
 */

namespace Utils;

use Models\Usuario;
use Exception;

class GestorAutenticacion 
{
    private static ?GestorAutenticacion $instancia = null;
    private ?array $usuarioActual = null;
    private int $tiempoExpiracion;

    /**
     * Constructor privado para Singleton
     */
    private function __construct() 
    {
        // Cargar variables de entorno si existe DotEnv
        if (file_exists(__DIR__ . '/../config/DotEnv.php') && !isset($_ENV['SESSION_LIFETIME'])) {
            require_once __DIR__ . '/../config/DotEnv.php';
        }
        
        // Usar SESSION_LIFETIME de .env (en minutos) o valor predeterminado de 120 minutos
        $this->tiempoExpiracion = ((int)($_ENV['SESSION_LIFETIME'] ?? 120)) * 60;
        $this->iniciarSesionSegura();
    }

    /**
     * Obtiene la instancia única (Singleton)
     * @return GestorAutenticacion
     */
    public static function obtenerInstancia(): GestorAutenticacion 
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Inicia sesión segura con configuración personalizada
     */
    private function iniciarSesionSegura(): void 
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuración segura de sesión
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            
            session_start();
            
            // Regenerar ID de sesión periódicamente
            if (!isset($_SESSION['ultima_regeneracion'])) {
                $this->regenerarIdSesion();
            } elseif (time() - $_SESSION['ultima_regeneracion'] > 300) {
                $this->regenerarIdSesion();
            }
        }
    }

    /**
     * Regenera el ID de sesión para mayor seguridad
     */
    private function regenerarIdSesion(): void 
    {
        session_regenerate_id(true);
        $_SESSION['ultima_regeneracion'] = time();
    }

    /**
     * Autentica usuario con credenciales
     * @param string $correo
     * @param string $contrasena
     * @return bool True si la autenticación es exitosa
     */
    public function autenticar(string $correo, string $contrasena): bool 
    {
        try {
            $modeloUsuario = new Usuario();
            $usuario = $modeloUsuario->obtenerPorCorreo($correo);
            
            if ($usuario && password_verify($contrasena, $usuario['contrasena']) && $usuario['activo']) {
                $this->establecerSesionUsuario($usuario);
                $this->registrarInicioSesion($usuario['id_usuario']);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Establece la sesión del usuario autenticado
     * @param array $usuario Datos del usuario
     */
    private function establecerSesionUsuario(array $usuario): void 
    {
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_correo'] = $usuario['correo'];
        $_SESSION['usuario_tipo'] = $usuario['tipo_usuario']; // Variable de sesión estandarizada
        $_SESSION['tiempo_inicio'] = time();
        $_SESSION['csrf_token'] = $this->generarTokenSeguro();
        
        $this->usuarioActual = $usuario;
    }

    /**
     * Registra el inicio de sesión en base de datos
     * @param int $idUsuario
     */
    private function registrarInicioSesion(int $idUsuario): void 
    {
        // Aquí se podría registrar en tabla de auditoría
        // Por ahora solo log
        error_log("Usuario {$idUsuario} inició sesión desde IP: " . $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Verifica si el usuario está autenticado
     * @return bool
     */
    public function estaAutenticado(): bool 
    {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['tiempo_inicio'])) {
            return false;
        }

        // Verificar expiración de sesión
        if (time() - $_SESSION['tiempo_inicio'] > $this->tiempoExpiracion) {
            $this->cerrarSesion();
            return false;
        }

        // Actualizar tiempo de actividad
        $_SESSION['tiempo_inicio'] = time();
        return true;
    }

    /**
     * Obtiene el usuario actual
     * @return array|null
     */
    public function obtenerUsuarioActual(): ?array 
    {
        if ($this->estaAutenticado()) {
            return [
                'id_usuario' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'correo' => $_SESSION['usuario_correo'],
                'tipo_usuario' => $_SESSION['usuario_tipo']
            ];
        }
        return null;
    }

    /**
     * Verifica si el usuario es artesano
     * @return bool
     */
    public function esArtesano(): bool 
    {
        return $this->estaAutenticado() && $_SESSION['usuario_tipo'] === 'artesano';
    }

    /**
     * Verifica si el usuario es cliente
     * @return bool
     */
    public function esCliente(): bool 
    {
        return $this->estaAutenticado() && $_SESSION['usuario_tipo'] === 'cliente';
    }

    /**
     * Genera un token seguro compatible con versiones antiguas de PHP
     * @param int $length Longitud del token
     * @return string
     */
    private function generarTokenSeguro(int $length = 32): string 
    {
        // Token usando MD5 y microtime (menos seguro pero compatible)
        $token = md5(uniqid(microtime(), true));
        
        // Asegurarnos de tener la longitud correcta
        if (strlen($token) < $length * 2) {
            $token .= md5($token);
        }
        
        return substr($token, 0, $length * 2);
    }

    /**
     * Verifica el token CSRF
     * @param string $token Token CSRF a verificar
     * @return bool True si el token es válido, false en caso contrario
     */
    public function verificarTokenCSRF(string $token): bool 
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Genera un nuevo token CSRF y lo almacena en la sesión
     * @return string Token CSRF generado
     */
    public function generarTokenCSRF(): string 
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Cierra la sesión del usuario
     */
    public function cerrarSesion(): void 
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $this->usuarioActual = null;
    }

    /**
     * Previene clonación
     */
    private function __clone() {}

    /**
     * Previene deserialización
     */
    public function __wakeup() 
    {
        throw new Exception("No se puede deserializar un Singleton");
    }
}
