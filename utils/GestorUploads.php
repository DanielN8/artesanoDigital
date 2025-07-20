<?php
/**
 * Gestor de Uploads - Manejo seguro de archivos subidos
 * Responsabilidad: Validar, sanitizar y guardar archivos de forma segura
 */

namespace Utils;

use Exception;

class GestorUploads 
{
    private string $directorioBase;
    private array $tiposPermitidos;
    private int $tamanoMaximo;

    public function __construct() 
    {
        // Cargar variables de entorno si existe DotEnv
        if (file_exists(__DIR__ . '/../config/DotEnv.php') && !isset($_ENV['UPLOAD_MAX_SIZE'])) {
            require_once __DIR__ . '/../config/DotEnv.php';
        }
        
        $this->directorioBase = $_ENV['UPLOAD_PATH'] ?? __DIR__ . '/../uploads/';
        $this->tiposPermitidos = explode(',', $_ENV['UPLOAD_ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,gif,webp');
        $this->tamanoMaximo = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880); // 5MB default
        
        $this->crearDirectorioSiNoExiste();
    }

    /**
     * Procesa upload de imagen de producto
     * @param array $archivo
     * @param string $subdirectorio
     * @return array [exitoso => bool, mensaje => string, ruta => string|null]
     */
    public function subirImagen(array $archivo, string $subdirectorio = 'productos'): array 
    {
        try {
            // Validar archivo
            $validacion = $this->validarArchivo($archivo);
            if (!$validacion['valido']) {
                return [
                    'exitoso' => false,
                    'mensaje' => $validacion['error'],
                    'ruta' => null
                ];
            }

            // Crear subdirectorio si no existe
            $directorioDestino = $this->directorioBase . $subdirectorio . '/';
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0755, true);
            }

            // Generar nombre único
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $nombreArchivo = $this->generarNombreUnico() . '.' . $extension;
            $rutaCompleta = $directorioDestino . $nombreArchivo;

            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                // Aplicar permisos seguros
                chmod($rutaCompleta, 0644);
                
                return [
                    'exitoso' => true,
                    'mensaje' => 'Archivo subido exitosamente',
                    'ruta' => $subdirectorio . '/' . $nombreArchivo
                ];
            } else {
                return [
                    'exitoso' => false,
                    'mensaje' => 'Error al mover el archivo',
                    'ruta' => null
                ];
            }

        } catch (Exception $e) {
            error_log("Error en upload: " . $e->getMessage());
            return [
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor',
                'ruta' => null
            ];
        }
    }

    /**
     * Elimina un archivo de forma segura
     * @param string $rutaArchivo
     * @return bool
     */
    public function eliminarArchivo(string $rutaArchivo): bool 
    {
        try {
            $rutaCompleta = $this->directorioBase . $rutaArchivo;
            
            // Verificar que el archivo esté dentro del directorio permitido
            if (!$this->estaEnDirectorioPermitido($rutaCompleta)) {
                return false;
            }

            if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
                return unlink($rutaCompleta);
            }

            return false;

        } catch (Exception $e) {
            error_log("Error al eliminar archivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida archivo subido
     * @param array $archivo
     * @return array [valido => bool, error => string]
     */
    private function validarArchivo(array $archivo): array 
    {
        // Verificar errores de upload
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'error' => $this->obtenerMensajeError($archivo['error'])];
        }

        // Verificar tamaño
        if ($archivo['size'] > $this->tamanoMaximo) {
            $tamanoMB = round($this->tamanoMaximo / 1024 / 1024, 2);
            return ['valido' => false, 'error' => "El archivo excede el tamaño máximo de {$tamanoMB}MB"];
        }

        // Verificar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->tiposPermitidos)) {
            $tiposTexto = implode(', ', $this->tiposPermitidos);
            return ['valido' => false, 'error' => "Tipo de archivo no permitido. Permitidos: {$tiposTexto}"];
        }

        // Verificar que sea realmente una imagen
        $infoImagen = getimagesize($archivo['tmp_name']);
        if ($infoImagen === false) {
            return ['valido' => false, 'error' => 'El archivo no es una imagen válida'];
        }

        // Verificar MIME type
        $tiposMIMEPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($infoImagen['mime'], $tiposMIMEPermitidos)) {
            return ['valido' => false, 'error' => 'Tipo MIME no permitido'];
        }

        return ['valido' => true, 'error' => ''];
    }

    /**
     * Genera nombre único para archivo
     * @return string
     */
    private function generarNombreUnico(): string 
    {
        return uniqid('img_', true) . '_' . time();
    }

    /**
     * Crea directorio base si no existe
     */
    private function crearDirectorioSiNoExiste(): void 
    {
        if (!is_dir($this->directorioBase)) {
            mkdir($this->directorioBase, 0755, true);
        }
    }

    /**
     * Verifica que el archivo esté en directorio permitido
     * @param string $ruta
     * @return bool
     */
    private function estaEnDirectorioPermitido(string $ruta): bool 
    {
        $rutaReal = realpath($ruta);
        $directorioReal = realpath($this->directorioBase);
        
        return $rutaReal !== false && $directorioReal !== false && 
               strpos($rutaReal, $directorioReal) === 0;
    }

    /**
     * Obtiene mensaje de error legible
     * @param int $codigoError
     * @return string
     */
    private function obtenerMensajeError(int $codigoError): string 
    {
        switch ($codigoError) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo es demasiado grande';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se seleccionó ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta directorio temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo';
            case UPLOAD_ERR_EXTENSION:
                return 'Extensión de archivo bloqueada';
            default:
                return 'Error desconocido';
        }
    }
}
