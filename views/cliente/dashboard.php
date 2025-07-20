<?php 
/**
 * Vista Dashboard del Cliente
 * Responsabilidad: Mostrar panel principal del cliente con pedidos y navegación
 */

// Variables para el layout
$titulo = $titulo ?? 'Panel de Cliente - Artesano Digital';
$descripcion = 'Panel de control para clientes de Artesano Digital';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
            <p>Desde aquí puedes gestionar tus pedidos y explorar productos</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Pedidos Realizados</h3>
                <p class="stat-number"><?= $estadisticas['pedidos_totales'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Gastado</h3>
                <p class="stat-number">$<?= number_format($estadisticas['total_compras'], 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>Productos Favoritos</h3>
                <p class="stat-number"><?= $estadisticas['productos_favoritos'] ?></p>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="section">
                <h2>Pedidos Recientes</h2>
                <?php if (!empty($pedidos_recientes)): ?>
                    <div class="pedidos-lista">
                        <?php foreach ($pedidos_recientes as $pedido): ?>
                            <div class="pedido-card">
                                <div class="pedido-info">
                                    <span class="pedido-id">Pedido #<?= $pedido['id'] ?></span>
                                    <span class="pedido-fecha"><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></span>
                                </div>
                                <div class="pedido-detalles">
                                    <span class="pedido-metodo">Método: <?= ucfirst(str_replace('_', ' ', $pedido['metodo_pago'])) ?></span>
                                </div>
                                <div class="pedido-monto">$<?= number_format($pedido['total'], 2) ?></div>
                                <div class="pedido-estado estado-<?= $pedido['estado'] ?>">
                                    <?= ucfirst($pedido['estado']) ?>
                                </div>
                                <div class="pedido-acciones">
                                    <a href="/artesanoDigital/cliente/pedido/<?= $pedido['id'] ?>" class="btn-detalles">Ver detalles</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="texto-vacio">No tienes pedidos realizados aún.</p>
                    <a href="/artesanoDigital/productos" class="btn btn-primary">Explorar Productos</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.dashboard-header {
    margin-bottom: 2rem;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1.5rem;
}

.dashboard-header h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #333;
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    grid-gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #007bff;
    margin: 0.5rem 0;
}

.section {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.section h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: #333;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.75rem;
}

.pedidos-lista {
    display: grid;
    grid-gap: 1rem;
}

.pedido-card {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr 1fr 1fr;
    align-items: center;
    padding: 1rem;
    border-radius: 6px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.pedido-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.pedido-id {
    font-weight: bold;
    display: block;
}

.pedido-fecha {
    color: #6c757d;
    font-size: 0.9rem;
}

.pedido-monto {
    font-weight: bold;
    font-size: 1.1rem;
    color: #28a745;
}

.pedido-estado {
    font-size: 0.85rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    display: inline-block;
    font-weight: 500;
    text-align: center;
}

.estado-pendiente {
    background-color: #fff3cd;
    color: #856404;
}

.estado-enviado {
    background-color: #cce5ff;
    color: #004085;
}

.estado-entregado {
    background-color: #d4edda;
    color: #155724;
}

.estado-cancelado {
    background-color: #f8d7da;
    color: #721c24;
}

.btn-detalles {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    background: #007bff;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.85rem;
    transition: background 0.2s;
}

.btn-detalles:hover {
    background: #0069d9;
}

.texto-vacio {
    text-align: center;
    color: #6c757d;
    padding: 2rem 0;
}

.table-responsive {
    margin-top: 1.5rem;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    background-color: #f8f9fa;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f2f2f2;
}

.badge {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1;
    display: inline-block;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-primary {
    background-color: #007bff;
    color: #fff;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-danger {
    background-color: #dc3545;
    color: #fff;
}

@media (max-width: 768px) {
    .pedido-card {
        grid-template-columns: 1fr;
        grid-gap: 0.75rem;
    }
    
    .pedido-info, .pedido-detalles, .pedido-monto, .pedido-estado, .pedido-acciones {
        text-align: left;
    }
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
