<?php
/**
 * Vista de lista de productos para artesanos - Estilo Shopify
 */

// Variables para el layout
$titulo = $titulo ?? 'Mis Productos - Artesano Digital';
$descripcion = $descripcion ?? 'Gestiona tu catálogo de productos';

// Incluir dependencias necesarias
require_once dirname(__FILE__) . '/../../models/Tienda.php';
require_once dirname(__FILE__) . '/../../utils/GestorAutenticacion.php';
require_once dirname(__FILE__) . '/../../config/Database.php';

use Models\Tienda;
use Utils\GestorAutenticacion;

// Verificar autenticación antes de continuar
$gestorAuth = GestorAutenticacion::obtenerInstancia();
if (!$gestorAuth->estaAutenticado()) {
    header('Location: /artesanoDigital/login?redirect=artesano/mis_productos');
    exit;
}

$usuario = $gestorAuth->obtenerUsuarioActual();
if (!isset($usuario['tipo_usuario']) || $usuario['tipo_usuario'] !== 'artesano') {
    header('Location: /artesanoDigital/dashboard/cliente');
    exit;
}

// Obtener datos del artesano y sus productos
$modeloTienda = new Tienda();
$tienda = $modeloTienda->obtenerPorUsuario($usuario['id_usuario']);
$productos = [];

if ($tienda) {
    // Obtener productos de la tienda
    $db = \Config\Database::obtenerInstancia();
    $conexion = $db->obtenerConexion();
    
    $sql = "SELECT p.*, t.nombre_tienda 
            FROM productos p 
            LEFT JOIN tiendas t ON p.id_tienda = t.id_tienda 
            WHERE p.id_tienda = ? 
            ORDER BY p.fecha_creacion DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tienda['id_tienda']]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Iniciar captura de contenido
ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f6f6f7;
            color: #202223;
            line-height: 1.5;
        }
        
        /* Header estilo Shopify */
        .shopify-header {
            background: #ffffff;
            border-bottom: 1px solid #e1e3e5;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .back-btn {
            color: #6d7175;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.15s ease;
        }
        
        .back-btn:hover {
            background-color: #f1f2f3;
            color: #202223;
        }
        
        .page-title {
            font-size: 20px;
            font-weight: 600;
            color: #202223;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn-primary {
            background: #008060;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.15s ease;
        }
        
        .btn-primary:hover {
            background: #007a5a;
        }
        
        .btn-secondary {
            background: white;
            color: #202223;
            border: 1px solid #c9cccf;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        
        .btn-secondary:hover {
            background: #f1f2f3;
        }
        
        /* Container principal */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e1e3e5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: 600;
            color: #202223;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #6d7175;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Filtros y búsqueda */
        .filters-container {
            background: white;
            border: 1px solid #e1e3e5;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid #c9cccf;
            border-radius: 6px;
            font-size: 14px;
            background: white;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #008060;
            box-shadow: 0 0 0 2px rgba(0, 128, 96, 0.1);
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6d7175;
        }
        
        .filter-select {
            padding: 10px 16px;
            border: 1px solid #c9cccf;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            min-width: 150px;
        }
        
        /* Tabla de productos estilo Shopify */
        .products-table-container {
            background: white;
            border: 1px solid #e1e3e5;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th {
            background: #f6f6f7;
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #6d7175;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e1e3e5;
        }
        
        .products-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f2f3;
            vertical-align: middle;
        }
        
        .products-table tr:last-child td {
            border-bottom: none;
        }
        
        .products-table tr:hover {
            background-color: #f9fafb;
        }
        
        /* Columna de producto */
        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #e1e3e5;
        }
        
        .product-placeholder {
            width: 50px;
            height: 50px;
            background: #f1f2f3;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6d7175;
        }
        
        .product-details h4 {
            font-size: 14px;
            font-weight: 500;
            color: #202223;
            margin-bottom: 2px;
        }
        
        .product-description {
            font-size: 13px;
            color: #6d7175;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-active {
            background: #d1f7c4;
            color: #0f5132;
        }
        
        .status-inactive {
            background: #f3f4f6;
            color: #6d7175;
        }
        
        /* Stock indicators */
        .stock-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        
        .stock-normal {
            color: #008060;
        }
        
        .stock-bajo {
            color: #ffa500;
        }
        
        .stock-agotado {
            color: #dc2626;
        }
        
        /* Precios */
        .price-container {
            font-size: 14px;
            font-weight: 500;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #6d7175;
            margin-right: 8px;
        }
        
        .final-price {
            color: #dc2626;
            font-weight: 600;
        }
        
        .current-price {
            color: #202223;
        }
        
        /* Acciones */
        .actions-container {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            background: none;
            border: 1px solid #c9cccf;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            color: #6d7175;
            transition: all 0.15s ease;
        }
        
        .btn-action:hover {
            background: #f1f2f3;
            color: #202223;
        }
        
        .btn-edit {
            background: #008060;
            color: white;
            border-color: #008060;
        }
        
        .btn-edit:hover {
            background: #007a5a;
            color: white;
        }
        
        .btn-delete {
            background: #dc2626 !important;
            color: white !important;
            border-color: #dc2626 !important;
        }
        
        .btn-delete:hover {
            background: #b91c1c !important;
            color: white !important;
        }
        
        /* Modal de edición */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-container {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e1e3e5;
            background: #f6f6f7;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #202223;
            font-size: 18px;
            font-weight: 600;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #6d7175;
            padding: 5px;
        }
        
        .modal-close:hover {
            color: #202223;
        }
        
        .modal-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #202223;
            font-size: 14px;
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #c9cccf;
            border-radius: 6px;
            font-size: 14px;
            background: white;
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: #008060;
            box-shadow: 0 0 0 2px rgba(0, 128, 96, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .form-group.col-6 {
            grid-column: span 1;
        }
        
        .form-help {
            font-size: 12px;
            color: #6d7175;
            margin-top: 5px;
        }
        
        .checkbox-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .checkbox-inline input[type="checkbox"] {
            margin: 0;
        }
        
        .btn-danger {
            background: #d72c0d;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.15s ease;
        }
        
        .btn-danger:hover {
            background: #bf2600;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px;
            border-top: 1px solid #e1e3e5;
            background: #f6f6f7;
        }
        
        .btn-cancel {
            background: white;
            color: #6d7175;
            border: 1px solid #c9cccf;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn-cancel:hover {
            background: #f1f2f3;
        }
        
        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
        }
        
        .toast {
            background: white;
            border: 1px solid #e1e3e5;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 300px;
            animation: slideInToast 0.3s ease;
        }
        
        .toast.success {
            border-left: 4px solid #008060;
        }
        
        .toast.error {
            border-left: 4px solid #dc2626;
        }
        
        .toast.warning {
            border-left: 4px solid #ffa500;
        }
        
        @keyframes slideInToast {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            color: #6d7175;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #c9cccf;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #202223;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            font-size: 14px;
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 16px;
            }
            
            .shopify-header {
                padding: 16px;
            }
            
            .filters-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .products-table {
                font-size: 13px;
            }
            
            .products-table th,
            .products-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Header estilo Shopify -->
    <header class="shopify-header">
        <div class="header-left">
            <a href="/artesanoDigital/dashboard/artesano" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Dashboard
            </a>
            <h1 class="page-title">Productos</h1>
        </div>
        
        <div class="header-actions">
            <button class="btn-secondary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i>
                Actualizar
            </button>
            <button class="btn-primary" onclick="agregarProducto()">
                <i class="fas fa-plus"></i>
                Agregar producto
            </button>
        </div>
    </header>

    <div class="main-container">
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?= count($productos) ?></div>
                <div class="stat-label">Total Productos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($productos, fn($p) => $p['activo'] == 1)) ?></div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($productos, fn($p) => $p['stock'] <= 0)) ?></div>
                <div class="stat-label">Sin Stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] < 10)) ?></div>
                <div class="stat-label">Stock Bajo</div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="filters-container">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchProducts" class="search-input" placeholder="Buscar productos...">
            </div>
            
            <select id="filterStatus" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
            
            <select id="filterStock" class="filter-select">
                <option value="">Todo el stock</option>
                <option value="normal">Stock normal</option>
                <option value="bajo">Stock bajo (< 10)</option>
                <option value="agotado">Sin stock</option>
            </select>
        </div>

        <!-- Tabla de productos -->
        <div class="products-table-container">
            <?php if (empty($productos)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No tienes productos aún</h3>
                    <p>Comienza agregando tu primer producto para empezar a vender en tu tienda de artesanías.</p>
                    <button class="btn-primary" onclick="agregarProducto()">
                        <i class="fas fa-plus"></i>
                        Agregar primer producto
                    </button>
                </div>
            <?php else: ?>
                <table class="products-table" id="productsTable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Estado</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th>Fecha creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): 
                            $tieneDescuento = isset($producto['descuento']) && $producto['descuento'] > 0;
                            $precioOriginal = floatval($producto['precio']);
                            $descuento = floatval($producto['descuento'] ?? 0);
                            $precioFinal = $tieneDescuento ? max(0, $precioOriginal - $descuento) : $precioOriginal;

                            $stockStatus = 'normal';
                            if ($producto['stock'] <= 0) {
                                $stockStatus = 'agotado';
                            } elseif ($producto['stock'] < 10) {
                                $stockStatus = 'bajo';
                            }
                        ?>
                            <tr data-id="<?= $producto['id_producto'] ?>" class="product-row">
                                <td>
                                    <div class="product-info">
                                        <?php if (!empty($producto['imagen'])): ?>
                                            <img src="/artesanoDigital/<?= htmlspecialchars($producto['imagen']) ?>" 
                                                 alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                                 class="product-image">
                                        <?php else: ?>
                                            <div class="product-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="product-details">
                                            <h4><?= htmlspecialchars($producto['nombre']) ?></h4>
                                            <div class="product-description">
                                                <?= htmlspecialchars($producto['descripcion'] ?? 'Sin descripción') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="status-badge status-<?= $producto['activo'] ? 'active' : 'inactive' ?>">
                                        <?= $producto['activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <div class="stock-indicator stock-<?= $stockStatus ?>">
                                        <i class="fas fa-boxes"></i>
                                        <?= $producto['stock'] ?> unidades
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="price-container">
                                        <?php if ($tieneDescuento): ?>
                                            <span class="original-price">B/. <?= number_format($precioOriginal, 2) ?></span>
                                            <span class="final-price">B/. <?= number_format($precioFinal, 2) ?></span>
                                        <?php else: ?>
                                            <span class="current-price">B/. <?= number_format($precioOriginal, 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <td>
                                    <?= date('d/m/Y', strtotime($producto['fecha_creacion'])) ?>
                                </td>
                                
                                <td>
                                    <div class="actions-container">
                                        <button class="btn-action btn-edit" onclick="abrirModalEditar(<?= $producto['id_producto'] ?>)" title="Editar producto">
                                            <i class="fas fa-edit"></i>
                                            Editar
                                        </button>
                                        <button class="btn-action btn-delete" onclick="eliminarProducto(<?= $producto['id_producto'] ?>)" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de edición -->
    <div id="modalEditar" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2>Editar Producto</h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            
            <form id="formEditarProducto" onsubmit="guardarProducto(event)">
                <div class="modal-body">
                    <input type="hidden" id="editProductoId" name="id_producto">
                    
                    <div class="form-group">
                        <label for="editNombre">Nombre del Producto</label>
                        <input type="text" id="editNombre" name="nombre" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDescripcion">Descripción</label>
                        <textarea id="editDescripcion" name="descripcion" class="form-textarea" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="editPrecio">Precio (B/.)</label>
                            <input type="number" id="editPrecio" name="precio" class="form-input" min="0" step="0.01" required>
                        </div>
                        <div class="form-group col-6">
                            <label for="editStock">Stock Disponible</label>
                            <input type="number" id="editStock" name="stock" class="form-input" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="editIdTienda">Tienda</label>
                            <select id="editIdTienda" name="id_tienda" class="form-select" required>
                                <!-- Se cargará dinámicamente -->
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label for="editDescuento">Descuento (%)</label>
                            <input type="number" id="editDescuento" name="descuento" class="form-input" min="0" max="100" value="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editImagen">Imagen del Producto</label>
                        <input type="file" id="editImagen" name="imagen" class="form-input" accept="image/*">
                        <div class="form-help">Imagen principal del producto. Recomendado: 800x800px</div>
                        <div id="editImagenPreview" class="mt-2 d-none">
                            <img src="" alt="" style="max-width: 100%; max-height: 200px;">
                        </div>
                        <div id="imagenActual" class="imagen-preview" style="margin-top: 10px; display: none;">
                            <img id="imgPreview" src="" alt="Imagen actual" style="max-width: 200px; max-height: 150px; border-radius: 6px; border: 1px solid #e1e3e5;">
                            <p style="font-size: 12px; color: #6d7175; margin-top: 5px;">Imagen actual</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" id="editActivo" name="activo" value="1" checked>
                            Publicar producto inmediatamente
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                    <button type="button" class="btn-danger" onclick="confirmarEliminar()">
                        <i class="fas fa-trash"></i>
                        Eliminar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Funcionalidades de búsqueda y filtros
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchProducts');
            const filterStatus = document.getElementById('filterStatus');
            const filterStock = document.getElementById('filterStock');
            const table = document.getElementById('productsTable');
            
            // Función de filtrado
            function filterTable() {
                if (!table) return;
                
                const searchTerm = searchInput.value.toLowerCase();
                const statusFilter = filterStatus.value;
                const stockFilter = filterStock.value;
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const productName = row.querySelector('.product-details h4').textContent.toLowerCase();
                    const productDesc = row.querySelector('.product-description').textContent.toLowerCase();
                    const statusBadge = row.querySelector('.status-badge');
                    const stockIndicator = row.querySelector('.stock-indicator');
                    
                    let showRow = true;
                    
                    // Filtro de búsqueda
                    if (searchTerm && !productName.includes(searchTerm) && !productDesc.includes(searchTerm)) {
                        showRow = false;
                    }
                    
                    // Filtro de estado
                    if (statusFilter) {
                        const isActive = statusBadge.classList.contains('status-active');
                        if ((statusFilter === 'active' && !isActive) || (statusFilter === 'inactive' && isActive)) {
                            showRow = false;
                        }
                    }
                    
                    // Filtro de stock
                    if (stockFilter) {
                        const stockClass = Array.from(stockIndicator.classList).find(cls => cls.startsWith('stock-'));
                        if (stockFilter !== stockClass.replace('stock-', '')) {
                            showRow = false;
                        }
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
            }
            
            // Event listeners
            searchInput.addEventListener('input', filterTable);
            filterStatus.addEventListener('change', filterTable);
            filterStock.addEventListener('change', filterTable);
        });
        
        // Funciones de acciones
        function agregarProducto() {
            // Redirigir al dashboard para abrir el modal de nuevo producto
            window.location.href = '/artesanoDigital/dashboard/artesano';
        }
        
        // Guardar producto
        function guardarProducto(event) {
            event.preventDefault();
            
            const form = document.getElementById('formEditarProducto');
            const formData = new FormData(form);
            formData.append('accion', 'actualizar_producto');
            // Asegurar que el id_producto se envía correctamente
            if (!formData.has('id_producto')) {
                const id = document.getElementById('editProductoId').value;
                formData.append('id_producto', id);
            }
            
            // Mostrar indicador de carga
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            
            showToast('Guardando cambios...', 'info');
            
            fetch('/artesanoDigital/api/productos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.exito) {
                    showToast('Producto actualizado correctamente', 'success');
                    cerrarModal();
                    setTimeout(() => {
                        location.reload(); // Recargar la página para mostrar los cambios
                    }, 1500);
                } else {
                    // Mostrar el mensaje exacto del backend
                    showToast('Error al actualizar el producto: ' + (data.message || data.mensaje || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al actualizar el producto: ' + error, 'error');
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
        
        // Eliminar producto
        function eliminarProducto(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
                showToast('Eliminando producto...', 'warning');
                
                fetch('/artesanoDigital/api/productos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `accion=eliminar_producto&id_producto=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.exito) {
                        showToast('Producto eliminado correctamente', 'success');
                        setTimeout(() => {
                            location.reload(); // Recargar la página para mostrar los cambios
                        }, 1500);
                    } else {
                        showToast('Error al eliminar el producto: ' + (data.message || data.mensaje || 'Error desconocido'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al eliminar el producto', 'error');
                });
            }
        }
        
        // Función para mostrar notificaciones toast
        function showToast(message, type = 'info') {
            // Crear contenedor si no existe
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            
            // Crear toast
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            // Icono según el tipo
            let icon = 'fas fa-info-circle';
            if (type === 'success') icon = 'fas fa-check-circle';
            else if (type === 'error') icon = 'fas fa-exclamation-circle';
            else if (type === 'warning') icon = 'fas fa-exclamation-triangle';
            
            toast.innerHTML = `
                <i class="${icon}"></i>
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            
            // Auto eliminar después de 4 segundos
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 4000);
        }
        
        // Abrir modal de edición
        function abrirModalEditar(id) {
            // Mostrar indicador de carga
            showToast('Cargando datos del producto...', 'info');
            
            // Para depuración: mostrar el ID que se está enviando
            console.log("Cargando producto con ID:", id);
            
            // Primero cargar la tienda del artesano
            fetch(`/artesanoDigital/api/productos.php?accion=obtener_tienda`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(tiendaData => {
                if (tiendaData.success && tiendaData.tienda) {
                    const selectTienda = document.getElementById('editIdTienda');
                    selectTienda.innerHTML = '';
                    const option = document.createElement('option');
                    option.value = tiendaData.tienda.id_tienda;
                    option.textContent = tiendaData.tienda.nombre_tienda;
                    option.selected = true;
                    selectTienda.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error al cargar tienda:', error);
            });
            
            // Cargar datos del producto usando la API dedicada
            fetch(`/artesanoDigital/api/productos.php?id=${id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // Para depuración: ver el estado de la respuesta
                console.log("Estado de la respuesta:", response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text().then(text => {
                    try {
                        // Intentar parsear como JSON
                        return JSON.parse(text);
                    } catch (e) {
                        // Si no es JSON, mostrar el texto para depuración
                        console.error("Respuesta no es JSON:", text);
                        throw new Error("La respuesta no es JSON válido: " + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                // Para depuración: ver la respuesta completa
                console.log("Datos recibidos:", data);
                
                if (data && (data.success || data.exito)) {
                    // Si la respuesta es exitosa pero no hay producto, puede ser que esté en data directamente
                    const producto = data.producto || data;
                    
                    if (producto && producto.id_producto) {
                        // Llenar el formulario
                        document.getElementById('editProductoId').value = producto.id_producto;
                        document.getElementById('editNombre').value = producto.nombre || '';
                        document.getElementById('editDescripcion').value = producto.descripcion || '';
                        document.getElementById('editPrecio').value = producto.precio || '';
                        document.getElementById('editStock').value = producto.stock || '0';
                        document.getElementById('editDescuento').value = producto.descuento || '0';
                        document.getElementById('editActivo').checked = producto.activo == 1;
                        
                        // Mostrar imagen actual si existe
                        const imagenPreview = document.getElementById('imagenActual');
                        const imgPreview = document.getElementById('imgPreview');
                        
                        if (producto.imagen && producto.imagen.trim() !== '') {
                            imgPreview.src = `/artesanoDigital/uploads/productos/${producto.imagen}`;
                            imagenPreview.style.display = 'block';
                        } else {
                            imagenPreview.style.display = 'none';
                        }
                        
                        // Mostrar modal
                        document.getElementById('modalEditar').classList.add('active');
                        document.body.style.overflow = 'hidden';
                        
                        showToast('Datos cargados correctamente', 'success');
                    } else {
                        showToast('Error: No se encontraron datos del producto', 'error');
                    }
                } else {
                    showToast('Error al cargar los datos del producto: ' + (data.message || data.mensaje || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                console.error('Error al cargar datos:', error);
                showToast('Error al cargar los datos del producto: ' + error.message, 'error');
            });
        }
        
        // Cerrar modal
        function cerrarModal() {
            document.getElementById('modalEditar').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Guardar producto
        function guardarProducto(event) {
            event.preventDefault();
            
            const form = document.getElementById('formEditarProducto');
            const formData = new FormData(form);
            formData.append('accion', 'actualizar_producto');
            // Asegurar que el id_producto se envía correctamente
            if (!formData.has('id_producto')) {
                const id = document.getElementById('editProductoId').value;
                formData.append('id_producto', id);
            }
            
            // Mostrar indicador de carga
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            
            showToast('Guardando cambios...', 'info');
            
            fetch('/artesanoDigital/api/productos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.exito) {
                    showToast('Producto actualizado correctamente', 'success');
                    cerrarModal();
                    setTimeout(() => {
                        location.reload(); // Recargar la página para mostrar los cambios
                    }, 1500);
                } else {
                    // Mostrar el mensaje exacto del backend
                    showToast('Error al actualizar el producto: ' + (data.message || data.mensaje || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al actualizar el producto: ' + error, 'error');
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
        
        // Confirmar eliminación desde el modal
        function confirmarEliminar() {
            const productoId = document.getElementById('editProductoId').value;
            const nombreProducto = document.getElementById('editNombre').value;
            
            if (confirm(`¿Estás seguro de que quieres eliminar el producto "${nombreProducto}"? Esta acción no se puede deshacer.`)) {
                eliminarProductoDesdeModal(productoId);
            }
        }
        
        // Eliminar producto desde el modal
        function eliminarProductoDesdeModal(id) {
            showToast('Eliminando producto...', 'warning');
            
            fetch('/artesanoDigital/api/productos.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `accion=eliminar_producto&id_producto=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.exito) {
                    showToast('Producto eliminado correctamente', 'success');
                    cerrarModal();
                    setTimeout(() => location.reload(), 1500); // Recargar después de un segundo y medio
                } else {
                    showToast('Error al eliminar el producto: ' + (data.message || data.mensaje || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al eliminar el producto: ' + error.message, 'error');
            });
        }
        
        // Eliminar producto desde la tabla
        function eliminarProducto(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
                showToast('Eliminando producto...', 'warning');
                
                fetch('/artesanoDigital/api/productos.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `accion=eliminar_producto&id_producto=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.exito) {
                        showToast('Producto eliminado correctamente', 'success');
                        setTimeout(() => location.reload(), 1500); // Recargar después de un segundo y medio
                    } else {
                        showToast('Error al eliminar el producto: ' + (data.message || data.mensaje || 'Error desconocido'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al eliminar el producto: ' + error.message, 'error');
                });
            }
        }
        
        // Función para mostrar toasts
        function showToast(message, type = 'success') {
            // Crear container si no existe
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            
            // Crear toast
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'fas fa-check-circle' : 
                        type === 'error' ? 'fas fa-exclamation-circle' : 
                        'fas fa-exclamation-triangle';
            
            toast.innerHTML = `
                <i class="${icon}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #6d7175; cursor: pointer; margin-left: auto;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }
        
        // Cerrar modal al hacer clic fuera
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target === modal) {
                cerrarModal();
            }
        });
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });
        
        // Preview de imagen al seleccionar archivo
        document.addEventListener('DOMContentLoaded', function() {
            const inputImagen = document.getElementById('editImagen');
            if (inputImagen) {
                inputImagen.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imagenPreview = document.getElementById('imagenActual');
                            const imgPreview = document.getElementById('imgPreview');
                            
                            if (imagenPreview && imgPreview) {
                                imgPreview.src = e.target.result;
                                imagenPreview.style.display = 'block';
                                
                                // Cambiar el texto
                                const textElement = imagenPreview.querySelector('p');
                                if (textElement) {
                                    textElement.textContent = 'Nueva imagen seleccionada';
                                }
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php
// Capturar el contenido
$contenido = ob_get_clean();
echo $contenido;
?>