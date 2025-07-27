<?php 
// Variables para el layout
$titulo = 'Catálogo de Productos - Artesano Digital';
$descripcion = 'Descubre productos únicos hechos a mano por talentosos artesanos de Panamá Oeste';
$estilosAdicionales = ['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'];

// Incluir el patrón decorador para productos con descuento
require_once __DIR__ . '/../../patrones/ProductoDecorador.php';
use Patrones\ProductoFactory;

// Procesar productos para evitar duplicaciones y obtener datos de la base de datos
$productosUnicos = [];
$productosDecorados = [];
$categorias = [];

if (!empty($productos)) {
    // Crear un array para evitar duplicados basado en ID
    $productosVistos = [];
    
    foreach ($productos as $producto) {
        // Validar que el producto tenga ID válido
        if (!isset($producto['id_producto']) || empty($producto['id_producto'])) {
            continue;
        }
        
        // Evitar duplicados por ID
        if (isset($productosVistos[$producto['id_producto']])) {
            continue;
        }
        $productosVistos[$producto['id_producto']] = true;
        
        // Validar campos requeridos
        if (!isset($producto['precio']) || !isset($producto['nombre'])) {
            continue;
        }
        
        // Calcular precio con descuento si existe
        $precioFinal = $producto['precio'];
        $tieneDescuento = false;
        $porcentajeDescuento = 0;
        
        if (isset($producto['descuento']) && $producto['descuento'] > 0) {
            $tieneDescuento = true;
            $porcentajeDescuento = round(($producto['descuento'] / $producto['precio']) * 100);
            $precioFinal = $producto['precio'] - $producto['descuento'];
        }
        
        // Determinar categoría basada en el nombre del producto
        $categoria = determinarCategoria($producto['nombre'], $producto['descripcion'] ?? '');
        if (!in_array($categoria, $categorias) && !empty($categoria)) {
            $categorias[] = $categoria;
        }
        
        // Preparar datos del producto
        $productoData = [
            'id_producto' => $producto['id_producto'],
            'nombre' => $producto['nombre'],
            'descripcion' => $producto['descripcion'] ?? '',
            'precio' => $producto['precio'],
            'precio_final' => $precioFinal,
            'descuento' => $producto['descuento'] ?? 0,
            'porcentaje_descuento' => $porcentajeDescuento,
            'tiene_descuento' => $tieneDescuento,
            'imagen' => $producto['imagen'] ?? null,
            'stock' => $producto['stock'] ?? 0,
            'categoria' => $categoria,
            'nombre_tienda' => $producto['nombre_tienda'] ?? 'Tienda Artesanal',
            'nombre_artesano' => $producto['nombre_artesano'] ?? 'Artesano', // Nombre del usuario
            'activo' => $producto['activo'] ?? 1
        ];
        
        // Solo agregar productos activos con stock positivo
        if ($productoData['activo'] && $productoData['stock'] > 0) {
            $productosDecorados[] = $productoData;
        }
    }
}

// Función para determinar categoría
function determinarCategoria($nombre, $descripcion) {
    $nombre_lower = strtolower($nombre);
    $desc_lower = strtolower($descripcion);
    
    if (strpos($nombre_lower, 'mola') !== false || 
        strpos($nombre_lower, 'huipil') !== false || 
        strpos($desc_lower, 'tejido') !== false ||
        strpos($nombre_lower, 'textil') !== false ||
        strpos($nombre_lower, 'bolso') !== false) {
        return 'textiles';
    } 
    elseif (strpos($nombre_lower, 'vasija') !== false || 
           strpos($nombre_lower, 'ceramica') !== false || 
           strpos($desc_lower, 'ceramica') !== false || 
           strpos($nombre_lower, 'barro') !== false || 
           strpos($nombre_lower, 'plato') !== false) {
        return 'ceramica';
    }
    elseif (strpos($nombre_lower, 'pulsera') !== false || 
           strpos($nombre_lower, 'collar') !== false || 
           strpos($nombre_lower, 'joyeria') !== false || 
           strpos($desc_lower, 'joyeria') !== false ||
           strpos($nombre_lower, 'tagua') !== false) {
        return 'joyeria';
    }
    elseif (strpos($nombre_lower, 'sombrero') !== false || 
           strpos($nombre_lower, 'madera') !== false || 
           strpos($desc_lower, 'madera') !== false) {
        return 'tradicional';
    }
    
    return 'otros';
}

// Iniciar captura de contenido
ob_start();
?>
<link rel="stylesheet" href="/artesanoDigital/assets/css/catalogo-productos.css">

<!-- HEADER DEL CATÁLOGO -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para verificar y corregir las rutas de imágenes
    function verificarImagenes() {
        const imagenes = document.querySelectorAll('.producto-imagen');
        
        imagenes.forEach(img => {
            // Verificar si la imagen existe
            fetch(img.src, { method: 'HEAD' })
                .then(response => {
                    if (!response.ok) {
                        console.warn('Imagen no encontrada: ' + img.src);
                        img.src = '/artesanoDigital/public/placeholder.jpg';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar imagen:', error);
                    img.src = '/artesanoDigital/public/placeholder.jpg';
                });
        });
    }
    
    // Ejecutar la verificación después de cargar la página
    setTimeout(verificarImagenes, 500);
});

// Función para mostrar la imagen completa en modal cuando se hace clic
function mostrarImagenCompleta(src) {
    const modal = document.createElement('div');
    modal.className = 'imagen-modal';
    modal.innerHTML = `
        <div class="imagen-modal-contenido">
            <span class="cerrar-modal">&times;</span>
            <img src="${src}" class="imagen-ampliada">
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.querySelector('.cerrar-modal').addEventListener('click', () => {
        modal.remove();
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
}
</script>

<style>
/* Estilos para el modal de imagen */
.imagen-modal {
    display: flex;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    align-items: center;
    justify-content: center;
}

.imagen-modal-contenido {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.imagen-ampliada {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.cerrar-modal {
    position: absolute;
    top: -30px;
    right: 0;
    color: white;
    font-size: 30px;
    cursor: pointer;
}

/* Mejorar visualización de imágenes en catálogo */
.producto-imagen {
    cursor: zoom-in;
    transition: transform 0.3s ease;
}

.producto-imagen:hover {
    transform: scale(1.03);
}
</style>
<header class="catalogo-header">
    <div class="container">
        <h1 class="catalogo-titulo">
            Catálogo de Productos Artesanales
        </h1>
        <p class="catalogo-descripcion">
            Descubre productos únicos hechos a mano por talentosos artesanos de Panamá Oeste
        </p>
    </div>
</header>

<div class="container">
    <div class="catalogo-layout">
        <!-- PANEL DE FILTROS -->
        <aside class="filtros-contenedor">
            <h3 class="filtros-titulo">
                <i class="fas fa-filter"></i>
                Filtrar Productos
            </h3>
            
            <div class="filtros-grid">
                <!-- Búsqueda por nombre -->
                <div class="filtro-grupo">
                    <label for="busqueda">
                        <i class="fas fa-search"></i>
                        Buscar producto
                    </label>
                    <input type="text" id="busqueda" class="input-busqueda" placeholder="Nombre del producto...">
                </div>

                <!-- Filtro por categoría -->
                <div class="filtro-grupo">
                    <label for="categoria">
                        <i class="fas fa-tags"></i>
                        Categoría
                    </label>
                    <select id="categoria" class="select-filtro">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>">
                                <?= ucfirst($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro por rango de precio -->
                <div class="filtro-grupo">
                    <label for="precio">
                        <i class="fas fa-dollar-sign"></i>
                        Rango de precio
                    </label>
                    <select id="precio" class="select-filtro">
                        <option value="">Todos los precios</option>
                        <option value="25">Hasta $25</option>
                        <option value="50">$25 - $50</option>
                        <option value="100">$50 - $100</option>
                        <option value="999">Más de $100</option>
                    </select>
                </div>

                <!-- Filtro por tienda -->
                <div class="filtro-grupo">
                    <label for="tienda">
                        <i class="fas fa-store-alt"></i>
                        Tienda
                    </label>
                    <select id="tienda" class="select-filtro">
                        <option value="">Todas las tiendas</option>
                        <?php 
                        $tiendas = array_unique(array_column($productosDecorados, 'nombre_tienda'));
                        foreach ($tiendas as $tienda): 
                        ?>
                            <option value="<?= htmlspecialchars($tienda) ?>">
                                <?= htmlspecialchars($tienda) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="filtros-acciones">
                <button type="button" class="btn btn-outline" onclick="limpiarFiltros()">
                    <i class="fas fa-eraser"></i>
                    Limpiar filtros
                </button>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="productos-contenido">
            <!-- Controles de productos -->
            <div class="productos-controles">
                <div class="productos-info">
                    <span class="productos-contador">
                        <strong id="contador-productos"><?= count($productosDecorados) ?></strong> productos encontrados
                    </span>
                </div>
                
                <div class="productos-ordenar">
                    <label for="ordenar">Ordenar por:</label>
                    <select id="ordenar" class="select-filtro" onchange="ordenarProductos()">
                        <option value="nombre">Nombre</option>
                        <option value="precio-asc">Precio: menor a mayor</option>
                        <option value="precio-desc">Precio: mayor a menor</option>
                        <option value="descuento">Con descuento</option>
                    </select>
                </div>
            </div>

            <!-- Grid de productos -->
            <?php if (!empty($productosDecorados)): ?>
                <div class="productos-grid" id="productosGrid">
                    <?php foreach ($productosDecorados as $producto): ?>
                        <article class="producto-tarjeta" 
                                data-nombre="<?= strtolower(htmlspecialchars($producto['nombre'])) ?>"
                                data-categoria="<?= htmlspecialchars($producto['categoria']) ?>"
                                data-precio="<?= htmlspecialchars($producto['precio_final']) ?>"
                                data-tienda="<?= htmlspecialchars($producto['nombre_tienda']) ?>">
                            
                            <!-- Badges -->
                            <?php if ($producto['tiene_descuento']): ?>
                                <div class="badge-descuento">
                                    -<?= $producto['porcentaje_descuento'] ?>%
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($producto['stock'] <= 5): ?>
                                <div class="badge-stock">
                                    Últimas <?= $producto['stock'] ?> unidades
                                </div>
                            <?php endif; ?>

                            <!-- Imagen del producto -->
                            <div class="producto-imagen-contenedor">
                                <?php
                                // Determinar la ruta correcta de la imagen
                                $rutaImagen = '/artesanoDigital/public/placeholder.jpg';
                                if (!empty($producto['imagen'])) {
                                    // Si la imagen ya incluye 'public/' o 'uploads/', usar tal como está
                                    if (strpos($producto['imagen'], 'public/') === 0 || strpos($producto['imagen'], 'uploads/') === 0) {
                                        $rutaImagen = '/artesanoDigital/' . $producto['imagen'];
                                    } else {
                                        // Si no, intentar con uploads/productos/ primero
                                        $rutaImagen = '/artesanoDigital/uploads/productos/' . $producto['imagen'];
                                    }
                                }
                                ?>
                                <img src="<?= htmlspecialchars($rutaImagen) ?>" 
                                     alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                                     class="producto-imagen"
                                     onerror="this.src='/artesanoDigital/public/placeholder.jpg'"
                                
                                <!-- Overlay con acciones -->
                                <div class="producto-overlay">
                                    <button type="button" class="btn-accion btn-vista" onclick="verDetalleProducto(<?= $producto['id_producto'] ?>)" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php
                                    // Preparar la ruta de imagen para JavaScript (escapar comillas)
                                    $imagenParaJS = '';
                                    if (!empty($producto['imagen'])) {
                                        if (strpos($producto['imagen'], 'public/') === 0 || strpos($producto['imagen'], 'uploads/') === 0) {
                                            $imagenParaJS = '/artesanoDigital/' . $producto['imagen'];
                                        } else {
                                            $imagenParaJS = '/artesanoDigital/uploads/productos/' . $producto['imagen'];
                                        }
                                    } else {
                                        $imagenParaJS = '/artesanoDigital/public/placeholder.jpg';
                                    }
                                    ?>
                                    <button type="button" class="btn-accion btn-carrito" onclick="agregarAlCarritoCompleto(<?= $producto['id_producto'] ?>, '<?= htmlspecialchars($producto['nombre']) ?>', <?= $producto['precio_final'] ?>, '<?= htmlspecialchars($imagenParaJS) ?>', 1, <?= $producto['stock'] ?>)" title="Agregar al carrito">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Información del producto -->
                            <div class="producto-info">
                                <div class="producto-header">
                                    <h3 class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                    <div class="producto-categoria">
                                        <span class="categoria-tag categoria-<?= $producto['categoria'] ?>">
                                            <?= ucfirst($producto['categoria']) ?>
                                        </span>
                                    </div>
                                </div>

                                <p class="producto-descripcion">
                                    <?= htmlspecialchars(substr($producto['descripcion'], 0, 100)) ?><?= strlen($producto['descripcion']) > 100 ? '...' : '' ?>
                                </p>

                                <!-- Precios -->
                                <div class="producto-precios">
                                    <?php if ($producto['tiene_descuento']): ?>
                                        <span class="precio-original">$<?= number_format($producto['precio'], 2) ?></span>
                                        <span class="precio-descuento">$<?= number_format($producto['precio_final'], 2) ?></span>
                                        <small class="ahorro">Ahorras $<?= number_format($producto['descuento'], 2) ?></small>
                                    <?php else: ?>
                                        <span class="producto-precio">$<?= number_format($producto['precio'], 2) ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Metadatos -->
                                <div class="producto-metadatos">
                                    <div class="tienda-info">
                                        <i class="fas fa-store"></i>
                                        <span><?= htmlspecialchars($producto['nombre_tienda']) ?></span>
                                    </div>
                                    <div class="stock-info">
                                        <i class="fas fa-boxes"></i>
                                        <span><?= $producto['stock'] ?> disponibles</span>
                                    </div>
                                </div>

                                <!-- Botón agregar al carrito -->
                                <button type="button" class="btn btn-primary btn-agregar" onclick="agregarAlCarritoCompleto(<?= $producto['id_producto'] ?>, '<?= htmlspecialchars($producto['nombre']) ?>', <?= $producto['precio_final'] ?>, '<?= htmlspecialchars($imagenParaJS) ?>', 1, <?= $producto['stock'] ?>)">
                                    <i class="fas fa-cart-plus"></i>
                                    Agregar al carrito
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Estado vacío -->
                <div class="productos-vacio">
                    <div class="vacio-icono">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3 class="vacio-titulo">No hay productos disponibles</h3>
                    <p class="vacio-descripcion">
                        Actualmente no tenemos productos en nuestro catálogo. 
                        Vuelve pronto para descubrir nuevas creaciones artesanales.
                    </p>
                    <div class="vacio-acciones">
                        <a href="/artesanoDigital/" class="btn btn-primary">
                            <i class="fas fa-home"></i>
                            Volver al inicio
                        </a>
                        <button type="button" class="btn btn-outline" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Toast Container para notificaciones -->
<div class="toast-container"></div>

<script>
// Variables globales
let productosOriginales = Array.from(document.querySelectorAll('.producto-tarjeta'));

function aplicarFiltros() {
    const terminoBusqueda = document.getElementById('busqueda').value.toLowerCase();
    const categoria = document.getElementById('categoria').value;
    const precioMaximo = document.getElementById('precio').value;
    const tiendaSeleccionada = document.getElementById('tienda').value;
    
    const productos = document.querySelectorAll('.producto-tarjeta');
    let contadorVisibles = 0;
    
    productos.forEach(producto => {
        let mostrar = true;
        
        // Filtrar por término de búsqueda
        if (terminoBusqueda && mostrar) {
            const nombre = producto.getAttribute('data-nombre') || '';
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
        
        // Filtrar por tienda
        if (tiendaSeleccionada && mostrar) {
            const tiendaProducto = producto.getAttribute('data-tienda');
            if (tiendaProducto !== tiendaSeleccionada) {
                mostrar = false;
            }
        }
        
        // Filtrar por precio
        if (precioMaximo && mostrar) {
            const precio = parseFloat(producto.getAttribute('data-precio'));
            const rangos = {
                '25': [0, 25],
                '50': [25, 50],
                '100': [50, 100],
                '999': [100, 999999]
            };
            
            if (rangos[precioMaximo]) {
                const [min, max] = rangos[precioMaximo];
                if (precio < min || precio > max) {
                    mostrar = false;
                }
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
    actualizarContadorResultados(contadorVisibles);
}

function limpiarFiltros() {
    document.getElementById('busqueda').value = '';
    document.getElementById('categoria').value = '';
    document.getElementById('precio').value = '';
    document.getElementById('tienda').value = '';
    document.getElementById('ordenar').value = 'nombre';
    
    // Mostrar todos los productos
    const productos = document.querySelectorAll('.producto-tarjeta');
    productos.forEach(producto => {
        producto.style.display = 'block';
    });
    
    actualizarContadorResultados(productos.length);
}

function ordenarProductos() {
    const criterio = document.getElementById('ordenar').value;
    const contenedor = document.getElementById('productosGrid');
    const productos = Array.from(contenedor.querySelectorAll('.producto-tarjeta:not([style*="display: none"])'));
    
    productos.sort((a, b) => {
        switch (criterio) {
            case 'nombre':
                return a.getAttribute('data-nombre').localeCompare(b.getAttribute('data-nombre'));
            case 'precio-asc':
                return parseFloat(a.getAttribute('data-precio')) - parseFloat(b.getAttribute('data-precio'));
            case 'precio-desc':
                return parseFloat(b.getAttribute('data-precio')) - parseFloat(a.getAttribute('data-precio'));
            case 'descuento':
                const aDescuento = a.querySelector('.badge-descuento') ? 1 : 0;
                const bDescuento = b.querySelector('.badge-descuento') ? 1 : 0;
                return bDescuento - aDescuento;
            default:
                return 0;
        }
    });
    
    // Reorganizar los elementos en el DOM
    productos.forEach(producto => {
        contenedor.appendChild(producto);
    });
}

function actualizarContadorResultados(contador) {
    const contadorElemento = document.getElementById('contador-productos');
    if (contadorElemento) {
        contadorElemento.textContent = contador;
    }
}

function verDetalleProducto(idProducto) {
    // Redirigir a la vista de detalle del producto
    window.location.href = `/artesanoDigital/productos/detalle?id=${idProducto}`;
}

function cargarMasProductos() {
    mostrarMensaje('Función de carga implementada próximamente...', 'info');
}

// Función específica para el catálogo que usa la API del carrito
function agregarAlCarritoCompleto(idProducto, nombre, precio, imagen, cantidad = 1, stock = 0) {
    // Validar parámetros
    if (!idProducto || !nombre || isNaN(precio)) {
        mostrarMensaje('Error: Datos insuficientes para agregar al carrito', 'error');
        return;
    }
    
    // Validar stock
    if (stock <= 0) {
        mostrarMensaje('Producto sin stock disponible', 'warning');
        return;
    }

    // Obtener el botón específico para mostrar estado de carga
    const boton = document.querySelector(`[onclick*="agregarAlCarritoCompleto(${idProducto}"]`);
    const textoOriginal = boton ? boton.innerHTML : '';
    
    // Deshabilitar botón y mostrar carga
    if (boton) {
        boton.disabled = true;
        boton.classList.add('loading');
        boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agregando...';
    }

    // Llamar a la API del carrito
    fetch('/artesanoDigital/api/carrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'agregar',
            id_producto: idProducto,
            cantidad: cantidad
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.exitoso) {
            mostrarMensaje(data.mensaje || 'Producto agregado al carrito', 'success');
            // Actualizar contador del carrito si existe
            if (data.carrito && data.carrito.cantidad_total) {
                actualizarContadorCarrito(data.carrito.cantidad_total);
            }
            
            // Cambiar botón temporalmente a "Agregado"
            if (boton) {
                boton.innerHTML = '<i class="fas fa-check"></i> ¡Agregado!';
                boton.classList.remove('loading');
                boton.classList.add('success');
                
                setTimeout(() => {
                    boton.innerHTML = textoOriginal;
                    boton.classList.remove('success');
                    boton.disabled = false;
                }, 2000);
            }
        } else {
            mostrarMensaje(data.mensaje || 'Error al agregar producto', 'error');
            // Restaurar botón
            if (boton) {
                boton.innerHTML = textoOriginal;
                boton.classList.remove('loading');
                boton.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión al agregar producto', 'error');
        // Restaurar botón
        if (boton) {
            boton.innerHTML = textoOriginal;
            boton.classList.remove('loading');
            boton.disabled = false;
        }
    });
}

function actualizarContadorCarrito(total) {
    const contadores = document.querySelectorAll('#carrito-contador, .carrito-contador');
    contadores.forEach(contador => {
        contador.textContent = total;
        contador.style.display = total > 0 ? 'inline' : 'none';
    });
}

function actualizarContadorCarrito(total) {
    const contadores = document.querySelectorAll('#carrito-contador, .carrito-contador');
    contadores.forEach(contador => {
        contador.textContent = total;
        contador.style.display = total > 0 ? 'inline' : 'none';
    });
}

// Función mejorada para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'info') {
    // Remover mensajes anteriores
    const mensajesAnteriores = document.querySelectorAll('.toast');
    mensajesAnteriores.forEach(toast => toast.remove());
    
    // Crear nuevo toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <div class="toast-contenido">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : tipo === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
            <span class="toast-mensaje">${mensaje}</span>
            <button class="toast-cerrar" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Agregar estilos si no existen
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    document.querySelector('.toast-container').appendChild(toast);
    
    // Auto cerrar
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// Inicializar funcionalidades al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Filtro en tiempo real para búsqueda
    document.getElementById('busqueda').addEventListener('input', aplicarFiltros);
    
    // Eventos para todos los filtros
    ['categoria', 'precio', 'tienda'].forEach(id => {
        document.getElementById(id).addEventListener('change', aplicarFiltros);
    });
});

function actualizarContadorCarrito(total) {
    const contadores = document.querySelectorAll('#carrito-contador, .carrito-contador');
    contadores.forEach(contador => {
        contador.textContent = total;
        contador.style.display = total > 0 ? 'inline' : 'none';
    });
}

// Función mejorada para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'info') {
    // Remover mensajes anteriores
    const mensajesAnteriores = document.querySelectorAll('.toast');
    mensajesAnteriores.forEach(toast => toast.remove());
    
    // Crear nuevo toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <div class="toast-contenido">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : tipo === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
            <span class="toast-mensaje">${mensaje}</span>
            <button class="toast-cerrar" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Agregar estilos si no existen
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    document.querySelector('.toast-container').appendChild(toast);
    
    // Auto cerrar
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// Inicializar funcionalidades al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Filtro en tiempo real para búsqueda
    document.getElementById('busqueda').addEventListener('input', aplicarFiltros);
    
    // Eventos para todos los filtros
    ['categoria', 'precio', 'tienda'].forEach(id => {
        document.getElementById(id).addEventListener('change', aplicarFiltros);
    });
});
</script>

<?php 
// Capturar el contenido y preparar para incluir base.php
$contenido = ob_get_clean();

// Agregar estilos específicos para el catálogo y componentes adicionales
$estilosAdicionales = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
    '/artesanoDigital/assets/css/catalogo-productos.css'
];

// Incluir el layout base con nuestro contenido y estilos
include __DIR__ . '/../layouts/base.php';
?>
