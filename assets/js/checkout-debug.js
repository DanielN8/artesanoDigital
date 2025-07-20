/**
 * Script de prueba para verificar el carrito y checkout
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== TEST CARRITO CHECKOUT ===');
    
    // Verificar si hay productos en localStorage
    const carrito = localStorage.getItem('carrito');
    const usuarioId = document.body.getAttribute('data-user-id');
    const carritoUsuario = usuarioId ? localStorage.getItem('carrito_' + usuarioId) : null;
    
    console.log('Carrito general:', carrito);
    console.log('Carrito usuario:', carritoUsuario);
    console.log('Usuario ID:', usuarioId);
    
    // Si estamos en checkout, verificar que los productos se muestren
    if (window.location.pathname.includes('cart_process')) {
        const productosEnPagina = document.querySelectorAll('.list-group-item');
        console.log('Productos mostrados en página:', productosEnPagina.length);
        
        // Log del total mostrado
        const totalElement = document.getElementById('cart-summary-total-step2');
        if (totalElement) {
            console.log('Total mostrado:', totalElement.textContent);
        }
        
        // Verificar si hay mensajes de error o advertencia
        const alertas = document.querySelectorAll('.alert');
        if (alertas.length > 0) {
            alertas.forEach(alerta => {
                console.log('Alerta encontrada:', alerta.textContent);
            });
        }
    }
});
