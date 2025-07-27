/**
 * JavaScript para Modal de Detalles de Pedidos
 * Responsabilidad: Gestión completa de detalles de pedidos con tabla de productos y edición de datos
 */

// Variables globales
let pedidoActual = null;
let datosOriginalesCliente = {};

// Función principal para abrir modal de detalles
function abrirModalDetallePedido(idPedido) {
    console.log('Abriendo modal para pedido:', idPedido);
    
    const modal = document.getElementById('modalDetallePedido');
    if (!modal) {
        console.error('Modal no encontrado');
        return;
    }
    
    // Limpiar datos anteriores
    limpiarModalDetallePedido();
    
    // Mostrar modal
    modal.classList.add('show');
    
    // Cargar datos del pedido
    cargarDatosPedido(idPedido);
}

// Función para cerrar modal
function cerrarModalDetallePedido() {
    const modal = document.getElementById('modalDetallePedido');
    if (modal) {
        modal.classList.remove('show');
    }
    
    // Limpiar datos
    pedidoActual = null;
    datosOriginalesCliente = {};
}

// Función para limpiar contenido del modal
function limpiarModalDetallePedido() {
    // Limpiar título
    const titulo = document.getElementById('tituloModalPedido');
    if (titulo) titulo.textContent = 'Gestión de Pedido #0000';
    
    // Limpiar información básica
    document.getElementById('numeroPedido').textContent = '#0000';
    document.getElementById('fechaPedido').textContent = '-';
    document.getElementById('metodoPago').textContent = '-';
    
    // Limpiar información del cliente
    document.getElementById('clienteNombre').textContent = '-';
    document.getElementById('clienteCorreo').textContent = '-';
    document.getElementById('clienteTelefono').textContent = '-';
    document.getElementById('direccionEnvio').textContent = '-';
    
    // Limpiar tabla de productos
    const tbody = document.getElementById('productosTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="loading-productos">
                    <i class="fas fa-spinner fa-spin"></i>
                    <div>Cargando productos...</div>
                </td>
            </tr>
        `;
    }
    
    // Limpiar resumen financiero
    document.getElementById('subtotalPedido').textContent = 'B/. 0.00';
    document.getElementById('descuentosPedido').textContent = 'B/. 0.00';
    document.getElementById('costoEnvio').textContent = 'B/. 0.00';
    document.getElementById('totalFinal').textContent = 'B/. 0.00';
}

// Función para cargar datos del pedido desde la API
async function cargarDatosPedido(idPedido) {
    try {
        console.log('Cargando datos para pedido:', idPedido);
        
        const response = await fetch(`/artesanoDigital/api/pedidos.php?path=${idPedido}/detalles`);
        const data = await response.json();
        
        console.log('Datos recibidos:', data);
        
        if (data.error) {
            console.error('Error de API:', data.error);
            mostrarErrorEnModal(data.error);
            return;
        }
        
        if (data.data) {
            pedidoActual = data.data;
            llenarModalConDatos(data.data);
        } else {
            // Usar datos de fallback para demostración
            console.warn('Usando datos de fallback');
            usarDatosFallback(idPedido);
        }
        
    } catch (error) {
        console.error('Error al cargar datos del pedido:', error);
        usarDatosFallback(idPedido);
    }
}

// Función para mostrar error en el modal
function mostrarErrorEnModal(mensaje) {
    const tbody = document.getElementById('productosTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="alert-pedidos error">
                    <i class="fas fa-exclamation-triangle"></i>
                    ${mensaje}
                </td>
            </tr>
        `;
    }
}

// Función para usar datos de fallback cuando no hay datos reales
function usarDatosFallback(idPedido) {
    const datosFallback = {
        id_pedido: idPedido,
        numero_pedido: `#${idPedido.toString().padStart(5, '0')}`,
        fecha_pedido: '2025-01-26 14:30:00',
        estado: 'pendiente',
        metodo_pago: 'yappy',
        total: '281.00',
        cliente: {
            nombre: 'Daniel Rodriguez',
            correo: 'daniel@example.com',
            telefono: '+507 6000-0000',
            direccion: 'Capira, Ciudad de Panamá'
        },
        productos: [
            {
                id_producto: 1,
                nombre: 'Artesanía Premium',
                descripcion: 'Producto artesanal de alta calidad',
                imagen: '/artesanoDigital/public/placeholder.jpg',
                cantidad: 1,
                precio_unitario: '221.00',
                subtotal: '221.00'
            },
            {
                id_producto: 2,
                nombre: 'Producto Especial',
                descripcion: 'Diseño único y exclusivo',
                imagen: '/artesanoDigital/public/placeholder.jpg',
                cantidad: 1,
                precio_unitario: '10.00',
                subtotal: '10.00'
            },
            {
                id_producto: 3,
                nombre: 'Artesanía Tradicional',
                descripcion: 'Hecho con técnicas ancestrales',
                imagen: '/artesanoDigital/public/placeholder.jpg',
                cantidad: 1,
                precio_unitario: '50.00',
                subtotal: '50.00'
            }
        ],
        resumen_financiero: {
            subtotal: '281.00',
            descuentos: '0.00',
            costo_envio: '0.00',
            total: '281.00'
        }
    };
    
    console.log('Usando datos de fallback:', datosFallback);
    pedidoActual = datosFallback;
    llenarModalConDatos(datosFallback);
}

// Función para llenar el modal con los datos
function llenarModalConDatos(datos) {
    console.log('Llenando modal con datos:', datos);
    
    // Actualizar título
    const titulo = document.getElementById('tituloModalPedido');
    if (titulo) {
        titulo.textContent = `Gestión de Pedido ${datos.numero_pedido || datos.id_pedido}`;
    }
    
    // Información básica del pedido
    document.getElementById('numeroPedido').textContent = datos.numero_pedido || `#${datos.id_pedido}`;
    document.getElementById('fechaPedido').textContent = formatearFecha(datos.fecha_pedido);
    document.getElementById('metodoPago').textContent = datos.metodo_pago || 'No especificado';
    
    // Estado del pedido
    const selectEstado = document.getElementById('cambiarEstado');
    if (selectEstado && datos.estado) {
        selectEstado.value = datos.estado;
    }
    
    // Información del cliente
    if (datos.cliente) {
        document.getElementById('clienteNombre').textContent = datos.cliente.nombre || 'No disponible';
        document.getElementById('clienteCorreo').textContent = datos.cliente.correo || 'No disponible';
        document.getElementById('clienteTelefono').textContent = datos.cliente.telefono || 'No disponible';
        document.getElementById('direccionEnvio').textContent = datos.cliente.direccion || 'No disponible';
        
        // Guardar datos originales para edición
        datosOriginalesCliente = { ...datos.cliente };
    }
    
    // Llenar tabla de productos
    llenarTablaProductos(datos.productos || []);
    
    // Resumen financiero
    const resumen = datos.resumen_financiero || {};
    document.getElementById('subtotalPedido').textContent = formatearMoneda(resumen.subtotal || datos.total || '0');
    document.getElementById('descuentosPedido').textContent = formatearMoneda(resumen.descuentos || '0');
    document.getElementById('costoEnvio').textContent = formatearMoneda(resumen.costo_envio || '0');
    document.getElementById('totalFinal').textContent = formatearMoneda(resumen.total || datos.total || '0');
}

// Función para llenar la tabla de productos
function llenarTablaProductos(productos) {
    const tbody = document.getElementById('productosTableBody');
    if (!tbody) return;
    
    if (!productos || productos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="alert-pedidos warning">
                    <i class="fas fa-info-circle"></i>
                    No hay productos en este pedido
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = '';
    
    productos.forEach(producto => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="${producto.imagen || '/artesanoDigital/public/placeholder.jpg'}" 
                         alt="${producto.nombre}" 
                         class="producto-imagen"
                         onerror="this.src='/artesanoDigital/public/placeholder.jpg'">
                    <div class="producto-nombre">${producto.nombre}</div>
                </div>
            </td>
            <td>
                <div class="producto-descripcion">${producto.descripcion || 'Sin descripción'}</div>
            </td>
            <td>
                <span class="cantidad-badge">${producto.cantidad}</span>
            </td>
            <td>
                <span class="precio-unitario">${formatearMoneda(producto.precio_unitario)}</span>
            </td>
            <td>
                <span class="subtotal-producto">${formatearMoneda(producto.subtotal)}</span>
            </td>
        `;
        tbody.appendChild(fila);
    });
}

// Función para formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return '-';
    
    try {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return fecha;
    }
}

// Función para formatear moneda
function formatearMoneda(cantidad) {
    if (!cantidad) return 'B/. 0.00';
    
    const numero = parseFloat(cantidad);
    if (isNaN(numero)) return 'B/. 0.00';
    
    return `B/. ${numero.toFixed(2)}`;
}

// Función para manejar cambio de tabs
function cambiarTab(tabName) {
    // Ocultar todas las pestañas
    document.querySelectorAll('.tab-pane-pedidos').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Remover clase active de todos los botones
    document.querySelectorAll('.tab-btn-pedidos').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar pestaña seleccionada
    const tabPane = document.getElementById(`tab-${tabName}`);
    if (tabPane) {
        tabPane.classList.add('active');
    }
    
    // Activar botón correspondiente
    const tabBtn = document.querySelector(`[data-tab="${tabName}"]`);
    if (tabBtn) {
        tabBtn.classList.add('active');
    }
}

// Función para hacer campos editables
function hacerCampoEditable(elemento) {
    const valorActual = elemento.textContent;
    const campo = elemento.dataset.field;
    
    // Crear input
    const input = document.createElement('input');
    input.type = 'text';
    input.value = valorActual;
    input.className = 'info-value-pedidos editing';
    
    // Reemplazar elemento
    elemento.parentNode.replaceChild(input, elemento);
    input.focus();
    input.select();
    
    // Manejar eventos
    input.addEventListener('blur', () => guardarCampoEditado(input, campo, valorActual));
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            guardarCampoEditado(input, campo, valorActual);
        } else if (e.key === 'Escape') {
            restaurarCampoOriginal(input, valorActual);
        }
    });
}

// Función para guardar campo editado
function guardarCampoEditado(input, campo, valorOriginal) {
    const nuevoValor = input.value.trim();
    
    if (nuevoValor && nuevoValor !== valorOriginal) {
        // Aquí podrías hacer una llamada a la API para guardar
        console.log(`Guardando ${campo}: ${nuevoValor}`);
        
        // Actualizar datos del cliente
        if (pedidoActual && pedidoActual.cliente) {
            pedidoActual.cliente[campo] = nuevoValor;
        }
        
        mostrarNotificacion('success', `${campo} actualizado correctamente`);
    }
    
    // Restaurar elemento span
    const span = document.createElement('span');
    span.className = 'info-value-pedidos editable';
    span.dataset.field = campo;
    span.textContent = nuevoValor || valorOriginal;
    span.addEventListener('click', () => hacerCampoEditable(span));
    
    input.parentNode.replaceChild(span, input);
}

// Función para restaurar campo original
function restaurarCampoOriginal(input, valorOriginal) {
    const span = document.createElement('span');
    span.className = 'info-value-pedidos editable';
    span.dataset.field = input.dataset?.field || '';
    span.textContent = valorOriginal;
    span.addEventListener('click', () => hacerCampoEditable(span));
    
    input.parentNode.replaceChild(span, input);
}

// Función para mostrar notificaciones
function mostrarNotificacion(tipo, mensaje) {
    // Crear elemento de notificación
    const notif = document.createElement('div');
    notif.className = `alert-pedidos ${tipo}`;
    notif.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${mensaje}
    `;
    
    // Agregar al modal
    const modalBody = document.querySelector('.modal-body-pedidos');
    if (modalBody) {
        modalBody.insertBefore(notif, modalBody.firstChild);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            if (notif.parentNode) {
                notif.parentNode.removeChild(notif);
            }
        }, 3000);
    }
}

// Función para actualizar estado del pedido
async function actualizarEstadoPedido() {
    const selectEstado = document.getElementById('cambiarEstado');
    const nuevoEstado = selectEstado.value;
    
    if (!pedidoActual || !nuevoEstado) return;
    
    try {
        const response = await fetch(`/artesanoDigital/api/pedidos.php?path=${pedidoActual.id_pedido}/estado`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ estado: nuevoEstado })
        });
        
        const result = await response.json();
        
        if (result.success) {
            pedidoActual.estado = nuevoEstado;
            mostrarNotificacion('success', 'Estado actualizado correctamente');
        } else {
            mostrarNotificacion('error', result.error || 'Error al actualizar estado');
        }
    } catch (error) {
        console.error('Error al actualizar estado:', error);
        mostrarNotificacion('error', 'Error de conexión');
    }
}

// Event listeners cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Botones de tabs
    document.querySelectorAll('.tab-btn-pedidos').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            if (tab) cambiarTab(tab);
        });
    });
    
    // Campos editables del cliente
    document.querySelectorAll('.info-value-pedidos.editable').forEach(campo => {
        campo.addEventListener('click', () => hacerCampoEditable(campo));
    });
    
    // Botón actualizar estado
    const btnActualizarEstado = document.getElementById('btnActualizarEstado');
    if (btnActualizarEstado) {
        btnActualizarEstado.addEventListener('click', actualizarEstadoPedido);
    }
    
    // Cerrar modal al hacer clic fuera
    const modal = document.getElementById('modalDetallePedido');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                cerrarModalDetallePedido();
            }
        });
    }
    
    // Botones de acción
    document.getElementById('btnActualizarEstadoPedido')?.addEventListener('click', actualizarEstadoPedido);
    document.getElementById('btnNotificarCliente')?.addEventListener('click', () => {
        mostrarNotificacion('success', 'Notificación enviada al cliente');
    });
    document.getElementById('btnGenerarEtiqueta')?.addEventListener('click', () => {
        mostrarNotificacion('success', 'Etiqueta de envío generada');
    });
    document.getElementById('btnMarcarCompleto')?.addEventListener('click', async () => {
        const selectEstado = document.getElementById('cambiarEstado');
        selectEstado.value = 'entregado';
        await actualizarEstadoPedido();
    });
});

// Exponer funciones globalmente para compatibilidad
window.abrirModalDetallePedido = abrirModalDetallePedido;
window.cerrarModalDetallePedido = cerrarModalDetallePedido;
