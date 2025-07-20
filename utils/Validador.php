<?php
/**
 * Clase Validador - Utilidad para validación y sanitización de datos
 * Responsabilidad: Validar y sanitizar entradas del usuario
 */

namespace Utils;

class Validador 
{
    /**
     * Sanitiza una cadena de texto
     * @param string $texto
     * @return string
     */
    public static function sanitizarTexto(string $texto): string 
    {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza un correo electrónico
     * @param string $correo
     * @return string
     */
    public static function sanitizarCorreo(string $correo): string 
    {
        return filter_var(trim($correo), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Valida formato de correo electrónico
     * @param string $correo
     * @return bool
     */
    public static function validarCorreo(string $correo): bool 
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida seguridad de contraseña
     * @param string $contrasena
     * @param int $longitudMinima
     * @return bool
     */
    public static function validarContrasena(string $contrasena, int $longitudMinima = 8): bool 
    {
        // Verificar longitud mínima
        if (strlen($contrasena) < $longitudMinima) {
            return false;
        }
        
        // Verificar que tenga al menos una letra minúscula, una mayúscula y un dígito
        $tieneMinus = preg_match('/[a-z]/', $contrasena);
        $tieneMayus = preg_match('/[A-Z]/', $contrasena);
        $tieneDigito = preg_match('/\d/', $contrasena);
        $tieneEspecial = preg_match('/[^a-zA-Z\d]/', $contrasena);
        
        // Debe cumplir al menos 3 de las 4 condiciones
        $condicionesCumplidas = ($tieneMinus ? 1 : 0) + 
                               ($tieneMayus ? 1 : 0) + 
                               ($tieneDigito ? 1 : 0) + 
                               ($tieneEspecial ? 1 : 0);
        
        return $condicionesCumplidas >= 3;
    }

    /**
     * Valida número de teléfono panameño
     * @param string $telefono
     * @return bool
     */
    public static function validarTelefono(string $telefono): bool 
    {
        // Formato más flexible: +507 6000-0000, 6000-0000, etc.
        $patron = '/^(\+507)?[\s\-]?[6-9]\d{3}[\s\-]?\d{4}$/';
        return preg_match($patron, $telefono);
    }

    /**
     * Valida precio (número decimal positivo)
     * @param mixed $precio
     * @return bool
     */
    public static function validarPrecio($precio): bool 
    {
        return is_numeric($precio) && (float)$precio > 0;
    }

    /**
     * Valida stock (número entero no negativo)
     * @param mixed $stock
     * @return bool
     */
    public static function validarStock($stock): bool 
    {
        return is_numeric($stock) && (int)$stock >= 0;
    }

    /**
     * Valida longitud de texto
     * @param string $texto
     * @param int $minimo
     * @param int $maximo
     * @return bool
     */
    public static function validarLongitud(string $texto, int $minimo = 1, int $maximo = 255): bool 
    {
        $longitud = strlen(trim($texto));
        return $longitud >= $minimo && $longitud <= $maximo;
    }

    /**
     * Valida archivo de imagen
     * @param array $archivo
     * @return array [valido => bool, error => string]
     */
    public static function validarImagen(array $archivo): array 
    {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $tamanoMaximo = (int)$_ENV['MAX_FILE_SIZE']; // 5MB por defecto
        
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'error' => 'Error al subir el archivo'];
        }
        
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            return ['valido' => false, 'error' => 'Formato de imagen no permitido'];
        }
        
        if ($archivo['size'] > $tamanoMaximo) {
            return ['valido' => false, 'error' => 'El archivo es demasiado grande'];
        }
        
        // Verificar que realmente sea una imagen
        $infoImagen = getimagesize($archivo['tmp_name']);
        if ($infoImagen === false) {
            return ['valido' => false, 'error' => 'El archivo no es una imagen válida'];
        }
        
        return ['valido' => true, 'error' => ''];
    }

    /**
     * Genera nombre único para archivo
     * @param string $nombreOriginal
     * @return string
     */
    public static function generarNombreUnicoArchivo(string $nombreOriginal): string 
    {
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Previene ataques XSS básicos
     * @param string $entrada
     * @return string
     */
    public static function prevenirXSS(string $entrada): string 
    {
        return htmlspecialchars($entrada, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Valida que no contenga SQL básico malicioso
     * @param string $entrada
     * @return bool
     */
    public static function validarNoSQL(string $entrada): bool 
    {
        $patronesSQL = [
            '/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b)/i',
            '/(\bor\b|\band\b)\s*\d+\s*=\s*\d+/i',
            '/\'\s*(or|and)\s*\'/i'
        ];
        
        foreach ($patronesSQL as $patron) {
            if (preg_match($patron, $entrada)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida token de seguridad básico
     * @param string $token
     * @return bool
     */
    public static function validarToken(string $token): bool 
    {
        return preg_match('/^[a-f0-9]{64}$/', $token);
    }

    /**
     * Sanitiza URL
     * @param string $url
     * @return string
     */
    public static function sanitizarURL(string $url): string 
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Valida dominio permitido para URLs
     * @param string $url
     * @param array $dominiosPermitidos
     * @return bool
     */
    public static function validarDominioPermitido(string $url, array $dominiosPermitidos): bool 
    {
        $dominio = parse_url($url, PHP_URL_HOST);
        return in_array($dominio, $dominiosPermitidos);
    }

    /**
     * Valida datos de registro de usuario
     * @param array $datos
     * @return array [valido => bool, errores => array]
     */
    public static function validarRegistroUsuario(array $datos): array 
    {
        $errores = [];
        
        if (!self::validarLongitud($datos['nombre'] ?? '', 2, 100)) {
            $errores[] = 'El nombre debe tener entre 2 y 100 caracteres';
        }
        
        if (!self::validarCorreo($datos['correo'] ?? '')) {
            $errores[] = 'El correo electrónico no es válido';
        }
        
        if (!self::validarContrasena($datos['contrasena'] ?? '')) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres y cumplir al menos 3 de estas condiciones: incluir mayúsculas, minúsculas, números y caracteres especiales';
        }
        
        if (!empty($datos['telefono']) && !self::validarTelefono($datos['telefono'])) {
            $errores[] = 'El formato del teléfono no es válido (ej: 6123-4567)';
        }
        
        if (!in_array($datos['tipo_usuario'] ?? '', ['cliente', 'artesano'])) {
            $errores[] = 'Tipo de usuario no válido';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Valida datos de producto
     * @param array $datos
     * @return array [valido => bool, errores => array]
     */
    public static function validarProducto(array $datos): array 
    {
        $errores = [];
        
        if (!self::validarLongitud($datos['nombre'] ?? '', 2, 100)) {
            $errores[] = 'El nombre del producto debe tener entre 2 y 100 caracteres';
        }
        
        if (!self::validarLongitud($datos['descripcion'] ?? '', 10, 1000)) {
            $errores[] = 'La descripción debe tener entre 10 y 1000 caracteres';
        }
        
        if (!self::validarPrecio($datos['precio'] ?? 0)) {
            $errores[] = 'El precio debe ser un número positivo';
        }
        
        if (!self::validarStock($datos['stock'] ?? 0)) {
            $errores[] = 'El stock debe ser un número entero no negativo';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}
