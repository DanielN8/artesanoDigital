/**
 * JavaScript para gestionar productos del artesano
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Referencias a elementos del DOM
    const btnNuevoProducto = document.getElementById('btnNuevoProducto');
    const modalProducto = document.getElementById('modalNuevoProducto');
    const formNuevoProducto = document.getElementById('formNuevoProducto');
    const closeButtons = document.querySelectorAll('.close, .cerrar-modal');
    const checkboxDescuento = document.getElementById('aplicarDescuento');
    const seccionDescuento = document.getElementById('seccionDescuento');
    const radioTipoDescuento = document.getElementsByName('tipo_descuento');
    const camposPorcentaje = document.getElementById('camposPorcentaje');
    const camposMonto = document.getElementById('camposMonto');
    const respuestaCreacion = document.getElementById('respuesta-creacion');
    
    // Abrir modal al hacer clic en el botón "Nuevo Producto"
    if (btnNuevoProducto) {
        btnNuevoProducto.addEventListener('click', function() {
            modalProducto.style.display = 'block';
            document.body.classList.add('modal-open');
        });
    }
    
    // Cerrar modal al hacer clic en botones de cierre
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            cerrarModal();
        });
    });
    
    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target === modalProducto) {
            cerrarModal();
        }
    });
    
    // Función para cerrar el modal
    function cerrarModal() {
        modalProducto.style.display = 'none';
        document.body.classList.remove('modal-open');
        formNuevoProducto.reset();
        seccionDescuento.style.display = 'none';
        respuestaCreacion.style.display = 'none';
    }
    
    // Mostrar/ocultar sección de descuentos
    if (checkboxDescuento) {
        checkboxDescuento.addEventListener('change', function() {
            if (this.checked) {
                seccionDescuento.style.display = 'block';
            } else {
                seccionDescuento.style.display = 'none';
            }
        });
    }
    
    // Cambiar entre tipos de descuento
    radioTipoDescuento.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'porcentaje') {
                camposPorcentaje.style.display = 'block';
                camposMonto.style.display = 'none';
            } else if (this.value === 'monto') {
                camposPorcentaje.style.display = 'none';
                camposMonto.style.display = 'block';
            }
        });
    });
    
    // Manejar el envío del formulario
    if (formNuevoProducto) {
        formNuevoProducto.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Mostrar indicador de carga
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = 'Guardando...';
            
            // Validación de descuento
            const aplicarDescuento = checkboxDescuento.checked;
            if (aplicarDescuento) {
                const tipoDescuento = document.querySelector('input[name="tipo_descuento"]:checked').value;
                
                if (tipoDescuento === 'porcentaje') {
                    const porcentaje = parseFloat(document.getElementById('descuento_porcentaje').value);
                    if (isNaN(porcentaje) || porcentaje <= 0 || porcentaje >= 100) {
                        mostrarMensaje('El porcentaje de descuento debe ser mayor a 0 y menor a 100', 'error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Guardar Producto';
                        return;
                    }
                } else if (tipoDescuento === 'monto') {
                    const monto = parseFloat(document.getElementById('descuento_monto').value);
                    const precio = parseFloat(document.getElementById('precio').value);
                    if (isNaN(monto) || monto <= 0 || monto >= precio) {
                        mostrarMensaje('El monto de descuento debe ser mayor a 0 y menor al precio del producto', 'error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Guardar Producto';
                        return;
                    }
                }
            }
            
            // Crear FormData para envío con archivos
            const formData = new FormData(this);
            
            // Enviar solicitud AJAX
            fetch('/artesanoDigital/views/artesano/crear_producto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Guardar Producto';
                
                if (data.exitoso) {
                    // Mostrar mensaje de éxito
                    mostrarMensaje(data.mensaje, 'success');
                    
                    // Limpiar el formulario después de 2 segundos
                    setTimeout(() => {
                        // Recargar la página para mostrar el nuevo producto
                        window.location.reload();
                    }, 2000);
                } else {
                    // Mostrar mensaje de error
                    mostrarMensaje(data.mensaje, 'error');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Guardar Producto';
                console.error('Error:', error);
                mostrarMensaje('Error al procesar la solicitud', 'error');
            });
        });
    }
    
    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
        respuestaCreacion.textContent = mensaje;
        respuestaCreacion.className = 'alert';
        respuestaCreacion.classList.add(tipo === 'success' ? 'alert-success' : 'alert-error');
        respuestaCreacion.style.display = 'block';
        
        // Hacer scroll al mensaje
        respuestaCreacion.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Inicializar validaciones de entrada
    const inputPrecio = document.getElementById('precio');
    const inputDescuentoMonto = document.getElementById('descuento_monto');
    const inputDescuentoPorcentaje = document.getElementById('descuento_porcentaje');
    
    if (inputPrecio) {
        inputPrecio.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '');
        });
    }
    
    if (inputDescuentoMonto) {
        inputDescuentoMonto.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '');
            
            const precio = parseFloat(inputPrecio.value) || 0;
            const descuento = parseFloat(this.value) || 0;
            
            if (descuento >= precio) {
                this.setCustomValidity('El descuento no puede ser mayor o igual al precio');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    if (inputDescuentoPorcentaje) {
        inputDescuentoPorcentaje.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '');
            
            const porcentaje = parseFloat(this.value) || 0;
            
            if (porcentaje <= 0 || porcentaje >= 100) {
                this.setCustomValidity('El porcentaje debe estar entre 1 y 99');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
