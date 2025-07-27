<?php
// Variables para el layout
$titulo = $titulo ?? 'Dashboard de Artesano - Artesano Digital';
$descripcion = $descripcion ?? 'Panel de administración para artesanos';

// Incluir modelo de Tienda
require_once dirname(__FILE__) . '/../../models/Tienda.php';
use Models\Tienda;

// Verificar si el artesano ya tiene una tienda
$modeloTienda = new Tienda();
$idUsuarioActual = $usuario['id_usuario'] ?? 0;
$tiendaExistente = $modeloTienda->obtenerPorUsuario($idUsuarioActual);
$tieneTienda = !empty($tiendaExistente);

// Verificar si hay mensajes en la URL y guardarlos en la sesión para mostrar como toast
if (isset($_GET['mensaje']) && isset($_GET['tipo'])) {
    $_SESSION['toast_mensaje'] = $_GET['mensaje'];
    $_SESSION['toast_tipo'] = $_GET['tipo'] === 'success' ? 'exito' : $_GET['tipo'];

    // Redirigir a la misma página pero sin los parámetros
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $redirect_url");
    exit;
}

// Iniciar captura de contenido
ob_start();


?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/artesanoDigital/assets/css/modal-detalle-pedidos.css">
<link rel="stylesheet" href="/artesanoDigital/assets/css/artesano-dashboard.css">
<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<div class="dashboard-container dashboard-bg-white">
    <div class="dashboard-header">
        <h1>Panel de Artesano</h1>
        <p>Bienvenido, <?= htmlspecialchars($usuario['nombre'] ?? 'Artesano') ?></p>
    </div>
    
    <?php if (!$tieneTienda): ?>
            <div class="alert alert-warning">
                <div class="alert-content">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <h4>Necesitas crear tu tienda</h4>
                        <p>Antes de publicar productos, debes crear tu tienda de artesanías. Es un paso simple que te permitirá organizar y mostrar tus productos.</p>
                        <button id="btnCrearTienda" class="btn btn-primary mt-2">Crear mi tienda ahora</button>
                    </div>
                </div>
            </div>
    <?php endif; ?>
    <!-- Cards de Resumen -->
    <div class="resumen-cards-horizontal">
        <div class="resumen-card resumen-blue">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">inventory_2</span></div>
            <div>
                <div class="resumen-label">Productos Activos</div>
                <div class="resumen-value"><?= $estadisticas['productos_activos'] ?? 0 ?></div>
            </div>
        </div>
        <div class="resumen-card resumen-green">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">shopping_cart</span></div>
            <div>
                <div class="resumen-label">Ventas</div>
                <div class="resumen-value"><?= $estadisticas['ventas_totales'] ?? 0 ?></div>
            </div>
        </div>
        <div class="resumen-card resumen-teal">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">attach_money</span></div>
            <div>
                <div class="resumen-label">Ingresos Totales</div>
                <div class="resumen-value">B/. <?= number_format($estadisticas['ingresos_totales'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="resumen-card resumen-yellow">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">local_shipping</span></div>
            <div>
                <div class="resumen-label">Pedidos Pendientes</div>
                <div class="resumen-value"><?= $estadisticas['pedidos_pendientes'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <!-- Botones de acción -->
    <div class="dashboard-actions-bar">

        <a href="/artesanoDigital/artesano/tienda" class="dashboard-btn dashboard-btn-green">
            <i class="fas fa-store"></i> <?= $tieneTienda ? 'Administrar' : 'Crear' ?> Mi Tienda
        </a>
        <a href="/artesanoDigital/artesano/mis_productos" class="dashboard-btn dashboard-btn-indigo">
            <i class="fas fa-chart-line"></i> Mis Productos
        </a>
    </div>

    <!-- Tabs de navegación -->
    <div class="dashboard-tabs">
        <ul class="tabs-nav">
            <li class="active" data-tab="tab-ventas"><i class="fas fa-store"></i> Mis Ventas</li>
            <li data-tab="tab-compras"><i class="fas fa-shopping-bag"></i> Mis Compras</li>
        </ul>
    </div>

    <!-- Contenido de pestañas -->
    <div class="tabs-content">
        <!-- TAB: Mis Ventas (Pedidos Recibidos) -->
        <div id="tab-ventas" class="tab-pane active">
            <div class="dashboard-main">
                <div class="card">
                    <div class="card-header">
                        <h3>Pedidos de Clientes</h3>
                        <div class="card-header-actions">
                            <span class="pedidos-contador" style="margin-right: 15px; font-size: 14px; color: #6c757d;">
                                Mostrando <?= count($pedidos_recientes ?? []) ?> pedidos
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pedidos_recientes ?? [])): ?>
                                    <p class="empty-state">No hay pedidos recientes</p>
                        <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table pedidos-table">
                                            <thead>
                                                <tr>
                                                    <th>Pedido #</th>
                                                    <th>Cliente</th>
                                                    <th>Fecha</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pedidos_recientes as $pedido): ?>
                                                            <tr class="estado-<?= $pedido['estado'] ?>">
                                                                <td>#<?= str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) ?></td>
                                                                <td><?= htmlspecialchars($pedido['cliente_nombre'] ?? 'Cliente') ?></td>
                                                                <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                                                <td>B/. <?= number_format($pedido['total'], 2) ?></td>
                                                                <td>
                                                                    <span class="badge status-<?= $pedido['estado'] ?>">
                                                                        <?= ucfirst($pedido['estado']) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-primary ver-pedido"
                                                                        data-id="<?= $pedido['id_pedido'] ?>">
                                                                        Ver detalles
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: Mis Compras (Pedidos realizados por el artesano) -->
        <div id="tab-compras" class="tab-pane">
            <div class="dashboard-main">
                <div class="card">
                    <div class="card-header">
                        <h3>Mis Pedidos Realizados</h3>
                        <p class="subtitle">Aquí puedes ver tus compras como cliente</p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pedidos_personales ?? [])): ?>
                                    <p class="empty-state">No has realizado ningún pedido como cliente todavía</p>
                                    <div class="empty-action">
                                        <a href="/artesanoDigital/productos" class="dashboard-btn dashboard-btn-blue">Explorar
                                            Productos</a>
                                    </div>
                        <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table pedidos-table">
                                            <thead>
                                                <tr>
                                                    <th>Pedido #</th>
                                                    <th>Fecha</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pedidos_personales as $pedido): ?>
                                                            <tr>
                                                                <td>#<?= str_pad($pedido['id_pedido'], 5, '0', STR_PAD_LEFT) ?></td>
                                                                <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                                                <td>B/. <?= number_format($pedido['total'], 2) ?></td>
                                                                <td>
                                                                    <span
                                                                        class="badge status-<?= $pedido['estado'] ?>"><?= ucfirst($pedido['estado']) ?></span>
                                                                </td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-primary ver-pedido-cliente"
                                                                        data-id="<?= $pedido['id_pedido'] ?>">
                                                                        Ver detalles
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

<!-- Modal para crear tienda -->
<div id="modalCrearTienda" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Crear Mi Tienda</h2>
        <form id="formCrearTienda" method="post" action="/artesanoDigital/artesano/tienda/procesar" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="crear_tienda">
            
            <div class="form-group">
                <label for="nombre_tienda">Nombre de la Tienda</label>
                <input type="text" id="nombre_tienda" name="nombre_tienda" class="form-input" required>
                <div class="form-help">Este nombre se mostrará en tu perfil y en tus productos</div>
            </div>
            
            <div class="form-group">
                <label for="descripcion_tienda">Descripción</label>
                <textarea id="descripcion_tienda" name="descripcion" class="form-textarea" rows="4" required></textarea>
                <div class="form-help">Cuenta brevemente sobre tu tienda y tus productos</div>
            </div>
            
            <div class="form-group">
                <label for="imagen_logo">Logo de la Tienda</label>
                <input type="file" id="imagen_logo" name="imagen_logo" class="form-input" accept="image/*">
                <div class="form-help">Recomendado: 400x400px, formato PNG o JPG</div>
                <div id="logo-preview" class="mt-2 d-none">
                    <img src="" alt="Vista previa del logo" style="max-width: 100%; max-height: 200px;">
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary cancelar-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Tienda</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para detalles de pedido -->
<div id="modalDetallePedido" class="modal-pedidos-detalle">
    <div class="modal-content-pedidos">
        <div class="modal-header-pedidos">
            <h3 class="modal-title-pedidos">
                <i class="fas fa-file-invoice"></i> Gestión de Pedido
            </h3>
            <button class="modal-close-pedidos" type="button">
                <i class="material-icons">close</i>
            </button>
        </div>
        
        <div class="modal-body-pedidos">
            <!-- Navegación de Tabs -->
            <div class="tabs-nav-pedidos">
                <button class="tab-btn-pedidos active" data-tab="resumen">
                    <i class="fas fa-clipboard-list"></i> Resumen
                </button>
                <button class="tab-btn-pedidos" data-tab="seguimiento">
                    <i class="fas fa-truck"></i> Seguimiento
                </button>
            </div>

            <!-- Contenido de pestañas -->
            <div class="tab-content-pedidos">
                <!-- TAB: Resumen del Pedido -->
                <div class="tab-pane-pedidos active" id="tab-resumen">
                    <!-- Información del Pedido -->
                    <div class="info-card-pedidos">
                        <h4><i class="fas fa-receipt"></i> Información del Pedido</h4>
                        <div class="info-grid-pedidos">
                            <span class="info-label-pedidos">Número de Pedido:</span>
                            <span class="info-value-pedidos" id="numeroPedido">#0000</span>
                            
                            <span class="info-label-pedidos">Fecha del Pedido:</span>
                            <span class="info-value-pedidos" id="fechaPedido">-</span>
                            
                            <span class="info-label-pedidos">Estado Actual:</span>
                            <div class="estado-selector-pedidos">
                                <select id="cambiarEstado" class="estado-select-pedidos">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <button id="btnActualizarEstado" class="btn-accion-pedidos primary">
                                    <i class="fas fa-save"></i> Actualizar
                                </button>
                            </div>
                            
                            <span class="info-label-pedidos">Método de Pago:</span>
                            <span class="info-value-pedidos" id="metodoPago">-</span>
                        </div>
                    </div>

                    <!-- Información del Cliente -->
                    <div class="info-card-pedidos">
                        <h4><i class="fas fa-user"></i> Información del Cliente (Editable)</h4>
                        <div class="info-grid-pedidos">
                            <span class="info-label-pedidos">Nombre Completo:</span>
                            <span class="info-value-pedidos editable" id="clienteNombre" data-field="nombre" title="Clic para editar">-</span>
                            
                            <span class="info-label-pedidos">Correo Electrónico:</span>
                            <span class="info-value-pedidos editable" id="clienteCorreo" data-field="correo" title="Clic para editar">-</span>
                            
                            <span class="info-label-pedidos">Teléfono:</span>
                            <span class="info-value-pedidos editable" id="clienteTelefono" data-field="telefono" title="Clic para editar">-</span>
                            
                            <span class="info-label-pedidos">Dirección de Envío:</span>
                            <span class="info-value-pedidos editable" id="direccionEnvio" data-field="direccion" title="Clic para editar">-</span>
                        </div>
                    </div>

                    <!-- Productos del Pedido -->
                    <div class="info-card-pedidos">
                        <h4><i class="material-icons">shopping_cart</i> Productos Solicitados</h4>
                        <div class="productos-table-container">
                            <table class="productos-table productos-table-pedidos">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="productosTableBody">
                                    <tr>
                                        <td colspan="4" class="loading-productos">
                                            <i class="material-icons">hourglass_empty</i>
                                            <div>Cargando productos...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Resumen Financiero -->
                    <div class="info-card-pedidos">
                        <h4><i class="fas fa-calculator"></i> Resumen Financiero</h4>
                        <div class="resumen-financiero-pedidos">
                            <div class="info-grid-pedidos">
                                <span class="info-label-pedidos">Subtotal de Productos:</span>
                                <span class="info-value-pedidos" id="subtotalPedido">B/. 0.00</span>
                                
                                <span class="info-label-pedidos">Descuentos Aplicados:</span>
                                <span class="info-value-pedidos" id="descuentosPedido">B/. 0.00</span>
                                
                                <span class="info-label-pedidos">Costo de Envío:</span>
                                <span class="info-value-pedidos" id="costoEnvio">B/. 0.00</span>
                            </div>
                            <div class="info-grid-pedidos total-final-row">
                                <span class="info-label-pedidos">Total Final:</span>
                                <span class="info-value-pedidos" id="totalFinal">B/. 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: Seguimiento -->
                <div class="tab-pane-pedidos" id="tab-seguimiento">
                    <div class="info-card-pedidos">
                        <h4><i class="fas fa-truck"></i> Estado del Envío</h4>
                        <div class="seguimiento-timeline">
                            <div class="timeline-item completed">
                                <div class="timeline-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Pedido Confirmado</h6>
                                    <p>El pedido ha sido recibido y confirmado</p>
                                    <small id="fechaConfirmacion">-</small>
                                </div>
                            </div>
                            <div class="timeline-item current">
                                <div class="timeline-icon">
                                    <i class="fas fa-cog fa-spin"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>En Preparación</h6>
                                    <p>El artesano está preparando tu pedido</p>
                                    <small>Estado actual</small>
                                </div>
                            </div>
                            <div class="timeline-item pending">
                                <div class="timeline-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Enviado</h6>
                                    <p>El pedido ha sido enviado</p>
                                    <small>Pendiente</small>
                                </div>
                            </div>
                            <div class="timeline-item pending">
                                <div class="timeline-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Entregado</h6>
                                    <p>El pedido ha sido entregado al cliente</p>
                                    <small>Pendiente</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-card-pedidos">
                        <h4><i class="fas fa-info-circle"></i> Información de Envío</h4>
                        <div class="envio-info-pedidos">
                            <div class="envio-item-pedidos">
                                <span class="envio-label-pedidos">Empresa de Envío:</span>
                                <span class="envio-value-pedidos" id="empresaEnvio">-</span>
                            </div>
                            
                            <div class="envio-item-pedidos">
                                <span class="envio-label-pedidos">Número de Seguimiento:</span>
                                <span class="envio-value-pedidos" id="numeroSeguimiento">-</span>
                            </div>
                            
                            <div class="envio-item-pedidos">
                                <span class="envio-label-pedidos">Fecha de Envío:</span>
                                <span class="envio-value-pedidos" id="fechaEnvio">-</span>
                            </div>
                            
                            <div class="envio-item-pedidos">
                                <span class="envio-label-pedidos">Fecha Estimada de Entrega:</span>
                                <span class="envio-value-pedidos" id="fechaEstimadaEntrega">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal para gestión de productos -->
<div id="modalDetalleProducto" class="modal">
    <div class="modal-content modal-large">
        <span class="close">&times;</span>
        <div class="modal-header">
            <h2>
                <i class="material-icons">inventory_2</i> 
                <span id="tituloModalProducto">Detalles del Producto</span>
            </h2>
            <div class="modal-actions">
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
        
        <div class="modal-body">
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
                <input type="hidden" id="editProductoId" name="producto_id" value="">
                <input type="hidden" name="accion" value="actualizar_producto">
                
                <!-- Contenido de pestañas -->
                <div class="tab-content">
                    <!-- TAB: Ver Detalles (Solo lectura) -->
                    <div class="tab-pane active" id="tab-detalles">
                        <div class="producto-detalles-completos">
                            <div class="detalle-imagen">
                                <div class="imagen-container">
                                    <img id="modalProductoImagen" src="" alt="Imagen del producto" style="max-width: 100%; border-radius: 8px;">
                                    <div id="modalSinImagen" class="sin-imagen" style="display: none; text-align: center; padding: 40px; background: #f9fafb; border-radius: 8px;">
                                        <i class="material-icons" style="font-size: 48px; color: #9ca3af;">image</i>
                                        <p style="margin-top: 12px; color: #6b7280;">Sin imagen</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detalle-info" style="margin-top: 24px;">
                                <div class="info-group" style="margin-bottom: 16px;">
                                    <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Nombre:</label>
                                    <span id="modalProductoNombre" style="color: #111827;"></span>
                                </div>
                                
                                <div class="info-group" style="margin-bottom: 16px;">
                                    <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Descripción:</label>
                                    <p id="modalProductoDescripcion" style="color: #6b7280; line-height: 1.5; margin: 0;"></p>
                                </div>
                                
                                <div class="info-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 16px;">
                                    <div class="info-group">
                                        <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Precio:</label>
                                        <span id="modalProductoPrecio" class="precio-valor" style="font-size: 18px; font-weight: 600; color: #059669;"></span>
                                    </div>
                                    <div class="info-group">
                                        <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Descuento:</label>
                                        <span id="modalProductoDescuento" class="descuento-valor"></span>
                                    </div>
                                </div>
                                
                                <div class="info-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 16px;">
                                    <div class="info-group">
                                        <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Stock:</label>
                                        <span id="modalProductoStock" class="stock-valor"></span>
                                    </div>
                                    <div class="info-group">
                                        <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Estado:</label>
                                        <span id="modalProductoEstado" class="estado-valor" style="padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600;"></span>
                                    </div>
                                </div>
                                
                                <div class="info-group">
                                    <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Fecha de creación:</label>
                                    <span id="modalProductoFecha" style="color: #6b7280;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB: Información General (Edición) -->
                    <div class="tab-pane" id="tab-general">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="editNombre">
                                    <i class="material-icons">label</i> Nombre del Producto
                                </label>
                                <input type="text" id="editNombre" name="nombre" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="editDescripcion">
                                    <i class="material-icons">description</i> Descripción
                                </label>
                                <textarea id="editDescripcion" name="descripcion" class="form-textarea" rows="4" required></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label for="editCategoria">
                                        <i class="material-icons">category</i> Categoría
                                    </label>
                                    <select id="editCategoria" name="categoria" class="form-select">
                                        <option value="">Seleccionar categoría</option>
                                        <option value="artesanias">Artesanías</option>
                                        <option value="textiles">Textiles</option>
                                        <option value="ceramica">Cerámica</option>
                                        <option value="madera">Madera</option>
                                        <option value="joyeria">Joyería</option>
                                        <option value="decoracion">Decoración</option>
                                    </select>
                                </div>
                                <div class="form-group col-6">
                                    <label for="editActivo">
                                        <i class="material-icons">toggle_on</i> Estado
                                    </label>
                                    <select id="editActivo" name="activo" class="form-select">
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB: Precios y Stock -->
                    <div class="tab-pane" id="tab-precios">
                        <div class="price-stock-grid">
                            <div class="form-group">
                                <label for="editPrecio">
                                    <i class="material-icons">attach_money</i> Precio (B/.)
                                </label>
                                <input type="number" id="editPrecio" name="precio" class="form-input" min="0" step="0.01" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="editStock">
                                    <i class="material-icons">inventory</i> Stock Disponible
                                </label>
                                <input type="number" id="editStock" name="stock" class="form-input" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="editDescuentoValor">
                                    <i class="material-icons">discount</i> Descuento (B/.)
                                </label>
                                <input type="number" id="editDescuentoValor" name="descuento" class="form-input" min="0" step="0.01" value="0">
                            </div>
                            
                            <div class="price-preview">
                                <h4><i class="material-icons">calculate</i> Vista Previa de Precios</h4>
                                <div class="price-display">
                                    <div class="price-item">
                                        <span class="price-label">Precio Original:</span>
                                        <span id="precioOriginal" class="price-value">B/. 0.00</span>
                                    </div>
                                    <div class="price-item">
                                        <span class="price-label">Descuento:</span>
                                        <span id="montoDescuento" class="price-value">B/. 0.00</span>
                                    </div>
                                    <div class="price-item final">
                                        <span class="price-label">Precio Final:</span>
                                        <span id="precioFinal" class="price-value">B/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB: Imágenes -->
                    <div class="tab-pane" id="tab-imagenes">
                        <div class="image-section">
                            <div class="current-image">
                                <h4><i class="material-icons">image</i> Imagen Actual</h4>
                                <div class="image-preview-container">
                                    <img id="imagenActual" src="" alt="Imagen del producto" class="product-image-preview">
                                    <div class="image-overlay">
                                        <button type="button" id="btnCambiarImagen" class="btn btn-primary" disabled>
                                            <i class="material-icons">photo_camera</i> Cambiar Imagen
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="new-image" style="display: none;">
                                <h4><i class="material-icons">add_photo_alternate</i> Nueva Imagen</h4>
                                <div class="form-group">
                                    <input type="file" id="edit_imagen" name="imagen" class="form-input" accept="image/*">
                                    <div class="form-help">Recomendado: 800x800px, formato PNG o JPG</div>
                                </div>
                                <div id="nuevaImagenPreview" class="mt-2" style="display: none;">
                                    <img src="" alt="Vista previa" class="image-preview">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TAB: Estadísticas -->
                    <div class="tab-pane" id="tab-estadisticas">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="material-icons">shopping_cart</i>
                                </div>
                                <div class="stat-content">
                                    <h4>Ventas Totales</h4>
                                    <p id="ventasTotales" class="stat-number">0</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="material-icons">monetization_on</i>
                                </div>
                                <div class="stat-content">
                                    <h4>Ingresos Generados</h4>
                                    <p id="ingresosGenerados" class="stat-number">B/. 0.00</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="material-icons">visibility</i>
                                </div>
                                <div class="stat-content">
                                    <h4>Visualizaciones</h4>
                                    <p id="visualizaciones" class="stat-number">0</p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="material-icons">star</i>
                                </div>
                                <div class="stat-content">
                                    <h4>Calificación Promedio</h4>
                                    <p id="calificacionPromedio" class="stat-number">0.0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="recent-activity">
                            <h4><i class="material-icons">history</i> Actividad Reciente</h4>
                            <div id="actividadReciente" class="activity-list">
                                <p class="no-activity">No hay actividad reciente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="modal-footer">
            <div class="footer-actions">
                <button type="button" class="btn btn-secondary" id="btnCerrarProductoModal">
                    <i class="material-icons">close</i> Cerrar
                </button>
                <div class="edit-actions" style="display: none;">
                    <button type="button" class="btn btn-danger" id="btnEliminarProducto">
                        <i class="material-icons">delete</i> Eliminar
                    </button>
                    <button type="button" class="btn btn-warning" id="btnCancelarEdicion">
                        <i class="material-icons">cancel</i> Cancelar
                    </button>
                    <button type="submit" form="formEditarProducto" class="btn btn-primary">
                        <i class="material-icons">save</i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts adicionales
$scriptsAdicionales = [
    '/artesanoDigital/assets/js/dashboard-artesano.js'
];

// Capturar el contenido y incluir el layout
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>

 <script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencia al select de tiendas
    const selectTienda = document.getElementById('id_tienda');
    
    // Añadir la tienda del artesano al select si existe
    if (selectTienda) {
        <?php if ($tieneTienda): ?>
                // Crear y añadir la opción de la tienda
                const option = document.createElement('option');
                option.value = "<?= $tiendaExistente['id_tienda'] ?>";
                option.textContent = "<?= htmlspecialchars($tiendaExistente['nombre_tienda'] ?? 'Mi tienda') ?>";
                option.selected = true;
                selectTienda.appendChild(option);
        <?php else: ?>
                // Si no tiene tienda, mostrar mensaje en el select
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "Primero debes crear una tienda";
                option.disabled = true;
                option.selected = true;
                selectTienda.appendChild(option);
        <?php endif; ?>
    }
    
    // Vista previa de imagen del producto
    const inputImagen = document.getElementById('imagen');
    const imagenPreview = document.getElementById('imagen-preview');
    if (inputImagen && imagenPreview) {
        inputImagen.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagenPreview.classList.remove('d-none');
                    imagenPreview.querySelector('img').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
    
    // Mostrar modal de nueva tienda
    const btnCrearTienda = document.getElementById('btnCrearTienda');
    const modalCrearTienda = document.getElementById('modalCrearTienda');
    if (btnCrearTienda && modalCrearTienda) {
        btnCrearTienda.addEventListener('click', function() {
            modalCrearTienda.style.display = 'block';
        });
    }
    
    // Mostrar modal de nuevo producto
    const btnNuevoProducto = document.getElementById('btnNuevoProducto');
    const btnNuevoProductoTab = document.getElementById('btnNuevoProductoTab');
    const btnNuevoProductoEmpty = document.getElementById('btnNuevoProductoEmpty');
    const modalNuevoProducto = document.getElementById('modalNuevoProducto');
    
    [btnNuevoProducto, btnNuevoProductoTab, btnNuevoProductoEmpty].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                if (!btn.hasAttribute('disabled')) {
                    modalNuevoProducto.style.display = 'block';
                }
            });
        }
    });
    
    // Cerrar modales
    document.querySelectorAll('.close, .cancelar-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });
    
    // Cerrar modales cuando se hace clic fuera
    window.addEventListener('click', function(event) {
        document.querySelectorAll('.modal').forEach(modal => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    });
});

// JavaScript para modal de editar producto
document.addEventListener('DOMContentLoaded', function() {
    const modalEditarProducto = document.getElementById('modalEditarProducto');
    const formEditarProducto = document.getElementById('formEditarProducto');
    const btnEliminarProducto = document.getElementById('btnEliminarProducto');
    
    // Event listener para botones "Ver detalles" - Redirigir a página modularizada
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-ver-detalles')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.closest('.btn-ver-detalles');
            const productoId = btn.getAttribute('data-id');
            console.log('Redirigiendo a detalle de producto ID:', productoId);
            // Redirigir a la página de detalle modularizada
            window.location.href = `/artesanoDigital/artesano/detalle_productos?id=${productoId}`;
        }
    });
    
    // Función para abrir el modal y cargar datos del producto
    async function abrirModalEditarProducto(productoId) {
        try {
            // Mostrar modal
            modalEditarProducto.style.display = 'block';
            
            // Cargar datos del producto
            const response = await fetch(`/artesanoDigital/artesano/productos/crear?action=obtener&id=${productoId}`);
            const data = await response.json();
            
            if (data.success) {
                const producto = data.producto;
                
                // Llenar el formulario con los datos
                document.getElementById('editProductoId').value = producto.id_producto;
                document.getElementById('editNombre').value = producto.nombre;
                document.getElementById('editDescripcion').value = producto.descripcion;
                document.getElementById('editPrecio').value = producto.precio;
                document.getElementById('editStock').value = producto.stock;
                document.getElementById('editDescuento').value = producto.descuento || 0;
                document.getElementById('editActivo').checked = producto.activo == 1;
                
                // Establecer categoría
                const selectCategoria = document.getElementById('editCategoria');
                if (selectCategoria && producto.categoria) {
                    selectCategoria.value = producto.categoria;
                }
                
                // Cargar tienda
                const selectTienda = document.getElementById('editTienda');
                selectTienda.innerHTML = '';
                const option = document.createElement('option');
                option.value = producto.id_tienda;
                option.textContent = producto.nombre_tienda || 'Mi tienda';
                option.selected = true;
                selectTienda.appendChild(option);
                
                // Mostrar imagen actual si existe
                const imgActual = document.querySelector('#editImagenActual img');
                const contenedorImagenActual = document.getElementById('editImagenActual');
                
                if (producto.imagen && producto.imagen.trim() !== '') {
                    // Limpiar la ruta de la imagen
                    let rutaImagen = producto.imagen;
                    if (rutaImagen.startsWith('/')) {
                        rutaImagen = rutaImagen.substring(1);
                    }
                    
                    imgActual.src = '/artesanoDigital/' + rutaImagen;
                    imgActual.onerror = function() {
                        console.log('Error al cargar imagen:', this.src);
                        contenedorImagenActual.style.display = 'none';
                    };
                    imgActual.onload = function() {
                        console.log('Imagen cargada correctamente:', this.src);
                    };
                    contenedorImagenActual.style.display = 'block';
                } else {
                    contenedorImagenActual.style.display = 'none';
                }
                
            } else {
                alert('Error al cargar los datos del producto');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al cargar los datos del producto');
        }
    }
    
    // Vista previa de nueva imagen
    const inputEditImagen = document.getElementById('editImagen');
    const editImagenPreview = document.getElementById('editImagenPreview');
    if (inputEditImagen && editImagenPreview) {
        inputEditImagen.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    editImagenPreview.classList.remove('d-none');
                    editImagenPreview.querySelector('img').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            } else {
                editImagenPreview.classList.add('d-none');
            }
        });
    }
    
    // Manejar eliminación de producto
    if (btnEliminarProducto) {
        btnEliminarProducto.addEventListener('click', function() {
            const productoId = document.getElementById('editProductoId').value;
            const nombreProducto = document.getElementById('editNombre').value;
            
            if (confirm(`¿Estás seguro de que quieres eliminar el producto "${nombreProducto}"? Esta acción no se puede deshacer.`)) {
                eliminarProducto(productoId);
            }
        });
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
                alert('Producto eliminado exitosamente');
                modalEditarProducto.style.display = 'none';
                // Recargar la página para mostrar los cambios
                window.location.reload();
            } else {
                alert('Error al eliminar el producto: ' + (data.message || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
        }
    }
    
    // Manejar envío del formulario de edición
    if (formEditarProducto) {
        formEditarProducto.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(formEditarProducto);
            // Añadir la acción específica para actualizar
            formData.append('accion', 'actualizar_producto');
            
            fetch('/artesanoDigital/artesano/productos/crear', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Producto actualizado exitosamente');
                    modalEditarProducto.style.display = 'none';
                    // Recargar la página para mostrar los cambios
                    window.location.reload();
                } else {
                    alert('Error al actualizar el producto: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar el producto');
            });
        });
    }
    
    // Cerrar modal al hacer clic en cerrar o cancelar
    document.querySelectorAll('#modalEditarProducto .close, #modalEditarProducto .cancelar-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
            modalEditarProducto.style.display = 'none';
        });
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target == modalEditarProducto) {
            modalEditarProducto.style.display = 'none';
        }
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Referencias al modal y sus elementos
        const modal = document.getElementById('modalNuevoProducto');
        const btnNuevoProducto = document.getElementById('btnNuevoProducto');
        const cerrarModalBtns = document.querySelectorAll('.close, .cerrar-modal');

        // Checkbox y sección de descuentos
        const aplicarDescuentoCheck = document.getElementById('aplicarDescuento');
        const seccionDescuento = document.getElementById('seccionDescuento');

        // Radio buttons para tipo de descuento
        const descuentoPorcentajeRadio = document.getElementById('descuentoPorcentaje');
        const descuentoMontoRadio = document.getElementById('descuentoMonto');

        // Campos de descuento
        const camposPorcentaje = document.getElementById('camposPorcentaje');
        const camposMonto = document.getElementById('camposMonto');

        // Referencias a botones adicionales
        const btnNuevoProductoTab = document.getElementById('btnNuevoProductoTab');
        const btnNuevoProductoEmpty = document.getElementById('btnNuevoProductoEmpty');
        const modalCrearTienda = document.getElementById('modalCrearTienda');
        const btnCrearTienda = document.getElementById('btnCrearTienda');
        
        // Obtener el select para tiendas
        const selectTienda = document.getElementById('id_tienda');
        
        // Cargar las tiendas del usuario en el select
        function cargarTiendas() {
            // Limpiar el select
            if (selectTienda) {
                selectTienda.innerHTML = '';
                
                // Agregar option para la tienda actual del artesano
                <?php if ($tieneTienda && isset($tiendaExistente['id_tienda'])): ?>
                        const option = document.createElement('option');
                        option.value = '<?= htmlspecialchars($tiendaExistente['id_tienda']) ?>';
                        option.textContent = '<?= htmlspecialchars($tiendaExistente['nombre_tienda'] ?? 'Mi tienda') ?>';
                        option.selected = true;
                        selectTienda.appendChild(option);
                <?php endif; ?>
            }
        }
        
        // Abrir el modal al hacer clic en el botón "Nuevo Producto"
        btnNuevoProducto.addEventListener('click', function () {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Evitar scroll en el fondo
            cargarTiendas(); // Cargar las tiendas al abrir el modal
        });

        // Eventos para los botones adicionales
        if (btnNuevoProductoTab) {
            btnNuevoProductoTab.addEventListener('click', function () {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                cargarTiendas();
            });
        }
        
        if (btnNuevoProductoEmpty) {
            btnNuevoProductoEmpty.addEventListener('click', function () {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                cargarTiendas();
            });
        }
        
        if (btnCrearTienda) {
            btnCrearTienda.addEventListener('click', function () {
                modalCrearTienda.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        }
        
        // Cerrar el modal al hacer clic en la X o el botón Cancelar
        cerrarModalBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                modal.style.display = 'none';
                if (modalCrearTienda) modalCrearTienda.style.display = 'none';
                document.body.style.overflow = 'auto'; // Restaurar scroll
            });
        });

        // Cerrar los modales al hacer clic fuera de ellos
        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
            
            if (modalCrearTienda && event.target === modalCrearTienda) {
                modalCrearTienda.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Mostrar/ocultar sección de descuentos
        if (aplicarDescuentoCheck) {
            aplicarDescuentoCheck.addEventListener('change', function () {
                seccionDescuento.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Cambiar entre tipos de descuento
        if (descuentoPorcentajeRadio && descuentoMontoRadio) {
            descuentoPorcentajeRadio.addEventListener('change', function () {
                if (this.checked) {
                    camposPorcentaje.style.display = 'block';
                    camposMonto.style.display = 'none';
                }
            });

            descuentoMontoRadio.addEventListener('change', function () {
                if (this.checked) {
                    camposPorcentaje.style.display = 'none';
                    camposMonto.style.display = 'block';
                }
            });
        }

        // Validar formulario antes de enviar
        const formNuevoProducto = document.getElementById('formNuevoProducto');
        if (formNuevoProducto) {
            formNuevoProducto.addEventListener('submit', function (e) {
                // Validar que haya una tienda seleccionada
                const selectTienda = document.getElementById('id_tienda');
                if (!selectTienda.value || selectTienda.value === '') {
                    e.preventDefault();
                    alert('Primero debes crear una tienda antes de agregar productos. Usa el botón "Crear mi tienda ahora" que se encuentra arriba.');
                    return false;
                }
                
                const precio = parseFloat(document.getElementById('precio').value);
                const stock = parseInt(document.getElementById('stock').value);

                if (isNaN(precio) || precio <= 0) {
                    e.preventDefault();
                    alert('Por favor, introduce un precio válido mayor que 0');
                    return false;
                }

                if (isNaN(stock) || stock < 0) {
                    e.preventDefault();
                    alert('Por favor, introduce una cantidad en stock válida (0 o más)');
                    return false;
                }

                if (aplicarDescuentoCheck && aplicarDescuentoCheck.checked) {
                    if (descuentoPorcentajeRadio.checked) {
                        const porcentaje = parseFloat(document.getElementById('descuento_porcentaje').value);
                        if (isNaN(porcentaje) || porcentaje <= 0 || porcentaje >= 100) {
                            e.preventDefault();
                            alert('Por favor, introduce un porcentaje de descuento válido (entre 1 y 99)');
                            return false;
                        }
                    } else {
                        const monto = parseFloat(document.getElementById('descuento_monto').value);
                        if (isNaN(monto) || monto <= 0 || monto >= precio) {
                            e.preventDefault();
                            alert('El monto de descuento debe ser mayor que 0 y menor que el precio del producto');
                            return false;
                        }
                    }
                }

                // Si todo está bien, el formulario se enviará
                return true;
            });
        }

        // ===== CÓDIGO PARA MODAL DE GESTIÓN DE PEDIDOS =====
        const modalPedido = document.getElementById('modalDetallePedido');
        const btnVerPedidos = document.querySelectorAll('.ver-pedido');
        let pedidoActual = null;

        // Configurar pestañas del modal
        configurarPestanasModal();

        // Abrir modal de detalles de pedido
        btnVerPedidos.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.dataset.id;
                
                // Usar el nuevo sistema modular
                if (window.modalDetallePedidos) {
                    window.modalDetallePedidos.abrir(id);
                } else {
                    console.error('Modal de detalles de pedidos no está inicializado');
                    // Sistema modular no disponible - usar fallback silencioso
                }
            });
        });

        // Configurar eventos de cierre del modal
        configurarCierreModal();

        // Configurar eventos de los botones del modal
        configurarEventosModal();

        // ===== FUNCIONES PRINCIPALES =====

        function configurarPestanasModal() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remover clases activas
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
                    
                    // Activar pestaña seleccionada
                    this.classList.add('active');
                    const targetTab = this.getAttribute('data-tab');
                    document.getElementById(`tab-${targetTab}`).classList.add('active');
                });
            });
        }

        async function cargarDatosPedido(id) {
            // Mostrar loading
            mostrarCargando();
            
            try {
                // Simular carga de datos (aquí iría la llamada real a la API)
                const pedidoData = await obtenerDatosPedido(id);
                
                // Llenar datos en todas las pestañas
                llenarResumenPedido(pedidoData);
                llenarProductosPedido(pedidoData);
                llenarInfoCliente(pedidoData);
                llenarInfoEnvio(pedidoData);
                llenarSeguimiento(pedidoData);
                
                ocultarCargando();
            } catch (error) {
                ocultarCargando();
                throw error;
            }
        }

        function llenarResumenPedido(data) {
            // Actualizar número de pedido y estado
            document.getElementById('pedidoNumero').textContent = `#${String(data.id).padStart(5, '0')}`;
            document.getElementById('estadoActualBadge').textContent = formatearEstado(data.estado);
            document.getElementById('estadoActualBadge').className = `badge status-${data.estado}`;
            
            // Información general
            document.getElementById('fechaPedido').textContent = formatearFecha(data.fecha_pedido);
            document.getElementById('cambiarEstado').value = data.estado;
            document.getElementById('metodoPago').textContent = data.metodo_pago || 'No especificado';
            document.getElementById('totalPedido').textContent = `B/. ${formatearPrecio(data.total)}`;
            
            // Resumen financiero
            document.getElementById('subtotalPedido').textContent = `B/. ${formatearPrecio(data.subtotal || data.total)}`;
            document.getElementById('costoEnvio').textContent = `B/. ${formatearPrecio(data.costo_envio || 0)}`;
            document.getElementById('descuentosPedido').textContent = `B/. ${formatearPrecio(data.descuentos || 0)}`;
            document.getElementById('totalFinal').textContent = `B/. ${formatearPrecio(data.total)}`;
        }

        function llenarProductosPedido(data) {
            const tbody = document.getElementById('tablaProductosPedido');
            tbody.innerHTML = '';
            
            if (data.productos && data.productos.length > 0) {
                data.productos.forEach(producto => {
                    const precioUnitario = producto.precio_unitario || producto.precio || 0;
                    const subtotal = precioUnitario * producto.cantidad;
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <div class="producto-info">
                                <strong>${producto.nombre}</strong>
                                <small class="text-muted d-block">${producto.descripcion || ''}</small>
                            </div>
                        </td>
                        <td>B/. ${formatearPrecio(precioUnitario)}</td>
                        <td>${producto.cantidad}</td>
                        <td>B/. ${formatearPrecio(subtotal)}</td>
                        <td>
                            <span class="badge status-${data.estado}">${formatearEstado(data.estado)}</span>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay productos en este pedido</td></tr>';
            }
        }

        function llenarInfoCliente(data) {
            document.getElementById('clienteNombre').value = data.cliente_nombre || '';
            document.getElementById('clienteEmail').value = data.cliente_email || '';
            document.getElementById('clienteTelefono').value = data.cliente_telefono || '';
            document.getElementById('clienteDesde').textContent = formatearFecha(data.cliente_registro || data.fecha_pedido);
            
            // Cargar historial del cliente
            cargarHistorialCliente(data.cliente_id);
        }

        function llenarInfoEnvio(data) {
            document.getElementById('direccionEnvio').value = data.direccion_envio || '';
            document.getElementById('ciudadEnvio').value = data.ciudad_envio || '';
            document.getElementById('codigoPostal').value = data.codigo_postal || '';
            document.getElementById('notasEntrega').value = data.notas_entrega || '';
            
            // Información de envío
            document.getElementById('empresaEnvio').value = data.empresa_envio || '';
            document.getElementById('numeroSeguimiento').value = data.numero_seguimiento || '';
            document.getElementById('fechaEnvio').value = data.fecha_envio ? data.fecha_envio.split(' ')[0] : '';
            document.getElementById('fechaEstimadaEntrega').value = data.fecha_estimada_entrega ? data.fecha_estimada_entrega.split(' ')[0] : '';
        }

        function llenarSeguimiento(data) {
            const timeline = document.getElementById('timelinePedido');
            timeline.innerHTML = '';
            
            // Eventos predeterminados basados en el estado
            const eventos = generarEventosEstado(data);
            
            if (data.eventos) {
                eventos.push(...data.eventos);
            }
            
            // Ordenar eventos por fecha
            eventos.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
            
            eventos.forEach(evento => {
                const item = document.createElement('div');
                item.className = `timeline-item ${evento.tipo}`;
                item.innerHTML = `
                    <div class="timeline-item-header">
                        <span class="timeline-item-tipo">${evento.titulo}</span>
                        <span class="timeline-item-fecha">${formatearFechaHora(evento.fecha)}</span>
                    </div>
                    <div class="timeline-item-descripcion">${evento.descripcion}</div>
                `;
                timeline.appendChild(item);
            });
        }

        function configurarCierreModal() {
            const cerrarBtns = modalPedido.querySelectorAll('.close, #btnCerrarModal');
            
            cerrarBtns.forEach(btn => {
                btn.addEventListener('click', cerrarModal);
            });
            
            // Cerrar al hacer clic fuera
            modalPedido.addEventListener('click', function(e) {
                if (e.target === modalPedido) {
                    cerrarModal();
                }
            });
        }

        function configurarEventosModal() {
            // Actualizar estado
            document.getElementById('btnActualizarEstado').addEventListener('click', actualizarEstadoPedido);
            
            // Editar cliente
            document.getElementById('btnEditarCliente').addEventListener('click', editarInfoCliente);
            document.getElementById('btnGuardarCliente').addEventListener('click', guardarInfoCliente);
            
            // Actualizar dirección
            document.getElementById('btnActualizarDireccion').addEventListener('click', actualizarDireccion);
            
            // Actualizar envío
            document.getElementById('btnActualizarEnvio').addEventListener('click', actualizarInfoEnvio);
            
            // Seguimiento
            document.getElementById('btnAgregarEvento').addEventListener('click', mostrarFormEvento);
            document.getElementById('btnCancelarEvento').addEventListener('click', ocultarFormEvento);
            document.getElementById('btnGuardarEvento').addEventListener('click', guardarEvento);
            
            // Acciones del footer
            document.getElementById('btnMarcarCompleto').addEventListener('click', marcarComoCompletado);
            document.getElementById('btnEnviarNotificacion').addEventListener('click', enviarNotificacionCliente);
        }

        // ===== FUNCIONES DE EVENTOS =====

        async function actualizarEstadoPedido() {
            const nuevoEstado = document.getElementById('cambiarEstado').value;
            
            if (!nuevoEstado) {
                alert('Por favor seleccione un estado');
                return;
            }
            
            try {
                const response = await fetch(`/artesanoDigital/api/pedidos/${pedidoActual}/estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ estado: nuevoEstado })
                });
                
                if (response.ok) {
                    // Actualizar interfaz
                    document.getElementById('estadoActualBadge').textContent = formatearEstado(nuevoEstado);
                    document.getElementById('estadoActualBadge').className = `badge status-${nuevoEstado}`;
                    
                    // Agregar evento al timeline
                    agregarEventoTimeline({
                        tipo: 'estado_cambio',
                        titulo: 'Estado Actualizado',
                        descripcion: `El pedido cambió a: ${formatearEstado(nuevoEstado)}`,
                        fecha: new Date().toISOString()
                    });
                    
                    // Estado actualizado sin alert molesto
                    console.log('Estado actualizado correctamente');
                } else {
                    throw new Error('Error al actualizar el estado');
                }
            } catch (error) {
                console.error('Error:', error);
                // Error silencioso - el sistema modular maneja las notificaciones
            }
        }

        function editarInfoCliente() {
            document.getElementById('clienteTelefono').readOnly = false;
            document.getElementById('btnEditarCliente').style.display = 'none';
            document.getElementById('btnGuardarCliente').style.display = 'inline-block';
        }

        async function guardarInfoCliente() {
            const telefono = document.getElementById('clienteTelefono').value;
            
            try {
                // Aquí iría la llamada a la API para actualizar
                await new Promise(resolve => setTimeout(resolve, 500)); // Simular delay
                
                document.getElementById('clienteTelefono').readOnly = true;
                document.getElementById('btnEditarCliente').style.display = 'inline-block';
                document.getElementById('btnGuardarCliente').style.display = 'none';
                
                alert('Información del cliente actualizada');
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar la información del cliente');
            }
        }

        async function actualizarDireccion() {
            const direccion = document.getElementById('direccionEnvio').value;
            const ciudad = document.getElementById('ciudadEnvio').value;
            const codigoPostal = document.getElementById('codigoPostal').value;
            const notas = document.getElementById('notasEntrega').value;
            
            if (!direccion || !ciudad) {
                alert('La dirección y ciudad son requeridas');
                return;
            }
            
            try {
                // Llamada a la API
                await new Promise(resolve => setTimeout(resolve, 500));
                
                alert('Dirección de envío actualizada');
                
                agregarEventoTimeline({
                    tipo: 'envio',
                    titulo: 'Dirección Actualizada',
                    descripcion: `Dirección de envío modificada: ${direccion}, ${ciudad}`,
                    fecha: new Date().toISOString()
                });
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar la dirección');
            }
        }

        async function actualizarInfoEnvio() {
            const empresa = document.getElementById('empresaEnvio').value;
            const numeroSeguimiento = document.getElementById('numeroSeguimiento').value;
            const fechaEnvio = document.getElementById('fechaEnvio').value;
            const fechaEstimada = document.getElementById('fechaEstimadaEntrega').value;
            
            try {
                // Llamada a la API
                await new Promise(resolve => setTimeout(resolve, 500));
                
                alert('Información de envío actualizada');
                
                if (numeroSeguimiento) {
                    agregarEventoTimeline({
                        tipo: 'envio',
                        titulo: 'Número de Seguimiento Asignado',
                        descripcion: `Número de seguimiento: ${numeroSeguimiento} (${empresa})`,
                        fecha: new Date().toISOString()
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar la información de envío');
            }
        }

        function mostrarFormEvento() {
            document.getElementById('formAgregarEvento').style.display = 'block';
            document.getElementById('btnAgregarEvento').style.display = 'none';
        }

        function ocultarFormEvento() {
            document.getElementById('formAgregarEvento').style.display = 'none';
            document.getElementById('btnAgregarEvento').style.display = 'block';
            
            // Limpiar formulario
            document.getElementById('tipoEvento').value = '';
            document.getElementById('descripcionEvento').value = '';
        }

        function guardarEvento() {
            const tipo = document.getElementById('tipoEvento').value;
            const descripcion = document.getElementById('descripcionEvento').value;
            
            if (!tipo || !descripcion) {
                alert('Todos los campos son requeridos');
                return;
            }
            
            const tiposNombres = {
                'estado_cambio': 'Cambio de Estado',
                'envio': 'Información de Envío',
                'comunicacion': 'Comunicación con Cliente',
                'problema': 'Problema/Incidencia',
                'nota': 'Nota Interna'
            };
            
            agregarEventoTimeline({
                tipo: tipo,
                titulo: tiposNombres[tipo],
                descripcion: descripcion,
                fecha: new Date().toISOString()
            });
            
            ocultarFormEvento();
            alert('Evento agregado correctamente');
        }

        async function marcarComoCompletado() {
            if (confirm('¿Está seguro de marcar este pedido como completado?')) {
                document.getElementById('cambiarEstado').value = 'entregado';
                await actualizarEstadoPedido();
            }
        }

        async function enviarNotificacionCliente() {
            const email = document.getElementById('clienteEmail').value;
            
            if (!email) {
                alert('No hay email del cliente disponible');
                return;
            }
            
            try {
                // Simular envío de notificación
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                alert('Notificación enviada al cliente');
                
                agregarEventoTimeline({
                    tipo: 'comunicacion',
                    titulo: 'Notificación Enviada',
                    descripcion: `Notificación del estado del pedido enviada a: ${email}`,
                    fecha: new Date().toISOString()
                });
            } catch (error) {
                console.error('Error:', error);
                alert('Error al enviar la notificación');
            }
        }

        // ===== FUNCIONES AUXILIARES =====

        async function obtenerDatosPedido(id) {
            try {
                // Obtener datos del pedido
                const response = await fetch(`/artesanoDigital/api/pedidos/${id}`);
                if (!response.ok) {
                    throw new Error('Error al obtener datos del pedido');
                }
                
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.error || 'Error desconocido');
                }
                
                const pedidoData = result.data;
                
                // Obtener productos del pedido
                const productosResponse = await fetch(`/artesanoDigital/api/pedidos/${id}/detalles`);
                if (productosResponse.ok) {
                    const productosResult = await productosResponse.json();
                    if (productosResult.success) {
                        pedidoData.productos = productosResult.data.productos;
                        pedidoData.subtotal = productosResult.data.subtotal;
                        pedidoData.costo_envio = productosResult.data.costo_envio;
                        pedidoData.descuentos = productosResult.data.descuentos;
                        pedidoData.total = productosResult.data.total;
                    }
                }
                
                return {
                    id: pedidoData.id_pedido,
                    estado: pedidoData.estado,
                    fecha_pedido: pedidoData.fecha_pedido,
                    metodo_pago: pedidoData.metodo_pago,
                    total: pedidoData.total,
                    subtotal: pedidoData.subtotal,
                    costo_envio: pedidoData.costo_envio,
                    descuentos: pedidoData.descuentos,
                    cliente_nombre: pedidoData.cliente_nombre,
                    cliente_email: pedidoData.cliente_email,
                    cliente_telefono: pedidoData.cliente_telefono,
                    direccion_envio: pedidoData.direccion_envio,
                    notas_entrega: pedidoData.notas_entrega,
                    empresa_envio: pedidoData.empresa_envio,
                    numero_seguimiento: pedidoData.numero_seguimiento,
                    fecha_estimada_entrega: pedidoData.fecha_estimada_entrega,
                    productos: pedidoData.productos || []
                };
                
            } catch (error) {
                console.error('Error obteniendo datos del pedido:', error);
                throw error; // No usar datos simulados, mostrar el error real
            }
        }

        async function cargarHistorialCliente(clienteId) {
            try {
                // Simular historial
                const historial = [
                    { fecha: '2025-01-15', total: 42.50, estado: 'entregado' },
                    { fecha: '2024-12-20', total: 65.00, estado: 'entregado' },
                    { fecha: '2024-11-10', total: 38.75, estado: 'entregado' }
                ];
                
                const container = document.getElementById('historialCliente');
                container.innerHTML = '';
                
                if (historial.length > 0) {
                    historial.forEach(pedido => {
                        const item = document.createElement('div');
                        item.className = 'historial-item';
                        item.innerHTML = `
                            <div class="fecha">${formatearFecha(pedido.fecha)}</div>
                            <div>Pedido completado</div>
                            <div class="total">B/. ${formatearPrecio(pedido.total)}</div>
                        `;
                        container.appendChild(item);
                    });
                } else {
                    container.innerHTML = '<p class="text-center">Cliente nuevo, sin historial previo</p>';
                }
            } catch (error) {
                console.error('Error cargando historial:', error);
            }
        }

        function generarEventosEstado(data) {
            const eventos = [];
            
            eventos.push({
                tipo: 'estado_cambio',
                titulo: 'Pedido Creado',
                descripcion: 'El pedido fue creado por el cliente',
                fecha: data.fecha_pedido
            });
            
            if (data.estado !== 'pendiente') {
                eventos.push({
                    tipo: 'estado_cambio',
                    titulo: 'Estado Actualizado',
                    descripcion: `El pedido cambió a: ${formatearEstado(data.estado)}`,
                    fecha: data.fecha_pedido // En un caso real, tendríamos fecha de cada cambio
                });
            }
            
            return eventos;
        }

        function agregarEventoTimeline(evento) {
            const timeline = document.getElementById('timelinePedido');
            const item = document.createElement('div');
            item.className = `timeline-item ${evento.tipo}`;
            item.innerHTML = `
                <div class="timeline-item-header">
                    <span class="timeline-item-tipo">${evento.titulo}</span>
                    <span class="timeline-item-fecha">${formatearFechaHora(evento.fecha)}</span>
                </div>
                <div class="timeline-item-descripcion">${evento.descripcion}</div>
            `;
            
            // Insertar al principio (más reciente arriba)
            timeline.insertBefore(item, timeline.firstChild);
        }

        function mostrarModal() {
            modalPedido.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function cerrarModal() {
            modalPedido.style.display = 'none';
            document.body.style.overflow = 'auto';
            pedidoActual = null;
        }

        function mostrarCargando() {
            // Implementar loading state
        }

        function ocultarCargando() {
            // Ocultar loading state
        }

        function formatearEstado(estado) {
            const estados = {
                'pendiente': 'Pendiente',
                'en_proceso': 'En Proceso',
                'en_camino': 'En Camino',
                'entregado': 'Pedido Entregado',
                'cancelado': 'Cancelado'
            };
            return estados[estado] || estado;
        }

        function formatearFecha(fechaString) {
            const fecha = new Date(fechaString);
            return fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function formatearFechaHora(fechaString) {
            const fecha = new Date(fechaString);
            return fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatearPrecio(numero) {
            return Number(numero).toFixed(2);
        }

        // Manejo de pestañas del dashboard principal
        const tabLinks = document.querySelectorAll('.tabs-nav li');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabLinks.forEach(function (tabLink) {
            tabLink.addEventListener('click', function () {
                // Desactivar todas las pestañas
                tabLinks.forEach(function (link) {
                    link.classList.remove('active');
                });

                // Ocultar todos los contenidos
                tabPanes.forEach(function (pane) {
                    pane.classList.remove('active');
                });

                // Activar la pestaña seleccionada
                this.classList.add('active');

                // Mostrar el contenido correspondiente
                const tabId = this.getAttribute('data-tab');
                const targetPane = document.getElementById(tabId);
                if (targetPane) {
                    targetPane.classList.add('active');
                    
                    // Cargar contenido específico según la pestaña
                    if (tabId === 'tab-notificaciones') {
                        cargarNotificaciones();
                    } else if (tabId === 'tab-ventas') {
                        cargarPedidosRecientes();
                    }
                }
            });
        });

        // Funcionalidad del filtro de pedidos
        const filtroEstadoPedidos = document.getElementById('filtroEstadoPedidos');
        if (filtroEstadoPedidos) {
            filtroEstadoPedidos.addEventListener('change', function() {
                filtrarPedidosPorEstado(this.value);
            });
        }

        // Función para filtrar pedidos por estado
        function filtrarPedidosPorEstado(estado) {
            const filasPedidos = document.querySelectorAll('.pedidos-table tbody tr');
            
            filasPedidos.forEach(fila => {
                if (!estado) {
                    // Mostrar todos si no hay filtro
                    fila.style.display = '';
                } else {
                    // Mostrar solo los que coinciden con el estado
                    const claseEstado = fila.classList.contains(`estado-${estado}`);
                    fila.style.display = claseEstado ? '' : 'none';
                }
            });
            
            // Actualizar contador de resultados visibles
            const filasVisibles = document.querySelectorAll('.pedidos-table tbody tr:not([style*="display: none"])');
            const contador = document.querySelector('.pedidos-contador');
            if (contador) {
                contador.textContent = `Mostrando ${filasVisibles.length} pedidos`;
            }
        }

        // Función para cargar pedidos recientes
        function cargarPedidosRecientes() {
            // Esta función se puede expandir para hacer una llamada AJAX si es necesario
            console.log('Cargando pedidos recientes...');
        }

        // Actualizar contador cada 30 segundos
        setInterval(actualizarContadorNotificaciones, 30000);
    });
</script>



<script>
// JavaScript para manejar los botones de eliminar y editar productos
document.addEventListener('DOMContentLoaded', function() {

// ...existing code...

<script>
    if (buscarProductos) {
        buscarProductos.addEventListener('input', filtrarProductos);
    }
    if (filtroEstado) {
        filtroEstado.addEventListener('change', filtrarProductos);
    }
    if (filtroStock) {
        filtroStock.addEventListener('change', filtrarProductos);
    }
    if (btnRefrescarProductos) {
        btnRefrescarProductos.addEventListener('click', cargarProductos);
    }

    // Función para filtrar productos (cards y tabla)
    function filtrarProductos() {
        const busqueda = buscarProductos?.value.toLowerCase() || '';
        const stock = filtroStock?.value || '';
        
        // Buscar tanto filas de tabla como cards
        const elementos = document.querySelectorAll('.producto-row, .product-card');
        
        elementos.forEach(elemento => {
            // Seleccionar nombres de clase tanto para tabla como para cards
            const nombre = elemento.querySelector('.producto-nombre, .product-name')?.textContent.toLowerCase() || '';
            const descripcion = elemento.querySelector('.producto-descripcion, .product-description')?.textContent.toLowerCase() || '';
            const stockValue = parseInt(elemento.querySelector('.stock-badge, .stock-indicator')?.textContent.match(/\d+/)?.[0] || '0');
            
            let mostrar = true;
            
            // Filtro de búsqueda
            if (busqueda && !nombre.includes(busqueda) && !descripcion.includes(busqueda)) {
                mostrar = false;
            }
            
            // Filtro de stock
            if (stock === 'bajo' && stockValue >= 10) {
                mostrar = false;
            } else if (stock === 'agotado' && stockValue > 0) {
                mostrar = false;
            } else if (stock === 'normal' && stockValue < 10) {
                mostrar = false;
            }
            
            elemento.style.display = mostrar ? '' : 'none';
        });
    }

    // Variables globales para gestión de productos
    let productosData = [];
    let productoActual = null;

    // Función para cargar productos desde la API
    async function cargarProductos() {
        try {
            btnRefrescarProductos.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            const response = await fetch('/api/productos.php');
            if (!response.ok) {
                throw new Error('Error al cargar productos');
            }
            
            const result = await response.json();
            if (result.success) {
                productosData = result.data;
                renderizarListaProductos();
                actualizarEstadisticasProductos();
            } else {
                throw new Error(result.error || 'Error al cargar productos');
            }
            
        } catch (error) {
            console.error('Error al cargar productos:', error);
            alert('Error al cargar los productos: ' + error.message);
        } finally {
            btnRefrescarProductos.innerHTML = '<i class="fas fa-sync-alt"></i>';
        }
    }

    // Función para renderizar la lista de productos
    function renderizarListaProductos() {
        const container = document.querySelector('#productosTableBody');
        if (!container) return;

        let filtrados = [...productosData];

        // Aplicar filtros
        const busqueda = document.getElementById('buscarProducto')?.value.toLowerCase();
        const stock = document.getElementById('filtroStock')?.value;

        if (busqueda) {
            filtrados = filtrados.filter(p => 
                p.nombre.toLowerCase().includes(busqueda) ||
                p.descripcion.toLowerCase().includes(busqueda)
            );
        }

        if (stock !== 'todos') {
            filtrados = filtrados.filter(p => {
                if (stock === 'sin_stock') return p.stock == 0;
                if (stock === 'poco_stock') return p.stock > 0 && p.stock <= 5;
                if (stock === 'disponible') return p.stock > 5;
                return true;
            });
        }

        // Generar HTML de productos
        container.innerHTML = filtrados.map(producto => {
            const precioConDescuento = producto.precio - (producto.descuento || 0);
            const imagenSrc = producto.imagen ? producto.imagen : '/artesanoDigital/public/placeholder.jpg';
            
            return `
                <tr class="producto-fila">
                    <td>
                        <div class="producto-info">
                            <img src="${imagenSrc}" alt="${producto.nombre}" class="producto-mini-imagen">
                            <div>
                                <div class="producto-nombre">${producto.nombre}</div>
                                <div class="producto-descripcion-corta">${producto.descripcion.substring(0, 50)}...</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="precio-info">
                            ${(producto.descuento && producto.descuento > 0) ? 
                                `<span class="precio-original">B/. ${producto.precio.toFixed(2)}</span>
                                 <span class="precio-descuento">B/. ${precioConDescuento.toFixed(2)}</span>` :
                                `<span class="precio-actual">B/. ${producto.precio.toFixed(2)}</span>`
                            }
                        </div>
                    </td>
                    <td>
                        <span class="stock-badge ${producto.stock === 0 ? 'sin-stock' : producto.stock <= 5 ? 'poco-stock' : 'disponible'}">
                            ${producto.stock} unidades
                        </span>
                    </td>
                    <td>
                        <div class="actions-container">
                            <button class="action-btn view-btn" onclick="abrirModalProducto(${producto.id_producto})" title="Ver detalles">
                                <i data-lucide="eye"></i>
                                <span>Ver detalles</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Actualizar contador
        const contador = document.querySelector('.productos-contador');
        if (contador) {
            contador.textContent = `${filtrados.length} de ${productosData.length} productos`;
        }
    }

    // Función para actualizar estadísticas
    function actualizarEstadisticasProductos() {
        const totalProductos = productosData.length;
        const sinStock = productosData.filter(p => p.stock === 0).length;
        const activos = productosData.filter(p => p.activo == 1).length;
        
        // Actualizar contadores en la interfaz si existen
        const totalElement = document.querySelector('.stat-productos-total .stat-number');
        const sinStockElement = document.querySelector('.stat-sin-stock .stat-number');
        const activosElement = document.querySelector('.stat-productos-activos .stat-number');
        
        if (totalElement) totalElement.textContent = totalProductos;
        if (sinStockElement) sinStockElement.textContent = sinStock;
        if (activosElement) activosElement.textContent = activos;
    }

    <!-- CSS para el estilo minimalista del modal -->
    <style>
        /* Modal y diseño general */
        .modal {
            backdrop-filter: blur(4px);
        }
        
        .modal-dialog {
            max-width: 800px;
        }
        
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid #f1f3f4;
            padding: 24px;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Tabs minimalistas */
        .nav-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        
        .nav-tabs .nav-link:hover {
            background-color: #f9fafb;
            color: #374151;
        }
        
        .nav-tabs .nav-link.active {
            background-color: #fff;
            color: #111827;
            border-bottom: 3px solid #3b82f6;
        }
        
        /* Filas de detalles */
        .detail-row {
            display: flex;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-icon {
            width: 20px;
            height: 20px;
            color: #6b7280;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .detail-label {
            font-weight: 500;
            color: #374151;
            min-width: 120px;
            margin-right: 16px;
        }
        
        .detail-value {
            color: #1f2937;
            flex: 1;
        }
        
        /* Cards para precios y stock */
        .price-stock-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .price-card, .stock-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease;
        }
        
        .price-card:hover, .stock-card:hover {
            transform: translateY(-2px);
        }
        
        .price-card {
            border-left: 4px solid #10b981;
        }
        
        .stock-card {
            border-left: 4px solid #3b82f6;
        }
        
        .card-icon {
            width: 32px;
            height: 32px;
            margin: 0 auto 12px;
        }
        
        .price-card .card-icon {
            color: #10b981;
        }
        
        .stock-card .card-icon {
            color: #3b82f6;
        }
        
        .card-title {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .card-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
        
        /* Galería de imágenes */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }
        
        .image-item {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease;
        }
        
        .image-item:hover {
            transform: scale(1.02);
        }
        
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .no-image {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 150px;
            background-color: #f9fafb;
            color: #6b7280;
        }
        
        .no-image-icon {
            width: 32px;
            height: 32px;
            margin-bottom: 8px;
        }
        
        /* Botón de acción en tabla */
        .action-btn {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }
        
        .action-btn i {
            width: 16px;
            height: 16px;
        }
        
        /* Estado activo/inactivo */
        .estado-activo {
            color: #059669;
            font-weight: 600;
        }
        
        .estado-inactivo {
            color: #dc2626;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .price-stock-container {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .modal-dialog {
                margin: 10px;
                max-width: calc(100% - 20px);
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .detail-label {
                min-width: unset;
                margin-right: 0;
            }
        }
        
        /* Estilos para notificaciones - Diseño minimalista */
        .notificaciones-lista {
            max-height: 600px;
            overflow-y: auto;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .notificacion-item {
            display: flex;
            align-items: flex-start;
            padding: 20px;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }
        
        .notificacion-item:last-child {
            border-bottom: none;
        }
        
        .notificacion-item:hover {
            background-color: #f8fafc;
        }
        
        .notificacion-item.no-leida {
            background-color: #f9fafb;
            border-left: 3px solid #6b7280;
        }
        
        .notificacion-item.no-leida::before {
            content: '';
            position: absolute;
            right: 16px;
            top: 20px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #6b7280;
        }
        
        .notificacion-icono {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            flex-shrink: 0;
            font-size: 16px;
        }
        
        .notificacion-icono.success {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        
        .notificacion-icono.info {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        
        .notificacion-icono.warning {
            background-color: #f9fafb;
            color: #6b7280;
        }
        
        .notificacion-icono.primary {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        /* Colores específicos para tipos de notificaciones */
        .notificacion-icono.azul {
            background-color: #dbeafe;
            color: #2563eb;
        }
        
        .notificacion-icono.verde {
            background-color: #dcfce7;
            color: #16a34a;
        }
        
        .notificacion-icono.amarillo {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .notificacion-icono.rojo {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .notificacion-icono.gris {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        
        .notificacion-contenido {
            flex: 1;
            margin-right: 20px;
        }
        
        .notificacion-mensaje {
            font-size: 15px;
            color: #1f2937;
            margin-bottom: 6px;
            line-height: 1.5;
            font-weight: 400;
        }
        
        .notificacion-fecha {
            font-size: 13px;
            color: #9ca3af;
            font-weight: 400;
        }
        
        .notificacion-estado {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #6b7280;
            margin-left: auto;
            flex-shrink: 0;
            margin-top: 6px;
        }
        
        .notificacion-item.leida .notificacion-estado {
            background-color: transparent;
        }
        
        .notificaciones-vacia {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            background: #ffffff;
            border-radius: 8px;
        }
        
        .notificaciones-vacia i {
            font-size: 56px;
            margin-bottom: 20px;
            color: #e5e7eb;
        }
        
        .notificaciones-vacia h4 {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .notificaciones-vacia p {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 0;
        }
        
        .notificaciones-error {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
        }
        
        .notificaciones-error i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #ef4444;
        }
        
        .notificaciones-error h4 {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .notificaciones-error p {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 20px;
        }
        
        .btn-retry {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-retry:hover {
            background: #e5e7eb;
            border-color: #d1d5db;
            color: #374151;
        }
        
        .loading-spinner {
            padding: 60px 20px;
            color: #9ca3af;
            text-align: center;
            background: #ffffff;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .loading-spinner i {
            font-size: 24px;
            margin-bottom: 12px;
            color: #6b7280;
        }
        
        .pagination-container {
            margin: 24px 0;
            padding: 16px 0;
            border-top: 1px solid #f3f4f6;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 8px;
        }
        
        .page-item {
            display: inline-block;
        }
        
        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            color: #6b7280;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
            min-width: 40px;
            height: 40px;
        }
        
        .page-link:hover {
            background-color: #f9fafb;
            color: #374151;
            border-color: #d1d5db;
        }
        
        .page-item.active .page-link {
            background-color: #6b7280;
            color: white;
            border-color: #6b7280;
        }
        
        .page-item.disabled .page-link {
            color: #d1d5db;
            cursor: not-allowed;
            background-color: #f9fafb;
        }
        
        .page-item.disabled .page-link:hover {
            background-color: #f9fafb;
            color: #d1d5db;
            border-color: #e5e7eb;
        }

        /* ===== ESTILOS DEL MODAL ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 1000px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .modal-header h3 {
            margin: 0;
            color: #111827;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .btn-cerrar {
            background: none;
            border: none;
            color: #6b7280;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-cerrar:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-body {
            height: calc(90vh - 80px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-tabs {
            display: flex;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 24px;
        }

        .modal-tab-btn {
            background: none;
            border: none;
            padding: 16px 20px;
            color: #6b7280;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-tab-btn:hover {
            color: #374151;
            background: #f3f4f6;
        }

        .modal-tab-btn.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            background: white;
        }

        .modal-tab-content {
            display: none;
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-tab-content.active {
            display: block;
        }

        /* Resumen del producto */
        .resumen-producto {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .info-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .info-header h4 {
            margin: 0;
            color: #111827;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-producto {
            width: 100%;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 32px;
            margin-bottom: 24px;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-readonly {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item label {
            font-weight: 500;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .info-item span {
            color: #111827;
            font-weight: 600;
        }

        /* Seguimiento del producto */
        .seguimiento-producto {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .seguimiento-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .seguimiento-header h4 {
            margin: 0 0 8px 0;
            color: #111827;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .seguimiento-header p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .acciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .accion-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .accion-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .accion-card.peligrosa {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .accion-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .accion-card.peligrosa .accion-icon {
            background: #fecaca;
            color: #dc2626;
        }

        .accion-icon i {
            font-size: 1.25rem;
            color: #6b7280;
        }

        .accion-content h5 {
            margin: 0 0 8px 0;
            color: #111827;
            font-size: 1rem;
            font-weight: 600;
        }

        .accion-content p {
            margin: 0 0 16px 0;
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .estadisticas-producto {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .estadisticas-producto h5 {
            margin: 0 0 16px 0;
            color: #111827;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
        }

        /* Tab de Detalles */
        .producto-detalles-completos {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 32px;
            align-items: start;
        }

        .detalle-imagen .imagen-container {
            position: relative;
            width: 100%;
            height: 300px;
            background: #f9fafb;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }

        .detalle-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sin-imagen {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
        }

        .sin-imagen i {
            font-size: 3rem;
            margin-bottom: 8px;
        }

        .detalle-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-group label {
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-group span, .info-group p {
            color: #111827;
            font-size: 1rem;
            margin: 0;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .precio-valor {
            font-size: 1.25rem;
            font-weight: 700;
            color: #059669;
        }

        .descuento-valor {
            font-size: 1.1rem;
            font-weight: 600;
            color: #dc2626;
        }

        .stock-valor {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
        }

        .estado-valor {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        /* Tab de Editar */
        .form-editar {
            max-width: 600px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .toggle-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d1d5db;
            transition: 0.3s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #3b82f6;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        .imagen-upload {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .upload-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
            cursor: pointer;
        }

        .upload-placeholder i {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        /* Tab de Acciones */
        .acciones-lista {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 600px;
        }

        .accion-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .accion-item.peligrosa {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .accion-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .accion-info i {
            font-size: 1.5rem;
            color: #6b7280;
            width: 24px;
            text-align: center;
        }

        .accion-item.peligrosa .accion-info i {
            color: #dc2626;
        }

        .accion-info div h4 {
            margin: 0 0 4px 0;
            color: #111827;
            font-size: 1rem;
            font-weight: 600;
        }

        .accion-info div p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .btn-outline {
            background: white;
            border: 2px solid #e5e7eb;
            color: #374151;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .btn-danger {
            background: #dc2626;
            border: 2px solid #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal-container {
                margin: 10px;
                max-height: 95vh;
            }

            .producto-detalles-completos {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .detalle-imagen .imagen-container {
                height: 250px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .info-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
    </style>

    <script>
        // Inicializar iconos de Lucide
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <script>
        // Variables globales
        let modalProducto = null;
        let productoActual = null;

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            modalProducto = document.getElementById('modalDetalleProducto');
        });

        // Función para abrir modal de producto
        async function abrirModalProducto(idProducto) {
        if (!modalProducto || !idProducto) return;
        
        try {
            productoActual = idProducto;
            
            // Mostrar indicador de carga
            modalProducto.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Cargar datos del producto
            await cargarDatosProducto(idProducto);
            
            // Activar la pestaña general por defecto
            document.querySelectorAll('.producto-tabs .tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector('.producto-tabs .tab-btn[data-tab="general"]').classList.add('active');
            
            document.querySelectorAll('#modalDetalleProducto .tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById('tab-general').classList.add('active');
            
        } catch (error) {
            console.error('Error al abrir modal de producto:', error);
            mostrarNotificacion('Error al cargar los datos del producto', 'error');
            modalProducto.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Función para cargar datos del producto desde la API
    async function cargarDatosProducto(idProducto) {
        try {
            const response = await fetch(`/api/productos.php?path=${idProducto}`);
            if (!response.ok) {
                throw new Error('Error al cargar producto');
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.error || 'Error al cargar producto');
            }
            
            const producto = result.data;
            
            // Llenar datos en la vista de detalle
            document.getElementById('productoNombre').textContent = producto.nombre;
            document.getElementById('productoNombreDetalle').textContent = producto.nombre;
            document.getElementById('productoDescripcionDetalle').textContent = producto.descripcion;
            document.getElementById('productoIdDetalle').textContent = producto.id_producto;
            document.getElementById('productoFechaCreacionDetalle').textContent = formatearFecha(producto.fecha_creacion);
            
            // Mostrar estado
            const estadoElement = document.getElementById('productoActivoDetalle');
            estadoElement.textContent = producto.activo == 1 ? 'Activo' : 'Inactivo';
            estadoElement.className = `detail-value estado-badge ${producto.activo == 1 ? 'activo' : 'inactivo'}`;
            
            // Mostrar precios
            document.getElementById('productoPrecioDetalle').textContent = `B/. ${parseFloat(producto.precio).toFixed(2)}`;
            document.getElementById('productoDescuentoDetalle').textContent = `B/. ${parseFloat(producto.descuento || 0).toFixed(2)}`;
            const precioFinal = parseFloat(producto.precio) - parseFloat(producto.descuento || 0);
            document.getElementById('productoPrecioFinalDetalle').textContent = `B/. ${precioFinal.toFixed(2)}`;
            
            // Mostrar stock
            document.getElementById('productoStockDetalle').textContent = `${producto.stock} unidades`;
            const stockStatusElement = document.getElementById('productoStockStatusDetalle');
            
            if (producto.stock === 0) {
                stockStatusElement.textContent = 'Sin Stock';
                stockStatusElement.className = 'detail-value stock-badge sin-stock';
            } else if (producto.stock <= 5) {
                stockStatusElement.textContent = 'Stock Bajo';
                stockStatusElement.className = 'detail-value stock-badge poco-stock';
            } else {
                stockStatusElement.textContent = 'Disponible';
                stockStatusElement.className = 'detail-value stock-badge disponible';
            }
            
            // Cargar imagen si existe
            const imagenPreview = document.getElementById('imagenPreview');
            const noImagePlaceholder = document.getElementById('noImagePlaceholder');
            
            if (producto.imagen) {
                imagenPreview.src = producto.imagen;
                imagenPreview.style.display = 'block';
                noImagePlaceholder.style.display = 'none';
            } else {
                imagenPreview.style.display = 'none';
                noImagePlaceholder.style.display = 'flex';
            }
            
            // Inicializar iconos de Lucide después de cargar el contenido
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
        } catch (error) {
            console.error('Error al cargar datos del producto:', error);
            mostrarNotificacion('Error al cargar los datos del producto: ' + error.message, 'error');
            throw error;
        }
    }
    
    // Función para formatear fechas
    function formatearFecha(fechaString) {
        if (!fechaString) return '-';
        
        const fecha = new Date(fechaString);
        return fecha.toLocaleDateString('es-PA', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        }) + ' ' + fecha.toLocaleTimeString('es-PA', { 
            hour: '2-digit', 
            minute: '2-digit'
        });
    }

    // Función para limpiar formulario
    function limpiarFormularioProducto() {
        document.getElementById('productoNombreEdit').value = '';
        document.getElementById('productoDescripcion').value = '';
        document.getElementById('productoPrecio').value = '';
        document.getElementById('productoDescuentoValor').value = '0';
        document.getElementById('productoStock').value = '0';
        document.getElementById('productoActivoToggle').checked = true;
        
        // Limpiar imagen
        document.getElementById('imagenPreview').style.display = 'none';
        document.getElementById('imagenPreview').src = '';
        
        actualizarPreviewPrecios();
    }

    // Función para actualizar preview de precios
    function actualizarPreviewPrecios() {
        const precio = parseFloat(document.getElementById('productoPrecio')?.value || 0);
        const descuentoValor = parseFloat(document.getElementById('productoDescuentoValor')?.value || 0);
        
        // El descuento es siempre un monto fijo en la BD
        const descuentoAplicado = descuentoValor;
        
        const precioFinal = Math.max(0, precio - descuentoAplicado);
        
        document.getElementById('precioOriginalPreview').textContent = precio.toFixed(2);
        document.getElementById('descuentoPreview').textContent = descuentoAplicado.toFixed(2);
        document.getElementById('precioFinalPreview').textContent = precioFinal.toFixed(2);
    }

    // Función para subir imagen
    async function subirImagen(archivo) {
        const formData = new FormData();
        formData.append('imagen', archivo);
        
        try {
            const response = await fetch('/api/upload-imagen.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                return result.data.url;
            } else {
                throw new Error(result.error || 'Error al subir imagen');
            }
        } catch (error) {
            console.error('Error al subir imagen:', error);
            throw error;
        }
    }

    // Función para toggle estado del producto
    async function toggleEstadoProducto(idProducto, nuevoEstado) {
        try {
            const response = await fetch(`/api/productos.php?path=${idProducto}/estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ activo: nuevoEstado ? 1 : 0 })
            });
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.error || 'Error al actualizar estado');
            }
            
            // Actualizar datos locales
            const producto = productosData.find(p => p.id_producto == idProducto);
            if (producto) {
                producto.activo = nuevoEstado ? 1 : 0;
            }
            
            mostrarNotificacion('Estado actualizado correctamente', 'success');
            
        } catch (error) {
            console.error('Error al actualizar estado:', error);
            alert('Error al actualizar el estado del producto');
            // Revertir checkbox
            const checkbox = document.querySelector(`input[onchange*="${idProducto}"]`);
            if (checkbox) {
                checkbox.checked = !nuevoEstado;
            }
        }
    }

    // Función para duplicar producto
    async function duplicarProducto(idProducto) {
        if (!confirm('¿Deseas duplicar este producto?')) return;
        
        try {
            const response = await fetch(`/api/productos.php?path=${idProducto}/duplicar`, {
                method: 'POST'
            });
            
            const result = await response.json();
            if (result.success) {
                mostrarNotificacion('Producto duplicado correctamente', 'success');
                cargarProductos(); // Recargar lista
            } else {
                throw new Error(result.error || 'Error al duplicar producto');
            }
            
        } catch (error) {
            console.error('Error al duplicar producto:', error);
            alert('Error al duplicar el producto: ' + error.message);
        }
    }

    // Función para eliminar producto
    async function eliminarProducto(idProducto) {
        if (!confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')) return;
        
        try {
            const response = await fetch(`/api/productos.php?path=${idProducto}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                mostrarNotificacion('Producto eliminado correctamente', 'success');
                cargarProductos(); // Recargar lista
            } else {
                throw new Error(result.error || 'Error al eliminar producto');
            }
            
        } catch (error) {
            console.error('Error al eliminar producto:', error);
            alert('Error al eliminar el producto: ' + error.message);
        }
    }

    // Función para mostrar notificaciones
    function mostrarNotificacion(mensaje, tipo = 'info') {
        // Si existe el sistema global de notificaciones, usarlo
        if (typeof NotificacionesToast !== 'undefined') {
            // Convertir 'success' a 'exito' para compatibilidad con NotificacionesToast
            const tipoNotif = tipo === 'success' ? 'exito' : tipo;
            NotificacionesToast.mostrar(mensaje, tipoNotif);
            return;
        }
        
        // Fallback: Crear elemento de notificación local
        const notif = document.createElement('div');
        notif.className = `notificacion ${tipo}`;
        notif.innerHTML = `
            <div class="notificacion-contenido">
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${mensaje}</span>
            </div>
        `;
        
        // Agregar al contenedor de notificaciones
        let container = document.querySelector('.notificaciones-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notificaciones-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(notif);
        
        // Animar entrada
        setTimeout(() => notif.classList.add('show'), 100);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            notif.classList.remove('show');
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    }

    // Event listeners para actualizar preview de precios
        const descuentoValor = parseFloat(document.getElementById('productoDescuentoValor')?.value || 0);
        const descuentoTipo = document.getElementById('productoDescuentoTipo')?.value || 'fijo';
        
        let descuentoAplicado = 0;
        if (descuentoTipo === 'porcentaje') {
            descuentoAplicado = precio * (descuentoValor / 100);
        } else {
            descuentoAplicado = descuentoValor;
        }
        
        const precioFinal = Math.max(0, precio - descuentoAplicado);
        
        document.getElementById('precioOriginalPreview').textContent = precio.toFixed(2);
        document.getElementById('descuentoPreview').textContent = descuentoAplicado.toFixed(2);
        document.getElementById('precioFinalPreview').textContent = precioFinal.toFixed(2);
    }

    // Event listeners para actualizar preview de precios
    ['productoPrecio', 'productoDescuentoValor'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', actualizarPreviewPrecios);
        }
    });

    const descuentoTipoSelect = document.getElementById('productoDescuentoTipo');
    if (descuentoTipoSelect) {
        descuentoTipoSelect.addEventListener('change', actualizarPreviewPrecios);
    }

    // Manejo de pestañas en el modal de producto
    document.querySelectorAll('.producto-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            // Actualizar botones activos
            document.querySelectorAll('.producto-tabs .tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Mostrar contenido de pestaña
            document.querySelectorAll('#modalDetalleProducto .tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            const targetPane = document.getElementById(`tab-${tab}`);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });

    // Cerrar modal de producto
    if (btnCerrarProductoModal) {
        btnCerrarProductoModal.addEventListener('click', function() {
            modalProducto.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    // Event listeners para actualizar preview de precios
    ['productoPrecio', 'productoDescuentoValor'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', actualizarPreviewPrecios);
        }
    });

    const descuentoTipoSelect = document.getElementById('productoDescuentoTipo');
    if (descuentoTipoSelect) {
        descuentoTipoSelect.addEventListener('change', actualizarPreviewPrecios);
    }

    // Event listener para subida de imágenes
    const inputImagen = document.getElementById('productoImagen');
    if (inputImagen) {
        inputImagen.addEventListener('change', async function(e) {
            const archivo = e.target.files[0];
            if (!archivo) return;
            
            // Validar tipo de archivo
            if (!archivo.type.startsWith('image/')) {
                alert('Por favor selecciona una imagen válida');
                return;
            }
            
            // Validar tamaño (máximo 5MB)
            if (archivo.size > 5 * 1024 * 1024) {
                alert('La imagen es demasiado grande. Máximo 5MB');
                return;
            }
            
            try {
                // Mostrar preview inmediato
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagenPreview').src = e.target.result;
                    document.getElementById('imagenPreview').style.display = 'block';
                };
                reader.readAsDataURL(archivo);
                
                // Subir imagen
                const urlImagen = await subirImagen(archivo);
                document.getElementById('imagenPreview').dataset.url = urlImagen;
                
            } catch (error) {
                console.error('Error al procesar imagen:', error);
                alert('Error al subir la imagen: ' + error.message);
            }
        });
    }

    // Event listeners para filtros y búsqueda
    ['buscarProducto', 'filtroStock'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', renderizarListaProductos);
            element.addEventListener('change', renderizarListaProductos);
        }
    });

    // Manejo de pestañas en el modal de producto
    document.querySelectorAll('.producto-tabs .tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            // Actualizar botones activos
            document.querySelectorAll('.producto-tabs .tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Mostrar contenido de pestaña
            document.querySelectorAll('#modalDetalleProducto .tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            const targetPane = document.getElementById(`tab-${tab}`);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });

    // Cerrar modal de producto
    if (btnCerrarProductoModal) {
        btnCerrarProductoModal.addEventListener('click', function() {
            modalProducto.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    // Cerrar modal de producto
    if (btnCerrarProductoModal) {
        btnCerrarProductoModal.addEventListener('click', function() {
            modalProducto.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    // Cargar productos al inicializar
    cargarProductos();

    // === GESTIÓN DE NOTIFICACIONES ===
    
    // Variables para notificaciones
    let paginaActualNotificaciones = 1;
    let filtroActualNotificaciones = '';

    // Referencias DOM para notificaciones
    const notificacionesContainer = document.getElementById('notificacionesContainer');
    const filtroNotificaciones = document.getElementById('filtroNotificaciones');
    const marcarTodasLeidasBtn = document.getElementById('marcarTodasLeidas');
    const paginacionNotificaciones = document.getElementById('paginacionNotificaciones');

    // Event listeners para notificaciones
    if (filtroNotificaciones) {
        filtroNotificaciones.addEventListener('change', function() {
            filtroActualNotificaciones = this.value;
            paginaActualNotificaciones = 1;
            cargarNotificaciones();
        });
    }

    if (marcarTodasLeidasBtn) {
        marcarTodasLeidasBtn.addEventListener('click', marcarTodasNotificacionesComoLeidas);
    }

    // Función para cargar notificaciones
    async function cargarNotificaciones() {
        if (!notificacionesContainer) return;
        
        try {
            notificacionesContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando notificaciones...</div>';
            
            const params = new URLSearchParams({
                action: 'obtener',
                pagina: paginaActualNotificaciones
            });
            
            if (filtroActualNotificaciones && filtroActualNotificaciones !== 'todas') {
                params.append('tipo', filtroActualNotificaciones);
            }
            
            const response = await fetch(`/artesanoDigital/api/notificaciones.php?${params}`);
            const data = await response.json();
            
            if (data.success) {
                renderizarNotificaciones(data.data);
                if (data.paginacion) {
                    renderizarPaginacionNotificaciones(data.paginacion);
                }
            } else {
                throw new Error(data.error || 'Error al cargar notificaciones');
            }
            
        } catch (error) {
            console.error('Error al cargar notificaciones:', error);
            notificacionesContainer.innerHTML = `
                <div class="notificaciones-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Error al cargar notificaciones</h4>
                    <p>${error.message}</p>
                    <button onclick="cargarNotificaciones()" class="btn-retry">
                        <i class="fas fa-redo"></i> Intentar de nuevo
                    </button>
                </div>
            `;
        }
    }

    // Función para obtener icono según tipo de notificación
    function obtenerIconoNotificacion(tipo) {
        const iconos = {
            'nuevo_pedido': 'fas fa-shopping-cart',
            'estado_actualizado': 'fas fa-sync-alt',
            'stock_bajo': 'fas fa-exclamation-triangle',
            'pedido_confirmado': 'fas fa-check-circle'
        };
        return iconos[tipo] || 'fas fa-bell';
    }

    // Función para obtener color según tipo de notificación
    function obtenerColorNotificacion(tipo) {
        const colores = {
            'nuevo_pedido': 'azul',
            'estado_actualizado': 'amarillo',
            'stock_bajo': 'rojo',
            'pedido_confirmado': 'verde'
        };
        return colores[tipo] || 'gris';
    }

    // Función para renderizar notificaciones
    function renderizarNotificaciones(notificaciones) {
        if (!notificacionesContainer) return;
        
        if (notificaciones.length === 0) {
            notificacionesContainer.innerHTML = `
                <div class="notificaciones-vacia">
                    <i class="fas fa-bell-slash"></i>
                    <h4>No hay notificaciones</h4>
                    <p>No tienes notificaciones ${filtroActualNotificaciones ? 'de este tipo' : 'en este momento'}.</p>
                </div>
            `;
            return;
        }
        
        const html = notificaciones.map(notificacion => {
            const icono = obtenerIconoNotificacion(notificacion.tipo);
            const color = obtenerColorNotificacion(notificacion.tipo);
            
            return `
                <div class="notificacion-item ${!notificacion.leida ? 'no-leida' : 'leida'}" 
                     data-id="${notificacion.id_notificacion}"
                     onclick="marcarNotificacionComoLeida(${notificacion.id_notificacion})">
                    <div class="notificacion-icono ${color}">
                        <i class="${icono}"></i>
                    </div>
                    <div class="notificacion-contenido">
                        <div class="notificacion-mensaje">${notificacion.mensaje}</div>
                        <div class="notificacion-fecha">${notificacion.fecha_formateada}</div>
                    </div>
                    ${!notificacion.leida ? '<div class="notificacion-estado"></div>' : ''}
                </div>
            `;
        }).join('');
        
        notificacionesContainer.innerHTML = html;
    }

    // Función para renderizar paginación de notificaciones
    function renderizarPaginacionNotificaciones(paginacion) {
        if (!paginacionNotificaciones) return;
        
        if (paginacion.total_paginas <= 1) {
            paginacionNotificaciones.style.display = 'none';
            return;
        }
        
        paginacionNotificaciones.style.display = 'block';
        
        let html = '';
        
        // Botón anterior
        if (paginacion.pagina_actual > 1) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="cambiarPaginaNotificaciones(${paginacion.pagina_actual - 1})">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                     </li>`;
        }
        
        // Números de página
        for (let i = 1; i <= paginacion.total_paginas; i++) {
            if (i === paginacion.pagina_actual) {
                html += `<li class="page-item active">
                            <span class="page-link">${i}</span>
                         </li>`;
            } else {
                html += `<li class="page-item">
                            <a class="page-link" href="#" onclick="cambiarPaginaNotificaciones(${i})">${i}</a>
                         </li>`;
            }
        }
        
        // Botón siguiente
        if (paginacion.pagina_actual < paginacion.total_paginas) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="cambiarPaginaNotificaciones(${paginacion.pagina_actual + 1})">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                     </li>`;
        }
        
        paginacionNotificaciones.querySelector('.pagination').innerHTML = html;
    }

    // Función para cambiar página de notificaciones
    window.cambiarPaginaNotificaciones = function(nuevaPagina) {
        paginaActualNotificaciones = nuevaPagina;
        cargarNotificaciones();
    };

    // Función para marcar notificación como leída
    window.marcarNotificacionComoLeida = async function(idNotificacion) {
        try {
            const response = await fetch('/artesanoDigital/api/notificaciones.php?action=marcar-leida', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_notificacion: idNotificacion
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar visualmente la notificación
                const notificacionElement = document.querySelector(`[data-id="${idNotificacion}"]`);
                if (notificacionElement) {
                    notificacionElement.classList.remove('no-leida');
                    notificacionElement.classList.add('leida');
                    const estadoElement = notificacionElement.querySelector('.notificacion-estado');
                    if (estadoElement) {
                        estadoElement.remove();
                    }
                }
                
                // Actualizar contador de notificaciones
                actualizarContadorNotificaciones();
            } else {
                console.error('Error al marcar notificación como leída:', data.error);
            }
            
        } catch (error) {
            console.error('Error al marcar notificación como leída:', error);
        }
    };
                    }
                }
            }
            
        } catch (error) {
            console.error('Error al marcar notificación como leída:', error);
        }
    };

    // Función para marcar todas las notificaciones como leídas
    async function marcarTodasNotificacionesComoLeidas() {
        try {
            const response = await fetch('/artesanoDigital/api/notificaciones.php?action=marcar-todas-leidas', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarNotificacion('Todas las notificaciones marcadas como leídas', 'success');
                cargarNotificaciones(); // Recargar lista
                actualizarContadorNotificaciones(); // Actualizar contador
            } else {
                throw new Error(data.error || 'Error al actualizar notificaciones');
            }
            
        } catch (error) {
            console.error('Error al marcar todas las notificaciones como leídas:', error);
            mostrarNotificacion('Error al actualizar las notificaciones', 'error');
        }
    }

    // Función para limpiar notificaciones antiguas
    async function limpiarNotificacionesAntiguas(dias = 30) {
        if (!confirm(`¿Estás seguro de que deseas eliminar las notificaciones más antiguas que ${dias} días?`)) {
            return;
        }

        try {
            const response = await fetch('/artesanoDigital/api/limpiar_notificaciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ dias: dias })
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarNotificacion(data.mensaje, 'success');
                cargarNotificaciones(); // Recargar lista
                actualizarContadorNotificaciones(); // Actualizar contador
            } else {
                throw new Error(data.error || 'Error al limpiar notificaciones');
            }
            
        } catch (error) {
            console.error('Error al limpiar notificaciones:', error);
            mostrarNotificacion('Error al limpiar las notificaciones', 'error');
        }
    }

    // Cargar notificaciones cuando se activa la pestaña
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-tab="tab-notificaciones"]') || e.target.closest('[data-tab="tab-notificaciones"]')) {
            setTimeout(() => {
                cargarNotificaciones();
            }, 100);
        }
    });

    // Toggle de estado de productos en la tabla
    document.querySelectorAll('.estado-producto').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            const idProducto = this.dataset.id;
            const nuevoEstado = this.checked;
            
            try {
                // TODO: Implementar cambio de estado via API
                console.log(`Cambiar estado del producto ${idProducto} a ${nuevoEstado}`);
                
                // Actualizar etiqueta
                const label = this.closest('.estado-toggle').querySelector('.estado-label');
                if (label) {
                    label.textContent = nuevoEstado ? 'Activo' : 'Inactivo';
                }
                
            } catch (error) {
                console.error('Error al cambiar estado:', error);
                // Revertir el toggle en caso de error
                this.checked = !nuevoEstado;
            }
        });
    });

    // Manejo de upload de imágenes
    const imagenPrincipalArea = document.getElementById('imagenPrincipalArea');
    const imagenPrincipalInput = document.getElementById('imagenPrincipalInput');
    const imagenPrincipalPreview = document.getElementById('imagenPrincipalPreview');

    if (imagenPrincipalArea && imagenPrincipalInput) {
        imagenPrincipalArea.addEventListener('click', () => {
            imagenPrincipalInput.click();
        });

        imagenPrincipalInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imagenPrincipalPreview) {
                        imagenPrincipalPreview.src = e.target.result;
                        imagenPrincipalPreview.style.display = 'block';
                        imagenPrincipalArea.querySelector('.upload-placeholder').style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Contadores de caracteres para SEO
    ['productoMetaTitulo', 'productoMetaDescripcion'].forEach(id => {
        const element = document.getElementById(id);
        const counter = document.getElementById(id.replace('producto', '').replace('Meta', 'meta').toLowerCase() + 'Counter');
        
        if (element && counter) {
            element.addEventListener('input', function() {
                counter.textContent = this.value.length;
            });
        }
    });
    
    // Verificar si hay mensajes de toast en la sesión y mostrarlos
    <?php if (isset($_SESSION['toast_mensaje'])): ?>
                // Si existe la función Toast del sistema principal, usarla
                if (typeof window.Toast !== 'undefined') {
                    // Usar sistema Toast global
                    window.Toast.<?php echo $_SESSION['toast_tipo']; ?>('<?php echo addslashes($_SESSION['toast_mensaje']); ?>');
                } else if (typeof NotificacionesToast !== 'undefined') {
                    // Usar sistema NotificacionesToast
                    NotificacionesToast.<?php echo $_SESSION['toast_tipo']; ?>('<?php echo addslashes($_SESSION['toast_mensaje']); ?>');
                } else {
                    // Fallback a la función local
                    mostrarNotificacion('<?php echo addslashes($_SESSION['toast_mensaje']); ?>', '<?php echo $_SESSION['toast_tipo'] === 'exito' ? 'success' : $_SESSION['toast_tipo']; ?>');
                }
            <?php
            // Limpiar los mensajes de la sesión después de usarlos
            unset($_SESSION['toast_mensaje']);
            unset($_SESSION['toast_tipo']);
    endif; ?>
});
</script>

<!-- Incluir JavaScript para modales de pedidos -->
<script src="/artesanoDigital/assets/js/modal-detalle-pedidos.js"></script>
<!-- Incluir sistema de notificaciones -->
<script src="/artesanoDigital/assets/js/notificaciones.js"></script>
<script>
// Inicializar el sistema de notificaciones toast
document.addEventListener('DOMContentLoaded', function() {
    if (typeof NotificacionesToast !== 'undefined') {
        NotificacionesToast.init();
    }
    
    // Prevenir que cualquier formulario dentro del modal de productos se envíe de forma tradicional
    const modalProducto = document.getElementById('modalDetalleProducto');
    if (modalProducto) {
        const forms = modalProducto.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Envío de formulario tradicional prevenido');
                return false;
            });
        });
    }
});

// ===== SISTEMA DE MODAL DE PRODUCTOS =====
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalDetallesProducto');
    const btnCerrarModal = document.getElementById('btnCerrarModal');

    // Cerrar modal
    function cerrarModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    btnCerrarModal.addEventListener('click', cerrarModal);

    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            cerrarModal();
        }
    });

    // Manejar tabs del modal
    const tabBtns = modal.querySelectorAll('.tab-btn');
    const tabContents = modal.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remover active de todos los tabs
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Activar el tab seleccionado
            this.classList.add('active');
            document.getElementById('tab-' + targetTab).classList.add('active');
        });
    });

    // Función para abrir el modal con datos del producto
    window.abrirModalProducto = function(productoId) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Mostrar loading en el modal
        mostrarCargandoModal();
        
        // Hacer petición para obtener datos del producto
        fetch(`/artesanoDigital/controllers/ControladorProductosArtesano.php?action=obtener&id=${productoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    llenarDatosModal(data.producto);
                } else {
                    mostrarErrorModal(data.message || 'Error al cargar el producto');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarErrorModal('Error de conexión');
            });
    };

    function mostrarCargandoModal() {
        const detallesTab = document.getElementById('tab-detalles');
        detallesTab.innerHTML = `
            <div style="display: flex; justify-content: center; align-items: center; height: 300px;">
                <div style="text-align: center; color: #6b7280;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 16px;"></i>
                    <p>Cargando producto...</p>
                </div>
            </div>
        `;
    }

    function mostrarErrorModal(mensaje) {
        const detallesTab = document.getElementById('tab-detalles');
        detallesTab.innerHTML = `
            <div style="display: flex; justify-content: center; align-items: center; height: 300px;">
                <div style="text-align: center; color: #dc2626;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 16px;"></i>
                    <p>${mensaje}</p>
                </div>
            </div>
        `;
    }

    function llenarDatosModal(producto) {
        // Actualizar título del modal
        document.getElementById('modalTitulo').textContent = `Detalles: ${producto.nombre}`;
        
        // Llenar datos en el tab de detalles
        document.getElementById('modalProductoNombre').textContent = producto.nombre;
        document.getElementById('modalProductoDescripcion').textContent = producto.descripcion || 'Sin descripción';
        
        // Manejar imagen
        const modalImagen = document.getElementById('modalProductoImagen');
        const modalSinImagen = document.getElementById('modalSinImagen');
        
        if (producto.imagen) {
            modalImagen.src = `/artesanoDigital/${producto.imagen}`;
            modalImagen.style.display = 'block';
            modalSinImagen.style.display = 'none';
        } else {
            modalImagen.style.display = 'none';
            modalSinImagen.style.display = 'flex';
        }
        
        // Precios
        const precioOriginal = parseFloat(producto.precio);
        const descuento = parseFloat(producto.descuento || 0);
        const precioFinal = descuento > 0 ? Math.max(0, precioOriginal - descuento) : precioOriginal;
        
        if (descuento > 0) {
            document.getElementById('modalProductoPrecio').innerHTML = `
                <span style="text-decoration: line-through; color: #6b7280; font-size: 1rem; margin-right: 8px;">
                    B/. ${precioOriginal.toFixed(2)}
                </span>
                <span style="color: #059669; font-weight: 700; font-size: 1.25rem;">
                    B/. ${precioFinal.toFixed(2)}
                </span>
            `;
            document.getElementById('modalProductoDescuento').innerHTML = `<span style="color: #dc2626;">-B/. ${descuento.toFixed(2)}</span>`;
        } else {
            document.getElementById('modalProductoPrecio').innerHTML = `B/. ${precioOriginal.toFixed(2)}`;
            document.getElementById('modalProductoDescuento').innerHTML = `<span style="color: #6b7280;">Sin descuento</span>`;
        }
        
        // Stock
        const stock = parseInt(producto.stock);
        let stockClass = 'normal';
        let stockIcon = 'fa-boxes';
        
        if (stock <= 0) {
            stockClass = 'agotado';
            stockIcon = 'fa-exclamation-triangle';
        } else if (stock < 10) {
            stockClass = 'bajo';
            stockIcon = 'fa-exclamation-circle';
        }
        
        document.getElementById('modalProductoStock').innerHTML = `
            <i class="fas ${stockIcon}" style="margin-right: 8px;"></i>
            ${stock} unidades
        `;
        
        // Estado
        const estadoBadge = document.getElementById('modalProductoEstado');
        if (producto.activo == 1) {
            estadoBadge.textContent = 'Activo';
            estadoBadge.style.backgroundColor = '#d1fae5';
            estadoBadge.style.color = '#047857';
        } else {
            estadoBadge.textContent = 'Inactivo';
            estadoBadge.style.backgroundColor = '#fecaca';
            estadoBadge.style.color = '#b91c1c';
        }
        
        // Fecha de creación
        const fecha = new Date(producto.fecha_creacion);
        document.getElementById('modalProductoFecha').textContent = fecha.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Llenar formulario de edición
        llenarFormularioEdicion(producto);
        
        // Configurar botones de acciones
        configurarBotonesAcciones(producto);
        
        // Restaurar la estructura original del tab de detalles
        restaurarTabDetalles();
    }

    function restaurarTabDetalles() {
        const detallesTab = document.getElementById('tab-detalles');
        if (!detallesTab.querySelector('.producto-detalles-completos')) {
            detallesTab.innerHTML = `
                <div class="producto-detalles-completos">
                    <div class="detalle-imagen">
                        <div class="imagen-container">
                            <img id="modalProductoImagen" src="" alt="Imagen del producto">
                            <div id="modalSinImagen" class="sin-imagen" style="display: none;">
                                <i class="fas fa-image"></i>
                                <p>Sin imagen</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detalle-info">
                        <div class="info-group">
                            <label>Nombre:</label>
                            <span id="modalProductoNombre"></span>
                        </div>
                        
                        <div class="info-group">
                            <label>Descripción:</label>
                            <p id="modalProductoDescripcion"></p>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-group">
                                <label>Precio:</label>
                                <span id="modalProductoPrecio" class="precio-valor"></span>
                            </div>
                            <div class="info-group">
                                <label>Descuento:</label>
                                <span id="modalProductoDescuento" class="descuento-valor"></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-group">
                                <label>Stock:</label>
                                <span id="modalProductoStock" class="stock-valor"></span>
                            </div>
                            <div class="info-group">
                                <label>Estado:</label>
                                <span id="modalProductoEstado" class="estado-valor"></span>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <label>Fecha de creación:</label>
                            <span id="modalProductoFecha"></span>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    function llenarFormularioEdicion(producto) {
        document.getElementById('editProductoId').value = producto.id_producto;
        document.getElementById('editNombre').value = producto.nombre;
        document.getElementById('editDescripcion').value = producto.descripcion || '';
        document.getElementById('editPrecio').value = producto.precio;
        document.getElementById('editDescuentoValor').value = producto.descuento || 0;
        document.getElementById('editStock').value = producto.stock;
        document.getElementById('editActivo').checked = producto.activo == 1;
        
        // Preview de imagen existente en tab general
        const previewImagen = document.getElementById('previewImagen');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        
        if (producto.imagen) {
            previewImagen.src = `/artesanoDigital/${producto.imagen}`;
            previewImagen.style.display = 'block';
            uploadPlaceholder.style.display = 'none';
        } else {
            previewImagen.style.display = 'none';
            uploadPlaceholder.style.display = 'flex';
        }
        
        // Configurar imagen actual en tab de imágenes
        const imagenActual = document.getElementById('imagenActual');
        const btnCambiarImagen = document.getElementById('btnCambiarImagen');
        
        if (producto.imagen) {
            imagenActual.src = `/artesanoDigital/${producto.imagen}`;
            imagenActual.style.display = 'block';
            btnCambiarImagen.disabled = false;
        } else {
            imagenActual.src = '/artesanoDigital/public/placeholder.jpg';
            imagenActual.style.display = 'block';
            btnCambiarImagen.disabled = false;
        }
        
        // Limpiar preview de nueva imagen
        const nuevaImagenPreview = document.getElementById('nuevaImagenPreview');
        const editImagenInput = document.getElementById('edit_imagen');
        if (nuevaImagenPreview) {
            nuevaImagenPreview.style.display = 'none';
        }
        if (editImagenInput) {
            editImagenInput.value = '';
        }
        
        // Ocultar sección de nueva imagen
        const newImageSection = document.querySelector('.new-image');
        if (newImageSection) {
            newImageSection.style.display = 'none';
        }
    }

    function configurarBotonesAcciones(producto) {
        const btnDuplicar = document.getElementById('btnDuplicar');
        const btnVerEnTienda = document.getElementById('btnVerEnTienda');
        const btnEliminar = document.getElementById('btnEliminar');
        const btnToggleEdicion = document.getElementById('btnToggleEdicion');
        
        // Configurar duplicar
        btnDuplicar.onclick = () => duplicarProducto(producto.id_producto);
        
        // Configurar ver en tienda
        btnVerEnTienda.onclick = () => {
            window.open(`/artesanoDigital/views/cliente/producto.php?id=${producto.id_producto}`, '_blank');
        };
        
        // Configurar eliminar
        btnEliminar.onclick = () => confirmarEliminarProducto(producto.id_producto);
        
        // Configurar toggle de edición
        btnToggleEdicion.onclick = () => toggleModoEdicion();
        
        // Resetear al modo vista
        setModoVista();
    }

    function toggleModoEdicion() {
        const btnToggle = document.getElementById('btnToggleEdicion');
        const isEditMode = btnToggle.textContent.includes('Cancelar');
        
        if (isEditMode) {
            setModoVista();
        } else {
            setModoEdicion();
        }
    }

    function setModoVista() {
        const btnToggle = document.getElementById('btnToggleEdicion');
        const tabGeneral = document.querySelector('[data-tab="general"]');
        const tabPrecios = document.querySelector('[data-tab="precios"]');
        const editActions = document.querySelector('.edit-actions');
        
        // Cambiar texto del botón
        btnToggle.innerHTML = '<i class="material-icons">edit</i> Editar';
        btnToggle.className = 'btn btn-secondary';
        
        // Mostrar solo tab de información general
        document.querySelectorAll('.producto-tabs .tab-btn').forEach(btn => {
            if (btn.dataset.tab === 'general') {
                btn.style.display = 'flex';
                btn.click(); // Activar el tab
            } else {
                btn.style.display = 'none';
            }
        });
        
        // Ocultar campos de edición en el tab general
        document.querySelectorAll('#tab-general input, #tab-general textarea, #tab-general select').forEach(field => {
            field.style.display = 'none';
        });
        
        // Mostrar elementos de solo lectura
        document.querySelectorAll('#tab-general .info-group').forEach(group => {
            group.style.display = 'block';
        });
        
        // Ocultar acciones de edición
        if (editActions) editActions.style.display = 'none';
    }

    function setModoEdicion() {
        const btnToggle = document.getElementById('btnToggleEdicion');
        const editActions = document.querySelector('.edit-actions');
        
        // Cambiar texto del botón
        btnToggle.innerHTML = '<i class="material-icons">cancel</i> Cancelar';
        btnToggle.className = 'btn btn-warning';
        
        // Mostrar todos los tabs
        document.querySelectorAll('.producto-tabs .tab-btn').forEach(btn => {
            btn.style.display = 'flex';
        });
        
        // Mostrar campos de edición
        document.querySelectorAll('#tab-general input, #tab-general textarea, #tab-general select').forEach(field => {
            field.style.display = 'block';
        });
        
        // Ocultar elementos de solo lectura
        document.querySelectorAll('#tab-general .info-group').forEach(group => {
            group.style.display = 'none';
        });
        
        // Mostrar acciones de edición
        if (editActions) editActions.style.display = 'flex';
        
        // Activar el tab de edición (general)
        const tabGeneral = document.querySelector('[data-tab="general"]');
        if (tabGeneral) tabGeneral.click();
    }

    // Manejar preview de imagen
    const editImagenInput = document.getElementById('edit_imagen');
    if (editImagenInput) {
        editImagenInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('nuevaImagenPreview');
            
            if (file) {
                // Validar tipo de archivo
                if (!file.type.startsWith('image/')) {
                    mostrarNotificacion('Por favor selecciona una imagen válida', 'error');
                    return;
                }
                
                // Validar tamaño (máximo 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    mostrarNotificacion('La imagen es demasiado grande. Máximo 5MB', 'error');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = previewContainer.querySelector('img');
                    img.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Manejar botón cambiar imagen
    const btnCambiarImagen = document.getElementById('btnCambiarImagen');
    if (btnCambiarImagen) {
        btnCambiarImagen.addEventListener('click', function() {
            const newImageSection = document.querySelector('.new-image');
            if (newImageSection) {
                newImageSection.style.display = newImageSection.style.display === 'none' ? 'block' : 'none';
            }
        });
    }

    document.getElementById('editImagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewImagen = document.getElementById('previewImagen');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImagen.src = e.target.result;
                previewImagen.style.display = 'block';
                uploadPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    // Funciones de acciones
    // Función para mostrar notificaciones
    function mostrarNotificacion(mensaje, tipo = 'info') {
        // Crear container de notificaciones si no existe
        let container = document.getElementById('notificaciones-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificaciones-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
        
        // Crear notificación
        const notification = document.createElement('div');
        notification.style.cssText = `
            background: ${tipo === 'success' ? '#10b981' : tipo === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            pointer-events: auto;
            transform: translateX(400px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            max-width: 300px;
            word-wrap: break-word;
        `;
        
        const icon = tipo === 'success' ? 'check_circle' : tipo === 'error' ? 'error' : 'info';
        notification.innerHTML = `
            <i class="material-icons" style="font-size: 20px;">${icon}</i>
            <span>${mensaje}</span>
        `;
        
        container.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => notification.style.transform = 'translateX(0)', 10);
        
        // Auto-eliminar después de 4 segundos
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    function duplicarProducto(id) {
        if (confirm('¿Estás seguro de que quieres duplicar este producto?')) {
            // Mostrar loading
            mostrarNotificacion('Duplicando producto...', 'info');
            
            // Enviar solicitud de duplicación
            fetch('/artesanoDigital/controllers/ControladorProductosArtesano.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=duplicar&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('Producto duplicado correctamente', 'success');
                    cerrarModal();
                    // Recargar la tabla de productos
                    if (typeof cargarProductos === 'function') {
                        cargarProductos();
                    }
                } else {
                    mostrarNotificacion(data.message || 'Error al duplicar el producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión al duplicar', 'error');
            });
        }
    }

    function confirmarEliminarProducto(id) {
        if (confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
            // Mostrar loading
            mostrarNotificacion('Eliminando producto...', 'info');
            
            // Enviar solicitud de eliminación
            fetch('/artesanoDigital/controllers/ControladorProductosArtesano.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=eliminar&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('Producto eliminado correctamente', 'success');
                    cerrarModal();
                    // Recargar la tabla de productos
                    if (typeof cargarProductos === 'function') {
                        cargarProductos();
                    }
                } else {
                    mostrarNotificacion(data.message || 'Error al eliminar el producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión al eliminar', 'error');
            });
        }
    }

    // Manejar envío del formulario de edición
    document.getElementById('formEditarProducto').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('accion', 'actualizar_producto');
        
        // Enviar datos de edición
        fetch('/artesanoDigital/controllers/ControladorProductosArtesano.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar notificación de éxito
                mostrarNotificacion('Producto actualizado correctamente', 'success');
                cerrarModal();
                // Recargar la tabla de productos
                if (typeof cargarProductos === 'function') {
                    cargarProductos();
                }
            } else {
                mostrarNotificacion(data.message || 'Error al actualizar el producto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error de conexión', 'error');
        });
    });

    // Configurar tabs del modal de productos
    function configurarTabsModal() {
        const modalTabBtns = document.querySelectorAll('.modal-tab-btn');
        const modalTabContents = document.querySelectorAll('.modal-tab-content');
        
        modalTabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                // Quitar clase active de todos los botones y contenidos
                modalTabBtns.forEach(b => b.classList.remove('active'));
                modalTabContents.forEach(c => c.classList.remove('active'));
                
                // Activar el botón y contenido seleccionado
                this.classList.add('active');
                document.getElementById(`modal-tab-${targetTab}`).classList.add('active');
            });
        });
    }

    // Función para mostrar tabs del modal de pedidos
    function mostrarTab(tabName) {
        console.log('Mostrando tab:', tabName);
        
        // Ocultar todas las pestañas
        const allTabs = document.querySelectorAll('.tab-pane');
        allTabs.forEach(tab => {
            tab.style.display = 'none';
            tab.classList.remove('active');
        });
        
        // Quitar clase active de todos los botones
        const allTabBtns = document.querySelectorAll('.tab-btn');
        allTabBtns.forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Mostrar la pestaña seleccionada
        const selectedTab = document.getElementById(`tab-${tabName}`);
        const selectedBtn = document.querySelector(`[data-tab="${tabName}"]`);
        
        if (selectedTab) {
            selectedTab.style.display = 'block';
            selectedTab.classList.add('active');
        }
        
        if (selectedBtn) {
            selectedBtn.classList.add('active');
        }
    }


    // Configurar las pestañas del modal de pedidos al cargarse
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar tabs de productos
        configurarTabsModal();
        
        // Asegurar que el tab de resumen esté visible por defecto
        setTimeout(() => {
            mostrarTab('resumen');
        }, 100);
    });


});

</script>
