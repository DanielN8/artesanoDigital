<?php
/**
 * Patrón Decorator para extensiones de funcionalidad en notificaciones
 * Responsabilidad: Añadir funcionalidades adicionales a las notificaciones
 */

namespace Patrones;

/**
 * Interfaz base para notificaciones
 */
interface ComponenteNotificacion 
{
    public function enviar(string $destinatario, string $mensaje): bool;
    public function obtenerTipo(): string;
}

/**
 * Notificación básica (componente concreto)
 */
class NotificacionBasica implements ComponenteNotificacion 
{
    private string $tipo;

    public function __construct(string $tipo = 'basica') 
    {
        $this->tipo = $tipo;
    }

    public function enviar(string $destinatario, string $mensaje): bool 
    {
        // Implementación básica - guardar en base de datos
        try {
            $db = \Config\Database::obtenerInstancia();
            $conexion = $db->obtenerConexion();
            
            $sql = "INSERT INTO notificaciones (id_usuario, tipo, mensaje, fecha_creacion) 
                    VALUES (:id_usuario, :tipo, :mensaje, NOW())";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'id_usuario' => $destinatario,
                'tipo' => $this->tipo,
                'mensaje' => $mensaje
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Error al enviar notificación básica: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTipo(): string 
    {
        return $this->tipo;
    }
}

/**
 * Decorador base abstracto
 */
abstract class DecoradorNotificacion implements ComponenteNotificacion 
{
    protected ComponenteNotificacion $notificacion;

    public function __construct(ComponenteNotificacion $notificacion) 
    {
        $this->notificacion = $notificacion;
    }

    public function enviar(string $destinatario, string $mensaje): bool 
    {
        return $this->notificacion->enviar($destinatario, $mensaje);
    }

    public function obtenerTipo(): string 
    {
        return $this->notificacion->obtenerTipo();
    }
}

/**
 * Decorador para envío por correo electrónico
 */
class DecoradorCorreo extends DecoradorNotificacion 
{
    public function enviar(string $destinatario, string $mensaje): bool 
    {
        // Primero ejecutar la funcionalidad base
        $resultadoBase = parent::enviar($destinatario, $mensaje);
        
        // Luego añadir funcionalidad de correo
        return $resultadoBase && $this->enviarCorreo($destinatario, $mensaje);
    }

    private function enviarCorreo(string $idUsuario, string $mensaje): bool 
    {
        try {
            // Obtener datos del usuario para el correo
            $modeloUsuario = new \Models\Usuario();
            $usuario = $modeloUsuario->obtenerPorId($idUsuario);
            
            if (!$usuario) {
                return false;
            }

            $correoDestino = $usuario['correo'];
            $nombreDestino = $usuario['nombre'];
            
            // Configurar headers del correo
            $asunto = "Notificación - " . $_ENV['APP_NAME'];
            $headers = [
                'From' => $_ENV['MAIL_FROM_ADDRESS'],
                'Reply-To' => $_ENV['MAIL_FROM_ADDRESS'],
                'Content-Type' => 'text/html; charset=UTF-8',
                'MIME-Version' => '1.0'
            ];
            
            $cuerpoCorreo = $this->construirCuerpoCorreo($nombreDestino, $mensaje);
            
            // Enviar correo (en producción usar PHPMailer o similar)
            return mail($correoDestino, $asunto, $cuerpoCorreo, implode("\r\n", $headers));
            
        } catch (\Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
            return false;
        }
    }

    private function construirCuerpoCorreo(string $nombre, string $mensaje): string 
    {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #faf8f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
                .header { background-color: #f5e6d3; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; }
                .footer { background-color: #f5e6d3; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='color: #8b4513; margin: 0;'>" . $_ENV['APP_NAME'] . "</h2>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>{$mensaje}</p>
                    <p>Puedes acceder a tu cuenta para ver más detalles.</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responder.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    public function obtenerTipo(): string 
    {
        return parent::obtenerTipo() . '_con_correo';
    }
}

/**
 * Decorador para logging avanzado
 */
class DecoradorLog extends DecoradorNotificacion 
{
    public function enviar(string $destinatario, string $mensaje): bool 
    {
        $resultado = parent::enviar($destinatario, $mensaje);
        
        // Añadir logging detallado
        $this->registrarLog($destinatario, $mensaje, $resultado);
        
        return $resultado;
    }

    private function registrarLog(string $destinatario, string $mensaje, bool $exitoso): void 
    {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $estado = $exitoso ? 'EXITOSO' : 'FALLIDO';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
            
            $logEntry = "[{$timestamp}] NOTIFICACION {$estado} - Usuario: {$destinatario}, IP: {$ip}, Mensaje: " . substr($mensaje, 0, 100) . "\n";
            
            $logDir = __DIR__ . '/../logs';
            
            // Crear el directorio logs si no existe
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            file_put_contents($logDir . '/notificaciones.log', $logEntry, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            error_log("Error al registrar log de notificación: " . $e->getMessage());
        }
    }

    public function obtenerTipo(): string 
    {
        return parent::obtenerTipo() . '_con_log';
    }
}

/**
 * Decorador para añadir marca de tiempo personalizada
 */
class DecoradorMarcaTiempo extends DecoradorNotificacion 
{
    public function enviar(string $destinatario, string $mensaje): bool 
    {
        $mensajeConMarca = $this->añadirMarcaTiempo($mensaje);
        
        // Usar el mensaje modificado
        return $this->notificacion->enviar($destinatario, $mensajeConMarca);
    }

    private function añadirMarcaTiempo(string $mensaje): string 
    {
        $timestamp = date('d/m/Y H:i:s');
        return "[{$timestamp}] {$mensaje}";
    }

    public function obtenerTipo(): string 
    {
        return parent::obtenerTipo() . '_con_timestamp';
    }
}

/**
 * Factory para crear notificaciones decoradas
 */
class FactoriaNotificaciones 
{
    /**
     * Crea una notificación con decoradores específicos
     * @param string $tipo Tipo base de notificación
     * @param array $decoradores Lista de decoradores a aplicar
     * @return ComponenteNotificacion
     */
    public static function crear(string $tipo, array $decoradores = []): ComponenteNotificacion 
    {
        $notificacion = new NotificacionBasica($tipo);
        
        foreach ($decoradores as $decorador) {
            switch ($decorador) {
                case 'correo':
                    $notificacion = new DecoradorCorreo($notificacion);
                    break;
                case 'log':
                    $notificacion = new DecoradorLog($notificacion);
                    break;
                case 'timestamp':
                    $notificacion = new DecoradorMarcaTiempo($notificacion);
                    break;
            }
        }
        
        return $notificacion;
    }
}
