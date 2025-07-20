/**
 * JavaScript para la gestión de la tienda de artesanos
 */

document.addEventListener('DOMContentLoaded', function() {
    const formTienda = document.getElementById('formTienda');
    
    if (formTienda) {
        formTienda.addEventListener('submit', function(event) {
            // Validación básica
            const nombreTienda = document.getElementById('nombre_tienda').value.trim();
            
            if (nombreTienda.length === 0) {
                event.preventDefault();
                mostrarMensaje('error', 'El nombre de la tienda es obligatorio');
                return false;
            }
            
            // Validar imagen si se ha seleccionado una
            const imagenInput = document.getElementById('imagen_logo');
            if (imagenInput.files.length > 0) {
                const archivo = imagenInput.files[0];
                const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
                const tamanoMaximo = 2 * 1024 * 1024; // 2MB
                
                if (!tiposPermitidos.includes(archivo.type)) {
                    event.preventDefault();
                    mostrarMensaje('error', 'El archivo debe ser una imagen (JPEG, PNG o GIF)');
                    return false;
                }
                
                if (archivo.size > tamanoMaximo) {
                    event.preventDefault();
                    mostrarMensaje('error', 'La imagen no debe superar los 2MB');
                    return false;
                }
            }
            
            // Si todo está bien, mostrar loader
            mostrarLoader('Procesando...');
        });
    }
    
    // Previsualización de la imagen del logo
    const imagenInput = document.getElementById('imagen_logo');
    const previewContainer = document.getElementById('logo-preview-container');
    
    if (imagenInput && previewContainer) {
        imagenInput.addEventListener('change', function() {
            previsualizarImagen(this, previewContainer);
        });
    }
});

/**
 * Muestra un mensaje de notificación
 * @param {string} tipo - Tipo de mensaje: 'success', 'error', 'info', 'warning'
 * @param {string} mensaje - Texto del mensaje
 */
function mostrarMensaje(tipo, mensaje) {
    // Si existe la función notificar del sistema principal, usarla
    if (typeof notificar === 'function') {
        notificar(tipo, mensaje);
    } else {
        // Crear una notificación básica si no existe la función global
        const alertClass = tipo === 'success' ? 'alert-success' : 
                          tipo === 'error' ? 'alert-danger' : 
                          tipo === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        // Insertar al inicio del contenedor
        const container = document.querySelector('.dashboard-container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Eliminar después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }
}

/**
 * Muestra un indicador de carga durante el envío del formulario
 */
function mostrarLoader(mensaje) {
    const loaderDiv = document.createElement('div');
    loaderDiv.className = 'loading-overlay';
    loaderDiv.innerHTML = `
        <div class="loading-spinner"></div>
        <p>${mensaje || 'Cargando...'}</p>
    `;
    
    document.body.appendChild(loaderDiv);
}

/**
 * Previsualiza una imagen antes de subirla
 */
function previsualizarImagen(input, container) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Limpiar el contenedor
            container.innerHTML = '';
            
            // Crear la imagen de previsualización
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail mt-2';
            img.style.maxWidth = '150px';
            
            // Crear etiqueta
            const label = document.createElement('p');
            label.className = 'mb-1';
            label.textContent = 'Vista previa:';
            
            // Agregar al contenedor
            container.appendChild(label);
            container.appendChild(img);
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
