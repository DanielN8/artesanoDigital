<?php 
// Variables para el layout
$titulo = isset($producto) ? $producto['nombre'] . ' - Artesano Digital' : 'Producto - Artesano Digital';
$descripcion = isset($producto) ? substr($producto['descripcion'], 0, 150) . '...' : 'Producto artesanal hecho a mano en Panamá Oeste';
$estilosAdicionales = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
    '/artesanoDigital/assets/css/detalle-producto.css'
];

// Iniciar captura de contenido
ob_start();
?>

<main class="main-contenido">
    <div class="contenedor">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/artesanoDigital/">
                <i class="fas fa-home"></i>
                Inicio
            </a>
            <span><i class="fas fa-chevron-right"></i></span>
            <a href="/artesanoDigital/productos">
                <i class="fas fa-tags"></i>
                Productos
            </a>
            <span><i class="fas fa-chevron-right"></i></span>
            <span><?= htmlspecialchars($producto['nombre']) ?></span>
        </nav>

        <!-- Detalle del producto -->
        <div class="producto-detalle">
            <div class="producto-imagenes">
                <div class="imagen-principal">
                    <?php
                    // Determinar la ruta correcta de la imagen para detalle
                    $rutaImagenDetalle = '/artesanoDigital/public/placeholder.jpg';
                    if (!empty($producto['imagen'])) {
                        // Si la imagen ya incluye 'public/' o 'uploads/', usar tal como está
                        if (strpos($producto['imagen'], 'public/') === 0 || strpos($producto['imagen'], 'uploads/') === 0) {
                            $rutaImagenDetalle = '/artesanoDigital/' . $producto['imagen'];
                        } else {
                            // Si no, intentar con uploads/productos/ primero
                            $rutaImagenDetalle = '/artesanoDigital/uploads/productos/' . $producto['imagen'];
                        }
                    }
                    ?>
                    <img src="<?= htmlspecialchars($rutaImagenDetalle) ?>" 
                         alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                         class="imagen-grande"
                         onerror="this.src='/artesanoDigital/public/placeholder.jpg'"
                    
                    <!-- Badge de descuento si existe -->
                    <?php if (isset($producto['descuento']) && $producto['descuento'] > 0): ?>
                        <?php $porcentajeDescuento = round(($producto['descuento'] / $producto['precio']) * 100); ?>
                        <div class="badge-descuento">
                            -<?= $porcentajeDescuento ?>%
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="producto-informacion">
                <h1 class="producto-titulo"><?= htmlspecialchars($producto['nombre']) ?></h1>
                
                <div class="producto-meta">
                    <div class="artesano-info">
                        <i class="fas fa-user-tie"></i>
                        <span>Por <strong><?= htmlspecialchars($producto['nombre_artesano'] ?? 'Artesano') ?></strong></span>
                    </div>
                    <div class="tienda-info">
                        <i class="fas fa-store"></i>
                        <span>Tienda: <strong><?= htmlspecialchars($producto['nombre_tienda'] ?? 'Tienda') ?></strong></span>
                    </div>
                </div>

                <div class="producto-precio-contenedor">
                    <?php if (isset($producto['descuento']) && $producto['descuento'] > 0): ?>
                        <span class="precio-original">$<?= number_format($producto['precio'], 2) ?></span>
                        <span class="precio-actual">$<?= number_format($producto['precio'] - $producto['descuento'], 2) ?></span>
                        <span class="ahorro">¡Ahorras $<?= number_format($producto['descuento'], 2) ?>!</span>
                    <?php else: ?>
                        <span class="precio-actual">$<?= number_format($producto['precio'], 2) ?></span>
                    <?php endif; ?>
                </div>

                <div class="producto-stock">
                    <?php if ($producto['stock'] > 0): ?>
                        <span class="stock-disponible">
                            <i class="fas fa-check-circle"></i>
                            En stock (<?= $producto['stock'] ?> disponibles)
                        </span>
                    <?php else: ?>
                        <span class="stock-agotado">
                            <i class="fas fa-times-circle"></i>
                            Agotado
                        </span>
                    <?php endif; ?>
                </div>

                <div class="producto-descripcion-detalle">
                    <h3><i class="fas fa-info-circle"></i> Descripción</h3>
                    <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                </div>

                <div class="producto-acciones-detalle">
                    <?php if ($producto['stock'] > 0): ?>
                        <div class="cantidad-selector">
                            <label for="cantidad">
                                <i class="fas fa-sort-numeric-up"></i>
                                Cantidad:
                            </label>
                            <div class="cantidad-control">
                                <button type="button" class="btn-cantidad" onclick="decrementarCantidad()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="cantidad" class="input-cantidad" value="1" min="1" max="<?= $producto['stock'] ?>">
                                <button type="button" class="btn-cantidad" onclick="incrementarCantidad()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="botones-accion">
                            <button class="btn btn-primario btn-grande btn-carrito" onclick="agregarAlCarrito(<?= $producto['id_producto'] ?>)">
                                <i class="fas fa-cart-plus" style="margin-right: 8px;"></i>
                                Agregar al Carrito
                            </button>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-deshabilitado btn-grande" disabled>
                            <i class="fas fa-ban" style="margin-right: 8px;"></i>
                            Producto Agotado
                        </button>
                    <?php endif; ?>
                </div>

                <div class="producto-caracteristicas">
                    <h3><i class="fas fa-list"></i> Características</h3>
                    <ul>
                        <li><strong>Producto:</strong> Hecho a mano con técnicas tradicionales</li>
                        <li><strong>Origen:</strong> Panamá Oeste</li>
                        <li><strong>Artesano:</strong> <?= htmlspecialchars($producto['nombre_artesano'] ?? 'Local') ?></li>
                        <li><strong>Fecha de creación:</strong> <?= date('d/m/Y', strtotime($producto['fecha_creacion'])) ?></li>
                        <?php if (isset($producto['telefono']) && $producto['telefono']): ?>
                            <li><strong>Contacto:</strong> <?= htmlspecialchars($producto['telefono']) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Información adicional de la tienda -->
                <div class="tienda-detalle">
                    <h3><i class="fas fa-store-alt"></i> Sobre la Tienda</h3>
                    <div class="tienda-info-completa">
                        <?php if (isset($producto['imagen_logo']) && $producto['imagen_logo']): ?>
                            <img src="/artesanoDigital/uploads/logos/<?= htmlspecialchars($producto['imagen_logo']) ?>" 
                                 alt="Logo <?= htmlspecialchars($producto['nombre_tienda']) ?>" 
                                 class="tienda-logo"
                                 onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div class="tienda-texto">
                            <h4><?= htmlspecialchars($producto['nombre_tienda']) ?></h4>
                            <?php if (isset($producto['descripcion_tienda']) && $producto['descripcion_tienda']): ?>
                                <p><?= nl2br(htmlspecialchars($producto['descripcion_tienda'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos relacionados -->
        <section class="productos-relacionados">
            <h2><i class="fas fa-heart"></i> Productos que podrían interesarte</h2>
            <div class="productos-grid">
                <!-- Aquí se pueden cargar productos relacionados dinámicamente -->
                <div class="producto-tarjeta-mini">
                    <img src="/artesanoDigital/public/placeholder.jpg" alt="Producto relacionado" class="producto-imagen-mini">
                    <div class="producto-info-mini">
                        <h4>Producto Similar</h4>
                        <p class="precio-mini">$30.00</p>
                        <button class="btn-mini">Ver</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Toast Container para notificaciones -->
<div class="toast-container"></div>

<script>
// Variables globales
let stockMaximo = <?= $producto['stock'] ?>;

// Función para incrementar cantidad
function incrementarCantidad() {
    const cantidadInput = document.getElementById('cantidad');
    let cantidad = parseInt(cantidadInput.value);
    if (cantidad < stockMaximo) {
        cantidadInput.value = cantidad + 1;
    }
}

// Función para decrementar cantidad
function decrementarCantidad() {
    const cantidadInput = document.getElementById('cantidad');
    let cantidad = parseInt(cantidadInput.value);
    if (cantidad > 1) {
        cantidadInput.value = cantidad - 1;
    }
}

// Función mejorada para agregar al carrito
function agregarAlCarrito(idProducto) {
    const cantidad = document.getElementById('cantidad').value;
    const cantidadNum = parseInt(cantidad);
    
    // Validaciones
    if (cantidadNum < 1 || cantidadNum > stockMaximo) {
        mostrarMensaje('Cantidad no válida', 'error');
        return;
    }
    
    // Obtener el botón para mostrar estado de carga
    const boton = document.querySelector('.btn-carrito');
    const textoOriginal = boton.innerHTML;
    
    // Deshabilitar botón y mostrar carga
    boton.disabled = true;
    boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agregando...';
    
    // Llamar a la API del carrito
    fetch('/artesanoDigital/api/carrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'agregar',
            id_producto: idProducto,
            cantidad: cantidadNum
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.exitoso) {
            mostrarMensaje(data.mensaje || 'Producto agregado al carrito', 'success');
            
            // Actualizar contador del carrito si existe
            if (data.carrito && data.carrito.cantidad_total) {
                actualizarContadorCarrito(data.carrito.cantidad_total);
            }
            
            // Cambiar botón temporalmente a "Agregado"
            boton.innerHTML = '<i class="fas fa-check"></i> ¡Agregado!';
            boton.classList.add('success');
            
            setTimeout(() => {
                boton.innerHTML = textoOriginal;
                boton.classList.remove('success');
                boton.disabled = false;
            }, 2000);
        } else {
            mostrarMensaje(data.mensaje || 'Error al agregar producto', 'error');
            // Restaurar botón
            boton.innerHTML = textoOriginal;
            boton.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión al agregar producto', 'error');
        // Restaurar botón
        boton.innerHTML = textoOriginal;
        boton.disabled = false;
    });
}

function comprarAhora(idProducto) {
    // Primero agregar al carrito
    agregarAlCarrito(idProducto);
    
    // Luego redirigir al checkout después de un breve delay
    setTimeout(() => {
        window.location.href = '/artesanoDigital/checkout';
    }, 1500);
}

// Función para actualizar el contador del carrito
function actualizarContadorCarrito(cantidad) {
    const contadores = document.querySelectorAll('.carrito-contador, .cart-count, #cart-count');
    
    contadores.forEach(contador => {
        contador.textContent = cantidad;
        contador.style.display = cantidad > 0 ? 'inline' : 'none';
        
        // Agregar efecto visual
        contador.style.animation = 'pulso 0.3s ease-in-out';
        setTimeout(() => {
            contador.style.animation = '';
        }, 300);
    });
}

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'info') {
    // Remover mensajes anteriores
    const mensajesAnteriores = document.querySelectorAll('.toast');
    mensajesAnteriores.forEach(toast => toast.remove());
    
    // Crear nuevo toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <div class="toast-contenido">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : tipo === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
            <span class="toast-mensaje">${mensaje}</span>
            <button class="toast-cerrar" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Agregar estilos si no existen
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    document.querySelector('.toast-container').appendChild(toast);
    
    // Auto cerrar
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// Validación de cantidad en tiempo real
document.getElementById('cantidad').addEventListener('input', function() {
    let valor = parseInt(this.value);
    if (valor < 1) {
        this.value = 1;
    } else if (valor > stockMaximo) {
        this.value = stockMaximo;
        mostrarMensaje(`Solo hay ${stockMaximo} unidades disponibles`, 'warning');
    }
});
</script>

<?php 
// Capturar el contenido
$contenido = ob_get_clean();

// Incluir el layout base
include __DIR__ . '/../layouts/base.php';
?>
