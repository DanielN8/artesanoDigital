<?php
// Patrón Singleton para la gestión de la conexión a base de datos

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

    public static function obtenerInstancia(): Database 
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

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

    public function obtenerConexion(): PDO 
    {
        if ($this->conexion === null) {
            $this->conectar();
        }
        return $this->conexion;
    }

    private function __clone() {}

    public function __wakeup() 
    {
        throw new Exception("No se puede deserializar un Singleton");
    }

    public function cerrarConexion(): void 
    {
        $this->conexion = null;
    }

    public function iniciarTransaccion(): bool 
    {
        return $this->conexion->beginTransaction();
    }

    public function confirmarTransaccion(): bool 
    {
        return $this->conexion->commit();
    }

    public function revertirTransaccion(): bool 
    {
        return $this->conexion->rollBack();
    }
}
