<?php 
// Variables para el layout
$titulo = 'Catálogo de Productos - Artesano Digital';
$descripcion = 'Descubre productos únicos hechos a mano por talentosos artesanos de Panamá Oeste';
$estilosAdicionales = ['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'];

// Incluir el patrón decorador para productos con descuento
require_once __DIR__ . '/../../patrones/ProductoDecorador.php';
use Patrones\ProductoFactory;

// Preparar productos con el patrón decorador
$productosDecorados = [];
if (!empty($productos)) {
    foreach ($productos as $producto) {
        // Verificar si el producto tiene un descuento aplicable
        if (isset($producto['descuento_porcentaje']) && $producto['descuento_porcentaje'] > 0) {
            // Crear un producto con descuento
            $productoDecorado = ProductoFactory::crearProducto($producto);
            $productosDecorados[] = $productoDecorado->obtenerDetalles();
        } else {
            // Producto sin descuento
            $productoDecorado = ProductoFactory::crearProducto($producto);
            $productosDecorados[] = $productoDecorado->obtenerDetalles();
        }
    }
}

// Iniciar captura de contenido
ob_start();
?>

<!-- Header del catálogo -->
<section class="catalogo-header">
    <div class="contenedor">
        <div class="catalogo-intro">
            <h1 class="catalogo-titulo">Catálogo de Productos</h1>
            <p class="catalogo-descripcion">
                Descubre productos únicos hechos a mano por talentosos artesanos de Panamá Oeste
            </p>
        </div>
        
        <!-- Filtros de búsqueda -->
        <div class="filtros-contenedor">
            <div class="filtros-grid">
                    <div class="filtro-grupo">
                        <label for="busqueda">Buscar producto</label>
                        <input type="text" id="busqueda" placeholder="Buscar por nombre..." class="input-busqueda">
                    </div>
                    <div class="filtro-grupo">
                        <label for="categoria">Categoría</label>
                        <select id="categoria" class="select-filtro">
                            <option value="">Todas las categorías</option>
                            <option value="textiles">Textiles</option>
                            <option value="ceramica">Cerámica</option>
                            <option value="joyeria">Joyería</option>
                            <option value="madera">Madera</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label for="precio">Precio máximo</label>
                        <select id="precio" class="select-filtro">
                            <option value="">Sin límite</option>
                            <option value="25">Hasta $25</option>
                            <option value="50">Hasta $50</option>
                            <option value="100">Hasta $100</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <button type="button" class="btn btn-primario" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Aplicar filtros
                        </button>
                    </div>
            </div>
        </div>
    </div>
</section>

    <!-- Grid de productos -->
    <section class="productos-seccion">
        <div class="contenedor">
            <div class="productos-resultados">
                <p class="resultados-info">
                    Mostrando <?= count($productosDecorados ?? []) ?> productos
                </p>
            </div>
            
            <div class="productos-grid" id="productosGrid">
                <?php if (!empty($productosDecorados)): ?>
                    <?php foreach ($productosDecorados as $producto): ?>
                        <?php 
                        // Determinar categoría basada en el nombre o descripción del producto
                        $categoria = '';
                        $nombre_lower = strtolower($producto['nombre']);
                        $desc_lower = strtolower($producto['descripcion']);
                        
                        if (strpos($nombre_lower, 'textil') !== false || 
                            strpos($desc_lower, 'textil') !== false || 
                            strpos($nombre_lower, 'mola') !== false || 
                            strpos($nombre_lower, 'huipil') !== false || 
                            strpos($desc_lower, 'tejido') !== false) {
                            $categoria = 'textiles';
                        } 
                        elseif (strpos($nombre_lower, 'ceramica') !== false || 
                               strpos($desc_lower, 'ceramica') !== false || 
                               strpos($nombre_lower, 'vasija') !== false || 
                               strpos($nombre_lower, 'barro') !== false || 
                               strpos($nombre_lower, 'plato') !== false) {
                            $categoria = 'ceramica';
                        }
                        elseif (strpos($nombre_lower, 'joyeria') !== false || 
                               strpos($desc_lower, 'joyeria') !== false || 
                               strpos($nombre_lower, 'pulsera') !== false || 
                               strpos($nombre_lower, 'collar') !== false) {
                            $categoria = 'joyeria';
                        }
                        elseif (strpos($nombre_lower, 'madera') !== false || 
                               strpos($desc_lower, 'madera') !== false) {
                            $categoria = 'madera';
                        }
                        ?>
                        <div class="producto-tarjeta" data-id="<?= $producto['id_producto'] ?>" data-categoria="<?= htmlspecialchars($categoria) ?>">
                            <?php if (isset($producto['tiene_descuento']) && $producto['tiene_descuento']): ?>
                                <div class="badge-descuento">
                                    <?php if (isset($producto['descuento_porcentaje'])): ?>
                                        <?= number_format($producto['descuento_porcentaje'], 0) ?>% OFF
                                    <?php else: ?>
                                        Oferta
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="producto-imagen-contenedor">
                                <img src="<?= isset($producto['imagen']) && $producto['imagen'] ? '/artesanoDigital/' . htmlspecialchars($producto['imagen']) : '/artesanoDigital/public/placeholder.jpg' ?>" 
                                     alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                                     class="producto-imagen">
                                <div class="producto-overlay">
                                    <button class="btn-carrito" onclick="agregarAlCarrito(
                                        <?= $producto['id_producto'] ?>, 
                                        '<?= addslashes(htmlspecialchars($producto['nombre'])) ?>', 
                                        <?= isset($producto['tiene_descuento']) && $producto['tiene_descuento'] ? $producto['precio_final'] : $producto['precio'] ?>,
                                        '<?= isset($producto['imagen']) && $producto['imagen'] ? '/artesanoDigital/' . addslashes(htmlspecialchars($producto['imagen'])) : '/artesanoDigital/public/placeholder.jpg' ?>',
                                        1,
                                        <?= $producto['stock'] ?? 0 ?>
                                    )">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="producto-info">
                                <h3 class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                <p class="producto-descripcion"><?= htmlspecialchars(substr($producto['descripcion'], 0, 100)) ?>...</p>
                                <div class="producto-detalles">
                                    <?php if (isset($producto['tiene_descuento']) && $producto['tiene_descuento']): ?>
                                        <div class="producto-precios">
                                            <span class="precio-original">$<?= number_format($producto['precio_original'], 2) ?></span>
                                            <span class="precio-descuento">$<?= number_format($producto['precio_final'], 2) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <p class="producto-precio">$<?= number_format($producto['precio'], 2) ?></p>
                                    <?php endif; ?>
                                    <p class="producto-artesano">Por <?= htmlspecialchars($producto['nombre_artesano'] ?? 'Artesano') ?></p>
                                    <p class="producto-tienda"><?= htmlspecialchars($producto['nombre_tienda'] ?? 'Tienda Artesanal') ?></p>
                                </div>
                                <div class="producto-acciones">
                                    <button class="btn btn-primario" onclick="agregarAlCarrito(
                                        <?= $producto['id_producto'] ?>, 
                                        '<?= addslashes(htmlspecialchars($producto['nombre'])) ?>', 
                                        <?= isset($producto['tiene_descuento']) && $producto['tiene_descuento'] ? $producto['precio_final'] : $producto['precio'] ?>,
                                        '<?= isset($producto['imagen']) && $producto['imagen'] ? '/artesanoDigital/' . addslashes(htmlspecialchars($producto['imagen'])) : '/artesanoDigital/public/placeholder.jpg' ?>',
                                        1,
                                        <?= $producto['stock'] ?? 0 ?>
                                    )">
                                        <i class="fas fa-shopping-cart"></i> Agregar al carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="productos-vacio">
                        <h3>No hay productos disponibles</h3>
                        <p>Pronto tendremos productos increíbles de nuestros artesanos.</p>
                        <a href="/artesanoDigital/" class="btn btn-primario">Volver al Inicio</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Paginación -->
            <div class="paginacion">
                <button class="btn btn-outline" onclick="cargarMasProductos()">
                    Cargar Más Productos
                </button>
            </div>
        </div>
    </section>

<script>
function aplicarFiltros() {
    const terminoBusqueda = document.getElementById('busqueda').value.toLowerCase();
    const categoria = document.getElementById('categoria').value;
    const precioMaximo = document.getElementById('precio').value;
    
    const productos = document.querySelectorAll('.producto-tarjeta');
    let contadorVisibles = 0;
    
    productos.forEach(producto => {
        let mostrar = true;
        
        // Filtrar por término de búsqueda
        if (terminoBusqueda) {
            const nombre = producto.querySelector('.producto-nombre').textContent.toLowerCase();
            const descripcion = producto.querySelector('.producto-descripcion').textContent.toLowerCase();
            if (!nombre.includes(terminoBusqueda) && !descripcion.includes(terminoBusqueda)) {
                mostrar = false;
            }
        }
        
        // Filtrar por categoría
        if (categoria && mostrar) {
            const categoriaProducto = producto.getAttribute('data-categoria');
            if (categoriaProducto !== categoria) {
                mostrar = false;
            }
        }
        
        // Filtrar por precio
        if (precioMaximo && mostrar) {
            // Verificar si tiene descuento
            const precioDescuento = producto.querySelector('.precio-descuento');
            let precioText;
            
            if (precioDescuento) {
                // Si tiene descuento, usar el precio con descuento
                precioText = precioDescuento.textContent.replace('$', '');
            } else {
                // Si no tiene descuento, usar el precio normal
                precioText = producto.querySelector('.producto-precio').textContent.replace('$', '');
            }
            const precio = parseFloat(precioText);
            if (precio > parseFloat(precioMaximo)) {
                mostrar = false;
            }
        }
        
        // Mostrar u ocultar producto
        if (mostrar) {
            producto.style.display = 'block';
            contadorVisibles++;
        } else {
            producto.style.display = 'none';
        }
    });
    
    // Actualizar contador de resultados
    const resultadosInfo = document.querySelector('.resultados-info');
    if (resultadosInfo) {
        resultadosInfo.textContent = `Mostrando ${contadorVisibles} productos`;
    }
}

function cargarMasProductos() {
    // Implementar carga de más productos
    console.log('Cargando más productos...');
    mostrarMensaje('Cargando más productos...', 'info');
}

// Función para agregar al carrito con detalles del producto
// Nota: Esta función ahora recibe todos los parámetros directamente desde el botón
function agregarAlCarrito(idProducto, nombre, precio, imagen, cantidad = 1, stock = 0) {
    // Validar que tengamos toda la información necesaria
    if (!idProducto || !nombre || isNaN(precio)) {
        console.error('Faltan datos para agregar al carrito');
        mostrarMensaje('Error: Datos insuficientes para agregar al carrito', 'error');
        return;
    }
    
    // Identificar el usuario actual para usar la clave correcta
    const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
    const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';
    
    // Agregar al carrito local con la clave específica del usuario
    let carrito = JSON.parse(localStorage.getItem(carritoKey)) || [];
    let productoExistente = carrito.find(item => item.id === idProducto);
    
    if (productoExistente) {
        productoExistente.cantidad += cantidad;
    } else {
        carrito.push({
            id: idProducto,
            nombre: nombre,
            precio: precio,
            imagen: imagen,
            cantidad: cantidad,
            stock: stock
        });
    }
    
    // Guardar con la clave específica del usuario
    localStorage.setItem(carritoKey, JSON.stringify(carrito));
    // Mantener compatibilidad con el código existente
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    // Sincronizar con el servidor
    fetch('/artesanoDigital/controllers/checkout.php', {
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
            actualizarContadorCarrito(data.total_productos);
            actualizarMiniCarrito();
            mostrarMensaje(`${nombre} agregado al carrito`, 'success');
        } else {
            mostrarMensaje(data.mensaje || 'No se pudo agregar al carrito', 'error');
        }
    })
    .catch(error => {
        console.error('Error al agregar producto:', error);
        mostrarMensaje('Error al agregar el producto', 'error');
    });
    
    // Actualizar el contador y mini carrito manualmente también
    const carritoItems = carrito.reduce((total, item) => total + item.cantidad, 0);
    const contadorCarrito = document.getElementById('carrito-contador');
    if (contadorCarrito) {
        contadorCarrito.textContent = carritoItems;
    }
    
    // Intentar actualizar el mini carrito si la función está disponible
    if (typeof actualizarMiniCarrito === 'function') {
        actualizarMiniCarrito();
    }
    
    mostrarMensaje(`${nombre} agregado al carrito`, 'success');
}

// Función para mostrar mensajes toast
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
// Capturar el contenido y preparar para incluir base.php
$contenido = ob_get_clean();

// Agregar estilos específicos para el catálogo
$estilosAdicionales[] = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';

// Añadir estilos internos para el head
$estilosInternos = '<style>
/* Estilos para la página de catálogo */
.catalogo-header {
    background: #f5f7fa;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-bottom: 1px solid #eaedf2;
    clear: both;
    position: relative;
    z-index: 1; /* Asegurar que esté por debajo del header principal */
    margin-top: 90px; /* Asegurar espacio suficiente después del header fijo */
}

.catalogo-titulo {
    font-size: 2.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    text-align: center;
}

.catalogo-descripcion {
    text-align: center;
    max-width: 700px;
    margin: 0 auto 2rem;
    color: #666;
    font-size: 1.1rem;
    line-height: 1.5;
}

.filtros-contenedor {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    margin-top: 2rem;
}

.filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filtro-grupo {
    display: flex;
    flex-direction: column;
}

.filtro-grupo label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    font-size: 0.9rem;
    color: #555;
}

.input-busqueda, .select-filtro {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: "Inter", sans-serif;
    font-size: 0.95rem;
}

.input-busqueda:focus, .select-filtro:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.productos-seccion {
    padding: 2rem 0;
}

.productos-resultados {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.resultados-info {
    font-size: 0.95rem;
    color: #666;
}

.productos-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (min-width: 576px) {
    .productos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 992px) {
    .productos-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.producto-tarjeta {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.producto-tarjeta:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.producto-imagen-contenedor {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.producto-imagen {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.producto-tarjeta:hover .producto-imagen {
    transform: scale(1.05);
}

.producto-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.producto-tarjeta:hover .producto-overlay {
    opacity: 1;
}

.btn-carrito {
    background: white;
    color: #333;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.btn-carrito:hover {
    background: #4a90e2;
    color: white;
    transform: scale(1.05);
}

.producto-info {
    padding: 1.25rem;
}

.producto-nombre {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 0.75rem;
    color: #333;
}

.producto-descripcion {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.producto-detalles {
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.producto-precio {
    font-weight: 600;
    font-size: 1.2rem;
    color: #4a90e2;
    margin: 0;
}

/* Estilos para productos con descuento */
.badge-descuento {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #FF3B30;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.precio-original {
    text-decoration: line-through;
    color: #999;
    margin-right: 0.75rem;
    font-size: 0.9rem;
}

.precio-descuento {
    color: #e63946;
    font-weight: 700;
}

.producto-artesano, .producto-tienda {
    font-size: 0.85rem;
    color: #666;
    margin: 0;
}

.producto-acciones {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
}

.btn {
    flex: 1;
    padding: 0.7rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    text-align: center;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn i {
    font-size: 14px;
}

.btn-primario {
    background: #4a90e2;
    color: white;
    border: none;
}

.btn-primario:hover {
    background: #357ab8;
}

.btn-outline {
    background: transparent;
    border: 1px solid #4a90e2;
    color: #4a90e2;
}

.btn-outline:hover {
    background: #4a90e2;
    color: white;
}

.paginacion {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
}

.productos-vacio {
    text-align: center;
    padding: 3rem;
    background: #f9f9f9;
    border-radius: 12px;
}

.productos-vacio h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #555;
}

.productos-vacio p {
    color: #777;
    margin-bottom: 2rem;
}

/* Toast notifications */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-top: 10px;
    overflow: hidden;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.toast-mostrar {
    opacity: 1;
    transform: translateY(0);
}

.toast-contenido {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.toast-mensaje {
    flex: 1;
    margin-right: 10px;
}

.toast-cerrar {
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    color: #777;
}

.toast-success {
    border-left: 4px solid #2ecc71;
}

.toast-error {
    border-left: 4px solid #e74c3c;
}

.toast-info {
    border-left: 4px solid #3498db;
}

.toast-warning {
    border-left: 4px solid #f39c12;
}

/* Estilos adicionales para mejorar los productos */
.producto-descripcion {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
    margin-bottom: 1rem;
    flex: 1;
}

.producto-info {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 1.25rem;
}

.producto-detalles {
    margin-top: auto;
    margin-bottom: 1rem;
}

.producto-precios {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.precio-original {
    text-decoration: line-through;
    color: #888;
    font-size: 0.9rem;
    margin-right: 8px;
}

.precio-descuento {
    color: #FF3B30;
    font-weight: 600;
    font-size: 1.1rem;
}

.producto-precio {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 0.5rem;
}

.producto-artesano, .producto-tienda {
    font-size: 0.85rem;
    color: #666;
    margin: 0.25rem 0;
}

.btn-primario:hover {
    background: #357ab8;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 991px) {
    .filtros-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
}

@media (max-width: 992px) {
    .productos-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    .catalogo-titulo {
        font-size: 1.8rem;
    }
}

@media (max-width: 576px) {
    .filtros-grid {
        grid-template-columns: 1fr;
    }
    
    .productos-grid {
        grid-template-columns: 1fr;
    }
    
    .producto-acciones {
        flex-direction: column;
    }
    
    .catalogo-titulo {
        font-size: 1.6rem;
    }
}
</style>';

// Ahora incluimos el layout base con nuestro contenido y estilos
include __DIR__ . '/../layouts/base.php';
