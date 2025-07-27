<?php 
/**
 * Vista Todos los Pedidos del Cliente
 * Responsabilidad: Mostrar listado completo de pedidos del cliente
 */

// Variables para el layout
$titulo = $titulo ?? 'Todos mis Pedidos - Artesano Digital';
$descripcion = 'Listado completo de tus pedidos en Artesano Digital';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="pedidos-page">
    <!-- Cabecera -->
    <header class="page-header">
        <div class="breadcrumb">
            <a href="/artesanoDigital/cliente/dashboard" class="breadcrumb-link">
                <i class="fas fa-arrow-left"></i> Volver al dashboard
            </a>
        </div>
        <div class="page-title">
            <h1>Todos mis Pedidos</h1>
            <p>Historial completo de tus compras en Artesano Digital</p>
        </div>
    </header>

    <!-- Estadísticas resumidas -->
    <div class="stats-summary">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="summary-content">
                <h3>Total de Pedidos</h3>
                <p class="summary-number"><?= $estadisticas['total_pedidos'] ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="summary-content">
                <h3>Total Gastado</h3>
                <p class="summary-number">$<?= number_format($estadisticas['total_compras'], 2) ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="summary-content">
                <h3>Pendientes</h3>
                <p class="summary-number"><?= $estadisticas['pedidos_pendientes'] ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="summary-content">
                <h3>Entregados</h3>
                <p class="summary-number"><?= $estadisticas['pedidos_entregados'] ?></p>
            </div>
        </div>
    </div>

    <!-- Lista de pedidos -->
    <section class="pedidos-list-section">
        <div class="section-header">
            <h2>Historial de Pedidos</h2>
            <div class="filter-actions">
                <select id="filtro-estado" class="filter-select">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="enviado">Enviados</option>
                    <option value="entregado">Entregados</option>
                    <option value="cancelado">Cancelados</option>
                </select>
                <select id="filtro-ordenar" class="filter-select">
                    <option value="fecha_desc">Más recientes</option>
                    <option value="fecha_asc">Más antiguos</option>
                    <option value="total_desc">Mayor valor</option>
                    <option value="total_asc">Menor valor</option>
                </select>
            </div>
        </div>
        
        <?php if (!empty($pedidos)): ?>
            <div class="pedidos-grid" id="pedidos-container">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="pedido-item" data-estado="<?= $pedido['estado'] ?>" data-fecha="<?= $pedido['fecha'] ?>" data-total="<?= $pedido['total'] ?>">
                        <div class="pedido-header">
                            <div class="pedido-number">
                                <span class="pedido-id">Pedido #<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <span class="pedido-fecha"><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></span>
                            </div>
                            <div class="pedido-status status-<?= $pedido['estado'] ?>">
                                <?= ucfirst($pedido['estado']) ?>
                            </div>
                        </div>
                        
                        <div class="pedido-content">
                            <div class="pedido-details">
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-credit-card"></i> Método de pago:</span>
                                    <span class="detail-value"><?= ucfirst(str_replace('_', ' ', $pedido['metodo_pago'])) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label"><i class="fas fa-calendar"></i> Fecha del pedido:</span>
                                    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="pedido-total">
                                <span class="total-label">Total:</span>
                                <span class="total-amount">$<?= number_format($pedido['total'], 2) ?></span>
                            </div>
                        </div>
                        
                        <div class="pedido-actions">
                            <a href="/artesanoDigital/cliente/pedido/<?= $pedido['id'] ?>" class="action-btn primary">
                                <i class="fas fa-eye"></i> Ver detalles
                            </a>
                            <?php if ($pedido['estado'] === 'pendiente'): ?>
                                <button class="action-btn secondary cancel-btn" data-id="<?= $pedido['id'] ?>">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-bag"></i>
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
    
    --radius-md: 0.375rem;
    --radius-lg: 0.5rem;
    
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-5: 1.25rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
    --space-10: 2.5rem;
    --space-12: 3rem;
}

.pedidos-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--space-8);
    background-color: var(--color-background);
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Cabecera */
.page-header {
    margin-bottom: var(--space-10);
}

.breadcrumb {
    margin-bottom: var(--space-6);
}

.breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--color-text-secondary);
    text-decoration: none;
    font-size: 0.9375rem;
    transition: color 0.2s;
}

.breadcrumb-link:hover {
    color: var(--color-primary);
}

.page-title h1 {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: var(--space-3);
}

.page-title p {
    color: var(--color-text-secondary);
    font-size: 1.125rem;
}

/* Estadísticas resumidas */
.stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-6);
    margin-bottom: var(--space-12);
}

.summary-card {
    background-color: var(--color-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: var(--space-6);
    display: flex;
    align-items: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    background-color: rgba(79, 70, 229, 0.1);
    color: var(--color-primary);
    margin-right: var(--space-4);
    font-size: 1.25rem;
    flex-shrink: 0;
}

.summary-content h3 {
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--color-text-secondary);
    margin-bottom: var(--space-2);
}

.summary-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--color-text);
    line-height: 1;
}

/* Sección de lista */
.pedidos-list-section {
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

.filter-actions {
    display: flex;
    gap: var(--space-3);
}

.filter-select {
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
    background-color: white;
    color: var(--color-text);
    font-size: 0.875rem;
    min-width: 140px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Grid de pedidos */
.pedidos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: var(--space-8);
}

.pedido-item {
    background-color: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-border);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.pedido-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.pedido-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-6);
    background-color: #f8fafc;
    border-bottom: 1px solid var(--color-border);
}

.pedido-number {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.pedido-id {
    font-weight: 600;
    color: var(--color-text);
    font-size: 1.125rem;
}

.pedido-fecha {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.pedido-status {
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

.pedido-content {
    padding: var(--space-6);
}

.pedido-details {
    margin-bottom: var(--space-6);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-3);
    font-size: 0.9375rem;
}

.detail-label {
    color: var(--color-text-secondary);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.detail-value {
    color: var(--color-text);
    font-weight: 500;
}

.pedido-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--space-4);
    border-top: 1px dashed var(--color-border);
}

.total-label {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--color-text);
}

.total-amount {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-success);
}

.pedido-actions {
    padding: var(--space-6);
    border-top: 1px solid var(--color-border);
    display: flex;
    gap: var(--space-3);
    justify-content: flex-end;
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
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn.primary {
    background-color: var(--color-primary);
    color: white;
}

.action-btn.primary:hover {
    background-color: var(--color-primary-dark);
    transform: translateY(-1px);
}

.action-btn.secondary {
    background-color: white;
    color: var(--color-danger);
    border: 1px solid var(--color-danger);
}

.action-btn.secondary:hover {
    background-color: var(--color-danger);
    color: white;
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: var(--space-12) var(--space-6);
    color: var(--color-text-secondary);
}

.empty-icon {
    font-size: 4rem;
    color: var(--color-text-secondary);
    margin-bottom: var(--space-6);
    opacity: 0.3;
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
@media (max-width: 1024px) {
    .pedidos-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--space-6);
    }
}

@media (max-width: 768px) {
    .pedidos-page {
        padding: var(--space-6);
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-4);
    }
    
    .filter-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .filter-select {
        min-width: auto;
    }
    
    .pedidos-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-summary {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .stats-summary {
        grid-template-columns: 1fr;
    }
    
    .page-title h1 {
        font-size: 1.875rem;
    }
    
    .pedido-actions {
        flex-direction: column;
    }
    
    .action-btn {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtroEstado = document.getElementById('filtro-estado');
    const filtroOrdenar = document.getElementById('filtro-ordenar');
    const pedidosContainer = document.getElementById('pedidos-container');
    const pedidosItems = Array.from(document.querySelectorAll('.pedido-item'));
    
    // Función para filtrar por estado
    function filtrarPorEstado() {
        const estadoSeleccionado = filtroEstado.value;
        
        pedidosItems.forEach(item => {
            const estadoItem = item.dataset.estado;
            if (estadoSeleccionado === '' || estadoItem === estadoSeleccionado) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Función para ordenar pedidos
    function ordenarPedidos() {
        const criterio = filtroOrdenar.value;
        const pedidosVisibles = pedidosItems.filter(item => item.style.display !== 'none');
        
        pedidosVisibles.sort((a, b) => {
            switch (criterio) {
                case 'fecha_desc':
                    return new Date(b.dataset.fecha) - new Date(a.dataset.fecha);
                case 'fecha_asc':
                    return new Date(a.dataset.fecha) - new Date(b.dataset.fecha);
                case 'total_desc':
                    return parseFloat(b.dataset.total) - parseFloat(a.dataset.total);
                case 'total_asc':
                    return parseFloat(a.dataset.total) - parseFloat(b.dataset.total);
                default:
                    return 0;
            }
        });
        
        // Reorganizar elementos en el DOM
        pedidosVisibles.forEach(item => {
            pedidosContainer.appendChild(item);
        });
    }
    
    // Event listeners
    filtroEstado.addEventListener('change', function() {
        filtrarPorEstado();
        ordenarPedidos();
    });
    
    filtroOrdenar.addEventListener('change', ordenarPedidos);
    
    // Manejar cancelación de pedidos
    const botonesCancelar = document.querySelectorAll('.cancel-btn');
    botonesCancelar.forEach(btn => {
        btn.addEventListener('click', function() {
            const idPedido = this.dataset.id;
            
            if (confirm('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')) {
                fetch(`/artesanoDigital/cliente/pedido/cancelar/${idPedido}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        alert('Pedido cancelado correctamente');
                        location.reload();
                    } else {
                        alert('Error al cancelar el pedido: ' + data.mensaje);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar tu solicitud');
                });
            }
        });
    });
});
</script>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
