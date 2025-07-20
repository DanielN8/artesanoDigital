document.addEventListener('DOMContentLoaded', function () {
    // Cargar las tiendas del artesano para el select
    cargarTiendasArtesano();
    
    // Activar las pestañas
    const tabLinks = document.querySelectorAll('.tabs-nav li');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabLinks.forEach(tab => {
        tab.addEventListener('click', function() {
            // Quitar clase active de todos los tabs
            tabLinks.forEach(t => t.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Activar el tab actual
            this.classList.add('active');
            const targetId = this.getAttribute('data-tab');
            document.getElementById(targetId).classList.add('active');
        });
    });
    
    // Botones para abrir modal de crear tienda
    const btnCrearTienda = document.getElementById('btnCrearTienda');
    const modalCrearTienda = document.getElementById('modalCrearTienda');
    
    if (btnCrearTienda && modalCrearTienda) {
        btnCrearTienda.addEventListener('click', function() {
            modalCrearTienda.style.display = 'block';
        });
    }
    
    // Botones para abrir modal de nuevo producto
    const btnsNuevoProducto = [
        document.getElementById('btnNuevoProducto'), 
        document.getElementById('btnNuevoProductoTab'),
        document.getElementById('btnNuevoProductoEmpty')
    ];
    
    const modalNuevoProducto = document.getElementById('modalNuevoProducto');
    
    if (modalNuevoProducto) {
        btnsNuevoProducto.forEach(btn => {
            if (btn && !btn.hasAttribute('disabled')) {
                btn.addEventListener('click', function() {
                    modalNuevoProducto.style.display = 'block';
                });
            }
        });
    }
    
    // Cerrar modales
    const btnsCerrarModal = document.querySelectorAll('.modal .close, .modal .cancelar-modal');
    btnsCerrarModal.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                const modal = btn.closest('.modal');
                if (modal) modal.style.display = 'none';
            });
        }
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        const modales = document.querySelectorAll('.modal');
        modales.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Vista previa de imágenes
    const inputImagenProducto = document.getElementById('imagen');
    const previewImagenProducto = document.getElementById('imagen-preview');
    
    if (inputImagenProducto && previewImagenProducto) {
        inputImagenProducto.addEventListener('change', function() {
            mostrarVistaPrevia(this, previewImagenProducto);
        });
    }
    
    const inputLogoTienda = document.getElementById('imagen_logo');
    const previewLogoTienda = document.getElementById('logo-preview');
    
    if (inputLogoTienda && previewLogoTienda) {
        inputLogoTienda.addEventListener('change', function() {
            mostrarVistaPrevia(this, previewLogoTienda);
        });
    }
    
    // Botones para editar y eliminar productos
    const botonesEditar = document.querySelectorAll('.btn-editar-producto');
    const botonesEliminar = document.querySelectorAll('.btn-eliminar-producto');
    
    botonesEditar.forEach(btn => {
        btn.addEventListener('click', function() {
            const idProducto = this.getAttribute('data-id');
            cargarDatosProducto(idProducto);
        });
    });
    
    botonesEliminar.forEach(btn => {
        btn.addEventListener('click', function() {
            const idProducto = this.getAttribute('data-id');
            if (confirm('¿Estás seguro de eliminar este producto?')) {
                eliminarProducto(idProducto);
            }
        });
    });
});

// Función para mostrar vista previa de imagen
function mostrarVistaPrevia(input, previewContainer) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = previewContainer.querySelector('img') || document.createElement('img');
            img.src = e.target.result;
            
            if (!previewContainer.contains(img)) {
                previewContainer.appendChild(img);
            }
            
            previewContainer.classList.remove('d-none');
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Función para cargar las tiendas del artesano en el select
function cargarTiendasArtesano() {
    const selectTienda = document.getElementById('id_tienda');
    if (!selectTienda) return;
    
    // Realizar petición AJAX para obtener las tiendas
    fetch('/artesanoDigital/controllers/ControladorAPI.php?accion=tiendas_artesano', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exitoso && data.tiendas && data.tiendas.length > 0) {
            // Limpiar select
            selectTienda.innerHTML = '';
            
            // Agregar opciones
            data.tiendas.forEach(tienda => {
                const option = document.createElement('option');
                option.value = tienda.id_tienda;
                option.textContent = tienda.nombre_tienda;
                selectTienda.appendChild(option);
            });
        } else {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No tienes tiendas disponibles';
            option.disabled = true;
            option.selected = true;
            selectTienda.appendChild(option);
        }
    })
    .catch(error => {
        console.error('Error al cargar tiendas:', error);
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Error al cargar tiendas';
        option.disabled = true;
        option.selected = true;
        selectTienda.appendChild(option);
    });
}

// Función para cargar datos de un producto para edición
function cargarDatosProducto(idProducto) {
    fetch(`/artesanoDigital/controllers/ControladorAPI.php?accion=producto_detalle&id=${idProducto}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exitoso && data.producto) {
            const producto = data.producto;
            const modal = document.getElementById('modalNuevoProducto');
            
            if (!modal) return;
            
            // Modificar el formulario para edición
            const form = modal.querySelector('form');
            form.action = '/artesanoDigital/controllers/ControladorProductosArtesano.php';
            form.querySelector('input[name="accion"]').value = 'actualizar_producto';
            
            // Agregar campo oculto con ID del producto
            let idProductoInput = form.querySelector('input[name="id_producto"]');
            if (!idProductoInput) {
                idProductoInput = document.createElement('input');
                idProductoInput.type = 'hidden';
                idProductoInput.name = 'id_producto';
                form.appendChild(idProductoInput);
            }
            idProductoInput.value = idProducto;
            
            // Llenar campos con datos del producto
            form.querySelector('#nombre').value = producto.nombre;
            form.querySelector('#descripcion').value = producto.descripcion;
            form.querySelector('#precio').value = producto.precio;
            form.querySelector('#stock').value = producto.stock;
            form.querySelector('#descuento').value = producto.descuento || 0;
            
            // Seleccionar tienda si existe el campo
            const selectTienda = form.querySelector('#id_tienda');
            if (selectTienda && producto.id_tienda) {
                // Asegurarse de que las opciones están cargadas
                if (selectTienda.options.length === 0) {
                    cargarTiendasArtesano();
                    // Usar un timeout para dar tiempo a cargar las tiendas
                    setTimeout(() => {
                        selectTienda.value = producto.id_tienda;
                    }, 300);
                } else {
                    selectTienda.value = producto.id_tienda;
                }
            }
            
            // Marcar checkbox de activo según estado
            const checkboxActivo = form.querySelector('#activo');
            if (checkboxActivo) {
                checkboxActivo.checked = producto.activo === 1 || producto.activo === '1';
            }
            
            // Mostrar vista previa de la imagen si existe
            if (producto.imagen) {
                const previewContainer = document.getElementById('imagen-preview');
                if (previewContainer) {
                    const img = previewContainer.querySelector('img') || document.createElement('img');
                    img.src = `/artesanoDigital/${producto.imagen}`;
                    
                    if (!previewContainer.contains(img)) {
                        previewContainer.appendChild(img);
                    }
                    
                    previewContainer.classList.remove('d-none');
                }
            }
            
            // Cambiar título del modal
            const modalTitle = modal.querySelector('h2');
            if (modalTitle) modalTitle.textContent = 'Editar Producto';
            
            // Cambiar texto del botón submit
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.textContent = 'Guardar Cambios';
            
            // Mostrar modal
            modal.style.display = 'block';
        } else {
            alert('Error al cargar los datos del producto');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos del producto');
    });
}

// Función para eliminar un producto
function eliminarProducto(idProducto) {
    const formData = new FormData();
    formData.append('accion', 'eliminar_producto');
    formData.append('id_producto', idProducto);
    
    fetch('/artesanoDigital/controllers/ControladorProductosArtesano.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exitoso) {
            // Eliminar el elemento del DOM
            const productoCard = document.querySelector(`.producto-card[data-id="${idProducto}"]`);
            if (productoCard) {
                productoCard.remove();
            }
            
            // Mostrar mensaje
            alert(data.mensaje);
            
            // Comprobar si ya no hay productos
            const productosGrid = document.querySelector('.productos-grid');
            if (productosGrid && productosGrid.children.length === 0) {
                productosGrid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-box-open fa-3x"></i>
                        <h4>No hay productos</h4>
                        <p>Agrega productos para mostrarlos en tu tienda</p>
                        <button id="btnNuevoProductoEmpty" class="dashboard-btn dashboard-btn-blue">
                            <i class="fas fa-plus"></i> Crear producto
                        </button>
                    </div>
                `;
                
                // Asociar evento al nuevo botón
                const btnNuevo = productosGrid.querySelector('#btnNuevoProductoEmpty');
                if (btnNuevo) {
                    btnNuevo.addEventListener('click', function() {
                        const modal = document.getElementById('modalNuevoProducto');
                        if (modal) modal.style.display = 'block';
                    });
                }
            }
        } else {
            alert(data.mensaje || 'Error al eliminar el producto');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al comunicarse con el servidor');
    });
}
