// Modal y funciones para ver detalles de pedidos en el Dashboard de Artesano
document.addEventListener('DOMContentLoaded', function() {
    // Botones para ver detalles de pedidos recibidos
    const botonesVerPedido = document.querySelectorAll('.ver-pedido');
    botonesVerPedido.forEach(btn => {
        btn.addEventListener('click', function() {
            const idPedido = this.getAttribute('data-id');
            mostrarDetallesPedidoRecibido(idPedido);
        });
    });

    // Botones para ver detalles de pedidos personales (como cliente)
    const botonesVerPedidoCliente = document.querySelectorAll('.ver-pedido-cliente');
    botonesVerPedidoCliente.forEach(btn => {
        btn.addEventListener('click', function() {
            const idPedido = this.getAttribute('data-id');
            mostrarDetallesPedidoPersonal(idPedido);
        });
    });

    // Función para mostrar detalles de pedidos recibidos
    function mostrarDetallesPedidoRecibido(idPedido) {
        fetch(`/artesanoDigital/api/pedidos/${idPedido}/detalles`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar los detalles del pedido');
                }
                return response.json();
            })
            .then(data => {
                // Crear modal con detalles del pedido
                const modal = crearModalPedidoRecibido(data);
                document.body.appendChild(modal);
                
                // Abrir modal
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                
                // Configurar cierre del modal
                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modal);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                crearNotificacion('error', 'Error al cargar los detalles del pedido');
            });
    }

    // Función para mostrar detalles de pedidos personales
    function mostrarDetallesPedidoPersonal(idPedido) {
        fetch(`/artesanoDigital/api/pedidos/cliente/${idPedido}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar los detalles del pedido');
                }
                return response.json();
            })
            .then(data => {
                // Crear modal con detalles del pedido
                const modal = crearModalPedidoPersonal(data);
                document.body.appendChild(modal);
                
                // Abrir modal
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                
                // Configurar cierre del modal
                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modal);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                crearNotificacion('error', 'Error al cargar los detalles del pedido');
            });
    }

    // Función para crear modal de pedido recibido
    function crearModalPedidoRecibido(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');
        
        // Estados con sus colores
        const estadoClases = {
            'pendiente': 'text-warning',
            'procesando': 'text-primary',
            'enviado': 'text-info',
            'entregado': 'text-success',
            'cancelado': 'text-danger'
        };
        
        // Construir HTML del modal
        let productosHTML = '';
        data.productos.forEach(producto => {
            productosHTML += `
                <div class="d-flex border-bottom py-2">
                    <div class="flex-shrink-0">
                        <img src="${producto.imagen ? '/artesanoDigital/uploads/' + producto.imagen : '/artesanoDigital/public/placeholder.jpg'}" 
                             class="producto-miniatura" alt="${Utils.sanitizeHTML(producto.nombre)}" width="50">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">${Utils.sanitizeHTML(producto.nombre)}</h6>
                        <div class="d-flex justify-content-between">
                            <small>${producto.cantidad} x B/. ${parseFloat(producto.precio).toFixed(2)}</small>
                            <span>B/. ${(producto.cantidad * parseFloat(producto.precio)).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles del Pedido #${String(data.id_pedido).padStart(5, '0')}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Información del Cliente</h6>
                                <p><strong>Nombre:</strong> ${Utils.sanitizeHTML(data.cliente.nombre)}</p>
                                <p><strong>Email:</strong> ${Utils.sanitizeHTML(data.cliente.email)}</p>
                                <p><strong>Teléfono:</strong> ${Utils.sanitizeHTML(data.cliente.telefono || 'No especificado')}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Detalles del Pedido</h6>
                                <p><strong>Fecha:</strong> ${new Date(data.fecha_pedido).toLocaleDateString()}</p>
                                <p><strong>Estado:</strong> <span class="${estadoClases[data.estado] || ''}">${data.estado.charAt(0).toUpperCase() + data.estado.slice(1)}</span></p>
                                <p><strong>Método de Pago:</strong> ${Utils.sanitizeHTML(data.metodo_pago || 'No especificado')}</p>
                            </div>
                        </div>
                        
                        <div class="direccion-envio mb-4">
                            <h6 class="text-muted">Dirección de Envío</h6>
                            <p>${Utils.sanitizeHTML(data.direccion_envio || 'No especificada')}</p>
                        </div>
                        
                        <h6 class="text-muted mb-3">Productos</h6>
                        <div class="productos-list">
                            ${productosHTML}
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                            <h6>Total</h6>
                            <h5>B/. ${parseFloat(data.total).toFixed(2)}</h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary actualizar-estado" data-id="${data.id_pedido}">
                            Actualizar Estado
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Agregar evento para actualizar estado
        modal.querySelector('.actualizar-estado').addEventListener('click', function() {
            const idPedido = this.getAttribute('data-id');
            mostrarModalActualizarEstado(idPedido);
        });
        
        return modal;
    }
    
    // Función para crear modal de pedido personal
    function crearModalPedidoPersonal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');
        
        // Estados con sus colores
        const estadoClases = {
            'pendiente': 'text-warning',
            'procesando': 'text-primary',
            'enviado': 'text-info',
            'entregado': 'text-success',
            'cancelado': 'text-danger'
        };
        
        // Construir HTML del modal
        let productosHTML = '';
        data.productos.forEach(producto => {
            productosHTML += `
                <div class="d-flex border-bottom py-2">
                    <div class="flex-shrink-0">
                        <img src="${producto.imagen ? '/artesanoDigital/uploads/' + producto.imagen : '/artesanoDigital/public/placeholder.jpg'}" 
                             class="producto-miniatura" alt="${Utils.sanitizeHTML(producto.nombre)}" width="50">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">${Utils.sanitizeHTML(producto.nombre)}</h6>
                        <div class="d-flex justify-content-between">
                            <small>${producto.cantidad} x B/. ${parseFloat(producto.precio).toFixed(2)}</small>
                            <span>B/. ${(producto.cantidad * parseFloat(producto.precio)).toFixed(2)}</span>
                        </div>
                        <small class="text-muted">Vendedor: ${Utils.sanitizeHTML(producto.vendedor || 'No especificado')}</small>
                    </div>
                </div>
            `;
        });
        
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mi Pedido #${String(data.id_pedido).padStart(5, '0')}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Detalles del Pedido</h6>
                                <p><strong>Fecha:</strong> ${new Date(data.fecha_pedido).toLocaleDateString()}</p>
                                <p><strong>Estado:</strong> <span class="${estadoClases[data.estado] || ''}">${data.estado.charAt(0).toUpperCase() + data.estado.slice(1)}</span></p>
                                <p><strong>Método de Pago:</strong> ${Utils.sanitizeHTML(data.metodo_pago || 'No especificado')}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Dirección de Envío</h6>
                                <p>${Utils.sanitizeHTML(data.direccion_envio || 'No especificada')}</p>
                            </div>
                        </div>
                        
                        <h6 class="text-muted mb-3">Productos</h6>
                        <div class="productos-list">
                            ${productosHTML}
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                            <h6>Total</h6>
                            <h5>B/. ${parseFloat(data.total).toFixed(2)}</h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }
    
    // Función para mostrar modal de actualización de estado
    function mostrarModalActualizarEstado(idPedido) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');
        
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Actualizar Estado del Pedido #${String(idPedido).padStart(5, '0')}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-actualizar-estado">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Nuevo Estado</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="">Seleccionar estado...</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="procesando">Procesando</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="comentario" class="form-label">Comentario (opcional)</label>
                                <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btn-guardar-estado">Guardar Cambios</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        // Configurar cierre del modal
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(modal);
        });
        
        // Evento para guardar cambios
        modal.querySelector('#btn-guardar-estado').addEventListener('click', function() {
            const estado = modal.querySelector('#estado').value;
            const comentario = modal.querySelector('#comentario').value;
            
            if (!estado) {
                crearNotificacion('error', 'Debes seleccionar un estado');
                return;
            }
            
            // Enviar actualización al servidor
            fetch(`/artesanoDigital/api/pedidos/${idPedido}/estado`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ estado, comentario })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el estado del pedido');
                }
                return response.json();
            })
            .then(data => {
                modalInstance.hide();
                crearNotificacion('success', 'El estado del pedido ha sido actualizado');
                
                // Actualizar la tabla de pedidos (recargar la página)
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                crearNotificacion('error', 'Error al actualizar el estado del pedido');
            });
        });
    }
    
    // Función para crear notificaciones
    function crearNotificacion(tipo, mensaje) {
        if (typeof notificar === 'function') {
            notificar(tipo, mensaje);
        } else {
            alert(mensaje);
        }
    }
});
