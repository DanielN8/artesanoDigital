<?php include __DIR__ . '/../layouts/base.php'; ?>

<main class="main-contenido">
    <div class="contenedor">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/artesanoDigital/">Inicio</a>
            <span>/</span>
            <a href="/artesanoDigital/productos">Productos</a>
            <span>/</span>
            <span><?= htmlspecialchars($producto['nombre']) ?></span>
        </nav>

        <!-- Detalle del producto -->
        <div class="producto-detalle">
            <div class="producto-imagenes">
                <div class="imagen-principal">
                    <img src="<?= $producto['imagen'] ? '/artesanoDigital/' . htmlspecialchars($producto['imagen']) : '/artesanoDigital/public/placeholder.jpg' ?>" 
                         alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                         class="imagen-grande">
                </div>
            </div>

            <div class="producto-informacion">
                <h1 class="producto-titulo"><?= htmlspecialchars($producto['nombre']) ?></h1>
                
                <div class="producto-meta">
                    <p class="producto-artesano">Por <?= htmlspecialchars($producto['nombre_artesano'] ?? 'Artesano') ?></p>
                    <p class="producto-tienda">Tienda: <?= htmlspecialchars($producto['nombre_tienda'] ?? 'Tienda') ?></p>
                </div>

                <div class="producto-precio-contenedor">
                    <span class="precio-actual">$<?= number_format($producto['precio'], 2) ?></span>
                </div>

                <div class="producto-stock">
                    <?php if ($producto['stock'] > 0): ?>
                        <span class="stock-disponible">✓ En stock (<?= $producto['stock'] ?> disponibles)</span>
                    <?php else: ?>
                        <span class="stock-agotado">✗ Agotado</span>
                    <?php endif; ?>
                </div>

                <div class="producto-descripcion-detalle">
                    <h3>Descripción</h3>
                    <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                </div>

                <div class="producto-acciones-detalle">
                    <?php if ($producto['stock'] > 0): ?>
                        <div class="cantidad-selector">
                            <label for="cantidad">Cantidad:</label>
                            <select id="cantidad" class="select-cantidad">
                                <?php for ($i = 1; $i <= min(10, $producto['stock']); $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <button class="btn btn-primario btn-grande" onclick="agregarAlCarrito(<?= $producto['id_producto'] ?>)">
                            Agregar al Carrito
                        </button>
                        
                        <button class="btn btn-secundario btn-grande" onclick="comprarAhora(<?= $producto['id_producto'] ?>)">
                            Comprar Ahora
                        </button>
                    <?php else: ?>
                        <button class="btn btn-deshabilitado btn-grande" disabled>
                            Producto Agotado
                        </button>
                    <?php endif; ?>
                </div>

                <div class="producto-caracteristicas">
                    <h3>Características</h3>
                    <ul>
                        <li><strong>Producto:</strong> Hecho a mano</li>
                        <li><strong>Origen:</strong> Panamá Oeste</li>
                        <li><strong>Artesano:</strong> <?= htmlspecialchars($producto['nombre_artesano'] ?? 'Local') ?></li>
                        <li><strong>Fecha de creación:</strong> <?= date('d/m/Y', strtotime($producto['fecha_creacion'])) ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Productos relacionados -->
        <section class="productos-relacionados">
            <h2>Productos Similares</h2>
            <div class="productos-grid">
                <!-- Aquí irían productos relacionados -->
                <div class="producto-tarjeta">
                    <img src="/artesanoDigital/public/placeholder.jpg" alt="Producto relacionado" class="producto-imagen">
                    <div class="producto-info">
                        <h3>Producto Similar</h3>
                        <p class="producto-precio">$30.00</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
function agregarAlCarrito(idProducto) {
    const cantidad = document.getElementById('cantidad').value;
    console.log('Agregando al carrito:', idProducto, 'cantidad:', cantidad);
    alert('Producto agregado al carrito (funcionalidad en desarrollo)');
}

function comprarAhora(idProducto) {
    const cantidad = document.getElementById('cantidad').value;
    console.log('Comprar ahora:', idProducto, 'cantidad:', cantidad);
    alert('Redirigiendo a checkout (funcionalidad en desarrollo)');
}
</script>
