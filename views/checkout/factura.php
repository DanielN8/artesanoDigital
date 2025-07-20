<?php
// Vista: Factura de confirmación de pedido
ob_start();
$direccion = $_SESSION['checkout_direccion'] ?? [];
$carrito = $_SESSION['carrito'] ?? [];
$metodo = $_SESSION['checkout_metodo_pago'] ?? '';
$datosPago = $_SESSION['checkout_datos_pago'] ?? [];
$fecha = date('d/m/Y');
$numeroFactura = uniqid('FAC-');
?>
<div class="contenedor">
  <div class="invoice-card">
    <div class="invoice-header">
      <h1>Factura</h1>
      <div class="invoice-meta">
        <span><strong>Factura #:</strong> <?= htmlspecialchars($numeroFactura) ?></span><br>
        <span><strong>Fecha:</strong> <?= htmlspecialchars($fecha) ?></span>
      </div>
    </div>
    <div class="invoice-info">
      <div class="company-info">
        <h2>Artesano Digital</h2>
        <p>Panamá Oeste, Panamá<br>Tel: +507 1234 5678<br>Email: info@artesanodigital.com</p>
      </div>
      <div class="customer-info">
        <h2>Datos de Envío</h2>
        <p>
          <?= htmlspecialchars($direccion['nombre'] ?? '') ?><br>
          <?= htmlspecialchars($direccion['direccion'] ?? '') ?><br>
          <?= htmlspecialchars($direccion['ciudad'] ?? '') ?><br>
          Tel: <?= htmlspecialchars($direccion['telefono'] ?? '') ?>
        </p>
      </div>
    </div>
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Cant.</th>
          <th>Descripción</th>
          <th>Precio Unit.</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $subtotal = 0; foreach ($carrito as $item):
            $lineTotal = $item['precio'] * $item['cantidad'];
            $subtotal += $lineTotal;
        ?>
        <tr>
          <td><?= $item['cantidad'] ?></td>
          <td><?= htmlspecialchars($item['nombre']) ?></td>
          <td>$<?= number_format($item['precio'], 2) ?></td>
          <td>$<?= number_format($lineTotal, 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
      $shipping = 0.00;
      $tax = $subtotal * 0.07;
      $grandTotal = $subtotal + $shipping + $tax;
    ?>
    <div class="invoice-summary">
      <div><span>Subtotal:</span><span>$<?= number_format($subtotal, 2) ?></span></div>
      <div><span>Envío:</span><span>$<?= number_format($shipping, 2) ?></span></div>
      <div><span>Impuesto (7%):</span><span>$<?= number_format($tax, 2) ?></span></div>
      <div class="total"><span>Total:</span><span>$<?= number_format($grandTotal, 2) ?></span></div>
    </div>
    <div class="invoice-payment">
      <strong>Método de Pago:</strong> <?= $metodo === 'tarjeta' ? 'Tarjeta de Crédito' : 'Yappy' ?>
    </div>
    <div class="invoice-footer">
      <p>Verifica que toda la información es correcta antes de proceder con el pago.</p>
      <form action="/artesanoDigital/checkout/completado" method="post" class="checkout-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button type="submit" class="btn btn-primary btn-block">Finalizar pago</button>
      </form>
    </div>
  </div>
</div>
<style>
.invoice-card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 2rem auto; max-width: 800px; }
.invoice-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.invoice-header h1 { margin: 0; font-size: 2rem; }
.invoice-meta span { display: block; font-size: 0.9rem; color: #555; }
.invoice-info { display: flex; justify-content: space-between; margin-bottom: 1.5rem; }
.company-info, .customer-info { width: 48%; }
.company-info h2, .customer-info h2 { margin-bottom: 0.5rem; font-size: 1.1rem; }
.invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
.invoice-table th, .invoice-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
.invoice-summary { max-width: 300px; margin-left: auto; margin-bottom: 1.5rem; }
.invoice-summary div { display: flex; justify-content: space-between; padding: 0.25rem 0; font-size: 0.95rem; }
.invoice-summary .total { font-weight: bold; border-top: 1px solid #ddd; margin-top: 0.5rem; padding-top: 0.5rem; }
.invoice-payment { font-size: 1rem; margin-bottom: 1.5rem; }
.invoice-footer { text-align: center; font-size: 0.9rem; color: #777; }
</style>
<?php
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
