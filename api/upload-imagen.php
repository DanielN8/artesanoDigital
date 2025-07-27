<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    if (!isset($_FILES['imagen'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No se ha enviado ninguna imagen']);
        exit;
    }
    
    $archivo = $_FILES['imagen'];
    
    // Validar archivo
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Error en la subida del archivo']);
        exit;
    }
    
    // Validar tipo de archivo
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($archivo['type'], $tiposPermitidos)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de archivo no permitido. Solo se permiten JPG, PNG, GIF y WebP']);
        exit;
    }
    
    // Validar tamaño (máximo 5MB)
    $tamañoMaximo = 5 * 1024 * 1024; // 5MB
    if ($archivo['size'] > $tamañoMaximo) {
        http_response_code(400);
        echo json_encode(['error' => 'El archivo es demasiado grande. Máximo 5MB']);
        exit;
    }
    
    // Crear directorio si no existe
    $directorioBase = dirname(__FILE__) . '/../uploads/productos/';
    if (!file_exists($directorioBase)) {
        mkdir($directorioBase, 0755, true);
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid('producto_') . '_' . time() . '.' . strtolower($extension);
    $rutaCompleta = $directorioBase . $nombreArchivo;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        // Optimizar imagen (redimensionar si es muy grande)
        optimizarImagen($rutaCompleta, $archivo['type']);
        
        // Generar URL relativa
        $urlImagen = '/uploads/productos/' . $nombreArchivo;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'url' => $urlImagen,
                'filename' => $nombreArchivo
            ],
            'message' => 'Imagen subida exitosamente'
        ]);
        
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar la imagen']);
    }
    
} catch (Exception $e) {
    error_log("Error en upload de imagen: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

function optimizarImagen($rutaArchivo, $tipo) {
    try {
        // Obtener dimensiones de la imagen
        list($ancho, $alto) = getimagesize($rutaArchivo);
        
        // Si la imagen es muy grande, redimensionarla
        $maxAncho = 1200;
        $maxAlto = 1200;
        
        if ($ancho > $maxAncho || $alto > $maxAlto) {
            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($maxAncho / $ancho, $maxAlto / $alto);
            $nuevoAncho = intval($ancho * $ratio);
            $nuevoAlto = intval($alto * $ratio);
            
            // Crear imagen desde archivo
            switch ($tipo) {
                case 'image/jpeg':
                    $imagenOriginal = imagecreatefromjpeg($rutaArchivo);
                    break;
                case 'image/png':
                    $imagenOriginal = imagecreatefrompng($rutaArchivo);
                    break;
                case 'image/gif':
                    $imagenOriginal = imagecreatefromgif($rutaArchivo);
                    break;
                case 'image/webp':
                    $imagenOriginal = imagecreatefromwebp($rutaArchivo);
                    break;
                default:
                    return; // No procesar tipos no soportados
            }
            
            if (!$imagenOriginal) {
                return; // Error al crear imagen
            }
            
            // Crear nueva imagen redimensionada
            $imagenNueva = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            
            // Preservar transparencia para PNG y GIF
            if ($tipo === 'image/png' || $tipo === 'image/gif') {
                imagealphablending($imagenNueva, false);
                imagesavealpha($imagenNueva, true);
                $transparente = imagecolorallocatealpha($imagenNueva, 255, 255, 255, 127);
                imagefill($imagenNueva, 0, 0, $transparente);
            }
            
            // Redimensionar
            imagecopyresampled(
                $imagenNueva, $imagenOriginal,
                0, 0, 0, 0,
                $nuevoAncho, $nuevoAlto, $ancho, $alto
            );
            
            // Guardar imagen optimizada
            switch ($tipo) {
                case 'image/jpeg':
                    imagejpeg($imagenNueva, $rutaArchivo, 85); // Calidad 85%
                    break;
                case 'image/png':
                    imagepng($imagenNueva, $rutaArchivo, 6); // Compresión nivel 6
                    break;
                case 'image/gif':
                    imagegif($imagenNueva, $rutaArchivo);
                    break;
                case 'image/webp':
                    imagewebp($imagenNueva, $rutaArchivo, 85); // Calidad 85%
                    break;
            }
            
            // Liberar memoria
            imagedestroy($imagenOriginal);
            imagedestroy($imagenNueva);
        }
        
    } catch (Exception $e) {
        error_log("Error optimizando imagen: " . $e->getMessage());
        // En caso de error, mantener imagen original
    }
}
?>
