/**
 * carrito-usuario.js - Funciones para manejo de carrito por usuario
 * Este archivo contiene funciones específicas para manejar múltiples carritos basados en usuarios
 */

// Función para limpiar carrito de sesión anterior
function limpiarCarritoAnterior() {
    // Limpiar solo el carrito anónimo si hay usuario
    const usuarioActualId = document.body.getAttribute('data-user-id') || null;
    
    if (usuarioActualId) {
        // Si hay usuario, eliminamos el carrito anónimo
        console.log('Usuario identificado, eliminando carrito anónimo');
        localStorage.removeItem('carrito_invitado');
        
        // Aseguramos que existe el carrito del usuario actual
        const carritoUsuario = localStorage.getItem(`carrito_${usuarioActualId}`);
        if (!carritoUsuario) {
            console.log('Inicializando carrito para el usuario', usuarioActualId);
            localStorage.setItem(`carrito_${usuarioActualId}`, JSON.stringify([]));
        }
    }
}

// Función para obtener la clave correcta del carrito según usuario
function obtenerClaveCarrito() {
    const usuarioActualId = document.body.getAttribute('data-user-id') || null;
    return usuarioActualId ? `carrito_${usuarioActualId}` : 'carrito_invitado';
}

// Función para migrar carrito anónimo al usuario que inicia sesión
function migrarCarritoAnonimo(idUsuarioNuevo) {
    if (!idUsuarioNuevo) return;
    
    // Obtener carrito anónimo
    const carritoAnonimo = JSON.parse(localStorage.getItem('carrito_invitado')) || [];
    if (carritoAnonimo.length === 0) return;
    
    console.log('Migrando carrito anónimo al usuario', idUsuarioNuevo);
    
    // Obtener o crear carrito del usuario
    let carritoUsuario = JSON.parse(localStorage.getItem(`carrito_${idUsuarioNuevo}`)) || [];
    
    // Fusionar carritos (añadir productos del carrito anónimo al del usuario)
    carritoAnonimo.forEach(productoAnonimo => {
        const productoExistente = carritoUsuario.find(p => p.id === productoAnonimo.id);
        
        if (productoExistente) {
            // Si ya existe, solo sumar cantidades
            productoExistente.cantidad += productoAnonimo.cantidad;
        } else {
            // Si no existe, añadir el producto completo
            carritoUsuario.push({...productoAnonimo});
        }
    });
    
    // Guardar el carrito fusionado
    localStorage.setItem(`carrito_${idUsuarioNuevo}`, JSON.stringify(carritoUsuario));
    
    // Limpiar el carrito anónimo
    localStorage.removeItem('carrito_invitado');
    
    // Sincronizar con el servidor
    if (typeof sincronizarCarritoConServidor === 'function') {
        sincronizarCarritoConServidor();
    }
    
    return carritoUsuario;
}

// Inicializar carrito al cargar
document.addEventListener('DOMContentLoaded', function() {
    limpiarCarritoAnterior();
    
    // Si hay un elemento para mostrar el total de productos en el carrito, actualizarlo
    const contadorCarrito = document.getElementById('carrito-contador');
    if (contadorCarrito) {
        const claveCarrito = obtenerClaveCarrito();
        const carrito = JSON.parse(localStorage.getItem(claveCarrito)) || [];
        const totalProductos = carrito.reduce((total, item) => total + item.cantidad, 0);
        contadorCarrito.textContent = totalProductos;
    }
});

// Exportar funciones para uso global
window.obtenerClaveCarrito = obtenerClaveCarrito;
window.migrarCarritoAnonimo = migrarCarritoAnonimo;
window.limpiarCarritoAnterior = limpiarCarritoAnterior;
