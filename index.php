<?php
/**
 * Router principal del Sistema Artesano Digital
 */

// Cargar autoloader de Composer si existe, sino usar autoloader personalizado
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Autoloader personalizado para desarrollo
    spl_autoload_register(function ($clase) {
        // Manejar clases con namespace
        $archivo = __DIR__ . '/' . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $clase) . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
        
        // Manejar clases sin namespace (principalmente en models)
        $nombreClase = basename(str_replace('\\', '/', $clase));
        $archivoModelo = __DIR__ . '/models/' . $nombreClase . '.php';
        if (file_exists($archivoModelo)) {
            require_once $archivoModelo;
            return;
        }
    });
}

// Cargar variables de entorno
if (file_exists(__DIR__ . '/.env')) {
    $lineas = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (strpos($linea, '=') !== false && strpos($linea, '#') !== 0) {
            list($clave, $valor) = explode('=', $linea, 2);
            $_ENV[trim($clave)] = trim($valor, '"');
        }
    }
}

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Remover el prefijo del directorio si existe
$basePath = '/artesanoDigital';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Debug - Guardar la URI procesada
file_put_contents(__DIR__ . '/debug_uri.log', date('Y-m-d H:i:s') . ' - URI Original: ' . $_SERVER['REQUEST_URI'] . ', URI Procesada: ' . $uri . "\n", FILE_APPEND);

// Configurar manejo de errores según el tipo de ruta
$esRutaAPI = ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($uri, ['/login', '/registro', '/auth/login', '/auth/registro']));

if ($esRutaAPI) {
    // Para rutas API, ocultar errores para evitar que rompan el JSON
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    
    // Configurar manejador de errores fatal para APIs
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Limpiar cualquier salida previa
            if (ob_get_level()) {
                ob_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
            exit;
        }
    });
} else {
    // Para rutas normales, mostrar errores en desarrollo
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

if (empty($uri)) {
    $uri = '/';
}

// Manejar rutas dinámicas antes del switch
// Ruta para ver detalles de pedido del cliente
if (preg_match('/^\/cliente\/pedido\/(\d+)$/', $uri, $matches)) {
    require_once 'controllers/ControladorCliente.php';
    $controlador = new Controllers\ControladorCliente();
    $idPedido = $matches[1];
    $controlador->verDetallePedido($idPedido);
    exit;
}

// Ruta para cancelar pedido del cliente
if (preg_match('/^\/cliente\/pedido\/cancelar\/(\d+)$/', $uri, $matches)) {
    require_once 'controllers/ControladorCliente.php';
    $controlador = new Controllers\ControladorCliente();
    $idPedido = $matches[1];
    $controlador->cancelarPedido($idPedido);
    exit;
}

// Router completo
switch ($uri) {
    case '/':
        // Página de inicio
        $titulo = 'Artesano Digital - Panamá Oeste';
        $descripcion = 'Plataforma de comercio electrónico para artesanos de Panamá Oeste';
        include 'views/inicio.php';
        break;
        
    case '/productos':
        // Catálogo de productos
        require_once 'controllers/ControladorProductos.php';
        $controlador = new ControladorProductos();
        $controlador->mostrarCatalogo();
        break;
        
    case '/productos/detalle':
        // Detalle de producto
        require_once 'controllers/ControladorProductos.php';
        $controlador = new ControladorProductos();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controlador->mostrarDetalle($id);
        } else {
            header('Location: /artesanoDigital/productos');
            exit;
        }
        break;
        
    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar login
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->procesarLogin();
        } else {
            // Mostrar formulario de login usando el controlador para generar el token CSRF
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->mostrarLogin();
        }
        break;
        
    case '/registro':
        require_once 'controllers/ControladorAuth.php';
        $controlador = new Controllers\ControladorAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar registro
            $controlador->procesarRegistro();
        } else {
            // Mostrar formulario de registro
            $controlador->mostrarRegistro();
        }
        break;
        
    case '/auth/login':
        // Alias para compatibilidad
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->procesarLogin();
        } else {
            header('Location: /artesanoDigital/login');
            exit;
        }
        break;
        
    case '/auth/registro':
        // Alias para compatibilidad
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'controllers/ControladorAuth.php';
            $controlador = new Controllers\ControladorAuth();
            $controlador->procesarRegistro();
        } else {
            header('Location: /artesanoDigital/registro');
            exit;
        }
        break;
        
    case '/logout':
        // Cerrar sesión
        require_once 'controllers/ControladorAuth.php';
        $controlador = new Controllers\ControladorAuth();
        $controlador->logout();
        break;
        
    case '/artesanos':
        include 'views/artesanos/listado.php';
        break;
        
    case '/nosotros':
        include 'views/paginas/nosotros.php';
        break;
        
    case '/como-funciona':
        include 'views/paginas/como-funciona.php';
        break;
        
    case '/contacto':
        include 'views/paginas/contacto.php';
        break;
        
    case '/ayuda':
        include 'views/paginas/ayuda.php';
        break;
        
    case '/terminos':
        include 'views/paginas/terminos.php';
        break;
        
    case '/privacidad':
        include 'views/paginas/privacidad.php';
        break;
        
    case '/devoluciones':
        include 'views/paginas/devoluciones.php';
        break;
        
    // Rutas del carrito
    case '/carrito':
        require_once 'controllers/ControladorCarrito.php';
        $controlador = new Controllers\ControladorCarrito();
        $controlador->mostrarCarrito();
        break;

    case '/carrito/agregar':
        require_once 'controllers/ControladorCarrito.php';
        $controlador = new Controllers\ControladorCarrito();
        $controlador->agregarProducto();
        break;

    case '/carrito/actualizar':
        require_once 'controllers/ControladorCarrito.php';
        $controlador = new Controllers\ControladorCarrito();
        $controlador->actualizarCantidad();
        break;

    case '/carrito/eliminar':
        require_once 'controllers/ControladorCarrito.php';
        $controlador = new Controllers\ControladorCarrito();
        $controlador->eliminarProducto();
        break;

    // Rutas de checkout multi-step
    case '/checkout/direccion':
        require_once 'controllers/ControladorCheckout.php';
        $controlador = new Controllers\ControladorCheckout();
        $controlador->direccion();
        break;

    case '/checkout/pago':
        require_once 'controllers/ControladorCheckout.php';
        $controlador = new Controllers\ControladorCheckout();
        $controlador->pago();
        break;

    case '/checkout/factura':
        require_once 'controllers/ControladorCheckout.php';
        $controlador = new Controllers\ControladorCheckout();
        $controlador->factura();
        break;

    case '/checkout/completado':
        require_once 'controllers/ControladorCheckout.php';
        $controlador = new Controllers\ControladorCheckout();
        $controlador->completado();
        break;
    // Vista única de proceso de checkout (todo en uno)
    case '/checkout/cart_process.php':
        require_once 'controllers/ControladorCheckout.php';
        $controlador = new Controllers\ControladorCheckout();
        $controlador->proceso();
        break;
        
    case '/api/carrito-info':
        require_once 'controllers/ControladorAPI.php';
        $controlador = new Controllers\ControladorAPI();
        $controlador->obtenerInfoCarrito();
        break;
        
    case '/api/notificaciones':
        require_once 'controllers/ControladorNotificaciones.php';
        $controlador = new Controllers\ControladorNotificaciones();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controlador->obtenerNotificaciones();
        }
        break;
        
    case '/api/notificaciones/marcar-leida':
        require_once 'controllers/ControladorNotificaciones.php';
        $controlador = new Controllers\ControladorNotificaciones();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controlador->marcarComoLeida();
        }
        break;
        
    case '/api/notificaciones/marcar-todas-leidas':
        require_once 'controllers/ControladorNotificaciones.php';
        $controlador = new Controllers\ControladorNotificaciones();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controlador->marcarTodasComoLeidas();
        }
        break;
        
    case '/api/notificaciones/contador':
        require_once 'controllers/ControladorNotificaciones.php';
        $controlador = new Controllers\ControladorNotificaciones();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controlador->obtenerContadorNoLeidas();
        }
        break;
        
    case '/api/pedidos/{id}/detalles':
        require_once 'controllers/ControladorAPI.php';
        $controlador = new Controllers\ControladorAPI();
        // Extraer el ID del pedido de la URL
        preg_match('/\/pedidos\/(\d+)\/detalles/', $ruta, $matches);
        $idPedido = $matches[1] ?? 0;
        $controlador->obtenerDetallesPedido($idPedido);
        break;
        
    case '/api/pedidos/cliente/{id}':
        require_once 'controllers/ControladorAPI.php';
        $controlador = new Controllers\ControladorAPI();
        // Extraer el ID del pedido de la URL
        preg_match('/\/cliente\/(\d+)/', $ruta, $matches);
        $idPedido = $matches[1] ?? 0;
        $controlador->obtenerDetallesPedidoPersonal($idPedido);
        break;
        
    case '/api/pedidos/{id}/estado':
        require_once 'controllers/ControladorAPI.php';
        $controlador = new Controllers\ControladorAPI();
        // Extraer el ID del pedido de la URL
        preg_match('/\/pedidos\/(\d+)\/estado/', $ruta, $matches);
        $idPedido = $matches[1] ?? 0;
        $controlador->actualizarEstadoPedido($idPedido);
        break;
        
    case '/dashboard/artesano':
        // Verificar autenticación
        require_once 'controllers/ControladorArtesano.php';
        $controlador = new Controllers\ControladorArtesano();
        $controlador->mostrarDashboard();
        break;
        
    case '/dashboard/cliente':
    case '/cliente/dashboard':
        // Verificar autenticación
        require_once 'controllers/ControladorCliente.php';
        $controlador = new Controllers\ControladorCliente();
        $controlador->mostrarDashboard();
        break;
    
    case '/cliente/pedidos':
        // Mostrar todos los pedidos del cliente
        require_once 'controllers/ControladorCliente.php';
        $controlador = new Controllers\ControladorCliente();
        $controlador->mostrarTodosPedidos();
        break;
    
    case '/perfil':
        // Perfil de usuario
        require_once 'controllers/ControladorPerfil.php';
        $controlador = new Controllers\ControladorPerfil();
        $controlador->mostrarPerfil();
        break;
    
    case '/artesano/tienda':
        // Gestión de tienda para artesanos
        require_once 'controllers/ControladorArtesano.php';
        $controlador = new Controllers\ControladorArtesano();
        $controlador->mostrarTienda();
        break;
        
    case '/artesano/productos/crear':
        // Crear nuevo producto como artesano
        require_once 'controllers/ControladorProductosArtesano.php';
        $controlador = new ControladorProductosArtesano();
        $controlador->procesarSolicitud();
        break;
        
    case '/artesano/productos/actualizar':
        // Actualizar producto existente como artesano
        require_once 'controllers/ControladorProductosArtesano.php';
        $controlador = new ControladorProductosArtesano();
        $controlador->procesarSolicitud();
        break;
        
    case '/artesano/productos/eliminar':
        // Eliminar producto como artesano
        require_once 'controllers/ControladorProductosArtesano.php';
        $controlador = new ControladorProductosArtesano();
        $controlador->procesarSolicitud();
        break;
        
    case '/artesano/detalle-productos':
        // Mostrar página de detalle y edición de producto para artesanos
        include 'views/artesano/detalle-productos.php';
        break;
        
    case '/artesano/mis_productos':
        // Mostrar página de listado de productos del artesano
        include 'views/artesano/mis_productos.php';
        break;
        
    case '/artesano/tienda/procesar':
        // Procesar creación/actualización de tienda
        require_once 'controllers/ControladorArtesano.php';
        $controlador = new Controllers\ControladorArtesano();
        $controlador->procesarTienda();
        break;
        
    case '/api/debug':
        // Endpoint de depuración para verificar JSON
        require_once 'controllers/ControladorAuth.php';
        $controlador = new Controllers\ControladorAuth();
        $controlador->debugAPI();
        break;
        
    default:
        // 404 - Página no encontrada
        http_response_code(404);
        include 'views/errors/404.php';
        break;
}
?>
