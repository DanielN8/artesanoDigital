<?php
/**
 * Vista de creación/edición de tienda de artesano
 * Permite a los artesanos configurar su tienda
 */

// Requerir controlador de autenticación para verificar usuario
require_once dirname(__FILE__) . '/../../utils/GestorAutenticacion.php';
require_once dirname(__FILE__) . '/../../config/Database.php';
require_once dirname(__FILE__) . '/../../models/Tienda.php';

use Utils\GestorAutenticacion;
use Config\Database;
use Models\Tienda;

$gestorAuth = GestorAutenticacion::obtenerInstancia();

// Verificar que el usuario esté autenticado y sea artesano
if (!$gestorAuth->estaAutenticado() || 
    $gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'artesano') {
    header('Location: /artesanoDigital/login');
    exit;
}

$usuario = $gestorAuth->obtenerUsuarioActual();
$idUsuario = $usuario['id_usuario'];
$mensaje = '';
$tipoMensaje = '';
$tienda = null;

// Verificar si ya tiene una tienda
$modeloTienda = new Tienda();
$tiendaExistente = $modeloTienda->obtenerPorUsuario($idUsuario);

// Procesar formulario de creación/actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion = Database::obtenerInstancia()->obtenerConexion();
        
        // Validar datos obligatorios
        if (empty($_POST['nombre_tienda'])) {
            throw new Exception("El nombre de la tienda es obligatorio");
        }
        
        $nombreTienda = trim($_POST['nombre_tienda']);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $imagenLogo = null;
        
        // Procesar imagen del logo si se ha subido
        if (isset($_FILES['imagen_logo']) && $_FILES['imagen_logo']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['imagen_logo'];
            $nombreTemporal = $archivo['tmp_name'];
            $nombreOriginal = $archivo['name'];
            
            // Validar que sea una imagen
            $tipoArchivo = $archivo['type'];
            if (!in_array($tipoArchivo, ['image/jpeg', 'image/png', 'image/gif'])) {
                throw new Exception("El archivo debe ser una imagen (JPEG, PNG o GIF)");
            }
            
            // Generar nombre único para la imagen
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $imagenLogo = 'logos/' . uniqid('tienda_') . '.' . $extension;
            
            // Directorio de carga
            $directorioDestino = dirname(__FILE__) . '/../../uploads/logos/';
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0755, true);
            }
            
            // Mover archivo
            if (!move_uploaded_file($nombreTemporal, dirname(__FILE__) . '/../../uploads/' . $imagenLogo)) {
                throw new Exception("Error al cargar la imagen del logo");
            }
        }
        
        // Si ya existe la tienda, actualizar
        if ($tiendaExistente) {
            $datosActualizar = [
                'nombre_tienda' => $nombreTienda,
                'descripcion' => $descripcion
            ];
            
            // Solo actualizar la imagen si se subió una nueva
            if ($imagenLogo) {
                $datosActualizar['imagen_logo'] = $imagenLogo;
                
                // Eliminar logo anterior si existe
                if (!empty($tiendaExistente['imagen_logo'])) {
                    $rutaLogoAnterior = dirname(__FILE__) . '/../../uploads/' . $tiendaExistente['imagen_logo'];
                    if (file_exists($rutaLogoAnterior)) {
                        unlink($rutaLogoAnterior);
                    }
                }
            }
            
            $resultado = $modeloTienda->actualizar($tiendaExistente['id_tienda'], $datosActualizar);
            if ($resultado['exitoso']) {
                $mensaje = "Tienda actualizada correctamente";
                $tipoMensaje = "success";
                
                // Actualizar los datos de la tienda después de la modificación
                $tiendaExistente = $modeloTienda->obtenerPorUsuario($idUsuario);
            } else {
                throw new Exception($resultado['mensaje']);
            }
        } else {
            // Crear nueva tienda
            $datosTienda = [
                'id_usuario' => $idUsuario,
                'nombre_tienda' => $nombreTienda,
                'descripcion' => $descripcion,
                'imagen_logo' => $imagenLogo
            ];
            
            $resultado = $modeloTienda->crear($datosTienda);
            if ($resultado['exitoso']) {
                $mensaje = "Tienda creada correctamente";
                $tipoMensaje = "success";
                
                // Obtener los datos de la nueva tienda
                $tiendaExistente = $modeloTienda->obtenerPorUsuario($idUsuario);
            } else {
                throw new Exception($resultado['mensaje']);
            }
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = "error";
    }
}

// Preparar datos para la vista
$titulo = $tiendaExistente ? 'Editar mi Tienda' : 'Crear mi Tienda';
$tienda = $tiendaExistente;

// Estilos y scripts adicionales
$estilosAdicionales = [
    '/artesanoDigital/assets/css/tienda.css'
];

$scriptsAdicionales = [
    '/artesanoDigital/assets/js/tienda-artesano.js'
];

// Iniciar captura de contenido para el layout base
ob_start();
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><?= $titulo ?></h1>
        <p>Configura tu tienda para vender tus productos artesanales</p>
    </div>
    
    <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?= $tipoMensaje === 'success' ? 'success' : 'danger' ?>">
        <?= $mensaje ?>
    </div>
    <?php endif; ?>
    
    <?php if (!$tiendaExistente): ?>
    <div class="alert alert-info">
        <h4><i class="fas fa-info-circle"></i> Importante: Crea tu tienda para empezar a vender</h4>
        <p>Antes de poder crear productos, necesitas configurar tu tienda. Completa el formulario a continuación para comenzar.</p>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form id="formTienda" method="post" action="/artesanoDigital/artesano/tienda" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombre_tienda">Nombre de la Tienda *</label>
                    <input type="text" class="form-control" id="nombre_tienda" name="nombre_tienda" required
                           value="<?= htmlspecialchars($tienda['nombre_tienda'] ?? '') ?>">
                    <small class="form-text text-muted">Elige un nombre que represente tu marca y sea fácil de recordar</small>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción de la Tienda</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($tienda['descripcion'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Describe tu tienda, qué tipo de artesanías vendes y qué te hace especial</small>
                </div>

                <div class="form-group">
                    <label for="imagen_logo">Logo de la Tienda</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="imagen_logo" name="imagen_logo" accept="image/jpeg,image/png,image/gif">
                        <label class="custom-file-label" for="imagen_logo">Seleccionar imagen...</label>
                    </div>
                    <small class="form-text text-muted">Formatos aceptados: JPEG, PNG, GIF. Tamaño máximo: 2MB</small>
                    
                    <div id="logo-preview-container">
                        <?php if (!empty($tienda['imagen_logo'])): ?>
                        <p class="mb-1">Logo actual:</p>
                        <img src="/artesanoDigital/uploads/<?= htmlspecialchars($tienda['imagen_logo']) ?>" 
                             alt="Logo de la tienda" class="img-thumbnail" style="max-width: 150px;">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/artesanoDigital/dashboard/artesano" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <?= $tienda ? 'Actualizar Tienda' : 'Crear Tienda' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('formTienda').addEventListener('submit', function(event) {
    const nombreTienda = document.getElementById('nombre_tienda').value.trim();
    
    if (nombreTienda.length === 0) {
        event.preventDefault();
        alert('El nombre de la tienda es obligatorio');
        return false;
    }
    
    // Validación de la imagen si se ha seleccionado
    const inputImagen = document.getElementById('imagen_logo');
    if (inputImagen.files.length > 0) {
        const archivo = inputImagen.files[0];
        const tipoArchivo = archivo.type;
        const tamanoArchivo = archivo.size;
        
        // Validar tipo
        if (!['image/jpeg', 'image/png', 'image/gif'].includes(tipoArchivo)) {
            event.preventDefault();
            alert('El archivo debe ser una imagen (JPEG, PNG o GIF)');
            return false;
        }
        
        // Validar tamaño (2MB máximo)
        if (tamanoArchivo > 2 * 1024 * 1024) {
            event.preventDefault();
            alert('La imagen no debe superar los 2MB');
            return false;
        }
    }
    
    return true;
});

// Mostrar nombre del archivo seleccionado
document.getElementById('imagen_logo').addEventListener('change', function() {
    const fileName = this.files[0]?.name;
    if (fileName) {
        const label = this.nextElementSibling;
        label.textContent = fileName;
    }
});

// Mostrar notificación de éxito con animación
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 3000);
    }
});
</script>

<style>
.dashboard-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.dashboard-header {
    margin-bottom: 2rem;
    text-align: center;
}

.dashboard-header h1 {
    margin-bottom: 0.5rem;
    color: #333;
}

.dashboard-header p {
    color: #666;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-control:focus {
    border-color: #007bff;
    outline: none;
}

.form-control-file {
    padding: 0.5rem 0;
}

.form-text {
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

.text-muted {
    color: #6c757d;
}

.form-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0069d9;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    transition: opacity 0.5s;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.img-thumbnail {
    padding: 0.25rem;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    max-width: 100%;
}
</style>

<?php
// Capturar el contenido y pasarlo al layout base
$contenido = ob_get_clean();

// Cargar plantilla base con el contenido capturado
include_once dirname(__FILE__) . '/../layouts/base.php';
?>
