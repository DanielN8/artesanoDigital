<?php
/**
 * Controlador para la gestión de tiendas de artesanos
 * Se encarga de procesar las solicitudes relacionadas con la creación, edición y eliminación de tiendas
 */

require_once dirname(__FILE__) . '/../utils/GestorAutenticacion.php';
require_once dirname(__FILE__) . '/../models/Tienda.php';
require_once dirname(__FILE__) . '/../utils/GestorUploads.php';

use Utils\GestorAutenticacion;
use Models\Tienda;
use Utils\GestorUploads;

// Verificar que el usuario esté autenticado y sea artesano
$gestorAuth = GestorAutenticacion::obtenerInstancia();
if (!$gestorAuth->estaAutenticado() || $gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'artesano') {
    // Responder con JSON para solicitudes AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['exitoso' => false, 'mensaje' => 'No autorizado']);
        exit;
    }
    
    // Redirigir para solicitudes normales
    header('Location: /artesanoDigital/login');
    exit;
}

// Obtener datos del usuario actual
$usuario = $gestorAuth->obtenerUsuarioActual();
$idUsuario = $usuario['id_usuario'];

// Procesar la acción solicitada
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'crear_tienda':
        crearTienda($idUsuario);
        break;
        
    case 'actualizar_tienda':
        actualizarTienda($idUsuario);
        break;
        
    default:
        // Redirigir si no hay acción válida
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['exitoso' => false, 'mensaje' => 'Acción no válida']);
            exit;
        }
        
        header('Location: /artesanoDigital/artesano/tienda');
        exit;
}

/**
 * Crea una nueva tienda para el artesano
 * @param int $idUsuario ID del usuario artesano
 */
function crearTienda(int $idUsuario): void
{
    // Validar datos recibidos
    if (empty($_POST['nombre_tienda'])) {
        responderError('El nombre de la tienda es obligatorio');
        return;
    }
    
    $nombreTienda = trim($_POST['nombre_tienda']);
    $descripcion = trim($_POST['descripcion_tienda'] ?? '');
    $rutaImagen = null;
    
    // Procesar imagen si se ha subido
    if (isset($_FILES['imagen_logo']) && $_FILES['imagen_logo']['error'] === UPLOAD_ERR_OK) {
        $gestorUploads = new GestorUploads();
        $resultado = $gestorUploads->procesarImagen(
            $_FILES['imagen_logo'],
            'public/tiendas/',  // Carpeta de destino
            ['jpg', 'jpeg', 'png', 'gif'],  // Tipos permitidos
            2 * 1024 * 1024  // 2MB máximo
        );
        
        if ($resultado['exitoso']) {
            $rutaImagen = $resultado['ruta'];
        } else {
            responderError($resultado['mensaje']);
            return;
        }
    }
    
    // Preparar datos para crear la tienda
    $datosTienda = [
        'id_usuario' => $idUsuario,
        'nombre_tienda' => $nombreTienda,
        'descripcion' => $descripcion,
        'imagen_logo' => $rutaImagen
    ];
    
    // Crear la tienda en la base de datos
    $modeloTienda = new Tienda();
    $resultado = $modeloTienda->crear($datosTienda);
    
    if ($resultado['exitoso']) {
        // Éxito
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            responderExito('Tienda creada con éxito', ['id_tienda' => $resultado['id']]);
        } else {
            // Guardar mensaje en sesión para mostrarlo después de la redirección
            $_SESSION['mensaje'] = 'Tienda creada con éxito';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: /artesanoDigital/artesano/dashboard');
            exit;
        }
    } else {
        // Error
        responderError($resultado['mensaje']);
    }
}

/**
 * Actualiza una tienda existente
 * @param int $idUsuario ID del usuario artesano
 */
function actualizarTienda(int $idUsuario): void
{
    // Validar que se haya proporcionado un ID de tienda
    if (empty($_POST['id_tienda'])) {
        responderError('ID de tienda no válido');
        return;
    }
    
    $idTienda = (int)$_POST['id_tienda'];
    
    // Verificar que la tienda pertenezca al usuario
    $modeloTienda = new Tienda();
    $tienda = $modeloTienda->obtenerPorUsuario($idUsuario);
    
    if (!$tienda || $tienda['id_tienda'] != $idTienda) {
        responderError('No tienes permiso para modificar esta tienda');
        return;
    }
    
    // Preparar datos a actualizar
    $datosActualizar = [];
    
    if (isset($_POST['nombre_tienda']) && !empty($_POST['nombre_tienda'])) {
        $datosActualizar['nombre_tienda'] = trim($_POST['nombre_tienda']);
    }
    
    if (isset($_POST['descripcion_tienda'])) {
        $datosActualizar['descripcion'] = trim($_POST['descripcion_tienda']);
    }
    
    // Procesar imagen si se ha subido una nueva
    if (isset($_FILES['imagen_logo']) && $_FILES['imagen_logo']['error'] === UPLOAD_ERR_OK) {
        $gestorUploads = new GestorUploads();
        $resultado = $gestorUploads->procesarImagen(
            $_FILES['imagen_logo'],
            'uploads/tiendas/',
            ['jpg', 'jpeg', 'png', 'gif'],
            2 * 1024 * 1024
        );
        
        if ($resultado['exitoso']) {
            $datosActualizar['imagen_logo'] = $resultado['ruta'];
            
            // Eliminar la imagen anterior si existe
            if (!empty($tienda['imagen_logo'])) {
                @unlink(dirname(__FILE__) . '/../' . $tienda['imagen_logo']);
            }
        } else {
            responderError($resultado['mensaje']);
            return;
        }
    }
    
    // Si no hay datos para actualizar
    if (empty($datosActualizar)) {
        responderError('No hay cambios para guardar');
        return;
    }
    
    // Actualizar la tienda
    $resultado = $modeloTienda->actualizar($idTienda, $datosActualizar);
    
    if ($resultado['exitoso']) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            responderExito('Tienda actualizada correctamente');
        } else {
            $_SESSION['mensaje'] = 'Tienda actualizada correctamente';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: /artesanoDigital/artesano/tienda');
            exit;
        }
    } else {
        responderError($resultado['mensaje']);
    }
}

/**
 * Responde con un mensaje de error
 * @param string $mensaje Mensaje de error
 */
function responderError(string $mensaje): void
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['exitoso' => false, 'mensaje' => $mensaje]);
        exit;
    } else {
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: /artesanoDigital/artesano/tienda');
        exit;
    }
}

/**
 * Responde con un mensaje de éxito
 * @param string $mensaje Mensaje de éxito
 * @param array $datos Datos adicionales a enviar (opcional)
 */
function responderExito(string $mensaje, array $datos = []): void
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['exitoso' => true, 'mensaje' => $mensaje], $datos));
        exit;
    } else {
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: /artesanoDigital/artesano/dashboard');
        exit;
    }
}
