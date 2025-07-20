<?php
/**
 * Clase Database - Patrón Singleton para gestión de conexión a base de datos
 * Responsabilidad: Proporcionar una única instancia de conexión PDO segura
 */

namespace Config;

use PDO;
use PDOException;
use Exception;

class Database 
{
    private static ?Database $instancia = null;
    private ?PDO $conexion = null;
    private string $host;
    private string $baseDatos;
    private string $usuario;
    private string $contrasena;
    private string $charset;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct() 
    {
        // Cargar variables de entorno si existe DotEnv
        if (file_exists(__DIR__ . '/DotEnv.php') && !isset($_ENV['DB_HOST'])) {
            require_once __DIR__ . '/DotEnv.php';
        }
        
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->baseDatos = $_ENV['DB_DATABASE'] ?? 'artesano_digital';
        $this->usuario = $_ENV['DB_USERNAME'] ?? 'root';
        $this->contrasena = $_ENV['DB_PASSWORD'] ?? '';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
        
        $this->conectar();
    }

    /**
     * Obtiene la instancia única de Database (Singleton)
     * @return Database Instancia única
     */
    public static function obtenerInstancia(): Database 
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Establece la conexión PDO con configuración segura
     * @throws Exception Si falla la conexión
     */
    private function conectar(): void 
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->baseDatos};charset={$this->charset}";
            
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];

            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena, $opciones);
            
        } catch (PDOException $e) {
            throw new Exception("Error de conexión a base de datos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene la conexión PDO
     * @return PDO Conexión activa
     */
    public function obtenerConexion(): PDO 
    {
        if ($this->conexion === null) {
            $this->conectar();
        }
        return $this->conexion;
    }

    /**
     * Previene la clonación del objeto Singleton
     */
    private function __clone() {}

    /**
     * Previene la deserialización del objeto Singleton
     */
    public function __wakeup() 
    {
        throw new Exception("No se puede deserializar un Singleton");
    }

    /**
     * Cierra la conexión
     */
    public function cerrarConexion(): void 
    {
        $this->conexion = null;
    }

    /**
     * Inicia una transacción
     * @return bool True si se inició correctamente
     */
    public function iniciarTransaccion(): bool 
    {
        return $this->conexion->beginTransaction();
    }

    /**
     * Confirma una transacción
     * @return bool True si se confirmó correctamente
     */
    public function confirmarTransaccion(): bool 
    {
        return $this->conexion->commit();
    }

    /**
     * Revierte una transacción
     * @return bool True si se revirtió correctamente
     */
    public function revertirTransaccion(): bool 
    {
        return $this->conexion->rollBack();
    }
}
