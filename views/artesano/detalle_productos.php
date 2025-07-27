<?php
/**
 * Vista de detalles de producto para artesanos
 * Responsabilidad: Mostrar, editar y eliminar productos específicos (RUD - Read, Update, Delete)
 */

// Variables para el layout
$titulo = $titulo ?? 'Gestión de Productos - Artesano Digital';
$descripcion = $descripcion ?? 'Editar y gestionar productos específicos';

// Verificar si hay un ID de producto en la URL
$idProducto = $_GET['id'] ?? null;
if (!$idProducto) {
    header('Location: /artesanoDigital/artesano/dashboard');
    exit;
}

// Incluir dependencias necesarias
require_once dirname(__FILE__) . '/../../models/Tienda.php';
require_once dirname(__FILE__) . '/../../controllers/ControladorProductosArtesano.php';

use Models\Tienda;

// Verificar si el artesano ya tiene una tienda
$modeloTienda = new Tienda();
$idUsuarioActual = $usuario['id_usuario'] ?? 0;
$tiendaExistente = $modeloTienda->obtenerPorUsuario($idUsuarioActual);
$tieneTienda = !empty($tiendaExistente);

// Iniciar captura de contenido
ob_start();
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/artesanoDigital/assets/css/modal-detalle-pedidos.css">
<link rel="stylesheet" href="/artesanoDigital/assets/css/artesano-dashboard.css">

<div class="producto-detalle-container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="/artesanoDigital/artesano/dashboard" class="breadcrumb-link">
            <i class="material-icons">dashboard</i> Dashboard
        </a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Gestión de Producto</span>
    </nav>

    <!-- Header del producto -->
    <div class="producto-header">
        <div class="header-info">
            <h1 id="productoTitulo">
                <i class="material-icons">inventory_2</i>
                <span id="productoNombre">Cargando...</span>
            </h1>
            <p id="productoDescripcionBreve">Gestiona los detalles de tu producto</p>
        </div>
        <div class="header-actions">
            <button id="btnToggleEdicion" class="btn btn-secondary">
                <i class="material-icons">edit</i> Editar
            </button>
            <button id="btnDuplicar" class="btn btn-outline" title="Duplicar producto">
                <i class="material-icons">content_copy</i> Duplicar
            </button>
            <button id="btnVerEnTienda" class="btn btn-outline" title="Ver en tienda">
                <i class="material-icons">launch</i> Ver en tienda
            </button>
            <button id="btnEliminar" class="btn btn-danger" title="Eliminar producto">
                <i class="material-icons">delete</i> Eliminar
            </button>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="producto-main">
        <!-- Navegación por pestañas -->
        <div class="producto-tabs">
            <button class="tab-btn active" data-tab="detalles">
                <i class="material-icons">visibility</i> Ver Detalles
            </button>
            <button class="tab-btn" data-tab="general">
                <i class="material-icons">edit</i> Editar Información
            </button>
            <button class="tab-btn" data-tab="precios">
                <i class="material-icons">attach_money</i> Precios y Stock
            </button>
            <button class="tab-btn" data-tab="imagenes">
                <i class="material-icons">image</i> Imágenes
            </button>
            <button class="tab-btn" data-tab="estadisticas">
                <i class="material-icons">analytics</i> Estadísticas
            </button>
        </div>
        
        <!-- Formulario principal -->
        <form id="formEditarProducto" method="post" enctype="multipart/form-data">
            <input type="hidden" id="editProductoId" name="producto_id" value="<?= htmlspecialchars($idProducto) ?>">
            <input type="hidden" name="accion" value="actualizar_producto">
            
            <!-- Contenido de pestañas -->
            <div class="tab-content">
                <!-- Tab: Ver Detalles (Solo lectura) -->
                <div id="tab-detalles" class="tab-pane active">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="material-icons">visibility</i> Detalles del Producto</h3>
                        </div>
                        <div class="card-body">
                            <div class="producto-vista-detalle">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label>Nombre del Producto</label>
                                            <p id="vistaNombre" class="info-value">-</p>
                                        </div>
                                        <div class="info-group">
                                            <label>Categoría</label>
                                            <p id="vistaCategoria" class="info-value">-</p>
                                        </div>
                                        <div class="info-group">
                                            <label>Precio</label>
                                            <p id="vistaPrecio" class="info-value precio-destacado">$0.00</p>
                                        </div>
                                        <div class="info-group">
                                            <label>Stock Disponible</label>
                                            <p id="vistaStock" class="info-value">0</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <label>Estado</label>
                                            <p id="vistaEstado" class="info-value">-</p>
                                        </div>
                                        <div class="info-group">
                                            <label>Fecha de Creación</label>
                                            <p id="vistaFechaCreacion" class="info-value">-</p>
                                        </div>
                                        <div class="info-group">
                                            <label>Última Actualización</label>
                                            <p id="vistaFechaActualizacion" class="info-value">-</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-group">
                                    <label>Descripción</label>
                                    <p id="vistaDescripcion" class="info-value descripcion-completa">-</p>
                                </div>
                                
                                <div class="imagen-producto-vista">
                                    <label>Imagen del Producto</label>
                                    <div class="imagen-container">
                                        <img id="vistaImagen" src="" alt="Imagen del producto" style="display: none;">
                                        <div id="vistaNoImagen" class="no-imagen-placeholder">
                                            <i class="material-icons">image_not_supported</i>
                                            <p>No hay imagen disponible</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Información General -->
                <div id="tab-general" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="material-icons">edit</i> Editar Información General</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editNombre">Nombre del Producto*</label>
                                        <input type="text" id="editNombre" name="nombre" class="form-control" 
                                               placeholder="Nombre del producto" required maxlength="255" readonly>
                                        <small class="form-text text-muted">Máximo 255 caracteres</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editCategoria">Categoría*</label>
                                        <select id="editCategoria" name="categoria" class="form-control" required disabled>
                                            <option value="">Seleccione una categoría</option>
                                            <option value="ceramica">Cerámica</option>
                                            <option value="textiles">Textiles</option>
                                            <option value="madera">Madera</option>
                                            <option value="joyeria">Joyería</option>
                                            <option value="pintura">Pintura</option>
                                            <option value="escultura">Escultura</option>
                                            <option value="otros">Otros</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="editDescripcion">Descripción del Producto*</label>
                                <textarea id="editDescripcion" name="descripcion" class="form-control" 
                                          rows="4" placeholder="Describe tu producto..." required readonly></textarea>
                                <small class="form-text text-muted">Describe las características, materiales y detalles únicos</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="editMaterial">Material Principal</label>
                                        <input type="text" id="editMaterial" name="material" class="form-control" 
                                               placeholder="Ej: Arcilla, Algodón, Roble" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="editDimensiones">Dimensiones</label>
                                        <input type="text" id="editDimensiones" name="dimensiones" class="form-control" 
                                               placeholder="Ej: 20x15x10 cm" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="editPeso">Peso</label>
                                        <input type="text" id="editPeso" name="peso" class="form-control" 
                                               placeholder="Ej: 500g" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Estado del Producto</label>
                                <div class="form-check-group">
                                    <div class="form-check">
                                        <input type="radio" id="editActivoSi" name="activo" value="1" class="form-check-input" disabled>
                                        <label class="form-check-label" for="editActivoSi">
                                            <i class="material-icons text-success">check_circle</i> Activo (Visible en tienda)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" id="editActivoNo" name="activo" value="0" class="form-check-input" disabled>
                                        <label class="form-check-label" for="editActivoNo">
                                            <i class="material-icons text-warning">pause_circle</i> Inactivo (Oculto en tienda)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Precios e Inventario -->
                <div id="tab-precios" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="material-icons">attach_money</i> Precios y Stock</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editPrecio">Precio Regular*</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" id="editPrecio" name="precio" class="form-control" 
                                                   step="0.01" min="0" placeholder="0.00" required readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editStock">Stock Disponible*</label>
                                        <input type="number" id="editStock" name="stock" class="form-control" 
                                               min="0" placeholder="0" required readonly>
                                        <small class="form-text text-muted">Cantidad disponible para venta</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" id="editTieneDescuento" class="form-check-input" disabled>
                                    <label class="form-check-label" for="editTieneDescuento">
                                        <i class="material-icons">local_offer</i> Aplicar descuento
                                    </label>
                                </div>
                            </div>
                            
                            <div id="descuentoSection" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="editDescuento">Descuento (%)</label>
                                            <input type="number" id="editDescuento" name="descuento" class="form-control" 
                                                   step="0.01" min="0" max="100" placeholder="0" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Precio con Descuento</label>
                                            <div class="precio-preview">
                                                <span class="precio-original">$<span id="precioOriginal">0.00</span></span>
                                                <span class="precio-descuento">$<span id="precioConDescuento">0.00</span></span>
                                                <span class="ahorro">Ahorro: $<span id="ahorroCalculado">0.00</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="material-icons">info</i>
                                <strong>Información:</strong> El stock se actualiza automáticamente cuando se realizan ventas.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Gestión de Imágenes -->
                <div id="tab-imagenes" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="material-icons">image</i> Gestión de Imágenes</h3>
                        </div>
                        <div class="card-body">
                            <!-- Imagen principal actual -->
                            <div class="current-image-section">
                                <h4>Imagen Principal Actual</h4>
                                <div class="current-image-preview" id="currentImagePreview">
                                    <img id="currentProductImage" src="" alt="Imagen actual del producto" style="display: none;">
                                    <div class="no-image-placeholder" id="noImagePlaceholder">
                                        <i class="material-icons">image_not_supported</i>
                                        <p>No hay imagen establecida</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cambiar imagen -->
                            <div class="change-image-section" id="changeImageSection" style="display: none;">
                                <h4>Cambiar Imagen Principal</h4>
                                <div class="upload-area" id="uploadArea">
                                    <input type="file" id="editImagen" name="imagen" accept="image/*" class="file-input">
                                    <div class="upload-content">
                                        <i class="material-icons">cloud_upload</i>
                                        <h5>Arrastra una imagen aquí o haz clic para seleccionar</h5>
                                        <p class="upload-specs">
                                            Formatos: JPG, PNG, GIF | Tamaño máximo: 5MB<br>
                                            Resolución recomendada: 800x600px o superior
                                        </p>
                                        <button type="button" class="btn btn-outline">
                                            <i class="material-icons">folder_open</i> Seleccionar Archivo
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Vista previa de nueva imagen -->
                                <div class="new-image-preview" id="newImagePreview" style="display: none;">
                                    <h5>Vista Previa de Nueva Imagen</h5>
                                    <div class="preview-container">
                                        <img id="newImageDisplay" src="" alt="Vista previa">
                                        <div class="preview-actions">
                                            <button type="button" class="btn btn-sm btn-danger" id="removeNewImage">
                                                <i class="material-icons">delete</i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="image-info" id="imageInfo">
                                        <!-- Info de la imagen se llenará con JS -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="material-icons">warning</i>
                                <strong>Importante:</strong> La imagen principal es la que se mostrará en las tarjetas de producto y la tienda.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Estadísticas -->
                <div id="tab-estadisticas" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="material-icons">analytics</i> Estadísticas del Producto</h3>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="material-icons">visibility</i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 id="statsVisualizaciones">-</h4>
                                        <p>Visualizaciones</p>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="material-icons">shopping_cart</i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 id="statsVentas">-</h4>
                                        <p>Ventas Totales</p>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="material-icons">attach_money</i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 id="statsIngresos">$-</h4>
                                        <p>Ingresos Generados</p>
                                    </div>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="material-icons">favorite</i>
                                    </div>
                                    <div class="stat-content">
                                        <h4 id="statsFavoritos">-</h4>
                                        <p>Agregado a Favoritos</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="performance-section">
                                <h4>Rendimiento del Producto</h4>
                                <div class="performance-bar">
                                    <div class="performance-label">Popularidad</div>
                                    <div class="progress">
                                        <div class="progress-bar" id="popularityBar" style="width: 0%"></div>
                                    </div>
                                    <span class="performance-percentage" id="popularityPercent">0%</span>
                                </div>
                                
                                <div class="performance-bar">
                                    <div class="performance-label">Conversión de Ventas</div>
                                    <div class="progress">
                                        <div class="progress-bar" id="conversionBar" style="width: 0%"></div>
                                    </div>
                                    <span class="performance-percentage" id="conversionPercent">0%</span>
                                </div>
                            </div>
                            
                            <div class="recommendations">
                                <h4>Recomendaciones</h4>
                                <div id="productRecommendations">
                                    <div class="recommendation-item">
                                        <i class="material-icons text-info">lightbulb</i>
                                        <span>Las estadísticas se actualizarán automáticamente con la actividad del producto.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="form-actions" style="display: none;">
                <button type="submit" class="btn btn-primary" id="btnGuardarCambios">
                    <i class="material-icons">save</i> Guardar Cambios
                </button>
                <button type="button" class="btn btn-secondary" id="btnCancelar">
                    <i class="material-icons">cancel</i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnVistaPrevia">
                    <i class="material-icons">preview</i> Vista Previa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Variables globales
let productoActual = null;
let enModoEdicion = false;

// Obtener el ID del producto desde la URL
function obtenerIdProductoDeURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id');
}

// Inicializar la página
document.addEventListener('DOMContentLoaded', function() {
    const idProducto = obtenerIdProductoDeURL();
    console.log('ID del producto obtenido de URL:', idProducto);
    
    if (idProducto) {
        cargarDatosProducto(idProducto);
    } else {
        console.error('No se encontró ID del producto en la URL');
        mostrarToast('ID de producto no encontrado', 'error');
        setTimeout(() => {
            window.location.href = '/artesanoDigital/artesano/dashboard';
        }, 2000);
    }
    
    // Inicializar eventos
    inicializarEventos();
});

// Función para cargar datos del producto
async function cargarDatosProducto(productoId) {
    try {
        mostrarToast('Cargando datos del producto...', 'info');
        
        const response = await fetch(`/artesanoDigital/api/productos.php?id=${productoId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Producto no encontrado');
        }
        
        const data = await response.json();
        
        if (data.success && data.producto) {
            productoActual = data.producto;
            llenarFormularioProducto(productoActual);
            llenarVistaDetalle(productoActual);
            // cargarEstadisticas(productoId);
        } else {
            throw new Error(data.message || data.error || 'Error al cargar el producto');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al cargar los datos del producto', 'error');
        setTimeout(() => {
            window.location.href = '/artesanoDigital/artesano/dashboard';
        }, 2000);
    }
}

// Función para llenar la vista de detalles (solo lectura)
function llenarVistaDetalle(producto) {
    document.getElementById('vistaNombre').textContent = producto.nombre || '-';
    document.getElementById('vistaCategoria').textContent = producto.categoria || '-';
    document.getElementById('vistaPrecio').textContent = `$${parseFloat(producto.precio || 0).toFixed(2)}`;
    document.getElementById('vistaStock').textContent = producto.stock || '0';
    
    // Estado con estilo
    const estadoElement = document.getElementById('vistaEstado');
    if (producto.activo == 1) {
        estadoElement.textContent = 'Activo';
        estadoElement.style.color = '#28a745';
    } else {
        estadoElement.textContent = 'Inactivo';
        estadoElement.style.color = '#dc3545';
    }
    
    document.getElementById('vistaDescripcion').textContent = producto.descripcion || '-';
    
    // Formatear fechas si están disponibles
    document.getElementById('vistaFechaCreacion').textContent = formatearFecha(producto.fecha_creacion) || '-';
    document.getElementById('vistaFechaActualizacion').textContent = formatearFecha(producto.fecha_actualizacion) || '-';
    
    // Imagen
    if (producto.imagen) {
        const imagePath = `/artesanoDigital/public/productos/${producto.imagen}`;
        document.getElementById('vistaImagen').src = imagePath;
        document.getElementById('vistaImagen').style.display = 'block';
        document.getElementById('vistaNoImagen').style.display = 'none';
    } else {
        document.getElementById('vistaImagen').style.display = 'none';
        document.getElementById('vistaNoImagen').style.display = 'block';
    }
}

// Función para formatear fechas
function formatearFecha(fechaString) {
    if (!fechaString) return null;
    
    try {
        const fecha = new Date(fechaString);
        return fecha.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch (error) {
        return fechaString;
    }
}

// Función para llenar el formulario con datos del producto
function llenarFormularioProducto(producto) {
    // Actualizar título
    document.getElementById('productoNombre').textContent = producto.nombre || 'Producto';
    
    // Llenar campos del formulario
    document.getElementById('editProductoId').value = producto.id_producto || '';
    document.getElementById('editNombre').value = producto.nombre || '';
    document.getElementById('editDescripcion').value = producto.descripcion || '';
    document.getElementById('editCategoria').value = producto.categoria || '';
    document.getElementById('editMaterial').value = producto.material || '';
    document.getElementById('editDimensiones').value = producto.dimensiones || '';
    document.getElementById('editPeso').value = producto.peso || '';
    document.getElementById('editPrecio').value = producto.precio || '';
    document.getElementById('editStock').value = producto.stock || '';
    
    // Manejar descuento
    const descuentoValue = parseFloat(producto.descuento || 0);
    document.getElementById('editDescuento').value = descuentoValue;
    
    // Estado activo/inactivo
    if (producto.activo == 1) {
        document.getElementById('editActivoSi').checked = true;
    } else {
        document.getElementById('editActivoNo').checked = true;
    }
    
    // Descuento
    if (descuentoValue > 0) {
        document.getElementById('editTieneDescuento').checked = true;
        document.getElementById('descuentoSection').style.display = 'block';
        calcularPrecioConDescuento();
    } else {
        document.getElementById('editTieneDescuento').checked = false;
        document.getElementById('descuentoSection').style.display = 'none';
    }
    
    // Imagen
    if (producto.imagen) {
        const imagePath = `/artesanoDigital/public/productos/${producto.imagen}`;
        document.getElementById('currentProductImage').src = imagePath;
        document.getElementById('currentProductImage').style.display = 'block';
        document.getElementById('noImagePlaceholder').style.display = 'none';
    } else {
        document.getElementById('currentProductImage').style.display = 'none';
        document.getElementById('noImagePlaceholder').style.display = 'block';
    }
}

// Función para inicializar todos los eventos
function inicializarEventos() {
    // Navegación por pestañas
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remover clase active de todos los botones y paneles
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Agregar clase active al botón clickeado y su panel correspondiente
            this.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');
        });
    });
    
    // Botón toggle edición
    document.getElementById('btnToggleEdicion').addEventListener('click', toggleModoEdicion);
    
    // Botón cancelar
    document.getElementById('btnCancelar').addEventListener('click', cancelarEdicion);
    
    // Botón eliminar
    document.getElementById('btnEliminar').addEventListener('click', confirmarEliminarProducto);
    
    // Formulario principal
    document.getElementById('formEditarProducto').addEventListener('submit', manejarEnvioFormulario);
    
    // Checkbox de descuento
    document.getElementById('editTieneDescuento').addEventListener('change', function() {
        const section = document.getElementById('descuentoSection');
        if (this.checked) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
            document.getElementById('editDescuento').value = '';
        }
        calcularPrecioConDescuento();
    });
    
    // Cálculo de precio con descuento
    document.getElementById('editPrecio').addEventListener('input', calcularPrecioConDescuento);
    document.getElementById('editDescuento').addEventListener('input', calcularPrecioConDescuento);
    
    // Manejo de imágenes
    inicializarManejadorImagenes();
}

// Función para calcular precio con descuento
function calcularPrecioConDescuento() {
    const precio = parseFloat(document.getElementById('editPrecio').value) || 0;
    const descuento = parseFloat(document.getElementById('editDescuento').value) || 0;
    
    const precioConDescuento = precio - (precio * descuento / 100);
    const ahorro = precio - precioConDescuento;
    
    document.getElementById('precioOriginal').textContent = precio.toFixed(2);
    document.getElementById('precioConDescuento').textContent = precioConDescuento.toFixed(2);
    document.getElementById('ahorroCalculado').textContent = ahorro.toFixed(2);
}

// Función para inicializar manejo de imágenes
function inicializarManejadorImagenes() {
    const fileInput = document.getElementById('editImagen');
    const uploadArea = document.getElementById('uploadArea');
    const newImagePreview = document.getElementById('newImagePreview');
    const newImageDisplay = document.getElementById('newImageDisplay');
    const removeNewImageBtn = document.getElementById('removeNewImage');
    
    if (!uploadArea) return;
    
    // Click en área de upload
    uploadArea.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('drag-over');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            manejarSeleccionImagen(files[0]);
        }
    });
    
    // Cambio en input file
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            manejarSeleccionImagen(e.target.files[0]);
        }
    });
    
    // Remover imagen
    if (removeNewImageBtn) {
        removeNewImageBtn.addEventListener('click', function() {
            fileInput.value = '';
            newImagePreview.style.display = 'none';
            newImageDisplay.src = '';
        });
    }
}

// Función para manejar selección de imagen
function manejarSeleccionImagen(file) {
    // Validar tipo de archivo
    if (!file.type.startsWith('image/')) {
        mostrarToast('Por favor selecciona un archivo de imagen válido', 'error');
        return;
    }
    
    // Validar tamaño (5MB)
    if (file.size > 5 * 1024 * 1024) {
        mostrarToast('La imagen no debe superar los 5MB', 'error');
        return;
    }
    
    // Mostrar vista previa
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('newImageDisplay').src = e.target.result;
        document.getElementById('newImagePreview').style.display = 'block';
        
        // Mostrar información del archivo
        const info = `
            <strong>Archivo:</strong> ${file.name}<br>
            <strong>Tamaño:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
            <strong>Tipo:</strong> ${file.type}
        `;
        document.getElementById('imageInfo').innerHTML = info;
    };
    reader.readAsDataURL(file);
}

// Función para toggle modo edición
function toggleModoEdicion() {
    enModoEdicion = !enModoEdicion;
    
    const btnToggle = document.getElementById('btnToggleEdicion');
    const campos = document.querySelectorAll('#formEditarProducto input, #formEditarProducto textarea, #formEditarProducto select');
    const botonesEdicion = document.querySelector('.form-actions');
    const changeImageSection = document.getElementById('changeImageSection');
    
    if (enModoEdicion) {
        // Activar modo edición
        btnToggle.innerHTML = '<i class="material-icons">cancel</i> Cancelar Edición';
        btnToggle.classList.remove('btn-secondary');
        btnToggle.classList.add('btn-warning');
        
        campos.forEach(campo => {
            if (campo.id !== 'editProductoId') {
                campo.removeAttribute('readonly');
                campo.removeAttribute('disabled');
            }
        });
        
        botonesEdicion.style.display = 'flex';
        if (changeImageSection) changeImageSection.style.display = 'block';
        
        mostrarToast('Modo edición activado', 'info');
    } else {
        // Volver a modo lectura
        btnToggle.innerHTML = '<i class="material-icons">edit</i> Editar';
        btnToggle.classList.remove('btn-warning');
        btnToggle.classList.add('btn-secondary');
        
        campos.forEach(campo => {
            if (campo.id !== 'editImagen' && campo.id !== 'editProductoId') {
                campo.setAttribute('readonly', 'readonly');
                if (campo.tagName === 'SELECT') {
                    campo.setAttribute('disabled', 'disabled');
                }
            }
        });
        
        botonesEdicion.style.display = 'none';
        if (changeImageSection) changeImageSection.style.display = 'none';
        
        mostrarToast('Modo lectura activado', 'info');
    }
}

// Función para mostrar toast messages
function mostrarToast(mensaje, tipo = 'info') {
    // Crear el toast si no existe
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'toast';
        document.body.appendChild(toast);
        
        // Agregar estilos básicos si no existen
        if (!document.querySelector('#toastStyles')) {
            const style = document.createElement('style');
            style.id = 'toastStyles';
            style.textContent = `
                .toast {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 4px;
                    color: white;
                    z-index: 1000;
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                    max-width: 300px;
                }
                .toast.show { transform: translateX(0); }
                .toast.toast-info { background: #17a2b8; }
                .toast.toast-success { background: #28a745; }
                .toast.toast-warning { background: #ffc107; color: #212529; }
                .toast.toast-error { background: #dc3545; }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Configurar el toast
    toast.textContent = mensaje;
    toast.className = `toast toast-${tipo} show`;
    
    // Auto-ocultar después de 3 segundos
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Función para cancelar edición
function cancelarEdicion() {
    if (productoActual) {
        llenarFormularioProducto(productoActual);
        toggleModoEdicion();
        mostrarToast('Cambios cancelados', 'warning');
    }
}

// Función para confirmar eliminación
function confirmarEliminarProducto() {
    if (!productoActual) return;
    
    const nombreProducto = productoActual.nombre;
    
    if (confirm(`¿Estás seguro de que quieres eliminar el producto "${nombreProducto}"? Esta acción no se puede deshacer.`)) {
        eliminarProducto(productoActual.id_producto);
    }
}

// Función para eliminar producto
async function eliminarProducto(productoId) {
    try {
        const response = await fetch('/artesanoDigital/artesano/productos/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `accion=eliminar_producto&producto_id=${productoId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarToast('Producto eliminado exitosamente', 'success');
            setTimeout(() => {
                window.location.href = '/artesanoDigital/artesano/dashboard';
            }, 1500);
        } else {
            mostrarToast('Error al eliminar el producto: ' + (data.message || 'Error desconocido'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al eliminar el producto', 'error');
    }
}

// Función para manejar envío del formulario
async function manejarEnvioFormulario(e) {
    e.preventDefault();
    
    if (!enModoEdicion) {
        mostrarToast('Activa el modo edición para guardar cambios', 'warning');
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        formData.append('accion', 'actualizar_producto');
        
        mostrarToast('Guardando cambios...', 'info');
        
        const response = await fetch('/artesanoDigital/artesano/productos/crear', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarToast('Producto actualizado exitosamente', 'success');
            
            // Recargar datos del producto
            await cargarDatosProducto(document.getElementById('editProductoId').value);
            
            // Salir del modo edición
            toggleModoEdicion();
        } else {
            mostrarToast('Error al actualizar el producto: ' + (data.message || 'Error desconocido'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al actualizar el producto', 'error');
    }
}
</script>

<style>
/* Estilos específicos para la página de detalles del producto */
.producto-detalle-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    font-size: 14px;
}

.breadcrumb-link {
    color: #007bff;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
}

.breadcrumb-separator {
    margin: 0 10px;
    color: #6c757d;
}

.breadcrumb-current {
    color: #6c757d;
}

.producto-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-info h1 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
}

.header-info p {
    margin: 5px 0 0 0;
    color: #666;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.producto-tabs {
    display: flex;
    background: white;
    border-radius: 8px 8px 0 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.tab-btn {
    padding: 15px 20px;
    border: none;
    background: none;
    color: #666;
    font-size: 14px;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.tab-btn:hover {
    background: #f8f9fa;
    color: #333;
}

.tab-btn.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background: #f8f9fa;
}

.tab-content {
    background: white;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-height: 400px;
}

.tab-pane {
    display: none;
    padding: 30px;
}

.tab-pane.active {
    display: block;
}

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}

.card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
}

.card-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
}

.card-body {
    padding: 20px;
}

/* Vista de detalles */
.producto-vista-detalle .info-group {
    margin-bottom: 20px;
}

.producto-vista-detalle .info-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    display: block;
}

.producto-vista-detalle .info-value {
    margin: 0;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    color: #555;
}

.precio-destacado {
    font-size: 18px;
    font-weight: bold;
    color: #28a745 !important;
}

.descripcion-completa {
    line-height: 1.6;
    min-height: 60px;
}

.imagen-producto-vista .imagen-container {
    width: 300px;
    height: 300px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.imagen-producto-vista img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-imagen-placeholder {
    text-align: center;
    color: #666;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-control[readonly] {
    background-color: #f8f9fa;
    color: #6c757d;
}

.form-control[disabled] {
    background-color: #e9ecef;
    color: #6c757d;
}

.row {
    display: flex;
    margin: 0 -10px;
}

.col-md-4, .col-md-6 {
    padding: 0 10px;
}

.col-md-4 {
    flex: 0 0 33.333333%;
}

.col-md-6 {
    flex: 0 0 50%;
}

.form-check-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-check-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.input-group {
    display: flex;
}

.input-group-prepend {
    display: flex;
}

.input-group-text {
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-right: none;
    border-radius: 4px 0 0 4px;
}

.input-group .form-control {
    border-radius: 0 4px 4px 0;
}

.precio-preview {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.precio-original {
    text-decoration: line-through;
    color: #666;
}

.precio-descuento {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
}

.ahorro {
    color: #dc3545;
    font-size: 12px;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

/* Gestión de imágenes */
.current-image-section {
    margin-bottom: 30px;
}

.current-image-preview {
    width: 200px;
    height: 200px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.current-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover, .upload-area.drag-over {
    border-color: #007bff;
    background: #f8f9fa;
}

.upload-content h5 {
    margin: 10px 0;
    color: #333;
}

.upload-specs {
    color: #666;
    font-size: 12px;
    margin-bottom: 15px;
}

.file-input {
    display: none;
}

.new-image-preview {
    margin-top: 20px;
}

.preview-container {
    position: relative;
    width: 200px;
    height: 200px;
    border-radius: 8px;
    overflow: hidden;
}

.preview-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-actions {
    position: absolute;
    top: 10px;
    right: 10px;
}

/* Estadísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.stat-icon {
    margin-right: 15px;
}

.stat-icon .material-icons {
    font-size: 30px;
    color: #007bff;
}

.stat-content h4 {
    margin: 0;
    font-size: 24px;
    color: #333;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.performance-section {
    margin-bottom: 30px;
}

.performance-bar {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    gap: 15px;
}

.performance-label {
    flex: 0 0 150px;
    font-size: 14px;
    color: #333;
}

.progress {
    flex: 1;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #007bff;
    transition: width 0.3s ease;
}

.performance-percentage {
    flex: 0 0 50px;
    text-align: right;
    font-size: 12px;
    color: #666;
}

.recommendation-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 10px;
}

/* Botones */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-outline {
    background: none;
    border: 1px solid #ddd;
    color: #333;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.form-actions {
    display: none;
    justify-content: space-between;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 0 0 8px 8px;
    border-top: 1px solid #ddd;
}

/* Responsive */
@media (max-width: 768px) {
    .producto-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .header-actions {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .row {
        flex-direction: column;
    }
    
    .col-md-4, .col-md-6 {
        flex: none;
        padding: 0;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .performance-bar {
        flex-direction: column;
        gap: 5px;
    }
    
    .performance-label {
        flex: none;
    }
}
</style>

<?php
// Scripts adicionales
$scriptsAdicionales = [
    '/artesanoDigital/assets/js/dashboard-artesano.js'
];

// Capturar el contenido y incluir el layout
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
