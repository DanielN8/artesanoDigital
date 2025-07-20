<?php
/**
 * Cargador de variables de entorno
 * Este archivo carga las variables de entorno del archivo .env
 */

class DotEnv
{
    protected $path;

    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('%s no existe', $path));
        }
        $this->path = $path;
    }

    public function load()
    {
        if (!is_readable($this->path)) {
            throw new \RuntimeException(sprintf('%s no es legible', $this->path));
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Separar clave y valor
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Quitar comillas si existen
            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }

            // Resolver variables embebidas como ${APP_NAME}
            $value = preg_replace_callback('/\${([A-Z0-9_]+)}/', function ($matches) {
                return $_ENV[$matches[1]] ?? '';
            }, $value);

            // Establecer variable de entorno
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Intentar cargar .env desde la raíz del proyecto
try {
    (new DotEnv(__DIR__ . '/../.env'))->load();
} catch (\Exception $e) {
    error_log('Error cargando .env: ' . $e->getMessage());
    
    // Variables por defecto si no existe .env
    $_ENV['DB_HOST'] = 'localhost';
    $_ENV['DB_DATABASE'] = 'artesano_digital';
    $_ENV['DB_USERNAME'] = 'root';
    $_ENV['DB_PASSWORD'] = '';
    $_ENV['DB_CHARSET'] = 'utf8mb4';
}
