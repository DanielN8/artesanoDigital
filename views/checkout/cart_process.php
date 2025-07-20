
<?php
// --- Sincronización del carrito desde localStorage a la sesión PHP (AJAX) ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['sincronizar_carrito']) &&
    isset($_POST['carrito_json']) &&
    empty($_SESSION['carrito'])
) {
    $carritoLocal = json_decode($_POST['carrito_json'], true);
    $productos = [];
    if (is_array($carritoLocal)) {
        foreach ($carritoLocal as $item) {
            // Normalizar campos del localStorage a formato backend
            $productos[] = [
                'id_producto' => $item['id'] ?? $item['id_producto'] ?? null,
                'nombre' => $item['nombre'] ?? $item['name'] ?? '',
                'precio' => $item['precio'] ?? $item['price'] ?? 0,
                'cantidad' => $item['cantidad'] ?? $item['quantity'] ?? 1,
                'id_tienda' => $item['id_tienda'] ?? null,
                'id_usuario_tienda' => $item['id_usuario_tienda'] ?? null
            ];
        }
        $_SESSION['carrito'] = $productos;
        echo json_encode(['ok' => true, 'msg' => 'Carrito sincronizado en sesión']);
        exit;
    }
    echo json_encode(['ok' => false, 'msg' => 'Formato de carrito inválido']);
    exit;
}
// Vista: Proceso de checkout en una sola página
// Esta vista contiene todos los pasos del proceso de compra

// --- SINCRONIZACIÓN DEL CARRITO: Recibir carrito por AJAX y guardar en $_SESSION['carrito'] ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['accion']) && $_POST['accion'] === 'sincronizar_carrito' &&
    isset($_POST['carrito'])
) {
    $data = json_decode($_POST['carrito'], true);
    if (is_array($data)) {
        // Normalizar formato: asegurarse de que cada producto tiene id_producto, nombre, cantidad, precio
        $productos = array_map(function($prod) {
            return [
                'id_producto' => $prod['id_producto'] ?? $prod['id'] ?? null,
                'nombre' => $prod['nombre'] ?? $prod['name'] ?? '',
                'cantidad' => intval($prod['cantidad'] ?? $prod['quantity'] ?? 1),
                'precio' => floatval($prod['precio'] ?? $prod['price'] ?? 0),
                // Puedes agregar más campos si tu app los usa
                'id_tienda' => $prod['id_tienda'] ?? null,
                'id_usuario_tienda' => $prod['id_usuario_tienda'] ?? null,
                'imagen' => $prod['imagen'] ?? null
            ];
        }, $data);
        
        // Filtrar productos válidos (que tengan id_producto)
        $productos = array_filter($productos, function($prod) {
            return !empty($prod['id_producto']);
        });
        
        $_SESSION['carrito'] = $productos;
        error_log("Carrito sincronizado desde localStorage: " . count($productos) . " productos válidos");
        
        echo json_encode(['ok' => true, 'msg' => 'Carrito sincronizado en sesión', 'count' => count($productos)]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Formato de carrito inválido']);
    }
    exit;
}

// Iniciar buffer de salida
ob_start();

/**
 * Función para vaciar completamente el carrito de compras
 * Limpia tanto en base de datos como en sesión
 * @param int $idUsuario ID del usuario cuyo carrito se limpiará
 * @param \PDO $conexion Conexión a la base de datos (opcional)
 * @return void
 */
function vaciarCarritoCompleto($idUsuario, $conexion = null) {
    // Si no se proporcionó una conexión y necesitamos limpiar la BD
    $dbConnectLocal = false;
    if ($idUsuario && !$conexion) {
        require_once __DIR__ . '/../../config/Database.php';
        $db = \Config\Database::obtenerInstancia();
        $conexion = $db->obtenerConexion();
        $dbConnectLocal = true;
    }
    
    // Limpiar en base de datos
    if ($idUsuario && $conexion) {
        try {
            // Iniciar transacción si no estamos dentro de una
            if ($dbConnectLocal) {
                $conexion->beginTransaction();
            }
            
            // Eliminar productos del carrito
            $sqlVaciar = "DELETE FROM carrito_productos 
                          WHERE id_carrito = (SELECT id_carrito FROM carritos WHERE id_usuario = :id_usuario)";
            $stmtVaciar = $conexion->prepare($sqlVaciar);
            $stmtVaciar->execute(['id_usuario' => $idUsuario]);
            
            // Actualizar contador y total en tabla carritos
            $sqlActualizar = "UPDATE carritos SET cantidad_productos = 0, total = 0 
                              WHERE id_usuario = :id_usuario";
            $stmtActualizar = $conexion->prepare($sqlActualizar);
            $stmtActualizar->execute(['id_usuario' => $idUsuario]);
            
            if ($dbConnectLocal) {
                $conexion->commit();
            }
            
            error_log("Carrito limpiado en base de datos para usuario $idUsuario");
        } catch (\Exception $e) {
            if ($dbConnectLocal && $conexion) {
                $conexion->rollBack();
            }
            error_log("Error al limpiar carrito en BD: " . $e->getMessage());
        }
    }
    
    // Limpiar en sesión
    $_SESSION['carrito'] = [];
    unset($_SESSION['carrito']);
    
    error_log("Carrito limpiado en sesión");
}

// --- Cargar productos del carrito ---
// Obtener usuario autenticado
$usuario = isset($usuario) ? $usuario : $_SESSION['usuario'] ?? null;

// Debug: Estado inicial
error_log("=== INICIO CHECKOUT DEBUG ===");
error_log("Usuario: " . (isset($usuario['id_usuario']) ? $usuario['id_usuario'] : 'No autenticado'));
error_log("Sesión carrito: " . (isset($_SESSION['carrito']) ? count($_SESSION['carrito']) . ' productos' : 'vacío'));
error_log("POST data: " . (empty($_POST) ? 'vacío' : 'presente - ' . implode(', ', array_keys($_POST))));
error_log("FILES data: " . (empty($_FILES) ? 'vacío' : 'presente'));

// Inicializar array de productos
$productos = [];

// 1. Intentar cargar productos de la sesión PHP
if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    $productos = $_SESSION['carrito'];
    error_log("Carrito cargado desde sesión PHP: " . count($productos) . " productos");
    // Debug: mostrar los productos
    foreach ($productos as $i => $prod) {
        error_log("Producto $i: ID=" . ($prod['id_producto'] ?? 'sin ID') . 
                 ", Nombre=" . ($prod['nombre'] ?? 'sin nombre') . 
                 ", Precio=" . ($prod['precio'] ?? 'sin precio') . 
                 ", Cantidad=" . ($prod['cantidad'] ?? 'sin cantidad'));
    }
}

// 2. Si tenemos un usuario autenticado y no hay productos en sesión, intentar cargar de la base de datos
if (empty($productos) && $usuario && isset($usuario['id_usuario'])) {
    try {
        require_once __DIR__ . '/../../models/Carrito.php';
        $carritoModel = new \Models\Carrito();
        $productosDB = $carritoModel->obtenerProductos($usuario['id_usuario']);
        if (!empty($productosDB)) {
            // Asegurarse de que cada producto tiene id_producto
            $productos = array_filter($productosDB, function($prod) {
                return isset($prod['id_producto']);
            });
            // Actualizar la sesión con los productos encontrados
            $_SESSION['carrito'] = $productos;
            error_log("Carrito cargado desde BD: " . count($productos) . " productos");
        }
    } catch (\Exception $e) {
        error_log("Error al cargar carrito de BD: " . $e->getMessage());
    }
}

    // Calcular total del carrito para tenerlo disponible en toda la página
$total_carrito = 0;
if (!empty($productos)) {
    // Cargar detalles completos de cada producto desde la base de datos
    require_once __DIR__ . '/../../models/Producto.php';
    $modeloProducto = new \Producto();
    
    foreach ($productos as $key => $producto) {
        // Verificar si tenemos toda la información necesaria del producto
        if (!isset($producto['id_tienda']) || !isset($producto['id_usuario_tienda']) || empty($producto['nombre'])) {
            // Cargar datos completos del producto desde la base de datos
            $productoCompleto = $modeloProducto->obtenerPorId($producto['id_producto']);
            
            if ($productoCompleto) {
                // Mantener cantidad y otros datos del carrito, pero completar con datos de la BD
                $productos[$key] = array_merge($productoCompleto, [
                    'cantidad' => $producto['cantidad'],
                    'id_producto' => $producto['id_producto'] // Asegurar que se mantiene este ID
                ]);
                
                error_log("Producto {$producto['id_producto']} completado desde BD");
            } else {
                error_log("ADVERTENCIA: No se pudo cargar el producto {$producto['id_producto']} desde la BD");
                // Remover productos que no se pueden cargar para evitar errores
                unset($productos[$key]);
                continue; // Saltar este producto si no se puede cargar
            }
        }
        
        $precio = floatval($productos[$key]['precio']);
        $cantidad = intval($productos[$key]['cantidad']);
        
        // Verificar valores válidos
        if (is_nan($precio) || $precio <= 0) {
            error_log("Precio inválido para producto '{$productos[$key]['nombre']}': {$productos[$key]['precio']}");
            $precio = 0;
        }
        
        if (is_nan($cantidad) || $cantidad <= 0) {
            error_log("Cantidad inválida para producto '{$productos[$key]['nombre']}': {$productos[$key]['cantidad']}");
            $cantidad = 1; // Usar 1 como valor por defecto en lugar de 0
            $productos[$key]['cantidad'] = $cantidad; // Actualizar en el array
        }
        
        $subtotal = $precio * $cantidad;
        $total_carrito += $subtotal;
        
        // Actualizar el subtotal en el array de productos
        $productos[$key]['subtotal'] = $subtotal;
        $productos[$key]['precio'] = $precio; // Asegurar que el precio esté bien formateado
        $productos[$key]['cantidad'] = $cantidad; // Asegurar que la cantidad esté bien formateada
    }
    
    // Re-indexar el array de productos para eliminar gaps causados por unset()
    $productos = array_values($productos);
    
    // Actualizar sesión con los productos completados
    $_SESSION['carrito'] = $productos;
    
    error_log("Total calculado al cargar la página: " . $total_carrito);
    
    // Si tenemos productos pero el total es 0 o inválido, hay un problema grave
    if ($total_carrito <= 0 && !empty($productos)) {
        error_log("ADVERTENCIA: El carrito tiene " . count($productos) . " productos pero el total calculado es $total_carrito");
    }
}// --- SINCRONIZACIÓN DEL CARRITO: Si el carrito de sesión está vacío, pero hay carrito en localStorage, sincronizar ---
?>
<script>
// --- Sincronización automática del carrito localStorage -> sesión PHP ---
document.addEventListener('DOMContentLoaded', function() {
  // Si el carrito PHP está vacío pero hay carrito en localStorage, sincronizar
  var phpCartVacio = <?php echo empty($productos) ? 'true' : 'false'; ?>;
  var phpCartCount = <?php echo count($productos); ?>;
  
  console.log('Estado del carrito PHP:', phpCartVacio ? 'vacío' : 'con ' + phpCartCount + ' productos');
  
  // Intentar cargar desde diferentes claves de localStorage
  var localCart = localStorage.getItem('carrito') || localStorage.getItem('artesanoDigital_cart') || localStorage.getItem('carrito_items');
  
  if (phpCartVacio && localCart) {
    try {
      var carrito = JSON.parse(localCart);
      console.log('Carrito encontrado en localStorage:', carrito);
      
      // Si el formato es { productos: [...] }, usar solo el array
      if (carrito && carrito.productos) {
        carrito = carrito.productos;
      }
      
      // Verificar que hay productos válidos
      if (Array.isArray(carrito) && carrito.length > 0) {
        console.log('Sincronizando carrito con', carrito.length, 'productos');
        
        // Enviar por AJAX a este mismo archivo
        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            try {
              var resp = JSON.parse(xhr.responseText);
              console.log('Respuesta de sincronización:', resp);
              if (resp.ok) {
                // Recargar para mostrar el carrito sincronizado
                window.location.reload();
              }
            } catch (e) {
              console.error('Error parsing response:', e);
            }
          }
        };
        xhr.send('accion=sincronizar_carrito&carrito=' + encodeURIComponent(JSON.stringify(carrito)));
        return; // Detener el resto del script hasta recargar
      }
    } catch (e) {
      console.error('Error parsing localStorage cart:', e);
    }
  }
  
  // Si llegamos aquí, mostrar el estado actual del carrito
  console.log('Carrito final cargado con', phpCartCount, 'productos');
});
</script>
<?php

// --- Patrón Strategy para métodos de pago ---
interface MetodoPagoStrategy {
    public function procesarPago(float $monto, array $datos): array;
}

class PagoTarjeta implements MetodoPagoStrategy {
    public function procesarPago(float $monto, array $datos): array {
        // Validación simple
        if (empty($datos['numero_tarjeta']) || empty($datos['nombre_titular']) || empty($datos['cvv']) || empty($datos['expiracion'])) {
            return ['exitoso' => false, 'mensaje' => 'Datos de tarjeta incompletos'];
        }
        // Simulación de éxito
        return [
            'exitoso' => true,
            'mensaje' => 'Pago con tarjeta procesado',
            'transaccion_id' => 'TXN_' . uniqid() . '_CARD'
        ];
    }
}

class PagoYappy implements MetodoPagoStrategy {
    public function procesarPago(float $monto, array $datos): array {
        if (empty($datos['telefono'])) {
            return ['exitoso' => false, 'mensaje' => 'Número de teléfono requerido para Yappy'];
        }
        // Simulación de éxito
        return [
            'exitoso' => true,
            'mensaje' => 'Pago con Yappy procesado',
            'transaccion_id' => 'TXN_' . uniqid() . '_YAPPY'
        ];
    }
}

class ProcesadorPago {
    private MetodoPagoStrategy $metodo;
    public function __construct(MetodoPagoStrategy $metodo) {
        $this->metodo = $metodo;
    }
    public function procesar(float $monto, array $datos): array {
        return $this->metodo->procesarPago($monto, $datos);
    }
}

// --- Procesamiento del pago y guardado en la base de datos ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_compra'])) {
    // Obtener datos necesarios
    $usuario = isset($usuario) ? $usuario : $_SESSION['usuario'] ?? null;
    if (!$usuario || !isset($usuario['id_usuario'])) {
        echo '<div class="alert alert-danger">Debes iniciar sesión para completar la compra</div>';
        exit;
    }
    
    // Los productos ya se cargaron al inicio del script, pero verificamos si hay cambios
    if (empty($productos)) {
        echo '<div class="alert alert-danger">No hay productos en el carrito</div>';
        error_log("No se encontraron productos en el carrito al procesar el pago");
    }
    
    // Usar el total ya calculado en la carga inicial
    $monto = $total_carrito;
    
    // Registramos el detalle del carrito para depuración
    error_log("Procesando pago con " . count($productos) . " productos");
    foreach ($productos as $key => $producto) {
        $precio = floatval($producto['precio']);
        $cantidad = intval($producto['cantidad']);
        $subtotal = $precio * $cantidad;
        
        // Registrar para depuración
        error_log("Producto {$key}: {$producto['nombre']}, Precio: {$precio}, Cantidad: {$cantidad}, Subtotal: {$subtotal}");
    }
    
    // Asegurarnos de que el monto es un número
    error_log("Total calculado para el pago: {$monto}");
    
    $metodo = $_POST['metodo_pago'] ?? '';
    $datosPago = [];
    
    if ($metodo === 'tarjeta') {
        $datosPago = [
            'nombre_titular' => $_POST['nombre_tarjeta'] ?? '',
            'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
            'expiracion' => $_POST['expiracion'] ?? '',
            'cvv' => $_POST['cvv'] ?? ''
        ];
        $procesador = new ProcesadorPago(new PagoTarjeta());
    } elseif ($metodo === 'yappy') {
        $datosPago = [
            'telefono' => $_POST['telefono_yappy'] ?? ''
        ];
        $procesador = new ProcesadorPago(new PagoYappy());
    } else {
        echo '<div class="alert alert-danger">Método de pago no válido</div>';
        exit;
    }
    
    // Procesar el pago
    $resultado = $procesador->procesar($monto, $datosPago);
    
    if (!$resultado['exitoso']) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($resultado['mensaje']) . '</div>';
    } else {
        // Preparar datos para guardar en la base de datos
        $direccionEnvio = json_encode([
            'nombre' => $_POST['nombre'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'ciudad' => $_POST['ciudad'] ?? '',
            'telefono' => $_POST['telefono'] ?? ''
        ]);
        
        // Conexión a la base de datos
        try {
            require_once __DIR__ . '/../../config/Database.php';
            $db = \Config\Database::obtenerInstancia();
            $conexion = $db->obtenerConexion();
            
            // Iniciar transacción
            $conexion->beginTransaction();
            
            // 1. Guardar pedido en la tabla pedidos
            // Asegurarse de que el monto sea un número decimal correcto para la base de datos
            $montoNumerico = floatval($monto);
            
            // Registrar el valor para depuración
            error_log("Guardando pedido con total: " . $montoNumerico . " (tipo: " . gettype($montoNumerico) . ")");
            
            // Asegurar que el total sea un número válido y positivo
            if (is_nan($montoNumerico) || $montoNumerico <= 0) {
                error_log("Error: Monto inválido ($montoNumerico), recalculando...");
                // Recalcular como respaldo
                $montoNumerico = 0;
                foreach ($productos as $p) {
                    $precio = floatval($p['precio']);
                    $cantidad = intval($p['cantidad']);
                    if ($precio > 0 && $cantidad > 0) {
                        $montoNumerico += $precio * $cantidad;
                    }
                }
                error_log("Monto recalculado: $montoNumerico");
                
                // Si aún es inválido, generar error
                if (is_nan($montoNumerico) || $montoNumerico <= 0) {
                    throw new \Exception("No se pudo calcular un total válido para el pedido.");
                }
            }
            
            $sqlPedido = "INSERT INTO pedidos (id_usuario, estado, metodo_pago, total, fecha_pedido, direccion_envio) 
                          VALUES (:id_usuario, 'pendiente', :metodo_pago, :total, NOW(), :direccion_envio)";
            
            $stmtPedido = $conexion->prepare($sqlPedido);
            
            // Usar bindValue con PDO::PARAM_STR para asegurar que se pase como string numérico
            $stmtPedido->bindValue(':id_usuario', $usuario['id_usuario'], PDO::PARAM_INT);
            $stmtPedido->bindValue(':metodo_pago', $metodo, PDO::PARAM_STR);
            $stmtPedido->bindValue(':total', $montoNumerico, PDO::PARAM_STR); // Usar el valor numérico directamente
            $stmtPedido->bindValue(':direccion_envio', $direccionEnvio, PDO::PARAM_STR);
            
            // Log para depuración
            error_log("SQL pedido preparado con total: {$montoNumerico}");
            
            $stmtPedido->execute();
            
            $idPedido = $conexion->lastInsertId();
            
            // 2. Guardar productos del pedido
            $productos = isset($productos) ? $productos : $_SESSION['carrito'] ?? [];
            
            foreach ($productos as $producto) {
                // Verificar que el producto tenga un ID válido
                if (!isset($producto['id_producto']) || empty($producto['id_producto'])) {
                    // Buscar ID en diferentes formatos posibles
                    $idProducto = $producto['id_producto'] ?? $producto['id'] ?? null;
                    
                    if (!$idProducto) {
                        error_log("Error: Producto sin ID válido, omitiendo: " . json_encode($producto));
                        continue; // Saltar este producto
                    } else {
                        // Usar el ID encontrado
                        $producto['id_producto'] = $idProducto;
                    }
                }
                
                // Verificar y corregir valores numéricos
                $cantidad = intval($producto['cantidad']);
                $precioUnitario = floatval($producto['precio']);
                
                if ($cantidad <= 0 || $precioUnitario <= 0) {
                    error_log("Error: Valores inválidos para producto ID {$producto['id_producto']}, Cantidad: {$cantidad}, Precio: {$precioUnitario}");
                    continue;
                }
                
                $sqlProducto = "INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio_unitario) 
                                VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";
                
                $stmtProducto = $conexion->prepare($sqlProducto);
                $stmtProducto->execute([
                    'id_pedido' => $idPedido,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario
                ]);
                
                // Actualizar stock del producto (opcional)
                $sqlStock = "UPDATE productos SET stock = stock - :cantidad 
                             WHERE id_producto = :id_producto";
                             
                $stmtStock = $conexion->prepare($sqlStock);
                $stmtStock->execute([
                    'cantidad' => $producto['cantidad'],
                    'id_producto' => $producto['id_producto']
                ]);
            }
            
            // 3. Crear notificación para el artesano
            $sqlNotificacion = "INSERT INTO notificaciones (id_usuario, tipo, mensaje) 
                                VALUES (:id_usuario, 'nuevo_pedido', :mensaje)";
            
            // Para cada producto, notificar al dueño de la tienda
            $productosAgrupados = [];
            foreach ($productos as $producto) {
                // Verificar que el producto tenga la información de tienda necesaria
                $idTienda = $producto['id_tienda'] ?? null;
                $idUsuarioTienda = $producto['id_usuario_tienda'] ?? null;
                
                // Si no tenemos la información, cargarla de la base de datos
                if (!$idTienda || !$idUsuarioTienda) {
                    require_once __DIR__ . '/../../models/Producto.php';
                    $modeloProducto = new \Producto();
                    $productoCompleto = $modeloProducto->obtenerPorId($producto['id_producto']);
                    
                    if ($productoCompleto) {
                        $idTienda = $productoCompleto['id_tienda'];
                        $idUsuarioTienda = $productoCompleto['id_usuario_tienda'];
                        error_log("Información de tienda cargada para producto {$producto['id_producto']}: tienda=$idTienda, artesano=$idUsuarioTienda");
                    }
                }
                
                // Solo procesar si tenemos información válida de tienda
                if ($idTienda && $idUsuarioTienda) {
                    if (!isset($productosAgrupados[$idTienda])) {
                        $productosAgrupados[$idTienda] = [
                            'id_usuario' => $idUsuarioTienda, 
                            'total' => 0
                        ];
                    }
                    $productosAgrupados[$idTienda]['total'] += $producto['precio'] * $producto['cantidad'];
                } else {
                    error_log("ADVERTENCIA: No se pudo obtener información de tienda para producto {$producto['id_producto']}");
                }
            }
            
            foreach ($productosAgrupados as $idTienda => $datos) {
                // Verificar que tenemos un ID de usuario válido antes de insertar
                if ($datos['id_usuario']) {
                    $mensaje = "Tienes un nuevo pedido #" . $idPedido . " por B/. " . 
                               number_format($datos['total'], 2);
                    
                    $stmtNotificacion = $conexion->prepare($sqlNotificacion);
                    $stmtNotificacion->execute([
                        'id_usuario' => $datos['id_usuario'],
                        'mensaje' => $mensaje
                    ]);
                } else {
                    error_log("ADVERTENCIA: No se pudo enviar notificación para tienda $idTienda - ID de usuario faltante");
                }
            }
            
            // 4. Vaciar carrito del usuario usando la función centralizada
            // Hacemos esto antes de confirmar la transacción para que sea parte de la misma
            // Si algo falla, el rollback restaurará el carrito en la base de datos
            vaciarCarritoCompleto($usuario['id_usuario'], $conexion);
            
            // Confirmar transacción
            $conexion->commit();
            
            // Guardar datos del pedido en la sesión para mostrarlos en la pantalla de finalización
            // Asegurarse de incluir todos los detalles de los productos con sus IDs
            $_SESSION['pedido_completado'] = [
                'id_pedido' => $idPedido,
                'referencia' => 'AD-' . str_pad($idPedido, 5, '0', STR_PAD_LEFT),
                'fecha' => date('Y-m-d H:i:s'),
                'total' => $monto,
                'productos' => array_map(function($producto) {
                    return [
                        'id_producto' => $producto['id_producto'],
                        'nombre' => $producto['nombre'],
                        'cantidad' => $producto['cantidad'],
                        'precio' => $producto['precio']
                    ];
                }, $productos),
                'metodo_pago' => $metodo,
                'transaccion_id' => $resultado['transaccion_id'] ?? null
            ];
            
            // El carrito ya se limpió en la función vaciarCarritoCompleto
            // pero nos aseguramos de que la sesión esté limpia
            $_SESSION['carrito'] = []; 
            unset($_SESSION['carrito']);
            
            // Redireccionar directamente a la página de finalización en lugar de usar JavaScript
            // Esto garantiza que se complete el proceso correctamente
            echo '<script>
                // Limpiar cualquier dato del carrito almacenado en localStorage
                localStorage.removeItem("artesanoDigital_cart");
                localStorage.removeItem("carrito_items");
                sessionStorage.removeItem("artesanoDigital_cart");
                
                // Función para limpiar cookies relacionadas con el carrito
                function deleteCartCookies() {
                    const cookies = document.cookie.split(";");
                    for (let i = 0; i < cookies.length; i++) {
                        const cookie = cookies[i].trim();
                        if (cookie.indexOf("carrito") >= 0 || cookie.indexOf("cart") >= 0) {
                            document.cookie = cookie.split("=")[0] + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
                        }
                    }
                }
                deleteCartCookies();
                
                // Redirección con JavaScript para asegurar que se aplique incluso si headers ya han sido enviados
                window.location.href = "/artesanoDigital/checkout/completado";
            </script>';
            
            // Como respaldo, intentamos también con PHP header (podría no funcionar si ya se envió contenido)
            if (!headers_sent()) {
                header('Location: /artesanoDigital/checkout/completado');
                exit();
            }
            
        } catch (\Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
                
                // Verificar si el carrito se limpió pero luego ocurrió un error
                // Si es así, debemos recuperar los productos del carrito
                if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
                    error_log("Error ocurrió después de limpiar el carrito, recuperando carrito desde los productos");
                    if (isset($productos) && !empty($productos)) {
                        $_SESSION['carrito'] = $productos;
                        error_log("Carrito recuperado con " . count($productos) . " productos");
                    }
                }
            }
            echo '<div class="alert alert-danger">Error al procesar el pedido: ' . 
                 htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>

<div class="checkout-container">
  <div class="checkout-header">
    <h1 class="checkout-title">Finalizar compra</h1>
    <p class="checkout-subtitle">Complete los siguientes pasos para realizar su pedido</p>
  </div>

  <!-- Proceso de checkout de una sola página con tabs/steps -->
  <div class="checkout-wrapper">
    <div class="checkout-steps">
      <div class="step active" data-step="1">
        <span class="step-number">1</span>
        <span class="step-title">Dirección</span>
      </div>
      <div class="step" data-step="2">
        <span class="step-number">2</span>
        <span class="step-title">Pago</span>
      </div>
      <div class="step" data-step="3">
        <span class="step-number">3</span>
        <span class="step-title">Confirmación</span>
      </div>
      <div class="step" data-step="4">
        <span class="step-number">4</span>
        <span class="step-title">Finalizado</span>
      </div>
    </div>

    <form id="checkout-form" method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
      <input type="hidden" name="finalizar_compra" value="1">
      
      <div id="cart-message-container"></div>
      
      <!-- PASO 1: DIRECCIÓN -->
      <div class="checkout-step-content" id="step-1">
        <h2>Información de envío</h2>
        
        <!-- Resumen del carrito -->
        <div class="cart-summary mb-4">
          <h4>Resumen de tu pedido</h4>
          <div class="card">
            <div class="card-body">
              <div id="cart-summary-step2">
                <!-- Mostrar productos del carrito con PHP -->
                <?php if (!empty($productos)): ?>
                  <ul class="list-group list-group-flush">
                    <?php 
                    $total = 0;
                    foreach ($productos as $producto): 
                      $precio = floatval($producto['precio']);
                      $cantidad = intval($producto['cantidad']);
                      $subtotal = $precio * $cantidad;
                      $total += $subtotal;
                      
                      // Debug: Log cada producto
                      error_log("Mostrando producto: " . ($producto['nombre'] ?? 'Sin nombre') . 
                               " - Precio: $precio - Cantidad: $cantidad - Subtotal: $subtotal");
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <span><?php echo htmlspecialchars($producto['nombre'] ?? 'Producto sin nombre'); ?></span>
                        <span class="badge badge-primary ml-2">x<?php echo $cantidad; ?></span>
                      </div>
                      <span>B/. <?php echo number_format($subtotal, 2); ?></span>
                    </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="alert alert-warning">
                    <p class="mb-0">No hay productos en el carrito</p>
                    <p class="mb-0 small">
                      Debug: Productos en sesión: <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 'Sin sesión'; ?> |
                      Usuario: <?php echo isset($usuario['id_usuario']) ? $usuario['id_usuario'] : 'No autenticado'; ?>
                    </p>
                  </div>
                <?php endif; ?>
              </div>
              <hr>
              <div class="d-flex justify-content-between font-weight-bold">
                <span>Total:</span>
                <span id="cart-summary-total-step2">B/. <?php echo number_format($total ?? $total_carrito ?? 0, 2); ?></span>
              </div>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="nombre">Nombre completo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required
                value="<?php echo $usuario['nombre'] ?? ''; ?>">
          </div>
          <div class="form-group col-md-6">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required
                value="<?php echo $usuario['telefono'] ?? ''; ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="direccion">Dirección</label>
          <textarea class="form-control" id="direccion" name="direccion" required rows="3"><?php echo $usuario['direccion'] ?? ''; ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="ciudad">Ciudad</label>
            <select class="form-control" id="ciudad" name="ciudad" required>
              <option value="">Seleccione...</option>
              <option value="La Chorrera">La Chorrera</option>
              <option value="Arraiján">Arraiján</option>
              <option value="Capira">Capira</option>
              <option value="Chame">Chame</option>
              <option value="San Carlos">San Carlos</option>
              <option value="Ciudad de Panamá">Ciudad de Panamá</option>
            </select>
          </div>
        </div>
        
        <div class="form-group text-right mt-4">
          <button type="button" class="btn btn-secondary prev-step">Regresar</button>
          <button type="button" class="btn btn-primary next-step">Continuar</button>
        </div>
      </div>

      <!-- PASO 2: PAGO -->
      <div class="checkout-step-content" id="step-2" style="display: none;">
        <h2>Método de pago</h2>
        
        <div class="payment-methods">
          <div class="form-check payment-method">
            <input class="form-check-input" type="radio" name="metodo_pago" id="tarjeta" value="tarjeta" checked>
            <label class="form-check-label" for="tarjeta">
              <i class="fas fa-credit-card"></i> Tarjeta de crédito/débito
            </label>
            <div class="payment-details" id="tarjeta-details">
              <div class="form-row">
                <div class="form-group col-12">
                  <label for="nombre_tarjeta">Nombre en la tarjeta</label>
                  <input type="text" class="form-control" id="nombre_tarjeta" name="nombre_tarjeta">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-8">
                  <label for="numero_tarjeta">Número de tarjeta</label>
                  <input type="text" class="form-control" id="numero_tarjeta" name="numero_tarjeta" 
                      placeholder="XXXX XXXX XXXX XXXX">
                </div>
                <div class="form-group col-md-2">
                  <label for="expiracion">MM/AA</label>
                  <input type="text" class="form-control" id="expiracion" name="expiracion" 
                      placeholder="MM/AA">
                </div>
                <div class="form-group col-md-2">
                  <label for="cvv">CVV</label>
                  <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-check payment-method">
            <input class="form-check-input" type="radio" name="metodo_pago" id="yappy" value="yappy">
            <label class="form-check-label" for="yappy">
              <i class="fab fa-mobile-alt"></i> Yappy
            </label>
            <div class="payment-details" id="yappy-details" style="display: none;">
              <div class="form-group">
                <label for="telefono_yappy">Número de teléfono registrado en Yappy</label>
                <input type="text" class="form-control" id="telefono_yappy" name="telefono_yappy" 
                    value="<?php echo $usuario['telefono'] ?? ''; ?>" placeholder="6XXX-XXXX">
                <small class="form-text text-muted">Recibirás una notificación en Yappy para completar el pago.</small>
              </div>
            </div>
          </div>
        </div>
        
        <div class="form-group text-right mt-4">
          <button type="button" class="btn btn-secondary prev-step">Regresar</button>
          <button type="button" class="btn btn-primary next-step">Continuar</button>
        </div>
      </div>

      <!-- PASO 3: CONFIRMACIÓN -->
      <div class="checkout-step-content" id="step-3" style="display: none;">
        <h2>Confirmar pedido</h2>
        <div class="order-summary">
          <div class="summary-section">
            <h4>Resumen de productos</h4>
            <div class="summary-products">
              <?php if(!empty($productos)): ?>
                <?php foreach ($productos as $producto): ?>
                  <div class="summary-product">
                    <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                    <span class="product-quantity">x<?php echo $producto['cantidad']; ?></span>
                    <span class="product-price">B/. <?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-center text-muted">No hay productos en el carrito</p>
              <?php endif; ?>
              <div class="summary-total">
                <span>Total a pagar:</span>
                <span class="total-price">B/. <?php echo number_format($total_carrito, 2); ?></span>
              </div>
            </div>
          </div>
          
          <div class="summary-section">
            <h4>Información de envío</h4>
            <div class="shipping-info">
              <p><strong>Nombre:</strong> <span id="summary-nombre"></span></p>
              <p><strong>Teléfono:</strong> <span id="summary-telefono"></span></p>
              <p><strong>Dirección:</strong> <span id="summary-direccion"></span></p>
              <p><strong>Ciudad:</strong> <span id="summary-ciudad"></span></p>
            </div>
          </div>
          
          <div class="summary-section">
            <h4>Método de pago</h4>
            <div class="payment-info">
              <p id="summary-payment-method"></p>
            </div>
          </div>
        </div>
        
        <div class="form-check mt-4">
          <input type="checkbox" class="form-check-input" id="acepto-terminos" name="acepto_terminos" required>
          <label class="form-check-label" for="acepto-terminos">
            Acepto los términos y condiciones de compra
          </label>
        </div>
        
        <div class="form-group text-right mt-4">
          <button type="button" class="btn btn-secondary prev-step">Regresar</button>
          <button type="submit" name="finalizar_compra" value="1" class="btn btn-success" id="btn-completar-compra">Completar compra</button>
        </div>
      </div>
      
      <!-- PASO 4: FINALIZACIÓN -->
      <div class="checkout-step-content" id="step-4" style="display: none;">
        <div class="completion-container text-center">
          <div class="completion-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h2>¡Gracias por comprar en Artesanos Digital!</h2>
          <p class="lead">Tu pedido ha sido registrado exitosamente.</p>
          
          <?php if (isset($_SESSION['pedido_completado'])): ?>
          <div class="order-details">
            <?php 
              // Generar un código de pedido con formato corto de Artesanos Digital
              $codigoPedido = 'AD-' . date('Ymd') . '-' . $_SESSION['pedido_completado']['id_pedido'] . rand(100, 999);
            ?>
            <h3 class="text-success">Pedido #<?php echo $codigoPedido; ?></h3>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($_SESSION['pedido_completado']['fecha'])); ?></p>
            <p><strong>Total:</strong> B/. <?php 
              // Usamos el valor calculado si está disponible, sino el almacenado en la sesión
              $totalCompletado = isset($total_carrito) ? $total_carrito : $_SESSION['pedido_completado']['total'];
              echo number_format($totalCompletado, 2); 
            ?></p>
            <p><strong>Método de Pago:</strong> <?php echo $_SESSION['pedido_completado']['metodo_pago'] === 'tarjeta' ? 'Tarjeta de Crédito/Débito' : 'Yappy'; ?></p>
            <?php if (isset($_SESSION['pedido_completado']['transaccion_id'])): ?>
            <p><strong>ID de Transacción:</strong> <?php echo $_SESSION['pedido_completado']['transaccion_id']; ?></p>
            <?php endif; ?>
            <p class="text-info">Número de registro interno: #<?php echo $_SESSION['pedido_completado']['id_pedido']; ?></p>

            <?php if (!empty($_SESSION['pedido_completado']['productos'])): ?>
            <div class="productos-pedido mt-4">
              <h5>Productos adquiridos:</h5>
              <ul class="list-group">
                <?php foreach ($_SESSION['pedido_completado']['productos'] as $producto): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                    <span class="badge badge-primary ml-2">×<?php echo $producto['cantidad']; ?></span>
                  </div>
                  <span>B/. <?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="order-details">
            <h3 class="text-success">Pedido #AD-<?php echo date('Ymd') . '-' . rand(1000, 9999); ?></h3>
            <p><strong>Estado:</strong> <span class="badge badge-success">Procesado</span></p>
          </div>
          <?php endif; ?>
          
          <p class="mt-4">Te hemos enviado un correo electrónico con la confirmación de tu pedido.</p>
          
          <div class="completion-actions mt-4">
            <a href="/artesanoDigital/dashboard/cliente" class="btn btn-outline-primary mr-3">Ver mis pedidos</a>
            <a href="/artesanoDigital" class="btn btn-primary">Seguir comprando</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
/* Estilos para el proceso de checkout */
.checkout-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.checkout-header {
  text-align: center;
  margin-bottom: 2rem;
}

.checkout-title {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.checkout-subtitle {
  color: #6c757d;
}

.checkout-wrapper {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 2rem;
}

/* Pasos del checkout */
.checkout-steps {
  display: flex;
  justify-content: space-between;
  margin-bottom: 2rem;
  position: relative;
}

.checkout-steps::before {
  content: "";
  position: absolute;
  top: 24px;
  left: 0;
  right: 0;
  height: 2px;
  background: #e9ecef;
  z-index: 1;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 2;
}

.step-number {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #f8f9fa;
  border: 2px solid #dee2e6;
  color: #adb5bd;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.step-title {
  color: #adb5bd;
  font-size: 0.9rem;
}

.step.active .step-number,
.step.completed .step-number {
  background: #007bff;
  border-color: #007bff;
  color: #fff;
}

.step.active .step-title,
.step.completed .step-title {
  color: #007bff;
  font-weight: bold;
}

.step.completed .step-number::after {
  content: "✓";
}

/* Contenido de los pasos */
.checkout-step-content {
  margin-bottom: 1.5rem;
}

.checkout-step-content h2 {
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 0.75rem;
  margin-bottom: 1.5rem;
}

/* Carrito */
.cart-table {
  width: 100%;
  border-collapse: collapse;
}

.cart-table th,
.cart-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.product-info {
  display: flex;
  align-items: center;
}

.product-image {
  width: 80px;
  height: 80px;
  margin-right: 1rem;
  border-radius: 4px;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-vendor {
  font-size: 0.85rem;
  color: #6c757d;
}

.quantity-selector {
  display: flex;
  align-items: center;
}

.quantity-btn {
  width: 30px;
  height: 30px;
  border: 1px solid #dee2e6;
  background: #f8f9fa;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.quantity-input {
  width: 40px;
  height: 30px;
  text-align: center;
  border: 1px solid #dee2e6;
  border-left: none;
  border-right: none;
}

.btn-remove {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
}

.cart-total td {
  padding-top: 1.5rem;
}

/* Métodos de pago */
.payment-methods {
  margin-bottom: 1.5rem;
}

.payment-method {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.payment-method label {
  font-weight: 500;
  margin-left: 0.5rem;
  cursor: pointer;
}

.payment-details {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px dashed #dee2e6;
}

/* Confirmación */
.order-summary {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
}

.summary-section {
  margin-bottom: 1.5rem;
}

.summary-section h4 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.summary-product {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px dashed #dee2e6;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  font-weight: bold;
  margin-top: 1rem;
  padding-top: 0.5rem;
  border-top: 2px solid #dee2e6;
}

.shipping-info p,
.payment-info p {
  margin-bottom: 0.5rem;
}

/* Estilos para el paso de finalización */
.completion-container {
  padding: 2rem 0;
}

.completion-icon {
  font-size: 5rem;
  color: #28a745;
  margin-bottom: 1.5rem;
}

.order-details {
  max-width: 500px;
  margin: 2rem auto;
  padding: 1.5rem;
  border: 1px solid #e9ecef;
  border-radius: 0.5rem;
  background-color: #f8f9fa;
}

.completion-actions .btn {
  min-width: 180px;
}
</style>

document.addEventListener('DOMContentLoaded', function() {
<script>
// --- Sincronización automática del carrito localStorage -> sesión PHP ---
document.addEventListener('DOMContentLoaded', function() {
  // Si el carrito PHP está vacío pero hay carrito en localStorage, sincronizar
  var phpCartVacio = <?php echo empty($productos) ? 'true' : 'false'; ?>;
  var localCart = localStorage.getItem('artesanoDigital_cart') || localStorage.getItem('carrito_items');
  if (phpCartVacio && localCart) {
    try {
      var carrito = JSON.parse(localCart);
      // Si el formato es { productos: [...] }, usar solo el array
      if (carrito && carrito.productos) {
        carrito = carrito.productos;
      }
      // Enviar por AJAX a este mismo archivo
      var xhr = new XMLHttpRequest();
      xhr.open('POST', window.location.href, true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          try {
            var resp = JSON.parse(xhr.responseText);
            if (resp.ok) {
              // Recargar para mostrar el carrito sincronizado
              window.location.reload();
            }
          } catch (e) {}
        }
      };
      xhr.send('sincronizar_carrito=1&carrito_json=' + encodeURIComponent(JSON.stringify(carrito)));
      return; // Detener el resto del script hasta recargar
    } catch (e) {}
  }
  console.log("Inicializando script de checkout");
  
  // Variables
  const steps = document.querySelectorAll('.step');
  const stepContents = document.querySelectorAll('.checkout-step-content');
  const nextButtons = document.querySelectorAll('.next-step');
  const prevButtons = document.querySelectorAll('.prev-step');
  const paymentMethods = document.querySelectorAll('input[name="metodo_pago"]');
  const form = document.getElementById('checkout-form');
  const cartMessageContainer = document.getElementById('cart-message-container');
  
  // Asegurarse de que el carrito está actualizado con la información del servidor
  // Esto evita diferencias entre el localStorage y la sesión/base de datos
  
  // Cargar información del carrito
  cargarDatosCarrito();
  
  // Función para cargar datos del carrito desde localStorage o sesión
  function cargarDatosCarrito() {
    console.log("Cargando datos del carrito...");
    
    // Intentar obtener datos del carrito desde varias fuentes
    let cartData;
    
    try {
      // 1. Primero intentar desde localStorage
      if (localStorage.getItem('artesanoDigital_cart')) {
        cartData = JSON.parse(localStorage.getItem('artesanoDigital_cart'));
        console.log("Carrito cargado desde localStorage");
      } 
      // 2. Si no, intentar desde sessionStorage
      else if (sessionStorage.getItem('artesanoDigital_cart')) {
        cartData = JSON.parse(sessionStorage.getItem('artesanoDigital_cart'));
        console.log("Carrito cargado desde sessionStorage");
      }
      
      // Si tenemos datos, mostrarlos
      if (cartData && cartData.productos && cartData.productos.length > 0) {
        mostrarProductosCarrito(cartData.productos);
        actualizarTotalCarrito(cartData.productos);
      } else {
        // Si no hay datos en storage, usar los datos renderizados por PHP (de $_SESSION['carrito'])
        console.log("Usando datos de carrito de la sesión PHP");
      }
    } catch (e) {
      console.error("Error al cargar datos del carrito:", e);
    }
  }
  
  // Función para mostrar productos en el resumen
  function mostrarProductosCarrito(productos) {
    const cartSummaryContainer = document.getElementById('cart-summary-step2');
    if (!cartSummaryContainer) return;
    
    let html = '<ul class="list-group list-group-flush">';
    
    productos.forEach(producto => {
      html += `
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <span>${producto.nombre || producto.name}</span>
            <span class="badge badge-primary ml-2">x${producto.cantidad || producto.quantity}</span>
          </div>
          <span>B/. ${((producto.precio || producto.price) * (producto.cantidad || producto.quantity)).toFixed(2)}</span>
        </li>
      `;
    });
    
    html += '</ul>';
    cartSummaryContainer.innerHTML = html;
  }
  
  // Función para actualizar el total del carrito
  function actualizarTotalCarrito(productos) {
    const totalElement = document.getElementById('cart-summary-total-step2');
    if (!totalElement) return;
    
    let total = 0;
    productos.forEach(producto => {
      const precio = parseFloat(producto.precio || producto.price);
      const cantidad = parseInt(producto.cantidad || producto.quantity);
      total += precio * cantidad;
    });
    
    totalElement.textContent = `B/. ${total.toFixed(2)}`;
  }
  
  console.log("Pasos encontrados:", steps.length);
  console.log("Contenidos de pasos encontrados:", stepContents.length);
  console.log("Botones siguiente encontrados:", nextButtons.length);
  
  // Iniciar en el paso 1 (Dirección)
  let currentStep = 1;
  
  // Debugging
  console.log('Script inicializado. Elementos encontrados:');
  console.log('- Pasos:', steps.length);
  console.log('- Contenidos de pasos:', stepContents.length);
  console.log('- Botones siguiente:', nextButtons.length);
  console.log('- Botones anterior:', prevButtons.length);
  console.log('- Métodos de pago:', paymentMethods.length);
  console.log('- Formulario:', form ? 'Sí' : 'No');
  
  // Inicializar - mostrar el paso 1 directamente
  updateSteps();
  
  // Event listeners para botones siguiente
  nextButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      console.log('Botón siguiente clickeado en paso:', currentStep);
      
      if (validateStep(currentStep)) {
        currentStep++;
        console.log('Avanzando al paso:', currentStep);
        updateSteps();
        updateSummary();
        
        // Si es el último paso, scroll hacia arriba para ver el botón de finalizar
        if (currentStep === 3) {
          window.scrollTo({top: 0, behavior: 'smooth'});
        }
      }
    });
  });

  // Event listeners para botones anterior
  prevButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      console.log('Botón anterior clickeado en paso:', currentStep);
      
      currentStep--;
      if (currentStep < 1) currentStep = 1;
      updateSteps();
    });
  });

  // Event listeners para métodos de pago
  paymentMethods.forEach(method => {
    method.addEventListener('change', function() {
      // Ocultar todos los detalles de pagos
      document.querySelectorAll('.payment-details').forEach(details => {
        details.style.display = 'none';
      });
      
      // Mostrar los detalles del método seleccionado
      const detailsElement = document.getElementById(`${this.value}-details`);
      if (detailsElement) {
        detailsElement.style.display = 'block';
      }
    });
  });

  // Validación antes de envío del formulario
  if (form) {
    form.addEventListener('submit', function(e) {
      console.log('Formulario enviado en paso:', currentStep);
      
      // Solo permitir enviar el formulario en el paso 3 (confirmación)
      if (currentStep !== 3) {
        e.preventDefault();
        console.log('Envío bloqueado: no estamos en el paso de confirmación');
        mostrarMensaje('Por favor complete todos los pasos antes de finalizar', 'warning');
        return false;
      }
      
      // Validar campos del paso actual
      if (!validateStep(3)) {
        e.preventDefault();
        console.log('Envío bloqueado: validación fallida');
        mostrarMensaje('Por favor completa todos los campos requeridos', 'error');
        return false;
      }
      
      // Todo correcto, añadir bandera para finalizar compra
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'finalizar_compra';
      hiddenInput.value = '1';
      form.appendChild(hiddenInput);
      
      console.log('Formulario validado, enviando...');
      return true;
    });
  }
  
  // Asegurar que el botón de completar compra funciona correctamente
  const btnCompletarCompra = document.getElementById('btn-completar-compra');
  if (btnCompletarCompra) {
    btnCompletarCompra.addEventListener('click', function(e) {
      e.preventDefault();
      if (validateStep(3)) {
        form.submit();
      }
    });
  }
  
  // Funciones auxiliares
  function updateSteps() {
    console.log('Actualizando pasos al paso:', currentStep);
    
    // Asegurar que currentStep es válido
    if (currentStep < 1) currentStep = 1;
    if (currentStep > 4) currentStep = 4;
    
    // Actualizar indicadores de paso
    steps.forEach((step, index) => {
      const stepNumber = index + 1;
      
      if (stepNumber < currentStep) {
        step.classList.add('completed');
        step.classList.remove('active');
      } else if (stepNumber === currentStep) {
        step.classList.add('active');
        step.classList.remove('completed');
      } else {
        step.classList.remove('active', 'completed');
      }
    });
    
    // Mostrar solo el contenido del paso actual
    stepContents.forEach((content, index) => {
      const stepNumber = index + 1;
      
      if (content) {
        if (stepNumber === currentStep) {
          content.style.display = 'block';
          console.log(`Mostrando contenido del paso ${stepNumber}`);
        } else {
          content.style.display = 'none';
          console.log(`Ocultando contenido del paso ${stepNumber}`);
        }
      } else {
        console.warn(`No se encontró el contenido para el paso ${stepNumber}`);
      }
    });
  }

  function validateStep(step) {
    switch(step) {
      case 1:
        // Validar campos de dirección
        const nombre = document.getElementById('nombre').value;
        const telefono = document.getElementById('telefono').value;
        const direccion = document.getElementById('direccion').value;
        const ciudad = document.getElementById('ciudad').value;
        
        if (!nombre || !telefono || !direccion || !ciudad) {
          mostrarMensaje('Por favor completa todos los campos obligatorios', 'error');
          return false;
        }
        return true;
        
      case 2:
        // Validar datos de pago
        const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
        
        if (metodoPago === 'tarjeta') {
          const nombreTarjeta = document.getElementById('nombre_tarjeta').value;
          const numeroTarjeta = document.getElementById('numero_tarjeta').value;
          const expiracion = document.getElementById('expiracion').value;
          const cvv = document.getElementById('cvv').value;
          
          if (!nombreTarjeta || !numeroTarjeta || !expiracion || !cvv) {
            mostrarMensaje('Por favor completa todos los campos de la tarjeta', 'error');
            return false;
          }
        } else if (metodoPago === 'yappy') {
          const telefonoYappy = document.getElementById('telefono_yappy').value;
          
          if (!telefonoYappy) {
            mostrarMensaje('Por favor ingresa el número de teléfono registrado en Yappy', 'error');
            return false;
          }
        }
        return true;
        
      case 3:
        // Validar aceptación de términos
        const aceptoTerminos = document.getElementById('acepto-terminos');
        if (!aceptoTerminos || !aceptoTerminos.checked) {
          mostrarMensaje('Debes aceptar los términos y condiciones para continuar', 'error');
          return false;
        }
        return true;
        
      default:
        return true;
    }
  }

  function updateSummary() {
    console.log("Actualizando resumen, paso actual:", currentStep);
    
    // Si estamos en el paso de confirmación, actualizar todos los datos del resumen
    if (currentStep === 3) {
      try {
        // Actualizar información de envío
        const nombreElem = document.getElementById('summary-nombre');
        const telefonoElem = document.getElementById('summary-telefono');
        const direccionElem = document.getElementById('summary-direccion');
        const ciudadElem = document.getElementById('summary-ciudad');
        
        if (nombreElem) nombreElem.textContent = document.getElementById('nombre').value;
        if (telefonoElem) telefonoElem.textContent = document.getElementById('telefono').value;
        if (direccionElem) direccionElem.textContent = document.getElementById('direccion').value;
        if (ciudadElem) {
          const ciudadSelect = document.getElementById('ciudad');
          ciudadElem.textContent = ciudadSelect.options[ciudadSelect.selectedIndex].text;
        }
        
        // Actualizar información de pago
        const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
        const paymentMethodElem = document.getElementById('summary-payment-method');
        
        if (metodoPago && paymentMethodElem) {
          if (metodoPago.value === 'tarjeta') {
            const numeroTarjeta = document.getElementById('numero_tarjeta');
            if (numeroTarjeta && numeroTarjeta.value) {
              const ultimos4 = numeroTarjeta.value.replace(/\s+/g, '').slice(-4);
              paymentMethodElem.textContent = `Tarjeta que termina en ${ultimos4}`;
            } else {
              paymentMethodElem.textContent = 'Tarjeta de crédito/débito';
            }
          } else if (metodoPago.value === 'yappy') {
            const telefonoYappy = document.getElementById('telefono_yappy');
            if (telefonoYappy && telefonoYappy.value) {
              paymentMethodElem.textContent = `Yappy al número ${telefonoYappy.value}`;
            } else {
              paymentMethodElem.textContent = 'Yappy';
            }
          }
        }
        
        // No necesitamos actualizar los productos ni el total, ya que están renderizados por el servidor
        // y siempre muestran los datos más actualizados
        
      } catch (e) {
        console.error("Error al actualizar resumen en paso 3:", e);
      }
    }
  }

  function mostrarMensaje(mensaje, tipo = 'info') {
    console.log(`Mostrando mensaje (${tipo}): ${mensaje}`);
    
    // Si hay un contenedor específico para mensajes
    if (cartMessageContainer) {
      const alertClass = tipo === 'error' ? 'alert-danger' : 
                        tipo === 'success' ? 'alert-success' : 
                        tipo === 'warning' ? 'alert-warning' : 'alert-info';
      
      cartMessageContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
          ${mensaje}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      `;
      
      // Asegurar que el mensaje es visible
      cartMessageContainer.scrollIntoView({ behavior: 'smooth' });
      
      // Auto-cerrar después de 5 segundos
      setTimeout(() => {
        const alert = cartMessageContainer.querySelector('.alert');
        if (alert) {
          alert.classList.remove('show');
          setTimeout(() => {
            cartMessageContainer.innerHTML = '';
          }, 300);
        }
      }, 5000);
    } else {
      // Si no hay contenedor, usar alert nativo
      alert(mensaje);
    }
  }
});
</script>

<?php
// Variables para el layout
$titulo = 'Checkout - Artesano Digital';
$descripcion = 'Completa tu compra en Artesano Digital';
$scriptsAdicionales = ['/artesanoDigital/assets/js/checkout-debug.js'];

// Obtener el contenido del buffer y limpiarlo
$contenido = ob_get_clean();

// Incluir la plantilla base
include __DIR__ . '/../layouts/base.php';
