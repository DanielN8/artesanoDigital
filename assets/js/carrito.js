/**
 * carrito.js - Gestión centralizada del carrito
 * Responsable de sincronizar localStorage, base de datos y UI
 */

// Carrito global y elementos UI
let carritoGlobal = [];
let carritoUI = {
    contador: document.getElementById('contador-carrito'),
    miniCarrito: document.getElementById('mini-carrito'),
    totalCarrito: document.getElementById('total-carrito'),
    listadoProductos: document.getElementById('productos-carrito'),
    botonVaciar: document.getElementById('vaciar-carrito'),
    subtotalCheckout: document.getElementById('checkout-subtotal'),
    impuestosCheckout: document.getElementById('checkout-impuestos'),
    envioCheckout: document.getElementById('checkout-envio'),
    totalCheckout: document.getElementById('checkout-total')
};

// Al cargar el documento
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrito desde el servidor y respaldo en localStorage
    initCarrito();
    
    // Sincronizar con servidor en todas las páginas excepto el proceso de checkout
    if (!window.location.pathname.includes('/checkout_process')) {
        // Para asegurar que se actualice el carrito correctamente
        sincronizarCarritoConServidor();
    }
    
    // Añadir listeners para botones de vaciar carrito si existen
    if (carritoUI.botonVaciar) {
        carritoUI.botonVaciar.addEventListener('click', vaciarCarrito);
    }
    
    // Event listener para añadir al carrito (delegación de eventos)
    document.body.addEventListener('click', function(e) {
        // Botones de "Añadir al carrito" en listas de productos
        const addToCartBtn = e.target.closest('.btn-add-to-cart, .add-to-cart');
        if (addToCartBtn) {
            e.preventDefault();
            
            const idProducto = parseInt(addToCartBtn.getAttribute('data-id'));
            const nombre = addToCartBtn.getAttribute('data-nombre');
            const precio = parseFloat(addToCartBtn.getAttribute('data-precio'));
            const imagen = addToCartBtn.getAttribute('data-imagen');
            const stock = parseInt(addToCartBtn.getAttribute('data-stock') || '1000');
            
            agregarAlCarrito(idProducto, nombre, precio, imagen, 1, stock);
        }
    });
});

/**
 * Inicializar carrito desde localStorage
 */
function initCarrito() {
    carritoGlobal = JSON.parse(localStorage.getItem('carrito')) || [];
    
    // Asegurar que cada producto tiene un subtotal calculado
    carritoGlobal.forEach(item => {
        if (!item.subtotal) {
            item.subtotal = item.precio * item.cantidad;
        }
    });
    
    // Actualizar contador
    actualizarContadorCarrito(carritoGlobal.reduce((total, item) => total + item.cantidad, 0));
    
    // Actualizar minicarrito si existe
    if (typeof actualizarMiniCarrito === 'function') {
        actualizarMiniCarrito();
    }
    
    // Calcular y actualizar total si estamos en la página de carrito
    if (window.location.pathname.includes('carrito') || window.location.pathname.includes('checkout')) {
        actualizarTotalCarrito();
    }
}

/**
 * Sincronizar carrito de localStorage con el servidor
 */
function sincronizarCarritoConServidor() {
    const carritoLocal = localStorage.getItem('carrito');
    if (!carritoLocal) return; // Si no hay carrito local, no hay nada que sincronizar
    
    fetch('/artesanoDigital/controllers/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'accion=sincronizar_carrito&carrito_local=' + encodeURIComponent(carritoLocal)
    })
    .then(res => res.json())
    .then(data => {
        if (data.exitoso) {
            console.log('Carrito sincronizado con el servidor');
            
            // Si el servidor devolvió un carrito actualizado, actualizamos el localStorage
            if (data.carrito) {
                // Convertir formato de servidor a formato localStorage
                const carritoLocalFormat = data.carrito.map(item => ({
                    id: parseInt(item.id_producto),
                    nombre: item.nombre,
                    precio: parseFloat(item.precio),
                    imagen: item.imagen ? '/artesanoDigital/uploads/' + item.imagen : '/artesanoDigital/public/placeholder.jpg',
                    cantidad: parseInt(item.cantidad),
                    stock: parseInt(item.stock)
                }));
                
                // Actualizar carrito global y localStorage
                carritoGlobal = carritoLocalFormat;
                localStorage.setItem('carrito', JSON.stringify(carritoLocalFormat));
                
                // Actualizar contador
                actualizarContadorCarrito(carritoLocalFormat.reduce((total, item) => total + item.cantidad, 0));
                
                // Actualizar minicarrito si existe
                if (typeof actualizarMiniCarrito === 'function') {
                    actualizarMiniCarrito();
                }
            }
        }
    })
    .catch(error => {
        console.error('Error al sincronizar carrito:', error);
    });
}

/**
 * Actualizar contador de productos en el carrito
 * @param {number} nuevoTotal - Total de productos
 */
function actualizarContadorCarrito(nuevoTotal) {
    const contador = document.getElementById('carrito-contador');
    if (contador) {
        contador.textContent = nuevoTotal;
        
        // Efecto visual para el contador
        contador.classList.add('animate-pulse');
        setTimeout(() => contador.classList.remove('animate-pulse'), 500);
    }
}

/**
 * Agregar producto al carrito
 * @param {number|string} idProducto - ID del producto
 * @param {string} nombre - Nombre del producto (opcional si se obtiene del servidor)
 * @param {number|string} precio - Precio del producto (opcional si se obtiene del servidor)
 * @param {string} imagen - URL de la imagen (opcional si se obtiene del servidor)
 * @param {number} cantidad - Cantidad a agregar
 * @param {number} stock - Stock disponible
 * @param {string} artesano - Nombre del artesano (opcional)
 */
function agregarAlCarrito(idProducto, nombre, precio, imagen, cantidad = 1, stock = 0, artesano = '') {
    // Validar el ID del producto (único campo requerido)
    idProducto = parseInt(idProducto);
    cantidad = parseInt(cantidad) || 1;
    
    if (isNaN(idProducto) || idProducto <= 0) {
        mostrarMensaje('ID de producto inválido', 'error');
        return;
    }
    
    // Obtener datos adicionales del DOM si no fueron proporcionados
    if (!nombre || isNaN(parseFloat(precio))) {
        const tarjeta = document.querySelector(`.producto-tarjeta button[onclick*="agregarAlCarrito(${idProducto})"]`);
        if (tarjeta) {
            const contenedor = tarjeta.closest('.producto-tarjeta');
            if (!nombre && contenedor.querySelector('h3')) {
                nombre = contenedor.querySelector('h3').textContent.trim();
            }
            if (isNaN(parseFloat(precio)) && contenedor.querySelector('.producto-precio')) {
                precio = parseFloat(contenedor.querySelector('.producto-precio').textContent.replace('$', '').trim());
            }
            if (!imagen && contenedor.querySelector('img')) {
                imagen = contenedor.querySelector('img').getAttribute('src');
            }
            if (!artesano && contenedor.querySelector('.producto-artesano')) {
                artesano = contenedor.querySelector('.producto-artesano').textContent.replace('Por ', '').trim();
            }
        }
    }
    
    // Si no tenemos toda la información del producto, intentaremos obtenerla
    const datosCompletos = nombre && !isNaN(parseFloat(precio));
    const productoLocal = carritoGlobal.find(item => item.id === idProducto);
    
    // Si ya existe en el carrito, actualizamos cantidad
    if (productoLocal) {
        // Verificar que no exceda el stock
        if (stock > 0 && productoLocal.cantidad + cantidad > stock) {
            mostrarMensaje(`No se pueden agregar más unidades. Stock máximo: ${stock}`, 'warning');
            productoLocal.cantidad = stock;
        } else {
            productoLocal.cantidad += cantidad;
        }
        
        // Asegurarse que precio sea un número válido
        if (isNaN(productoLocal.precio) || productoLocal.precio <= 0) {
            console.warn(`Precio inválido para ${productoLocal.nombre}. Utilizando valor por defecto.`);
            productoLocal.precio = parseFloat(precio) || 0;
        }
        
        // Actualizar subtotal con validación
        productoLocal.subtotal = parseFloat((productoLocal.precio * productoLocal.cantidad).toFixed(2));
        
        // Guardar en localStorage
        localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
        
        // Mostrar mensaje de éxito
        mostrarMensaje(`${productoLocal.nombre} actualizado en el carrito`, 'success');
    } 
    // Si no existe, necesitamos añadirlo
    else if (datosCompletos) {
        // Si tenemos todos los datos, lo añadimos directamente
        // Validar que precio sea un número válido
        precio = parseFloat(precio);
        if (isNaN(precio) || precio < 0) {
            console.error("Precio inválido:", precio);
            precio = 0;
            mostrarMensaje("Error: Precio de producto inválido", 'error');
        }
        
        const nuevoProducto = {
            id: idProducto,
            nombre: nombre,
            precio: precio,
            imagen: imagen || '/artesanoDigital/public/placeholder.jpg',
            artesano: artesano || '',
            cantidad: cantidad,
            stock: stock,
            subtotal: parseFloat((precio * cantidad).toFixed(2))
        };
        
        carritoGlobal.push(nuevoProducto);
        localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
        
        // Mostrar mensaje de éxito
        mostrarMensaje(`${nombre} añadido al carrito`, 'success');
    } 
    // Si no tenemos datos completos, mostrar indicador mientras obtenemos datos
    else {
        mostrarMensaje('Agregando al carrito...', 'info');
    }
    
    // Indicar que hay carrito en una cookie para detectarlo en carga inicial
    document.cookie = "has_cart=1; path=/; max-age=86400";
    
    // Sincronizar con el servidor y obtener datos completos del producto
    // Cambiamos la ruta para usar el controlador específico de carrito
    fetch('/artesanoDigital/controllers/ControladorCarrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `accion=agregar_producto&id_producto=${idProducto}&cantidad=${cantidad}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.exitoso) {
            // Si el servidor devuelve un carrito completo actualizado, lo usamos
            if (data.carrito) {
                carritoGlobal = data.carrito;
                localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
            } 
            // Si solo devuelve datos del producto añadido
            else if (data.producto && !datosCompletos && !productoLocal) {
                const nuevoProducto = {
                    id: idProducto,
                    nombre: data.producto.nombre,
                    precio: parseFloat(data.producto.precio),
                    imagen: data.producto.imagen || '/artesanoDigital/public/placeholder.jpg',
                    artesano: data.producto.artesano || '',
                    cantidad: cantidad,
                    stock: data.producto.stock || 0,
                    subtotal: parseFloat(data.producto.precio) * cantidad
                };
                
                carritoGlobal.push(nuevoProducto);
                localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
            }
            
            // Actualizar contador con el valor del servidor
            actualizarContadorCarrito(data.total_productos || carritoGlobal.reduce((total, item) => total + item.cantidad, 0));
            
            // Actualizar el total y todas las vistas del carrito
            actualizarTotalCarrito();
            actualizarMiniCarrito();
            
            // Actualizar el minicarrito si existe la función
            if (typeof actualizarMiniCarrito === 'function') {
                actualizarMiniCarrito();
            }
            
            // Mensaje de éxito
            mostrarMensaje(data.mensaje || 'Producto agregado al carrito', 'success');
        } else {
            mostrarMensaje(data.mensaje || 'Error al agregar al carrito', 'error');
            
            // Si no había datos completos y falló la operación, eliminar del carrito local
            if (!datosCompletos && !productoLocal) {
                carritoGlobal = carritoGlobal.filter(item => item.id !== idProducto);
                localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
            }
        }
    })
    .catch(error => {
        console.error('Error al agregar producto:', error);
        mostrarMensaje('Error al agregar el producto', 'error');
    });
}

/**
 * Eliminar producto del carrito
 * @param {number} idProducto - ID del producto a eliminar
 */
function eliminarDelCarrito(idProducto) {
    idProducto = parseInt(idProducto);
    
    // Guardar nombre del producto antes de eliminarlo
    const producto = carritoGlobal.find(item => item.id === idProducto);
    const nombreProducto = producto ? producto.nombre : 'Producto';
    
    // Eliminar del carrito local
    carritoGlobal = carritoGlobal.filter(item => item.id !== idProducto);
    localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
    
    // Actualizar contador y total localmente primero para mejor experiencia
    actualizarContadorCarrito(carritoGlobal.reduce((total, item) => total + item.cantidad, 0));
    actualizarTotalCarrito();
    
    // Si estamos en la página de carrito, eliminar el elemento de la UI
    if (window.location.pathname.includes('carrito') || window.location.pathname.includes('checkout')) {
        const productoElement = document.querySelector(`.producto-carrito[data-id="${idProducto}"]`);
        if (productoElement) {
            productoElement.classList.add('eliminando');
            setTimeout(() => {
                productoElement.remove();
                
                // Verificar si el carrito está vacío ahora
                const productosCarrito = document.querySelectorAll('.producto-carrito');
                if (productosCarrito.length === 0) {
                    const contenedorCarrito = document.querySelector('.contenedor-carrito');
                    if (contenedorCarrito) {
                        contenedorCarrito.innerHTML = '<div class="carrito-vacio"><p>Tu carrito está vacío</p><a href="/artesanoDigital/productos" class="btn">Ver productos</a></div>';
                    }
                }
            }, 300);
        }
    }
    
    // Actualizar en el servidor
    fetch('/artesanoDigital/controllers/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'accion=eliminar_producto&id_producto=' + idProducto
    })
    .then(res => res.json())
    .then(data => {
        if (data.exitoso) {
            // Actualizar minicarrito si existe
            if (typeof actualizarMiniCarrito === 'function') {
                actualizarMiniCarrito();
            }
            
            // Mensaje personalizado con nombre del producto
            mostrarMensaje(`${nombreProducto} eliminado del carrito`, 'info');
            
            // Si el servidor devuelve el carrito actualizado, usar esos datos
            if (data.carrito && Array.isArray(data.carrito)) {
                actualizarContadorCarrito(data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0));
            }
        } else {
            mostrarMensaje(data.mensaje || 'Error al eliminar producto', 'error');
        }
    })
    .catch(error => {
        console.error('Error al eliminar producto del servidor:', error);
        mostrarMensaje('Error al eliminar producto', 'error');
    });
}

/**
 * Actualizar cantidad de un producto en el carrito
 * @param {number} idProducto - ID del producto
 * @param {number} nuevaCantidad - Nueva cantidad
 */
function actualizarCantidadCarrito(idProducto, nuevaCantidad) {
    idProducto = parseInt(idProducto);
    nuevaCantidad = parseInt(nuevaCantidad);
    
    if (nuevaCantidad <= 0) {
        eliminarDelCarrito(idProducto);
        return;
    }
    
    // Actualizar en carrito local
    const producto = carritoGlobal.find(item => item.id === idProducto);
    if (producto) {
        // Verificar stock
        if (producto.stock > 0 && nuevaCantidad > producto.stock) {
            mostrarMensaje(`Stock máximo: ${producto.stock}`, 'warning');
            nuevaCantidad = producto.stock;
        }
        
        // Guardar antigua cantidad para mensaje
        const antiguaCantidad = producto.cantidad;
        
        // Actualizar cantidad y subtotal
        producto.cantidad = nuevaCantidad;
        producto.subtotal = producto.precio * nuevaCantidad;
        
        // Guardar en localStorage
        localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
        
        // Actualizar UI localmente primero para mejor experiencia
        actualizarContadorCarrito(carritoGlobal.reduce((total, item) => total + item.cantidad, 0));
        actualizarTotalCarrito();
        
        // Si estamos en la página de carrito, actualizar UI de subtotal
        if (window.location.pathname.includes('carrito') || window.location.pathname.includes('checkout')) {
            const subtotalElement = document.querySelector(`.producto-carrito[data-id="${idProducto}"] .subtotal`);
            if (subtotalElement) {
                subtotalElement.textContent = '$' + producto.subtotal.toFixed(2);
            }
        }
        
        // Actualizar en servidor
        fetch('/artesanoDigital/controllers/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `accion=actualizar_cantidad&id_producto=${idProducto}&cantidad=${nuevaCantidad}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.exitoso) {
                // Actualizar minicarrito si existe
                if (typeof actualizarMiniCarrito === 'function') {
                    actualizarMiniCarrito();
                }
                
                // Mostrar mensaje apropiado
                if (nuevaCantidad > antiguaCantidad) {
                    mostrarMensaje(`${producto.nombre}: cantidad aumentada a ${nuevaCantidad}`, 'success');
                } else {
                    mostrarMensaje(`${producto.nombre}: cantidad reducida a ${nuevaCantidad}`, 'info');
                }
                
                // Si el servidor devuelve el carrito actualizado, usar esos datos
                if (data.carrito && Array.isArray(data.carrito)) {
                    const total = data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0);
                    actualizarContadorCarrito(total);
                }
            } else {
                mostrarMensaje(data.mensaje || 'Error al actualizar cantidad', 'error');
            }
        })
        .catch(error => {
            console.error('Error al actualizar cantidad:', error);
            mostrarMensaje('Error al actualizar cantidad', 'error');
        });
    }
}

/**
 * Actualiza el total del carrito calculando la suma de los subtotales
 */
function actualizarTotalCarrito() {
    // Calcular el total con validación para evitar NaN
    let total = 0;
    try {
        total = carritoGlobal.reduce((sum, item) => {
            const itemSubtotal = item.subtotal || (item.precio * item.cantidad);
            // Verificar que el subtotal sea un número válido
            if (isNaN(itemSubtotal) || itemSubtotal < 0) {
                console.warn(`Subtotal inválido para ${item.nombre}:`, itemSubtotal);
                return sum;
            }
            return sum + itemSubtotal;
        }, 0);
    } catch (error) {
        console.error("Error al calcular total:", error);
        total = 0;
    }
    
    // Actualizar en la UI si estamos en la página de carrito o checkout
    const totalElement = document.querySelector('.total-carrito');
    if (totalElement) {
        totalElement.textContent = '$' + total.toFixed(2);
        console.log("Actualizando total-carrito:", total.toFixed(2));
    }
    
    // Actualizar total en el resumen si estamos en checkout
    const resumenTotal = document.querySelector('.resumen-total');
    if (resumenTotal) {
        resumenTotal.textContent = '$' + total.toFixed(2);
        console.log("Actualizando resumen-total:", total.toFixed(2));
    }
    
    // También actualizar cualquier otro elemento que muestre el total
    const otrosTotales = document.querySelectorAll('.cart-total, .checkout-total');
    otrosTotales.forEach(el => {
        if (el) {
            el.textContent = '$' + total.toFixed(2);
        }
    });
    
    // Actualizar los elementos de UI específicos si existen (nuevo)
    if (carritoUI.totalCarrito) {
        carritoUI.totalCarrito.textContent = '$' + total.toFixed(2);
    }
    
    // Actualizar elementos de checkout si estamos en esa página (nuevo)
    if (carritoUI.subtotalCheckout) {
        // Verificar que total sea un número válido
        if (isNaN(total) || total < 0) {
            console.error("Error: Total inválido", total);
            total = 0; // Corregir para evitar cálculos NaN
        }
        
        const impuestos = total * 0.07; // 7% ITBMS en Panamá
        const envio = 0.00; // Envío gratis
        const totalFinal = total + impuestos + envio;
        
        // Actualizar los elementos visuales
        carritoUI.subtotalCheckout.textContent = '$' + total.toFixed(2);
        carritoUI.impuestosCheckout.textContent = '$' + impuestos.toFixed(2);
        carritoUI.envioCheckout.textContent = '$' + envio.toFixed(2);
        carritoUI.totalCheckout.textContent = '$' + totalFinal.toFixed(2);
        
        // Actualizar todos los inputs hidden con los valores correctos
        const inputTotal = document.querySelector('input[name="total_pedido"]');
        if (inputTotal) {
            inputTotal.value = totalFinal.toFixed(2);
            console.log("Actualizando input total_pedido con:", totalFinal.toFixed(2));
        }
        
        const inputSubtotal = document.querySelector('input[name="subtotal"]');
        if (inputSubtotal) {
            inputSubtotal.value = total.toFixed(2);
        }
        
        const inputImpuestos = document.querySelector('input[name="impuestos"]');
        if (inputImpuestos) {
            inputImpuestos.value = impuestos.toFixed(2);
        }
        
        const inputEnvio = document.querySelector('input[name="envio"]');
        if (inputEnvio) {
            inputEnvio.value = envio.toFixed(2);
        }
    }
    
    return total;
}

/**
 * Vaciar todo el carrito
 */
function vaciarCarrito() {
    if (confirm('¿Estás seguro de que quieres vaciar todo tu carrito?')) {
        // Vaciar carrito local
        carritoGlobal = [];
        localStorage.removeItem('carrito');
        
        // Actualizar contador localmente primero para mejor experiencia
        actualizarContadorCarrito(0);
        
        // Si estamos en la página de carrito, actualizar la UI
        if (window.location.pathname.includes('carrito') || window.location.pathname.includes('checkout')) {
            const contenedorCarrito = document.querySelector('.contenedor-carrito');
            if (contenedorCarrito) {
                // Añadir una animación sutil antes de vaciar
                const productos = document.querySelectorAll('.producto-carrito');
                productos.forEach(prod => {
                    prod.classList.add('eliminando');
                });
                
                // Después de un breve retraso, mostrar el mensaje de carrito vacío
                setTimeout(() => {
                    contenedorCarrito.innerHTML = '<div class="carrito-vacio"><p>Tu carrito está vacío</p><a href="/artesanoDigital/productos" class="btn">Ver productos</a></div>';
                    // Actualizar total a cero
                    const totalElement = document.querySelector('.total-carrito');
                    if (totalElement) {
                        totalElement.textContent = '$0.00';
                    }
                    // Actualizar total en resumen si es checkout
                    const resumenTotal = document.querySelector('.resumen-total');
                    if (resumenTotal) {
                        resumenTotal.textContent = '$0.00';
                    }
                }, 300);
            }
        }
        
        // Vaciar en el servidor
        fetch('/artesanoDigital/controllers/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'accion=vaciar_carrito'
        })
        .then(res => res.json())
        .then(data => {
            if (data.exitoso) {
                // Actualizar minicarrito si existe
                if (typeof actualizarMiniCarrito === 'function') {
                    actualizarMiniCarrito();
                }
                
                // Mostrar mensaje de confirmación
                mostrarMensaje('Tu carrito ha sido vaciado', 'info');
            } else {
                mostrarMensaje(data.mensaje || 'Error al vaciar carrito', 'error');
            }
        })
        .catch(error => {
            console.error('Error al vaciar carrito:', error);
            mostrarMensaje('Error de conexión al vaciar carrito', 'error');
        });
    }
}

/**
 * Mostrar un mensaje toast
 * @param {string} mensaje - Texto del mensaje
 * @param {string} tipo - Tipo de mensaje (info, success, error, warning)
 */
function mostrarMensaje(mensaje, tipo = 'info') {
    // Si hay un contenedor específico para mensajes, usarlo
    const msgContainer = document.getElementById('cart-message-container');
    if (msgContainer) {
        // Crear una alerta Bootstrap
        const alertClass = tipo === 'error' ? 'alert-danger' : 
                        tipo === 'success' ? 'alert-success' : 
                        tipo === 'warning' ? 'alert-warning' : 'alert-info';
        
        msgContainer.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        // Auto-cerrar después de 3 segundos
        setTimeout(() => {
            const alert = msgContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => {
                    msgContainer.innerHTML = '';
                }, 150);
            }
        }, 3000);
        
        return;
    }
    
    // Si no hay contenedor específico, usar un toast
    // Verificar si ya existe el contenedor de toasts, si no, crearlo
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // Añadir estilos si no existen
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                .toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                }
                
                .toast {
                    max-width: 350px;
                    background-color: #fff;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                    border-radius: 0.25rem;
                    margin-bottom: 0.75rem;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                
                .toast-mostrar {
                    opacity: 1;
                }
                
                .toast-contenido {
                    padding: 0.75rem 1.25rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                
                .toast-cerrar {
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    font-weight: 700;
                    line-height: 1;
                    color: #000;
                    opacity: 0.5;
                    cursor: pointer;
                }
                
                .toast-info {
                    border-left: 4px solid #17a2b8;
                }
                
                .toast-success {
                    border-left: 4px solid #28a745;
                }
                
                .toast-error {
                    border-left: 4px solid #dc3545;
                }
                
                .toast-warning {
                    border-left: 4px solid #ffc107;
                }
                
                /* Animación para el contador */
                .animate-pulse {
                    animation: pulse 1s cubic-bezier(0, 0, 0.2, 1) infinite;
                }
                
                @keyframes pulse {
                    0%, 100% {
                        opacity: 1;
                    }
                    50% {
                        opacity: 0.5;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Crear el toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <div class="toast-contenido">
            <span class="toast-mensaje">${mensaje}</span>
            <button class="toast-cerrar">&times;</button>
        </div>
    `;
    
    // Añadir al contenedor
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
