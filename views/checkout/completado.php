<?php
// Vista: Pedido completado
ob_start();

// Verificar si hay un parámetro de error
$error = false;
if (isset($_GET['error']) && $_GET['error'] === '1') {
    $error = true;
}

// Obtener datos del pedido guardado en sesión
$pedidoCompletado = $_SESSION['pedido_completado'] ?? [];

// Verificar si hay un pedido completado en la sesión
if (empty($pedidoCompletado) && !$error) {
    // Si no hay datos de pedido y no es un error explícito, redirigir al carrito
    header('Location: /artesanoDigital/carrito');
    exit;
}

// Si no hay datos de pedido guardados o hay un error, generar información de fallback
if (empty($pedidoCompletado) || $error) {
    // Generar un número de referencia aleatorio con formato AD-XXXXX
    $referencia = 'AD-' . str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
    $idPedido = $referencia;
    $total = 0;
    $fecha = date('Y-m-d H:i:s');
    $metodo = 'No especificado';
} else {
    // Usar los datos guardados del pedido real
    $idPedido = $pedidoCompletado['id_pedido'] ?? '';
    $referencia = $pedidoCompletado['referencia'] ?? ('AD-' . str_pad($idPedido, 5, '0', STR_PAD_LEFT));
    $total = $pedidoCompletado['total'] ?? 0;
    $fecha = $pedidoCompletado['fecha'] ?? date('Y-m-d H:i:s');
    $metodo = $pedidoCompletado['metodo_pago'] ?? 'No especificado';
}

// Generar un número aleatorio para el ID de transacción de respaldo si es necesario
$transaccionId = $pedidoCompletado['transaccion_id'] ?? ('TXN' . mt_rand(100000, 999999));
?>
<div class="contenedor">
  <div class="checkout-card">
    <div class="checkout-success-header text-center">
      <div class="checkout-icon">
        <i class="fas fa-check-circle fa-4x text-success"></i>
      </div>
      <h2 class="checkout-title text-success">¡Pedido completado!</h2>
      <p class="mb-4">¡Gracias por comprar en Artesanos Digital!</p>
      
      <div class="order-summary">
        <div class="order-reference">
          <h4>Referencia del Pedido</h4>
          <p class="order-number"><strong>#<?php echo htmlspecialchars($referencia); ?></strong></p>
          <p class="order-date">Fecha: <?php echo date('d/m/Y H:i', strtotime($fecha)); ?></p>
        </div>
        
        <div class="payment-details">
          <h4>Detalles de Pago</h4>
          <p class="order-amount">Total pagado: <strong>B/. <?php echo number_format($total, 2); ?></strong></p>
          <p class="payment-method">Método de pago: <strong><?php echo htmlspecialchars(ucfirst($metodo)); ?></strong></p>
          <?php if (!empty($transaccionId)): ?>
          <p class="order-transaction">ID de Transacción: <strong><?php echo htmlspecialchars($transaccionId); ?></strong></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div class="order-details mt-4">
      <?php if (!empty($pedidoCompletado['productos'])): ?>
      <div class="order-products">
        <h4>Productos adquiridos:</h4>
        <div class="table-responsive">
          <table class="table table-striped product-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pedidoCompletado['productos'] as $producto): ?>
              <tr>
                <td class="product-id">#<?php echo htmlspecialchars($producto['id_producto']); ?></td>
                <td class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></td>
                <td class="product-quantity text-center"><?php echo $producto['cantidad']; ?></td>
                <td class="product-price">B/. <?php echo number_format($producto['precio'], 2); ?></td>
                <td class="product-total">B/. <?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td><strong>B/. <?php echo number_format($total, 2); ?></strong></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <?php else: ?>
      <div class="alert alert-info">
        No se encontraron detalles de los productos adquiridos.
      </div>
      <?php endif; ?>
      
      <div class="checkout-actions mt-4 text-center">
        <a href="/artesanoDigital/" class="btn btn-primary">Volver a la Tienda</a>
        <a href="http://localhost/artesanoDigital/dashboard/cliente" class="btn btn-secondary">Ver Mis Pedidos</a>
      </div>
    </div>
  </div>
</div>

<style>
.checkout-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0,0,0,0.1);
  padding: 2rem;
  margin: 2rem auto;
  max-width: 900px;
}

.checkout-icon {
  margin-bottom: 1.5rem;
}

.checkout-title {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.order-summary {
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  margin: 2rem 0;
  padding: 1.5rem;
  background: #f9f9f9;
  border-radius: 8px;
}

.order-reference, .payment-details {
  padding: 1rem;
}

.order-products {
  margin-top: 2rem;
}

.product-table {
  width: 100%;
}

.product-table th, .product-table td {
  padding: 0.75rem;
  vertical-align: middle;
}

.product-id {
  color: #666;
  font-size: 0.9rem;
}

.checkout-actions {
  margin-top: 2rem;
}

.checkout-actions .btn {
  margin: 0 0.5rem;
  padding: 0.5rem 1.5rem;
}

@media (max-width: 768px) {
  .order-summary {
    flex-direction: column;
  }
  
  .order-reference, .payment-details {
    width: 100%;
  }
}
</style>

<?php
$contenido = ob_get_clean();
// Asegurarnos de usar la ruta correcta para el layout base
include __DIR__ . '/../layouts/base.php';
?>
