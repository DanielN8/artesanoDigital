<?php
// Variables para el layout
$titulo = $titulo ?? 'Carrito de Compras - Artesano Digital';
$descripcion = $descripcion ?? 'Tu carrito de compras en Artesano Digital';

// Iniciar captura de contenido
ob_start();
?>

<div class="contenedor">
    <div class="carrito-seccion">
        <header class="carrito-header">
            <h1>Tu Carrito de Compras</h1>
            <p class="carrito-descripcion">Revisa los productos que has agregado a tu carrito antes de finalizar tu
                compra.</p>
            <?php
            // Asegúrate de que las rutas de imágenes apunten correctamente a la carpeta public
            // Las imágenes de productos se encuentran en /artesanoDigital/public/productos/
            ?>
        </header>

        <div id="carrito-contenedor">
            <!-- El contenido del carrito se cargará dinámicamente con JavaScript -->
        </div>
    </div>
</div>

<link rel="stylesheet" href="/artesanoDigital/assets/css/carrito.css">

<!-- Script para el carrito -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        cargarCarrito();
    });

    function cargarCarrito() {
        const carritoContenedor = document.getElementById('carrito-contenedor');

        // Usar la clave específica para el usuario actual
        const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';

        const carrito = JSON.parse(localStorage.getItem(carritoKey)) || [];

        if (carrito.length === 0) {
            // Mostrar carrito vacío
            carritoContenedor.innerHTML = `
                <div class="carrito-vacio">
                    <div class="carrito-vacio-icono">
                        <span class="material-icons">shopping_cart_off</span>
                    </div>
                    <h2>Tu carrito está vacío</h2>
                    <p>Parece que aún no has agregado productos a tu carrito.</p>
                    <a href="/artesanoDigital/productos" class="btn btn-primario">Explorar Productos</a>
                </div>
            `;
            return;
        }

        // Calcular total
        const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);

        // Generar HTML para mostrar los productos
        let html = `
            <div class="carrito-contenido">
                <div class="carrito-productos">
                    <table class="carrito-tabla">
                        <thead>
                            <tr>
                                <th colspan="2">Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        // Agregar cada producto
        carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            html += `
                <tr class="carrito-item" data-id="${item.id}">
                    <td class="producto-imagen">
                        <img src="${item.imagen}" alt="${item.nombre}">
                    </td>
                    <td class="producto-detalles">
                        <h3>${item.nombre}</h3>
                        <span class="tienda">Por ${item.artesano}</span>
                    </td>
                    <td class="producto-precio">$${item.precio.toFixed(2)}</td>
                    <td class="producto-cantidad">
                        <div class="cantidad-selector">
                            <button class="btn-cantidad" onclick="actualizarCantidad(${item.id}, ${Math.max(1, item.cantidad - 1)})">-</button>
                            <span class="cantidad-actual">${item.cantidad}</span>
                            <button class="btn-cantidad" onclick="actualizarCantidad(${item.id}, ${item.cantidad + 1})">+</button>
                        </div>
                    </td>
                    <td class="producto-total">$${subtotal.toFixed(2)}</td>
                    <td class="producto-acciones">
                        <button class="btn-eliminar" onclick="eliminarProducto(${item.id})">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                        </tbody>
                    </table>
                </div>
                
                <div class="carrito-resumen">
                    <h2>Resumen del Pedido</h2>
                    <div class="resumen-detalle">
                        <div class="fila">
                            <span>Subtotal</span>
                            <span>$${total.toFixed(2)}</span>
                        </div>
                        <div class="fila">
                            <span>Envío</span>
                            <span>Calculado en el checkout</span>
                        </div>
                        <hr>
                        <div class="fila total">
                            <span>Total</span>
                            <span>$${total.toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="resumen-acciones">
                        <a href="/artesanoDigital/productos" class="btn btn-outline">Seguir Comprando</a>
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <a href="/artesanoDigital/checkout/cart_process.php?step=checkout" class="btn btn-primario">Proceder a pagar</a>
                        <?php else: ?>
                            <a href="/artesanoDigital/login" class="btn btn-primario">Inicia Sesión para Comprar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        `;

        carritoContenedor.innerHTML = html;
    }

    function actualizarCantidad(idProducto, nuevaCantidad) {
        // Validar cantidad
        if (nuevaCantidad < 1) return;

        // Usar la clave específica para el usuario actual
        const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';

        // Obtener carrito con la clave específica
        let carrito = JSON.parse(localStorage.getItem(carritoKey)) || [];

        // Buscar producto
        const producto = carrito.find(item => item.id === idProducto);
        if (!producto) return;

        // Actualizar cantidad
        producto.cantidad = nuevaCantidad;

        // Guardar carrito con la clave específica
        localStorage.setItem(carritoKey, JSON.stringify(carrito));
        // Mantener compatibilidad
        localStorage.setItem('carrito', JSON.stringify(carrito));

        // Actualizar vista
        cargarCarrito();
        actualizarContadorCarrito(carrito.reduce((total, item) => total + item.cantidad, 0));

        // Mostrar mensaje
        mostrarMensaje('Cantidad actualizada', 'info');
    }

    function eliminarProducto(idProducto) {
        // Usar la clave específica para el usuario actual
        const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';

        let carrito = JSON.parse(localStorage.getItem(carritoKey)) || [];
        carrito = carrito.filter(item => item.id !== idProducto);

        // Guardar con la clave específica del usuario
        localStorage.setItem(carritoKey, JSON.stringify(carrito));
        // Mantener compatibilidad
        localStorage.setItem('carrito', JSON.stringify(carrito));

        // Actualizar vistas
        cargarCarrito();
        actualizarContadorCarrito(carrito.reduce((total, item) => total + item.cantidad, 0));

        // Mostrar mensaje
        mostrarMensaje('Producto eliminado del carrito', 'info');
    }

    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo = 'info') {
        // Crear elemento de toast
        const toast = document.createElement('div');
        toast.className = `toast toast-${tipo}`;
        toast.innerHTML = `
            <div class="toast-contenido">
                <span class="toast-mensaje">${mensaje}</span>
                <button class="toast-cerrar">&times;</button>
            </div>
        `;

        // Agregar al contenedor de toasts
        const toastContainer = document.querySelector('.toast-container') || (() => {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
            return container;
        })();

        toastContainer.appendChild(toast);

        // Mostrar con animación
        setTimeout(() => toast.classList.add('toast-mostrar'), 10);

        // Auto cerrar después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('toast-mostrar');
            setTimeout(() => toast.remove(), 300);
        }, 3000);

        // Evento para cerrar manualmente
        toast.querySelector('.toast-cerrar').addEventListener('click', () => {
            toast.classList.remove('toast-mostrar');
            setTimeout(() => toast.remove(), 300);
        });
    }
</script>

<?php
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>