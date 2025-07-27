// ===== MÓDULO PARA GESTIÓN DE MODAL DE DETALLE DE PEDIDOS =====

class ModalDetallePedidos {
    constructor() {
        this.modal = null;
        this.datosPedidoActual = null;
        this.init();
    }

    init() {
        this.modal = document.getElementById('modalDetallePedido');
        if (!this.modal) {
            console.error('Modal de detalle de pedidos no encontrado');
            return;
        }

        this.configurarEventos();
        this.configurarTabs();
    }

    configurarEventos() {
        // Cerrar modal
        const btnCerrar = this.modal.querySelector('.modal-close-pedidos');
        if (btnCerrar) {
            btnCerrar.addEventListener('click', () => this.cerrar());
        }

        // Cerrar al hacer clic fuera del modal
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.cerrar();
            }
        });

        // Configurar botón de actualizar estado
        const btnActualizar = this.modal.querySelector('#btnActualizarEstado');
        if (btnActualizar) {
            btnActualizar.addEventListener('click', () => this.actualizarEstado());
        }

        // Configurar campos editables del cliente
        this.configurarCamposEditables();
    }

    configurarTabs() {
        const tabBtns = this.modal.querySelectorAll('.tab-btn-pedidos');
        const tabPanes = this.modal.querySelectorAll('.tab-pane-pedidos');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.dataset.tab;
                
                // Remover clases activas
                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));
                
                // Activar tab seleccionado
                btn.classList.add('active');
                const targetPane = this.modal.querySelector(`#tab-${targetTab}`);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    }

    configurarCamposEditables() {
        const camposEditables = this.modal.querySelectorAll('.info-value-pedidos.editable');
        
        camposEditables.forEach(campo => {
            campo.addEventListener('click', () => this.editarCampoCliente(campo));
        });
    }

    abrir(idPedido) {
        if (!this.modal) return;

        this.modal.style.display = 'block';
        this.mostrarCargando();
        this.cargarDatos(idPedido);
    }

    cerrar() {
        if (this.modal) {
            this.modal.style.display = 'none';
            this.datosPedidoActual = null;
        }
    }

    mostrarCargando() {
        // Limpiar título
        const titulo = this.modal.querySelector('.modal-title-pedidos');
        if (titulo) {
            titulo.innerHTML = '<i class="material-icons">hourglass_empty</i> Cargando Pedido...';
        }

        // Valores por defecto mientras carga
        const elementosCarga = {
            '#numeroPedido': '#...',
            '#fechaPedido': 'Cargando...',
            '#metodoPago': 'Cargando...',
            '#clienteNombre': 'Cargando...',
            '#clienteCorreo': 'Cargando...',
            '#clienteTelefono': 'Cargando...',
            '#direccionEnvio': 'Cargando...',
            '#subtotalPedido': 'B/. 0.00',
            '#descuentosPedido': 'B/. 0.00',
            '#costoEnvio': 'B/. 0.00',
            '#totalFinal': 'B/. 0.00',
            '#empresaEnvio': 'Cargando...',
            '#numeroSeguimiento': 'Cargando...',
            '#fechaEnvio': 'Cargando...',
            '#fechaEstimadaEntrega': 'Cargando...'
        };

        Object.entries(elementosCarga).forEach(([selector, texto]) => {
            const elemento = this.modal.querySelector(selector);
            if (elemento) elemento.textContent = texto;
        });

        // Loading en tabla de productos
        this.mostrarCargandoProductos();
    }

    mostrarCargandoProductos() {
        const tbody = this.modal.querySelector('#productosTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="loading-productos">
                        <span class="material-icons loading-spinner">sync</span>
                        <div>Cargando productos del pedido...</div>
                    </td>
                </tr>
            `;
        }
    }

    async cargarDatos(idPedido) {
        try {
            // Llamada a la API real
            const response = await fetch(`/artesanoDigital/api/pedidos.php?path=${idPedido}/detalles`);
            
            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success && data.data) {
                this.datosPedidoActual = data.data;
                this.llenarModal(this.datosPedidoActual);
            } else {
                throw new Error(data.error || 'No se encontraron datos del pedido');
            }
        } catch (error) {
            console.error('Error al cargar datos del pedido:', error);
            this.mostrarError('Error al cargar los datos del pedido: ' + error.message);
        }
    }

    llenarModal(datos) {
        // Actualizar título
        const titulo = this.modal.querySelector('.modal-title-pedidos');
        if (titulo) {
            titulo.innerHTML = `<i class="material-icons">receipt_long</i> Gestión de Pedido ${datos.numero_pedido}`;
        }

        // Información del pedido
        this.actualizarElemento('#numeroPedido', datos.numero_pedido);
        this.actualizarElemento('#fechaPedido', this.formatearFecha(datos.fecha_pedido));
        this.actualizarElemento('#metodoPago', this.formatearMetodoPago(datos.metodo_pago));

        // Configurar selector de estado
        const selectEstado = this.modal.querySelector('#cambiarEstado');
        if (selectEstado) {
            selectEstado.value = datos.estado;
        }

        // Información del cliente
        this.actualizarElemento('#clienteNombre', datos.cliente.nombre);
        this.actualizarElemento('#clienteCorreo', datos.cliente.correo);
        this.actualizarElemento('#clienteTelefono', datos.cliente.telefono);
        this.actualizarElemento('#direccionEnvio', this.parsearDireccionEnvio(datos.cliente.direccion));

        // Productos
        this.llenarTablaProductos(datos.productos);

        // Resumen financiero
        this.actualizarResumenFinanciero(datos.resumen_financiero);

        // Información de envío
        this.actualizarInfoEnvio(datos.envio);
    }

    actualizarElemento(selector, valor) {
        const elemento = this.modal.querySelector(selector);
        if (elemento) {
            elemento.textContent = valor || '-';
        }
    }

    llenarTablaProductos(productos) {
        const tbody = this.modal.querySelector('#productosTableBody');
        if (!tbody) return;

        if (!productos || productos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="loading-productos">
                        <i class="material-icons">info</i>
                        <div>No hay productos en este pedido</div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = productos.map(producto => `
            <tr>
                <td>
                    <div class="producto-info-compacta">
                        <div class="producto-imagen-mini">
                            <img src="${this.obtenerImagenProducto(producto.imagen)}" 
                                 alt="${producto.nombre}" 
                                 onerror="this.src='/artesanoDigital/public/placeholder.jpg'">
                        </div>
                        <div class="producto-detalles-mini">
                            <h6>${producto.nombre}</h6>
                            <small>${producto.descripcion}</small>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="cantidad-badge">${producto.cantidad}</span>
                </td>
                <td class="text-right">
                    <span class="precio-unitario">B/. ${producto.precio_unitario}</span>
                </td>
                <td class="text-right">
                    <span class="subtotal-producto">B/. ${producto.subtotal}</span>
                </td>
            </tr>
        `).join('');
    }

    actualizarResumenFinanciero(resumen) {
        this.actualizarElemento('#subtotalPedido', `B/. ${resumen.subtotal}`);
        this.actualizarElemento('#descuentosPedido', `B/. ${resumen.descuentos}`);
        this.actualizarElemento('#costoEnvio', `B/. ${resumen.costo_envio}`);
        this.actualizarElemento('#totalFinal', `B/. ${resumen.total}`);
    }

    actualizarInfoEnvio(envio) {
        this.actualizarElemento('#empresaEnvio', envio.empresa || 'No asignada');
        this.actualizarElemento('#numeroSeguimiento', envio.numero_seguimiento || 'Sin número');
        this.actualizarElemento('#fechaEnvio', envio.fecha_envio || 'No enviado');
        this.actualizarElemento('#fechaEstimadaEntrega', envio.fecha_estimada_entrega || 'No estimada');
    }

    editarCampoCliente(campo) {
        const valorActual = campo.textContent.trim();
        const fieldName = campo.dataset.field;
        
        // Crear input para edición
        const input = document.createElement('input');
        input.type = fieldName === 'correo' ? 'email' : 'text';
        input.value = valorActual;
        input.className = 'edit-input-pedidos';
        input.style.cssText = `
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #667eea;
            border-radius: 6px;
            font-size: 0.95rem;
            background: white;
            outline: none;
        `;

        // Reemplazar contenido
        campo.innerHTML = '';
        campo.appendChild(input);
        campo.classList.add('editing');
        input.focus();
        input.select();

        // Función para guardar cambios
        const guardarCambios = () => {
            const nuevoValor = input.value.trim();
            if (nuevoValor && nuevoValor !== valorActual) {
                this.guardarCambioCliente(fieldName, nuevoValor);
            }
            campo.textContent = nuevoValor || valorActual;
            campo.classList.remove('editing');
        };

        // Eventos
        input.addEventListener('blur', guardarCambios);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                guardarCambios();
            } else if (e.key === 'Escape') {
                campo.textContent = valorActual;
                campo.classList.remove('editing');
            }
        });
    }

    async guardarCambioCliente(campo, valor) {
        try {
            const response = await fetch(`/artesanoDigital/api/pedidos.php?path=${this.datosPedidoActual.id_pedido}/direccion`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    campo: campo,
                    valor: valor
                })
            });

            if (response.ok) {
                this.mostrarNotificacion('Información del cliente actualizada correctamente', 'success');
            } else {
                throw new Error('Error al actualizar la información');
            }
        } catch (error) {
            console.error('Error al guardar cambios:', error);
            this.mostrarNotificacion('Error al actualizar la información', 'error');
        }
    }

    async actualizarEstado() {
        const selectEstado = this.modal.querySelector('#cambiarEstado');
        if (!selectEstado || !this.datosPedidoActual) return;

        const nuevoEstado = selectEstado.value;
        
        try {
            const response = await fetch(`/artesanoDigital/api/pedidos.php?path=${this.datosPedidoActual.id_pedido}/estado`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ estado: nuevoEstado })
            });

            if (response.ok) {
                this.mostrarNotificacion('Estado del pedido actualizado correctamente', 'success');
                // Actualizar el estado en el objeto actual
                this.datosPedidoActual.estado = nuevoEstado;
            } else {
                throw new Error('Error al actualizar el estado');
            }
        } catch (error) {
            console.error('Error al actualizar estado:', error);
            this.mostrarNotificacion('Error al actualizar el estado del pedido', 'error');
        }
    }

    mostrarError(mensaje) {
        const tbody = this.modal.querySelector('#productosTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="error-message">
                        <i class="material-icons">error</i>
                        <div>${mensaje}</div>
                    </td>
                </tr>
            `;
        }
    }

    mostrarNotificacion(mensaje, tipo = 'info') {
        // Sistema simple de notificaciones
        const notification = document.createElement('div');
        notification.className = `notification ${tipo}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${tipo === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        notification.textContent = mensaje;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    formatearFecha(fechaISO) {
        try {
            const fecha = new Date(fechaISO);
            return fecha.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch {
            return fechaISO;
        }
    }

    formatearMetodoPago(metodo) {
        const metodos = {
            'tarjeta_credito': 'Tarjeta de Crédito',
            'tarjeta': 'Tarjeta de Crédito',
            'yappy': 'Yappy',
            'efectivo': 'Efectivo',
            'transferencia': 'Transferencia Bancaria'
        };
        return metodos[metodo] || metodo;
    }

    parsearDireccionEnvio(direccion) {
        if (!direccion) return 'No especificada';
        
        // Si la dirección es un JSON string, parsearlo
        try {
            const direccionObj = JSON.parse(direccion);
            if (direccionObj.direccion && direccionObj.ciudad) {
                return `${direccionObj.direccion}, ${direccionObj.ciudad}`;
            }
        } catch (e) {
            // Si no es JSON, retornar como string normal
        }
        
        return direccion;
    }

    obtenerImagenProducto(imagen) {
        if (!imagen || imagen === 'null' || imagen === '' || imagen === '/artesanoDigital/public/placeholder.jpg') {
            return '/artesanoDigital/public/placeholder.jpg';
        }
        
        // Si la imagen ya tiene la ruta completa, usarla directamente
        if (imagen.startsWith('/artesanoDigital/') || imagen.startsWith('http')) {
            return imagen;
        }
        
        // Si la imagen empieza con 'uploads/', agregar solo la ruta base
        if (imagen.startsWith('uploads/')) {
            return `/artesanoDigital/${imagen}`;
        }
        
        // Para nombres de archivo sin ruta, agregar la ruta completa de uploads
        return `/artesanoDigital/uploads/productos/${imagen}`;
    }
}

// Inicializar el modal cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.modalDetallePedidos = new ModalDetallePedidos();
});

// Función global para abrir el modal (compatible con código existente)
function abrirModalDetallePedido(idPedido) {
    if (window.modalDetallePedidos) {
        window.modalDetallePedidos.abrir(idPedido);
    }
}
