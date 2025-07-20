<?php
// Asegurarnos de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title><?= htmlspecialchars($titulo ?? 'Sistema Artesano Digital') ?></title>
    <meta name="description" content="<?= htmlspecialchars($descripcion ?? 'Plataforma de comercio electrónico para artesanos de Panamá Oeste') ?>">
    <meta name="keywords" content="artesanías, Panamá, comercio electrónico, molas, cerámica, textiles">
    <meta name="author" content="Sistema Artesano Digital">

    <!-- CSS principal -->
    <link rel="stylesheet" href="/artesanoDigital/assets/css/estilos.css">
    
    <!-- Estilos adicionales -->
    <?php if (isset($estilosAdicionales) && is_array($estilosAdicionales)): ?>
        <?php foreach($estilosAdicionales as $estilo): ?>
    <link rel="stylesheet" href="<?= $estilo ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Google Fonts Inter y Material Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Override CSS: quitar estilos globales de button y fijar carrito -->
    <style>
        /* Forzar tema claro */
        html { color-scheme: light !important; }
        body { background-color: #faf8f5 !important; color: #2c2c2c !important; font-family: 'Inter', sans-serif; }

        /* Header fijo arriba */
        .header-principal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .main-contenido {
            margin-top: 90px; /* Espacio para compensar el header fijo */
        }

        /* Anular cualquier estilo global de button y diseñar sólo el carrito */
        button.carrito-btn {
            all: unset;
            position: absolute;
            top: 50%;
            right: 1.5rem;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        /* Hover sutil sin animaciones */
        button.carrito-btn:hover {
            background-color: #f5f5f5;
        }
        button.carrito-btn:focus {
            outline: none;
        }

        /* Badge pegado al icono */
        #carrito-contador {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: #e53e3e;
            color: #ffffff;
            border-radius: 50%;
            font-size: 0.75rem;
            line-height: 1;
            padding: 2px 5px;
            min-width: 18px;
            text-align: center;
            pointer-events: none;
        }

        /* Dropdown de usuario (sin animaciones) */
        .dropdown { position: relative; display: inline-block; }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 10rem;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: .25rem;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        }
        .dropdown-menu.activo { display: block; }
        .dropdown-item {
            display: block;
            width: 100%;
            padding: .5rem 1.25rem;
            color: #212529;
            text-decoration: none;
        }
        .dropdown-item:hover { background-color: #f8f9fa; color: #16181b; }
        
        /* Mini Carrito */
        .mini-carrito {
            display: none;
            position: absolute;
            top: 70px;
            right: 1.5rem;
            width: 350px;
            max-height: 500px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            z-index: 1000;
            overflow: hidden;
        }
        
        .mini-carrito.activo {
            display: block;
        }
        
        .mini-carrito-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .mini-carrito-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .cerrar-mini-carrito {
            all: unset;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        
        .cerrar-mini-carrito:hover {
            background-color: #f1f1f1;
        }
        
        .cerrar-mini-carrito .material-icons {
            font-size: 20px;
        }
        
        .mini-carrito-items {
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .mini-carrito-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .mini-carrito-item:last-child {
            border-bottom: none;
        }
        
        .mini-carrito-item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 0.75rem;
        }
        
        .mini-carrito-item-info {
            flex: 1;
        }
        
        .mini-carrito-item-nombre {
            margin: 0 0 0.25rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .mini-carrito-item-precio {
            font-size: 0.85rem;
            color: #666;
            display: block;
        }
        
        .mini-carrito-item-cantidad {
            font-size: 0.85rem;
            color: #777;
            margin-top: 0.25rem;
        }
        
        .mini-carrito-item-acciones {
            display: flex;
            gap: 0.5rem;
        }
        
        .mini-carrito-item-eliminar {
            all: unset;
            cursor: pointer;
            color: #e53e3e;
            font-size: 0.85rem;
        }
        
        .mini-carrito-vacio {
            padding: 2rem 1rem;
            text-align: center;
        }
        
        .mini-carrito-vacio-icono {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .mini-carrito-vacio-mensaje {
            color: #777;
        }
        
        .mini-carrito-footer {
            padding: 1rem;
            border-top: 1px solid #eee;
            background-color: #f9f9f9;
        }
        
        .mini-carrito-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .mini-carrito-acciones {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.9rem;
        }
        
        .dropdown-toggle {
            all: unset;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: .5rem;
            position: relative;
        }
        .dropdown-toggle::after {
            content: '';
            display: inline-block;
            margin-left: .5rem;
            border-top: .3em solid;
            border-right: .3em solid transparent;
            border-left: .3em solid transparent;
        }
    </style>

    <!-- Favicons -->
    <link rel="icon" href="/artesanoDigital/public/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/artesanoDigital/public/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/artesanoDigital/public/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/artesanoDigital/public/favicon-16x16.png">
</head>
<body class="<?= htmlspecialchars($claseBody ?? '') ?>" data-user-id="<?= isset($_SESSION['usuario_id']) ? htmlspecialchars($_SESSION['usuario_id']) : '' ?>">

    <!-- Header -->
    <header class="header-principal">
        <nav class="navbar">
            <div class="contenedor">
                <div class="navbar-contenido">

                    <!-- Logo -->
                    <div class="navbar-marca">
                        <a href="/artesanoDigital/" class="logo">
                            <img src="/artesanoDigital/public/placeholder-logo.png" alt="Artesano Digital" class="logo-img">
                            <span class="logo-texto">Artesano Digital</span>
                        </a>
                    </div>

                    <!-- Navegación principal -->
                    <div class="navbar-nav">
                        <a href="/artesanoDigital/" class="nav-link">Inicio</a>
                        <a href="/artesanoDigital/productos" class="nav-link">Productos</a>
                        <a href="/artesanoDigital/artesanos" class="nav-link">Artesanos</a>
                        <a href="/artesanoDigital/nosotros" class="nav-link">Nosotros</a>
                    </div>

                    <!-- Acciones del usuario -->
                    <div class="navbar-acciones">
                        <?php 
                        $carrito_count = isset($_SESSION['carrito_total']) ? (int)$_SESSION['carrito_total'] : 0;
                        ?>
                        <!-- Carrito anclado a la esquina derecha -->
                        <button class="carrito-btn" id="btnCarrito">
                            <span class="material-icons">shopping_cart</span>
                            <span id="carrito-contador"><?= $carrito_count ?></span>
                        </button>

                        <!-- Mini carrito desplegable -->
                        <div id="mini-carrito" class="mini-carrito">
                            <div class="mini-carrito-header">
                                <h3>Mi Carrito</h3>
                                <button id="cerrar-mini-carrito" class="cerrar-mini-carrito">
                                    <span class="material-icons">close</span>
                                </button>
                            </div>
                            <div id="mini-carrito-items" class="mini-carrito-items">
                                <!-- Aquí se cargarán los productos dinámicamente -->
                            </div>
                            <div class="mini-carrito-footer">
                                <div class="mini-carrito-total">
                                    <span>Total:</span>
                                    <span id="mini-carrito-precio-total">$0.00</span>
                                </div>
                                <div class="mini-carrito-acciones">
                                    <a href="/artesanoDigital/carrito" class="btn btn-outline btn-sm">Ver Carrito</a>
                                    <?php if (isset($_SESSION['usuario_id'])): ?>
                                        <a href="/artesanoDigital/checkout/cart_process.php?step=checkout" class="btn btn-primary btn-sm">Pagar</a>
                                    <?php else: ?>
                                        <a href="/artesanoDigital/login" class="btn btn-primary btn-sm">Pagar</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <div class="dropdown">
                                <button class="dropdown-toggle" data-dropdown="user-dropdown">
                                    <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
                                </button>
                                <div id="user-dropdown" class="dropdown-menu">
                                    <?php if ($_SESSION['usuario_tipo'] === 'artesano'): ?>
                                        <a href="/artesanoDigital/dashboard/artesano" class="dropdown-item">Mi Panel</a>
                                    <?php else: ?>
                                        <a href="/artesanoDigital/dashboard/cliente" class="dropdown-item">Mi Panel</a>
                                    <?php endif; ?>
                                    <a href="/artesanoDigital/perfil" class="dropdown-item">Mi Perfil</a>
                                    <a href="/artesanoDigital/logout" class="dropdown-item text-danger">Cerrar Sesión</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="auth-botones">
                                <a href="/artesanoDigital/login" class="btn btn-outline">Iniciar Sesión</a>
                                <a href="/artesanoDigital/registro" class="btn btn-primary">Registrarse</a>
                            </div>
                        <?php endif; ?>

                        <!-- Botón menú móvil (si aplica) -->
                        <button class="btn-menu-movil" id="btnMenuMovil">
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="main-contenido">
        <?= $contenido ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer-principal">
        <div class="contenedor">
            <!-- Aquí tu contenido de footer completo -->
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Sistema Artesano Digital. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JS principal -->
    <script src="/artesanoDigital/assets/js/carrito-usuario.js"></script>
    <script src="/artesanoDigital/assets/js/carrito.js"></script>
    <script src="/artesanoDigital/assets/js/main.js"></script>
    <script src="/artesanoDigital/assets/js/notificaciones.js"></script>
    <script src="/artesanoDigital/assets/js/pedidos-artesano.js"></script>
    
    <!-- Scripts adicionales -->
    <?php if (isset($scriptsAdicionales) && is_array($scriptsAdicionales)): ?>
        <?php foreach($scriptsAdicionales as $script): ?>
    <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Dropdown toggle y actualización de carrito -->
    <script>
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.dropdown-toggle');
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('activo'));
        if (btn) {
            const menu = document.getElementById(btn.getAttribute('data-dropdown'));
            menu.classList.toggle('activo');
        }
    });
    
    function actualizarContadorCarrito(nuevoTotal) {
        const cnt = document.getElementById('carrito-contador');
        if (cnt) cnt.textContent = nuevoTotal;
    }
    
    // Funciones para el mini carrito
    document.addEventListener('DOMContentLoaded', function() {
        // Limpiar carrito de sesiones anteriores si hay un usuario identificado
        const usuarioActualId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        if (usuarioActualId) {
            // Si hay un usuario identificado, eliminar el carrito de invitado
            localStorage.removeItem('carrito_invitado');
            // Y asegurarse de que usamos el carrito específico del usuario
            const carritoActual = JSON.parse(localStorage.getItem(`carrito_${usuarioActualId}`)) || [];
            localStorage.setItem('carrito', JSON.stringify(carritoActual));
        }
        
        // Sincronizar carrito de localStorage con el servidor
        sincronizarCarritoConServidor();
        
        // Inicializar carrito desde localStorage
        actualizarMiniCarrito();
        
        // Botón para abrir el mini carrito
        const btnCarrito = document.getElementById('btnCarrito');
        const miniCarrito = document.getElementById('mini-carrito');
        
        if (btnCarrito && miniCarrito) {
            btnCarrito.addEventListener('click', function(e) {
                e.preventDefault();
                miniCarrito.classList.toggle('activo');
            });
        }
        
        // Botón para cerrar el mini carrito
        const btnCerrarCarrito = document.getElementById('cerrar-mini-carrito');
        if (btnCerrarCarrito) {
            btnCerrarCarrito.addEventListener('click', function() {
                miniCarrito.classList.remove('activo');
            });
        }
        
        // Cerrar el mini carrito al hacer clic fuera de él
        document.addEventListener('click', function(e) {
            if (miniCarrito && 
                miniCarrito.classList.contains('activo') && 
                !miniCarrito.contains(e.target) && 
                e.target !== btnCarrito && 
                !btnCarrito.contains(e.target)) {
                miniCarrito.classList.remove('activo');
            }
        });
    });
    
    // Función para sincronizar el carrito de localStorage con el servidor
    function sincronizarCarritoConServidor() {
        // Obtener el identificador correcto para el carrito según el usuario
        const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';
        
        const carritoLocal = localStorage.getItem(carritoKey) || localStorage.getItem('carrito');
        if (!carritoLocal) return; // Si no hay carrito local, no hay nada que sincronizar
        
        fetch('/artesanoDigital/controllers/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'accion=sincronizar_carrito&carrito_local=' + encodeURIComponent(carritoLocal)
        })
        .then(res => res.json())
        .then(data => {
            if (data.exitoso) {
                console.log('Carrito sincronizado con el servidor');
                // Si el servidor devolvió un carrito actualizado, actualizamos el localStorage
                if (data.carrito) {
                    // Convertir formato de servidor a formato localStorage
                    const carritoLocalFormat = data.carrito.map(item => ({
                        id: item.id_producto,
                        nombre: item.nombre,
                        precio: parseFloat(item.precio),
                        imagen: item.imagen ? '/artesanoDigital/uploads/' + item.imagen : '/artesanoDigital/public/placeholder.jpg',
                        cantidad: parseInt(item.cantidad),
                        stock: parseInt(item.stock)
                    }));
                    localStorage.setItem('carrito', JSON.stringify(carritoLocalFormat));
                    actualizarMiniCarrito();
                }
            }
        })
        .catch(error => {
            console.error('Error al sincronizar carrito:', error);
        });
    }
    
    // Función para actualizar el mini carrito
    function actualizarMiniCarrito() {
        const miniCarritoItems = document.getElementById('mini-carrito-items');
        const miniCarritoPrecioTotal = document.getElementById('mini-carrito-precio-total');
        
        if (!miniCarritoItems || !miniCarritoPrecioTotal) return;
        
        // Obtener el identificador correcto para el carrito según el usuario
        const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';
        
        // Obtener carrito del localStorage usando la clave específica del usuario
        const carrito = JSON.parse(localStorage.getItem(carritoKey)) || [];
        
        // Actualizar contador
        actualizarContadorCarrito(carrito.reduce((total, item) => total + item.cantidad, 0));
        
        // Si el carrito está vacío
        if (carrito.length === 0) {
            miniCarritoItems.innerHTML = `
                <div class="mini-carrito-vacio">
                    <div class="mini-carrito-vacio-icono">
                        <span class="material-icons">shopping_cart</span>
                    </div>
                    <p class="mini-carrito-vacio-mensaje">Tu carrito está vacío</p>
                </div>
            `;
            miniCarritoPrecioTotal.textContent = '$0.00';
            return;
        }
        
        // Calcular total
        const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        miniCarritoPrecioTotal.textContent = `$${total.toFixed(2)}`;
        
        // Generar HTML de los items
        let html = '';
        carrito.forEach(item => {
            html += `
                <div class="mini-carrito-item" data-id="${item.id}">
                    <img src="${item.imagen}" alt="${item.nombre}" class="mini-carrito-item-img">
                    <div class="mini-carrito-item-info">
                        <h4 class="mini-carrito-item-nombre">${item.nombre}</h4>
                        <span class="mini-carrito-item-precio">$${item.precio.toFixed(2)}</span>
                        <div class="mini-carrito-item-cantidad">
                            Cantidad: ${item.cantidad}
                        </div>
                    </div>
                    <div class="mini-carrito-item-acciones">
                        <button class="mini-carrito-item-eliminar" onclick="eliminarDelCarrito(${item.id})">
                            <span class="material-icons">delete</span>
                        </button>
                    </div>
                </div>
            `;
        });
        
        miniCarritoItems.innerHTML = html;
    }
    
    // Función para eliminar un producto del carrito
    function eliminarDelCarrito(idProducto) {
        // Obtener el identificador correcto para el carrito según el usuario
        const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';
        
        let carrito = JSON.parse(localStorage.getItem(carritoKey)) || [];
        carrito = carrito.filter(item => item.id !== idProducto);
        localStorage.setItem(carritoKey, JSON.stringify(carrito));
        // Mantener compatibilidad
        localStorage.setItem('carrito', JSON.stringify(carrito));
        
        // Actualizar en el servidor también
        fetch('/artesanoDigital/controllers/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'accion=eliminar_producto&id_producto=' + idProducto
        })
        .then(res => res.json())
        .then(data => {
            console.log('Producto eliminado en el servidor', data);
        })
        .catch(error => {
            console.error('Error al eliminar producto del servidor:', error);
        });
        
        actualizarMiniCarrito();
        mostrarMensaje('Producto eliminado del carrito', 'info');
    }
    
    // Función para mostrar mensajes (similar a la de inicio.php)
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
    
    // Función para agregar un producto al carrito
    function agregarAlCarrito(idProducto, nombre, precio, imagen, cantidad = 1, stock = 0) {
        // Crear identificador único para este usuario
        const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
        const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';
        
        // Agregar al carrito local con key específico para este usuario
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
                mostrarMensaje('Producto agregado al carrito', 'success');
            } else {
                mostrarMensaje(data.mensaje || 'No se pudo agregar al carrito', 'error');
            }
        })
        .catch(error => {
            console.error('Error al agregar producto:', error);
            mostrarMensaje('Error al agregar el producto', 'error');
        });
    }
    </script>
</body>
</html>
