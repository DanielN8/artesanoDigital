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

<div class="dashboard">
    <!-- Cabecera del Dashboard -->
    <header class="dashboard-header">
        <div class="welcome-section">
            <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
            <p>Desde aquí puedes gestionar tus pedidos y explorar nuevos productos artesanales</p>
        </div>
        <div class="quick-actions">
            <a href="/artesanoDigital/productos" class="action-btn primary">
                <i class="material-icons">shopping_bag</i> Explorar Productos
            </a>
            <a href="/artesanoDigital/carrito" class="action-btn secondary">
                <i class="material-icons">shopping_cart</i> Ver Carrito
            </a>
        </div>
    </header>

    <!-- Estadísticas en Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="material-icons">shopping_bag</i>
            </div>
            <div class="stat-content">
                <h3>Pedidos Realizados</h3>
                <p class="stat-number"><?= $estadisticas['pedidos_totales'] ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="material-icons">attach_money</i>
            </div>
            <div class="stat-content">
                <h3>Total Gastado</h3>
                <p class="stat-number">$<?= number_format($estadisticas['total_compras'], 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Pedidos Recientes -->
    <section class="orders-section">
        <div class="section-header">
            <h2>Pedidos Recientes</h2>
            <a href="/artesanoDigital/cliente/pedidos" class="view-all">Ver todos</a>
        </div>
        
        <?php if (!empty($pedidos_recientes)): ?>
            <div class="orders-grid">
                <?php foreach ($pedidos_recientes as $pedido): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">Pedido #<?= $pedido['id'] ?></div>
                            <div class="order-status status-<?= $pedido['estado'] ?>">
                                <?= ucfirst($pedido['estado']) ?>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="order-info">
                                <div class="info-item">
                                    <span class="info-label"><i class="material-icons">event</i> Fecha:</span>
                                    <span class="info-value"><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"><i class="material-icons">payment</i> Método:</span>
                                    <span class="info-value"><?= ucfirst(str_replace('_', ' ', $pedido['metodo_pago'])) ?></span>
                                </div>
                                <div class="info-item total">
                                    <span class="info-label"><i class="material-icons">local_offer</i> Total:</span>
                                    <span class="info-value">$<?= number_format($pedido['total'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-footer">
                            <a href="/artesanoDigital/cliente/pedido/<?= $pedido['id'] ?>" class="btn-details">
                                <i class="material-icons">visibility</i> Ver detalles
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="material-icons">shopping_bag</i>
                </div>
                <p>No tienes pedidos realizados aún.</p>
                <a href="/artesanoDigital/productos" class="btn primary-btn">Explorar Productos</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
:root {
    --color-primary: #4f46e5;
    --color-primary-light: #6366f1;
    --color-primary-dark: #4338ca;
    --color-secondary: #64748b;
    --color-background: #f9fafb;
    --color-surface: #ffffff;
    --color-text: #1f2937;
    --color-text-secondary: #6b7280;
    --color-border: #e5e7eb;
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
    --color-info: #3b82f6;
    
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    
    --radius-sm: 0.25rem;
    --radius-md: 0.375rem;
    --radius-lg: 0.5rem;
    --radius-xl: 0.75rem;
    
    --space-1: 0.25rem;
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-5: 1.25rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
    --space-10: 2.5rem;
    --space-12: 3rem;
    --space-16: 4rem;
}

/* Estilos generales */
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--space-8);
    background-color: var(--color-background);
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Cabecera */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-8);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid var(--color-border);
}

.welcome-section h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: var(--color-text);
    margin-bottom: var(--space-2);
}

.welcome-section p {
    color: var(--color-text-secondary);
    font-size: 1rem;
    max-width: 36rem;
}

.quick-actions {
    display: flex;
    gap: var(--space-3);
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.action-btn.primary {
    background-color: var(--color-primary);
    color: white;
}

.action-btn.primary:hover {
    background-color: var(--color-primary-dark);
}

.action-btn.secondary {
    background-color: white;
    color: var(--color-text);
    border: 1px solid var(--color-border);
}

.action-btn.secondary:hover {
    background-color: var(--color-background);
    border-color: var(--color-secondary);
}

.action-btn i {
    font-size: 18px;
}

/* Tarjetas de estadísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-8);
    margin-bottom: var(--space-12);
}

.stat-card {
    background-color: var(--color-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: var(--space-8);
    display: flex;
    align-items: center;
    min-height: 140px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    background-color: rgba(79, 70, 229, 0.1);
    color: var(--color-primary);
    margin-right: var(--space-6);
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-icon .material-icons {
    font-size: 28px;
}

.stat-content {
    flex: 1;
}

.stat-content h3 {
    font-size: 1.125rem;
    font-weight: 500;
    color: var(--color-text-secondary);
    margin-bottom: var(--space-3);
    line-height: 1.3;
}

.stat-number {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--color-text);
    line-height: 1;
}

/* Sección de pedidos */
.orders-section {
    background-color: var(--color-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: var(--space-8);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-8);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid var(--color-border);
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-text);
}

.view-all {
    font-size: 0.875rem;
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: var(--space-8);
}

.order-card {
    background-color: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.order-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-6);
    background-color: #f8fafc;
    border-bottom: 1px solid var(--color-border);
}

.order-id {
    font-weight: 600;
    color: var(--color-text);
    font-size: 1.125rem;
}

.order-status {
    font-size: 0.875rem;
    padding: var(--space-2) var(--space-4);
    border-radius: 2rem;
    font-weight: 500;
}

.status-pendiente {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.status-enviado {
    background-color: rgba(59, 130, 246, 0.1);
    color: var(--color-info);
}

.status-entregado {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
}

.status-cancelado {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--color-danger);
}

.order-body {
    padding: var(--space-6);
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9375rem;
}

.info-label {
    color: var(--color-text-secondary);
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.info-label .material-icons {
    font-size: 16px;
}

.info-value {
    color: var(--color-text);
    font-weight: 500;
}

.info-item.total {
    margin-top: var(--space-3);
    padding-top: var(--space-3);
    border-top: 1px dashed var(--color-border);
}

.info-item.total .info-value {
    font-weight: 700;
    color: var(--color-success);
    font-size: 1.125rem;
}

.order-footer {
    padding: var(--space-6);
    border-top: 1px solid var(--color-border);
    text-align: right;
}

.btn-details {
    display: inline-flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-5);
    border-radius: var(--radius-md);
    font-size: 0.9375rem;
    font-weight: 500;
    background-color: white;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-details .material-icons {
    font-size: 18px;
}

.btn-details:hover {
    background-color: var(--color-primary);
    color: white;
    transform: translateY(-1px);
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: var(--space-12) var(--space-6);
    color: var(--color-text-secondary);
}

.empty-icon {
    font-size: 3.5rem;
    color: var(--color-text-secondary);
    margin-bottom: var(--space-6);
    opacity: 0.4;
}

.empty-icon .material-icons {
    font-size: 64px;
}

.empty-state p {
    margin-bottom: var(--space-8);
    font-size: 1.25rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-4) var(--space-8);
    border-radius: var(--radius-md);
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.primary-btn {
    background-color: var(--color-primary);
    color: white;
}

.primary-btn:hover {
    background-color: var(--color-primary-dark);
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-6);
    }
    
    .stat-card {
        min-height: 120px;
        padding: var(--space-6);
    }
    
    .orders-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--space-6);
    }
}

@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .orders-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-6);
    }
    
    .quick-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .orders-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .dashboard {
        padding: var(--space-6);
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
        min-height: auto;
        padding: var(--space-6);
    }
    
    .stat-icon {
        margin-right: 0;
        margin-bottom: var(--space-3);
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
    
    .order-status {
        align-self: flex-start;
    }
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
