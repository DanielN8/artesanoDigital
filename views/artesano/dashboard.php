<?php
// Variables para el layout
$titulo = $titulo ?? 'Dashboard de Artesano - Artesano Digital';
$descripcion = $descripcion ?? 'Panel de administración para artesanos';

// Incluir modelo de Tienda
require_once dirname(__FILE__) . '/../../models/Tienda.php';
use Models\Tienda;

// Verificar si el artesano ya tiene una tienda
$modeloTienda = new Tienda();
$tiendaExistente = $modeloTienda->obtenerPorUsuario($_SESSION['usuario_id'] ?? 0);
$tieneTienda = !empty($tiendaExistente);

// Iniciar captura de contenido
ob_start();
?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

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
        <button id="btnNuevoProducto" class="dashboard-btn dashboard-btn-blue" <?= !$tieneTienda ? 'disabled title="Primero debes crear una tienda"' : '' ?>>
            <i class="fas fa-plus-circle"></i> Nuevo Producto
        </button>
        <a href="/artesanoDigital/artesano/tienda" class="dashboard-btn dashboard-btn-green">
            <i class="fas fa-store"></i> <?= $tieneTienda ? 'Administrar' : 'Crear' ?> Mi Tienda
        </a>
        <a href="/artesanoDigital/artesano/ventas" class="dashboard-btn dashboard-btn-indigo"><i
                class="fas fa-chart-line"></i> Análisis de Ventas</a>
    </div>

    <!-- Tabs de navegación -->
    <div class="dashboard-tabs">
        <ul class="tabs-nav">
            <li class="active" data-tab="tab-ventas"><i class="fas fa-store"></i> Mis Ventas</li>
            <li data-tab="tab-compras"><i class="fas fa-shopping-bag"></i> Mis Compras</li>
            <li data-tab="tab-productos"><i class="fas fa-box"></i> Mis Productos</li>
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
                            <select id="filtroEstadoPedidos" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="enviado">Enviados</option>
                                <option value="entregado">Entregados</option>
                                <option value="cancelado">Cancelados</option>
                            </select>
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

        <!-- TAB: Mis Productos -->
        <div id="tab-productos" class="tab-pane">
            <div class="dashboard-main">
                <div class="card">
                    <div class="card-header">
                        <h3>Mis Productos</h3>
                        <div class="card-header-actions">
                            <button id="btnNuevoProductoTab" class="dashboard-btn dashboard-btn-blue btn-sm" <?= !$tieneTienda ? 'disabled title="Primero debes crear una tienda"' : '' ?>>
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($productos ?? [])): ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open fa-3x"></i>
                                <h4>Aún no tienes productos</h4>
                                <p>Comienza a crear productos para mostrarlos en tu tienda</p>
                                <button id="btnNuevoProductoEmpty" class="dashboard-btn dashboard-btn-blue" <?= !$tieneTienda ? 'disabled title="Primero debes crear una tienda"' : '' ?>>
                                    <i class="fas fa-plus"></i> Crear mi primer producto
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="productos-grid">
                                <?php foreach ($productos as $producto): 
                                    // Check if we have a decorated product with discount
                                    $tieneDescuento = isset($producto['descuento']) && $producto['descuento'] > 0;
                                    $precioOriginal = $producto['precio'];
                                    $precioFinal = $tieneDescuento ? $precioOriginal * (1 - $producto['descuento']/100) : $precioOriginal;
                                ?>
                                <div class="producto-card" data-id="<?= $producto['id_producto'] ?>">
                                    <div class="producto-imagen">
                                        <img src="<?= !empty($producto['imagen']) ? '/artesanoDigital/' . $producto['imagen'] : '/artesanoDigital/public/placeholder.jpg' ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                        <?php if ($tieneDescuento): ?>
                                            <span class="badge-descuento"><?= $producto['descuento'] ?>% OFF</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="producto-info">
                                        <h4 class="producto-titulo"><?= htmlspecialchars($producto['nombre']) ?></h4>
                                        <div class="producto-tienda"><?= htmlspecialchars($producto['nombre_tienda'] ?? 'Mi tienda') ?></div>
                                        <div class="producto-precio">
                                            <?php if ($tieneDescuento): ?>
                                                <span class="precio-original">B/. <?= number_format($precioOriginal, 2) ?></span>
                                                <span class="precio-descuento">B/. <?= number_format($precioFinal, 2) ?></span>
                                            <?php else: ?>
                                                <span>B/. <?= number_format($precioOriginal, 2) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="producto-stock">Stock: <?= $producto['stock'] ?> unidades</div>
                                        <div class="producto-acciones">
                                            <button class="btn-editar-producto" data-id="<?= $producto['id_producto'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-eliminar-producto" data-id="<?= $producto['id_producto'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

<!-- Modal para nuevo producto -->
<div id="modalNuevoProducto" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Agregar Nuevo Producto</h2>
        <form id="formNuevoProducto" method="post" action="/artesanoDigital/controllers/ControladorProductosArtesano.php" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="crear_producto">
            
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" id="nombre" name="nombre" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-textarea" rows="4" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="precio">Precio (B/.)</label>
                    <input type="number" id="precio" name="precio" class="form-input" min="0" step="0.01" required>
                </div>
                <div class="form-group col-6">
                    <label for="stock">Stock Disponible</label>
                    <input type="number" id="stock" name="stock" class="form-input" min="0" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="id_tienda">Tienda</label>
                    <select id="id_tienda" name="id_tienda" class="form-select" required>
                        <!-- Se cargará dinámicamente -->
                    </select>
                </div>
                <div class="form-group col-6">
                    <label for="descuento">Descuento (%)</label>
                    <input type="number" id="descuento" name="descuento" class="form-input" min="0" max="100" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="imagen">Imagen del Producto</label>
                <input type="file" id="imagen" name="imagen" class="form-input" accept="image/*" required>
                <div class="form-help">Imagen principal del producto. Recomendado: 800x800px</div>
                <div id="imagen-preview" class="mt-2 d-none">
                    <img src="" alt="Vista previa" style="max-width: 100%; max-height: 200px;">
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-inline">
                    <input type="checkbox" id="activo" name="activo" value="1" checked>
                    Publicar producto inmediatamente
                </label>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary cancelar-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
            </div>
        </form>
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
<div id="modalDetallePedido" class="modal">
    <div class="modal-content">
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
        option.textContent = "<?= htmlspecialchars($tiendaExistente['nombre']) ?>";
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
</script>

<style>
    .dashboard-bg-white {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 16px 0 rgba(0, 0, 0, 0.07);
        padding: 2.5rem 2rem 2.5rem 2rem;
        max-width: 1400px;
        margin: 2rem auto;
    }

    .resumen-cards-horizontal {
        display: flex;
        gap: 2rem;
        margin-bottom: 2.5rem;
        flex-wrap: wrap;
    }

    .resumen-card {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        background: #f8fafc;
        border-radius: 1rem;
        box-shadow: 0 1px 6px 0 rgba(0, 0, 0, 0.04);
        padding: 1.5rem 2.2rem;
        min-width: 220px;
        flex: 1 1 220px;
    }

    .resumen-icon {
        font-size: 2.2rem;
        background: #fff;
        border-radius: 50%;
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.07);
    }

    .resumen-label {
        font-size: 1rem;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .resumen-value {
        font-size: 2rem;
        font-weight: 700;
        color: #22223b;
    }

    .resumen-blue .resumen-icon {
        color: #2563eb;
    }

    .resumen-green .resumen-icon {
        color: #16a34a;
    }

    .resumen-teal .resumen-icon {
        color: #14b8a6;
    }

    .resumen-yellow .resumen-icon {
        color: #eab308;
    }

    .dashboard-actions-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .dashboard-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.7rem 1.5rem;
        border-radius: 0.7rem;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: background 0.2s, box-shadow 0.2s;
        box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.04);
    }

    .dashboard-btn-blue {
        background: #2563eb;
        color: #fff;
    }

    .dashboard-btn-blue:hover {
        background: #1d4ed8;
    }

    .dashboard-btn-green {
        background: #16a34a;
        color: #fff;
    }

    .dashboard-btn-green:hover {
        background: #15803d;
    }

    .dashboard-btn-indigo {
        background: #6366f1;
        color: #fff;
    }

    .dashboard-btn-indigo:hover {
        background: #4f46e5;
    }

    .pedidos-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 1rem;
        overflow: hidden;
    }

    .pedidos-table th,
    .pedidos-table td {
        padding: 1rem 0.7rem;
        text-align: left;
    }

    .pedidos-table th {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.98rem;
        font-weight: 700;
        border-bottom: 2px solid #e5e7eb;
    }

    .pedidos-table tr {
        border-bottom: 1px solid #e5e7eb;
    }

    .pedidos-table tr:last-child {
        border-bottom: none;
    }

    .pedidos-table td {
        font-size: 1rem;
        color: #22223b;
    }

    .badge {
        display: inline-block;
        padding: 0.35em 0.8em;
        border-radius: 0.7em;
        font-size: 0.95em;
        font-weight: 600;
    }

    .status-nuevo {
        background: #e3f2fd;
        color: #2563eb;
    }

    .status-confirmado {
        background: #e8f5e9;
        color: #16a34a;
    }

    .status-enviado {
        background: #fff3e0;
        color: #eab308;
    }

    .status-entregado {
        background: #e8f5e9;
        color: #16a34a;
    }

    .status-cancelado {
        background: #ffebee;
        color: #b91c1c;
    }

    /* Estilos para el modal popup */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .modal-content {
        background-color: #fff;
        margin: 3% auto;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 700px;
        position: relative;
        animation: slideDown 0.4s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal .close {
        position: absolute;
        right: 1.5rem;
        top: 1rem;
        color: #64748b;
        font-size: 2rem;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
    }

    .modal .close:hover {
        color: #0f172a;
    }

    .modal h2 {
        color: #334155;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.625rem;
    }

    .form-row>.form-group {
        padding: 0 0.625rem;
        margin-bottom: 1.25rem;
    }

    .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }

    .col-12 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .form-input,
    .form-textarea,
    .form-select {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #0f172a;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        transition: border-color 0.15s ease-in-out;
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
    }

    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 500;
        color: #475569;
        text-align: center;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem 0 0 0.5rem;
        border-right: none;
    }

    .input-group .form-input {
        border-radius: 0 0.5rem 0.5rem 0;
        position: relative;
        flex: 1 1 auto;
        width: 1%;
    }

    .form-help {
        display: block;
        margin-top: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
    }

    .checkbox-inline,
    .radio-inline {
        display: flex;
        align-items: center;
        margin-right: 1.5rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
    }

    .checkbox-inline input[type="checkbox"],
    .radio-inline input[type="radio"] {
        margin-right: 0.5rem;
    }

    .form-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn {
        display: inline-block;
        font-weight: 500;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.5rem;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
    }

    .btn-primary {
        color: #fff;
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .btn-primary:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .btn-secondary {
        color: #475569;
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }

    .btn-secondary:hover {
        background-color: #e2e8f0;
        border-color: #cbd5e1;
    }

    .precio-original {
        text-decoration: line-through;
        color: #94a3b8;
        margin-right: 0.5rem;
    }

    .precio-descuento {
        font-weight: 700;
        color: #ef4444;
    }

    .badge-descuento {
        display: inline-block;
        padding: 0.25em 0.75em;
        background-color: #fee2e2;
        color: #ef4444;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        margin-left: 0.5rem;
        text-transform: uppercase;
    }

    /* Estilos para modal de detalles de pedido */
    .detalle-pedido-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .detalle-seccion {
        margin-bottom: 1.5rem;
    }

    .detalle-seccion h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .detalle-grid {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.5rem 1rem;
    }

    .detalle-grid .etiqueta {
        font-weight: 500;
        color: #64748b;
    }

    .detalle-grid .valor {
        color: #1e293b;
    }

    .resumen-pedido {
        margin-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
    }

    .resumen-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .resumen-item.total {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
        border-top: 1px solid #e2e8f0;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            padding: 1.5rem;
            margin: 5% auto;
        }

        .form-row>.form-group {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .col-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .form-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .detalle-pedido-info {
            grid-template-columns: 1fr;
        }
    }
</style>

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
                option.textContent = '<?= htmlspecialchars($tiendaExistente['nombre'] ?? 'Mi tienda') ?>';
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

        // Código para el modal de detalles de pedido
        const modalPedido = document.getElementById('modalDetallePedido');
        const btnVerPedidos = document.querySelectorAll('.ver-pedido');
        const cerrarModalPedidoBtns = modalPedido?.querySelectorAll('.close, .cerrar-modal');
        const pedidoId = document.getElementById('pedidoId');
        const infoPedido = document.getElementById('infoPedido');
        const infoCliente = document.getElementById('infoCliente');
        const tablaProductosPedido = document.getElementById('tablaProductosPedido')?.querySelector('tbody');
        const pedidoSubtotal = document.getElementById('pedidoSubtotal');
        const pedidoEnvio = document.getElementById('pedidoEnvio');
        const pedidoTotal = document.getElementById('pedidoTotal');
        const btnActualizarEstado = document.getElementById('btnActualizarEstado');

        // Abrir modal de detalles de pedido
        btnVerPedidos.forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const id = btn.dataset.id;
                const pedidoInfo = JSON.parse(btn.dataset.info);

                // Mostrar ID del pedido
                pedidoId.textContent = `#${id}`;

                // Llenar información del pedido
                infoPedido.innerHTML = `
                <span class="etiqueta">Fecha:</span>
                <span class="valor">${formatearFecha(pedidoInfo.fecha_pedido)}</span>
                
                <span class="etiqueta">Estado:</span>
                <span class="valor">
                    <span class="badge status-${pedidoInfo.estado}">${pedidoInfo.estado}</span>
                </span>
                
                <span class="etiqueta">Método de Pago:</span>
                <span class="valor">${pedidoInfo.metodo_pago || 'No especificado'}</span>
                
                <span class="etiqueta">Fecha de Envío:</span>
                <span class="valor">${pedidoInfo.fecha_envio ? formatearFecha(pedidoInfo.fecha_envio) : 'Pendiente'}</span>
            `;

                // Llenar información del cliente
                infoCliente.innerHTML = `
                <span class="etiqueta">Nombre:</span>
                <span class="valor">${pedidoInfo.cliente_nombre}</span>
                
                <span class="etiqueta">Email:</span>
                <span class="valor">${pedidoInfo.cliente_email || 'No disponible'}</span>
                
                <span class="etiqueta">Teléfono:</span>
                <span class="valor">${pedidoInfo.cliente_telefono || 'No disponible'}</span>
                
                <span class="etiqueta">Dirección:</span>
                <span class="valor">${pedidoInfo.direccion_envio || 'No disponible'}</span>
            `;

                // Obtener los detalles del pedido (productos) mediante AJAX
                try {
                    const response = await fetch(`/artesanoDigital/api/pedidos/${id}/detalles`);
                    if (!response.ok) {
                        throw new Error('Error al obtener los detalles del pedido');
                    }

                    const detalles = await response.json();

                    // Llenar tabla de productos
                    tablaProductosPedido.innerHTML = '';

                    if (detalles.items && detalles.items.length > 0) {
                        detalles.items.forEach(item => {
                            const subtotal = item.precio * item.cantidad;
                            tablaProductosPedido.innerHTML += `
                            <tr>
                                <td>${item.nombre}</td>
                                <td>${item.cantidad}</td>
                                <td>B/. ${formatearPrecio(item.precio)}</td>
                                <td>B/. ${formatearPrecio(subtotal)}</td>
                            </tr>
                        `;
                        });

                        // Actualizar resumen
                        pedidoSubtotal.textContent = `B/. ${formatearPrecio(detalles.subtotal || 0)}`;
                        pedidoEnvio.textContent = `B/. ${formatearPrecio(detalles.costo_envio || 0)}`;
                        pedidoTotal.textContent = `B/. ${formatearPrecio(pedidoInfo.total || 0)}`;
                    } else {
                        tablaProductosPedido.innerHTML = `
                        <tr>
                            <td colspan="4" class="empty-state">No hay detalles disponibles para este pedido</td>
                        </tr>
                    `;
                    }

                } catch (error) {
                    console.error('Error:', error);
                    tablaProductosPedido.innerHTML = `
                    <tr>
                        <td colspan="4" class="empty-state">Error al cargar los detalles del pedido</td>
                    </tr>
                `;
                }

                // Configurar botón de actualizar estado
                btnActualizarEstado.dataset.id = id;
                btnActualizarEstado.dataset.estadoActual = pedidoInfo.estado;

                // Mostrar modal
                modalPedido.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        });

        // Cerrar modal de detalles de pedido
        if (cerrarModalPedidoBtns) {
            cerrarModalPedidoBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    modalPedido.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            });
        }

        // Cerrar modal al hacer clic fuera de él
        window.addEventListener('click', function (event) {
            if (event.target === modalPedido) {
                modalPedido.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Botón de actualizar estado
        if (btnActualizarEstado) {
            btnActualizarEstado.addEventListener('click', async function () {
                const id = this.dataset.id;
                const estadoActual = this.dataset.estadoActual;

                // Aquí puedes implementar un diálogo o selector para elegir el nuevo estado
                const nuevoEstado = prompt(
                    'Seleccione el nuevo estado del pedido:\n' +
                    '- nuevo\n' +
                    '- confirmado\n' +
                    '- enviado\n' +
                    '- entregado\n' +
                    '- cancelado',
                    estadoActual
                );

                if (!nuevoEstado || nuevoEstado === estadoActual) {
                    return;
                }

                const estadosValidos = ['nuevo', 'confirmado', 'enviado', 'entregado', 'cancelado'];
                if (!estadosValidos.includes(nuevoEstado)) {
                    alert('Estado no válido');
                    return;
                }

                try {
                    const response = await fetch(`/artesanoDigital/api/pedidos/${id}/estado`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ estado: nuevoEstado }),
                    });

                    if (!response.ok) {
                        throw new Error('Error al actualizar el estado del pedido');
                    }

                    const result = await response.json();

                    if (result.success) {
                        // Actualizar estado en la interfaz
                        const btnPedido = document.querySelector(`button.ver-pedido[data-id="${id}"]`);
                        if (btnPedido) {
                            const badge = btnPedido.closest('tr').querySelector('.badge');
                            if (badge) {
                                badge.className = `badge status-${nuevoEstado}`;
                                badge.textContent = nuevoEstado;
                            }
                        }
                        // Actualizar el dataset del botón y la información del modal
                        this.dataset.estadoActual = nuevoEstado;
                        const badgeModal = document.querySelector('#infoPedido .badge');
                        if (badgeModal) {
                            badgeModal.className = `badge status-${nuevoEstado}`;
                            badgeModal.textContent = nuevoEstado;
                        }
                        alert('Estado actualizado correctamente');
                    } else {
                        throw new Error(result.message || 'Error desconocido');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al actualizar el estado: ' + error.message);
                }
            });
        }

        // Funciones auxiliares
        function formatearFecha(fechaString) {
            const fecha = new Date(fechaString);
            return fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function formatearPrecio(numero) {
            return Number(numero).toFixed(2);
        }

        // Manejo de pestañas
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
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
</script>

<style>
    /* Estilos para pestañas */
    .dashboard-tabs {
        margin: 2rem 0 1rem;
    }

    .tabs-nav {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        border-bottom: 1px solid #e9ecef;
    }

    .tabs-nav li {
        padding: 0.75rem 1.25rem;
        cursor: pointer;
        margin-right: 0.5rem;
        border-radius: 5px 5px 0 0;
        border: 1px solid transparent;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tabs-nav li i {
        font-size: 16px;
    }

    .tabs-nav li.active {
        background: #fff;
        border: 1px solid #e9ecef;
        border-bottom: 1px solid #fff;
        color: #2563eb;
        margin-bottom: -1px;
    }

    .tabs-nav li:hover:not(.active) {
        background: #f8f9fa;
    }

    .tab-pane {
        display: none;
        padding-top: 1rem;
    }

    .tab-pane.active {
        display: block;
    }

    .subtitle {
        color: #6b7280;
        font-size: 14px;
        margin-top: -8px;
        margin-bottom: 16px;
    }

    .empty-action {
        margin-top: 20px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .tabs-nav li {
            padding: 0.5rem 0.7rem;
            font-size: 14px;
        }
    }
    
    /* Estilos para alertas */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .alert-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .alert-content i {
        font-size: 1.5rem;
    }
    
    .alert-warning {
        background-color: #fff8e1;
        border-left: 4px solid #ffb300;
        color: #775500;
    }
    
    .alert-success {
        background-color: #e8f5e9;
        border-left: 4px solid #43a047;
        color: #1b5e20;
    }
    
    .alert-error {
        background-color: #fdecea;
        border-left: 4px solid #e53935;
        color: #b71c1c;
    }
    
    /* Estilos para productos */
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .producto-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .producto-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .producto-imagen {
        height: 180px;
        position: relative;
        overflow: hidden;
        background: #f5f5f5;
    }
    
    .producto-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .badge-descuento {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #e53935;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .producto-info {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    
    .producto-titulo {
        font-size: 1.1rem;
        font-weight: 600;
        margin-top: 0;
        margin-bottom: 0.5rem;
    }
    
    .producto-tienda {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 0.75rem;
    }
    
    .producto-precio {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .precio-original {
        text-decoration: line-through;
        color: #999;
        font-size: 0.9rem;
    }
    
    .precio-descuento {
        font-weight: 600;
        color: #e53935;
    }
    
    .producto-stock {
        font-size: 0.85rem;
        color: #666;
        margin-top: auto;
        margin-bottom: 1rem;
    }
    
    .producto-acciones {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    
    .btn-editar-producto, .btn-eliminar-producto {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-editar-producto {
        background: #f1f3f9;
        color: #4e73df;
    }
    
    .btn-editar-producto:hover {
        background: #4e73df;
        color: white;
    }
    
    .btn-eliminar-producto {
        background: #feeceb;
        color: #e74a3b;
    }
    
    .btn-eliminar-producto:hover {
        background: #e74a3b;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .empty-state i {
        color: #ccc;
        margin-bottom: 1rem;
    }
    
    .empty-state h4 {
        margin-bottom: 0.5rem;
        color: #444;
    }
    
    .empty-state p {
        color: #777;
        margin-bottom: 1.5rem;
    }
    
    .card-header-actions {
        display: flex;
        align-items: center;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>