<?php 
/**
 * Vista de Detalle de Pedido
 * Responsabilidad: Mostrar los detalles completos de un pedido (factura)
 */

// Variables para el layout
$titulo = $titulo ?? 'Detalle de Pedido #' . $pedido['id'];
$descripcion = 'Detalle completo del pedido y productos adquiridos';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="pedido-detalle">
    <div class="navegacion">
        <a href="/artesanoDigital/cliente/dashboard" class="link-back">
            <i class="fas fa-arrow-left"></i> Volver al dashboard
        </a>
    </div>
    
    <div class="factura">
        <div class="factura-header">
            <div class="factura-titulo">
                <h1>Pedido #<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></h1>
                <div class="pedido-status status-<?= $pedido['estado'] ?>">
                    <?= ucfirst($pedido['estado']) ?>
                </div>
            </div>
            
            <div class="factura-info">
                <div class="info-grupo">
                    <div class="info-item">
                        <span class="label"><i class="fas fa-calendar"></i> Fecha:</span>
                        <span class="valor"><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="fas fa-credit-card"></i> Método de pago:</span>
                        <span class="valor"><?= ucfirst(str_replace('_', ' ', $pedido['metodo_pago'])) ?></span>
                    </div>
                    <?php if(isset($pedido['transaccion_id']) && !empty($pedido['transaccion_id'])): ?>
                    <div class="info-item">
                        <span class="label"><i class="fas fa-hashtag"></i> ID Transacción:</span>
                        <span class="valor"><?= $pedido['transaccion_id'] ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="info-grupo">
                    <div class="info-item">
                        <span class="label"><i class="fas fa-map-marker-alt"></i> Dirección de envío:</span>
                        <span class="valor"><?= htmlspecialchars($pedido['direccion_envio']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="factura-productos">
            <h2>Productos</h2>
            <?php
            // Debug: mostrar información de productos
            error_log("Vista pedido_detalle - Productos disponibles: " . count($productos));
            if (empty($productos)) {
                error_log("Vista pedido_detalle - Array de productos está vacío");
            }
            ?>
            <div class="tabla-responsive">
                <table class="tabla-productos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-right">Precio</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        if (empty($productos)): ?>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="mensaje-vacio">
                                        <i class="fas fa-box-open"></i>
                                        <p>No se encontraron productos para este pedido</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else:
                        foreach ($productos as $producto): 
                            $total_linea = $producto['precio_unitario'] * $producto['cantidad'];
                            $subtotal += $total_linea;
                        ?>
                        <tr>
                            <td class="producto-info">
                                <?php if(isset($producto['imagen']) && !empty($producto['imagen'])): ?>
                                <img src="/artesanoDigital/<?= $producto['imagen'] ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="producto-imagen">
                                <?php else: ?>
                                <div class="producto-imagen-placeholder">
                                    <i class="fas fa-box"></i>
                                </div>
                                <?php endif; ?>
                                <div class="producto-detalles">
                                    <h4><?= htmlspecialchars($producto['nombre']) ?></h4>
                                    <?php if(isset($producto['artesano']) && !empty($producto['artesano'])): ?>
                                    <p class="artesano-nombre">por <?= htmlspecialchars($producto['artesano']) ?></p>
                                    <?php endif; ?>
                                    <?php if(isset($producto['descripcion']) && !empty($producto['descripcion'])): ?>
                                    <p class="producto-descripcion"><?= htmlspecialchars($producto['descripcion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center"><?= $producto['cantidad'] ?></td>
                            <td class="text-right">$<?= number_format($producto['precio_unitario'], 2) ?></td>
                            <td class="text-right">$<?= number_format($total_linea, 2) ?></td>
                        </tr>
                        <?php endforeach; 
                        endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">Subtotal:</td>
                            <td class="text-right">$<?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <?php if(isset($pedido['costo_envio'])): ?>
                        <tr>
                            <td colspan="3" class="text-right">Envío:</td>
                            <td class="text-right">$<?= number_format($pedido['costo_envio'], 2) ?></td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-right">Envío:</td>
                            <td class="text-right">$0.00</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td colspan="3" class="text-right">Total:</td>
                            <td class="text-right">$<?= number_format($pedido['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <?php if($pedido['estado'] === 'pendiente'): ?>
        <div class="factura-acciones">
            <button id="btn-cancelar-pedido" class="btn-cancelar" data-id="<?= $pedido['id'] ?>">
                <i class="fas fa-times"></i> Cancelar pedido
            </button>
        </div>
        <?php endif; ?>
    </div>
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
    
    --space-2: 0.5rem;
    --space-3: 0.75rem;
    --space-4: 1rem;
    --space-5: 1.25rem;
    --space-6: 1.5rem;
    --space-8: 2rem;
}

.pedido-detalle {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--space-6);
    background-color: var(--color-background);
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.navegacion {
    margin-bottom: var(--space-6);
}

.link-back {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--color-text-secondary);
    font-size: 0.875rem;
    text-decoration: none;
    transition: color 0.2s;
}

.link-back:hover {
    color: var(--color-primary);
}

.factura {
    background-color: var(--color-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.factura-header {
    padding: var(--space-6);
    border-bottom: 1px solid var(--color-border);
}

.factura-titulo {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-5);
    padding-bottom: var(--space-4);
    border-bottom: 1px solid var(--color-border);
}

.factura-titulo h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-text);
    margin: 0;
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

.factura-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-5);
}

.info-grupo {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.info-item {
    font-size: 0.875rem;
}

.info-item .label {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-2);
}

.info-item .valor {
    font-weight: 500;
    color: var(--color-text);
}

.factura-productos {
    padding: var(--space-6);
    border-bottom: 1px solid var(--color-border);
}

.factura-productos h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-text);
    margin-top: 0;
    margin-bottom: var(--space-5);
    padding-bottom: var(--space-3);
    border-bottom: 1px solid var(--color-border);
}

.tabla-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.tabla-productos {
    width: 100%;
    border-collapse: collapse;
}

.tabla-productos th {
    text-align: left;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--color-text-secondary);
    padding: var(--space-3);
    border-bottom: 1px solid var(--color-border);
}

.tabla-productos td {
    padding: var(--space-4);
    border-bottom: 1px solid var(--color-border);
    vertical-align: top;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.producto-info {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.producto-imagen, .producto-imagen-placeholder {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-md);
    object-fit: cover;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-secondary);
}

.producto-detalles h4 {
    font-size: 0.9375rem;
    font-weight: 500;
    color: var(--color-text);
    margin: 0 0 var(--space-2);
}

.artesano-nombre {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
    margin: 0;
}

.producto-descripcion {
    margin: var(--space-1) 0 0 0;
    font-size: 0.7rem;
    color: var(--color-text-light);
    line-height: 1.4;
}

.mensaje-vacio {
    padding: var(--space-6);
    text-align: center;
    color: var(--color-text-muted);
}

.mensaje-vacio i {
    font-size: 2rem;
    margin-bottom: var(--space-3);
    display: block;
    color: var(--color-text-light);
}

.mensaje-vacio p {
    margin: 0;
    font-size: 0.95rem;
}

.tabla-productos tfoot tr {
    font-weight: 500;
}

.tabla-productos tfoot td {
    padding-top: var(--space-3);
    padding-bottom: var(--space-3);
    border-bottom: none;
}

.tabla-productos tfoot .total-row {
    font-weight: 700;
    font-size: 1.125rem;
    color: var(--color-text);
}

.tabla-productos tfoot .total-row td {
    padding-top: var(--space-4);
}

.factura-acciones {
    padding: var(--space-6);
    display: flex;
    justify-content: flex-end;
}

.btn-cancelar {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-5);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-weight: 500;
    background-color: white;
    color: var(--color-danger);
    border: 1px solid var(--color-danger);
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancelar:hover {
    background-color: var(--color-danger);
    color: white;
}

@media (max-width: 768px) {
    .factura-titulo {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
    
    .factura-info {
        grid-template-columns: 1fr;
    }
    
    .producto-info {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
    
    .producto-imagen, .producto-imagen-placeholder {
        width: 100%;
        height: 120px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnCancelar = document.getElementById('btn-cancelar-pedido');
    
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')) {
                const idPedido = this.dataset.id;
                
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
                        window.location.reload();
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
    }
});
</script>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
