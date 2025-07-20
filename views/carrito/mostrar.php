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
            <p class="carrito-descripcion">Revisa los productos que has agregado a tu carrito antes de finalizar tu compra.</p>
        </header>
        
        <div id="carrito-contenedor">
            <!-- El contenido del carrito se cargará dinámicamente con JavaScript -->
        </div>
    </div>
</div>

<!-- Estilos específicos del carrito -->
<style>
    .carrito-seccion {
        padding: 2rem 0;
    }
    
    .carrito-header {
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .carrito-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 1rem;
    }
    
    .carrito-descripcion {
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .carrito-vacio {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        text-align: center;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .carrito-vacio-icono {
        margin-bottom: 1.5rem;
        font-size: 3rem;
        color: #ccc;
    }
    
    .carrito-vacio-icono .material-icons {
        font-size: 4rem;
    }
    
    .carrito-vacio h2 {
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .carrito-vacio p {
        margin-bottom: 2rem;
        color: #666;
    }
    
    .carrito-contenido {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    
    .carrito-tabla {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .carrito-tabla th {
        background-color: #f9f9f9;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #555;
    }
    
    .carrito-tabla td {
        padding: 1rem;
        border-top: 1px solid #eee;
        vertical-align: middle;
    }
    
    .carrito-item {
        transition: background-color 0.2s;
    }
    
    .carrito-item:hover {
        background-color: #f9f9f9;
    }
    
    .producto-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .producto-imagen {
        width: 80px;
        height: 80px;
        overflow: hidden;
        border-radius: 6px;
        flex-shrink: 0;
    }
    
    .producto-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .producto-detalles {
        flex: 1;
    }
    
    .producto-detalles h3 {
        font-size: 1rem;
        font-weight: 500;
        margin: 0 0 0.5rem;
    }
    
    .vendedor, .tienda {
        font-size: 0.875rem;
        color: #666;
        margin: 0;
    }
    
    .cantidad-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        display: inline-flex;
    }
    
    .btn-cantidad {
        border: none;
        background: #f5f5f5;
        padding: 0.3rem 0.6rem;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        color: #555;
        transition: background-color 0.2s;
    }
    
    .btn-cantidad:hover {
        background: #e5e5e5;
    }
    
    .cantidad-actual {
        padding: 0.3rem 0.6rem;
        min-width: 2rem;
        text-align: center;
    }
    
    .btn-eliminar {
        background: none;
        border: none;
        cursor: pointer;
        color: #dc3545;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-eliminar:hover {
        color: #c82333;
    }
    
    .carrito-resumen {
        background: #fff;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .carrito-resumen h2 {
        font-size: 1.25rem;
        margin: 0 0 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #eee;
    }
    
    .resumen-detalle .fila {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    
    .resumen-detalle .total {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1rem;
    }
    
    .resumen-detalle hr {
        border: none;
        border-top: 1px solid #eee;
        margin: 1.5rem 0;
    }
    
    .resumen-acciones {
        margin-top: 2rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    @media (max-width: 768px) {
        .carrito-contenido {
            grid-template-columns: 1fr;
        }
        
        .carrito-tabla {
            overflow-x: auto;
            display: block;
        }
        
        .carrito-tabla thead {
            display: none;
        }
        
        .carrito-tabla tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        
        .carrito-tabla td {
            display: block;
            text-align: right;
            padding: 0.75rem;
            position: relative;
            border-top: none;
        }
        
        .carrito-tabla td:before {
            content: attr(data-label);
            float: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        
        .producto-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .producto-imagen {
            width: 100%;
            height: 120px;
        }
    }
</style>

<!-- Script para el carrito -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
