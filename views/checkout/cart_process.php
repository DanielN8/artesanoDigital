
<?php
// --- Sincronización del carrito desde localStorage a la sesión PHP (AJAX) ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['sincronizar_carrito']) &&
    isset($_POST['carrito_json'])
) {
    $carritoLocal = json_decode($_POST['carrito_json'], true);
    $productos = [];
    if (is_array($carritoLocal)) {
        foreach ($carritoLocal as $item) {
            // Normalizar campos del localStorage a formato backend
            $productos[] = [
                'id_producto' => $item['id'] ?? $item['id_producto'] ?? null,
                'nombre' => $item['nombre'] ?? $item['name'] ?? '',
                'precio' => floatval($item['precio'] ?? $item['price'] ?? 0),
                'descuento' => floatval($item['descuento'] ?? $item['discount'] ?? 0),
                'cantidad' => intval($item['cantidad'] ?? $item['quantity'] ?? 1),
                'id_tienda' => $item['id_tienda'] ?? null,
                'id_usuario_tienda' => $item['id_usuario_tienda'] ?? null,
                'imagen' => $item['imagen'] ?? null
            ];
            error_log("Sincronizando producto desde localStorage: " . json_encode($productos[count($productos)-1]));
        }
        $_SESSION['carrito'] = $productos;
        error_log("Carrito sincronizado desde localStorage: " . count($productos) . " productos");
        echo json_encode(['ok' => true, 'msg' => 'Carrito sincronizado en sesión']);
        exit;
    }
    echo json_encode(['ok' => false, 'msg' => 'Formato de carrito inválido']);
    exit;
}

// --- ENDPOINT PARA LIMPIAR CARRITO COMPLETAMENTE ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['accion']) && $_POST['accion'] === 'limpiar_carrito_completo'
) {
    $usuario = $_SESSION['usuario'] ?? null;
    
    if (!$usuario || !isset($usuario['id_usuario'])) {
        echo json_encode(['ok' => false, 'msg' => 'Usuario no autenticado']);
        exit;
    }
    
    $resultado = vaciarCarritoCompleto($usuario['id_usuario']);
    
    if ($resultado) {
        echo json_encode([
            'ok' => true, 
            'msg' => 'Carrito limpiado completamente',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'ok' => false, 
            'msg' => 'Error al limpiar carrito completamente'
        ]);
    }
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
            $productoNormalizado = [
                'id_producto' => $prod['id_producto'] ?? $prod['id'] ?? null,
                'nombre' => $prod['nombre'] ?? $prod['name'] ?? '',
                'cantidad' => intval($prod['cantidad'] ?? $prod['quantity'] ?? 1),
                'precio' => floatval($prod['precio'] ?? $prod['price'] ?? 0),
                'descuento' => floatval($prod['descuento'] ?? $prod['discount'] ?? 0),
                // Puedes agregar más campos si tu app los usa
                'id_tienda' => $prod['id_tienda'] ?? null,
                'id_usuario_tienda' => $prod['id_usuario_tienda'] ?? null,
                'imagen' => $prod['imagen'] ?? null
            ];
            
            // Log para depuración
            error_log("Producto sincronizado desde localStorage: " . json_encode($productoNormalizado));
            
            return $productoNormalizado;
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
 * Limpia tanto en base de datos como en sesión y frontend
 * @param int $idUsuario ID del usuario cuyo carrito se limpiará
 * @param \PDO $conexion Conexión a la base de datos (opcional)
 * @return bool Retorna true si la limpieza fue exitosa
 */
function vaciarCarritoCompleto($idUsuario, $conexion = null) {
    $exitoso = true;
    
    // Si no se proporcionó una conexión y necesitamos limpiar la BD
    $dbConnectLocal = false;
    if ($idUsuario && !$conexion) {
        try {
            require_once __DIR__ . '/../../config/Database.php';
            $db = \Config\Database::obtenerInstancia();
            $conexion = $db->obtenerConexion();
            $dbConnectLocal = true;
        } catch (\Exception $e) {
            error_log("No se pudo conectar a la BD para limpiar carrito: " . $e->getMessage());
            $exitoso = false;
        }
    }
    
    // Limpiar en base de datos
    if ($idUsuario && $conexion) {
        try {
            // Iniciar transacción si no estamos dentro de una
            if ($dbConnectLocal) {
                $conexion->beginTransaction();
            }
            
            // Verificar si existe un carrito para el usuario
            $sqlVerificar = "SELECT id_carrito FROM carritos WHERE id_usuario = :id_usuario";
            $stmtVerificar = $conexion->prepare($sqlVerificar);
            $stmtVerificar->execute(['id_usuario' => $idUsuario]);
            $carrito = $stmtVerificar->fetch();
            
            if ($carrito) {
                // Eliminar productos del carrito
                $sqlVaciarProductos = "DELETE FROM carrito_productos 
                                      WHERE id_carrito = :id_carrito";
                $stmtVaciarProductos = $conexion->prepare($sqlVaciarProductos);
                $stmtVaciarProductos->execute(['id_carrito' => $carrito['id_carrito']]);
                
                $productosEliminados = $stmtVaciarProductos->rowCount();
                error_log("Eliminados $productosEliminados productos del carrito en BD");
                
                // Actualizar contador y total en tabla carritos
                $sqlActualizar = "UPDATE carritos SET cantidad_productos = 0, total = 0, fecha_actualizacion = NOW() 
                                  WHERE id_usuario = :id_usuario";
                $stmtActualizar = $conexion->prepare($sqlActualizar);
                $stmtActualizar->execute(['id_usuario' => $idUsuario]);
                
                error_log("Carrito actualizado en BD - totales en cero");
            } else {
                error_log("No se encontró carrito en BD para usuario $idUsuario");
            }
            
            if ($dbConnectLocal) {
                $conexion->commit();
            }
            
            error_log("Carrito limpiado completamente en base de datos para usuario $idUsuario");
        } catch (\Exception $e) {
            if ($dbConnectLocal && $conexion) {
                $conexion->rollBack();
            }
            error_log("Error al limpiar carrito en BD: " . $e->getMessage());
            $exitoso = false;
        }
    }
    
    // Limpiar en sesión PHP
    try {
        if (isset($_SESSION['carrito'])) {
            $productosEnSesion = count($_SESSION['carrito']);
            $_SESSION['carrito'] = [];
            unset($_SESSION['carrito']);
            error_log("Eliminados $productosEnSesion productos del carrito en sesión PHP");
        }
        
        // Limpiar otras variables relacionadas con el carrito en sesión
        $variablesCarrito = [
            'carrito_total',
            'carrito_count', 
            'carrito_temporal',
            'carrito_backup',
            'last_cart_sync',
            'cart_last_update'
        ];
        
        foreach ($variablesCarrito as $variable) {
            if (isset($_SESSION[$variable])) {
                unset($_SESSION[$variable]);
                error_log("Variable de sesión '$variable' eliminada");
            }
        }
        
        error_log("Carrito limpiado completamente en sesión PHP");
    } catch (\Exception $e) {
        error_log("Error al limpiar carrito en sesión: " . $e->getMessage());
        $exitoso = false;
    }
    
    return $exitoso;
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
                // Preservar datos importantes del carrito (precio, descuento, cantidad)
                // Solo agregar los campos faltantes desde la BD
                $productos[$key] = array_merge($productoCompleto, [
                    'cantidad' => $producto['cantidad'],
                    'id_producto' => $producto['id_producto'], // Asegurar que se mantiene este ID
                    // Preservar precio y descuento si ya existen en el carrito
                    'precio' => $producto['precio'] ?? $productoCompleto['precio'],
                    'descuento' => $producto['descuento'] ?? $productoCompleto['descuento'] ?? 0
                ]);
                
                error_log("Producto {$producto['id_producto']} completado desde BD - Precio carrito: " . ($producto['precio'] ?? 'no definido') . ", Precio BD: " . $productoCompleto['precio']);
            } else {
                error_log("ADVERTENCIA: No se pudo cargar el producto {$producto['id_producto']} desde la BD");
                // Remover productos que no se pueden cargar para evitar errores
                unset($productos[$key]);
                continue; // Saltar este producto si no se puede cargar
            }
        }
        
        $precio = floatval($productos[$key]['precio']);
        $descuento = floatval($productos[$key]['descuento'] ?? 0);
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
        
        // Determinar si el precio ya tiene descuento aplicado o si necesitamos aplicarlo
        $precio_original = $precio;
        $precio_con_descuento = $precio;
        
        // Si tenemos descuento y el precio parece ser el precio original (mayor que el descuento)
        if ($descuento > 0 && $precio > $descuento) {
            // Asumir que el precio es el original y aplicar descuento
            $precio_original = $precio;
            $precio_con_descuento = $precio - $descuento;
            // Asegurar que el precio con descuento no sea negativo
            if ($precio_con_descuento < 0) {
                $precio_con_descuento = 0;
            }
            error_log("Aplicando descuento: Precio original {$precio_original}, Descuento {$descuento}, Precio final {$precio_con_descuento}");
        } 
        // Si tenemos descuento pero el precio es menor o igual al descuento, 
        // probablemente el precio ya tiene descuento aplicado
        else if ($descuento > 0 && $precio <= $descuento) {
            // El precio ya viene con descuento aplicado desde el frontend
            $precio_original = $precio + $descuento;
            $precio_con_descuento = $precio;
            error_log("Precio ya tiene descuento aplicado: Precio original calculado {$precio_original}, Precio con descuento {$precio_con_descuento}");
        }
        // Si no hay descuento, usar precio tal como está
        else {
            $precio_original = $precio;
            $precio_con_descuento = $precio;
        }
        
        $subtotal = $precio_con_descuento * $cantidad;
        $total_carrito += $subtotal;
        
        // Actualizar el subtotal y precio con descuento en el array de productos
        $productos[$key]['subtotal'] = $subtotal;
        $productos[$key]['precio_original'] = $precio_original; // Precio original
        $productos[$key]['precio'] = $precio_con_descuento; // Precio con descuento aplicado
        $productos[$key]['descuento'] = $descuento; // Descuento aplicado
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
  console.log("Inicializando checkout");
  
  var phpCartVacio = <?php echo empty($productos) ? 'true' : 'false'; ?>;
  var phpCartCount = <?php echo count($productos); ?>;
  
  console.log('Estado del carrito PHP:', phpCartVacio ? 'vacío' : 'con ' + phpCartCount + ' productos');
  
  // Si el carrito PHP está vacío pero hay carrito en localStorage, sincronizar
  if (phpCartVacio) {
    var localCart = localStorage.getItem('carrito') || localStorage.getItem('artesanoDigital_cart') || localStorage.getItem('carrito_items');
    
    if (localCart) {
      try {
        var cartData = JSON.parse(localCart);
        if (cartData && (Array.isArray(cartData) || (cartData.productos && Array.isArray(cartData.productos)))) {
          console.log('Carrito encontrado en localStorage, sincronizando...');
          
          var xhr = new XMLHttpRequest();
          xhr.open('POST', window.location.href, true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
              try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.ok) {
                  console.log('Carrito sincronizado, recargando página...');
                  window.location.reload();
                } else {
                  console.error('Error en sincronización:', resp.msg);
                }
              } catch (e) {
                console.error('Error parsing sync response:', e);
              }
            }
          };
          
          var productos = Array.isArray(cartData) ? cartData : cartData.productos;
          xhr.send('accion=sincronizar_carrito&carrito=' + encodeURIComponent(JSON.stringify(productos)));
          return;
        }
      } catch (e) {
        console.error('Error parsing localStorage cart:', e);
      }
    }
  }
  
  // Variables del carrito
  var phpCartVacio = <?php echo empty($productos) ? 'true' : 'false'; ?>;
  var phpCartCount = <?php echo count($productos); ?>;
  
  console.log('Estado del carrito PHP:', phpCartVacio ? 'vacío' : 'con ' + phpCartCount + ' productos');
  
  // Función para sincronizar carrito directamente
  function sincronizarCarrito() {
    // Intentar cargar desde diferentes claves de localStorage
    var localCart = localStorage.getItem('carrito') || localStorage.getItem('artesanoDigital_cart') || localStorage.getItem('carrito_items');
    
    if (localCart) {
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
          
          // Enviar por AJAX usando el endpoint correcto
          var xhr = new XMLHttpRequest();
          xhr.open('POST', window.location.href, true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
              try {
                var resp = JSON.parse(xhr.responseText);
                console.log('Respuesta de sincronización:', resp);
                if (resp.ok) {
                  console.log('Carrito sincronizado exitosamente, recargando...');
                  window.location.reload();
                } else {
                  console.error('Error en sincronización:', resp.msg);
                  inicializarFormulario();
                }
              } catch (e) {
                console.error('Error parsing response:', e, xhr.responseText);
                inicializarFormulario();
              }
            }
          };
          
          // Usar el formato correcto de sincronización
          xhr.send('sincronizar_carrito=1&carrito_json=' + encodeURIComponent(JSON.stringify(carrito)));
          return true;
        }
      } catch (e) {
        console.error('Error parsing localStorage cart:', e);
        inicializarFormulario();
      }
    }
    
    // No hay carrito en localStorage, inicializar directamente
    inicializarFormulario();
    return false;
  }
  
  // Función para limpiar carrito completamente desde el frontend
  function limpiarCarritoCompleto() {
    console.log('Limpiando carrito completamente...');
    
    // Llamar al endpoint de backend
    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          var resp = JSON.parse(xhr.responseText);
          console.log('Respuesta de limpieza:', resp);
          if (resp.ok) {
            console.log('Carrito limpiado en backend:', resp.msg);
            // Limpiar también en frontend
            limpiarCarritoFrontend();
          } else {
            console.error('Error limpiando carrito en backend:', resp.msg);
          }
        } catch (e) {
          console.error('Error parsing cleanup response:', e);
        }
      }
    };
    xhr.send('accion=limpiar_carrito_completo');
  }
  
  // Función para limpiar carrito solo en frontend
  function limpiarCarritoFrontend() {
    console.log('Limpiando carrito en frontend...');
    
    // Limpiar localStorage
    localStorage.removeItem('artesanoDigital_cart');
    localStorage.removeItem('carrito_items');
    localStorage.removeItem('carrito_temporal');
    localStorage.removeItem('checkout_data');
    localStorage.removeItem('cart_count');
    localStorage.removeItem('cart_total');
    
    // Limpiar sessionStorage
    sessionStorage.removeItem('artesanoDigital_cart');
    sessionStorage.removeItem('carrito_items');
    sessionStorage.removeItem('en_checkout');
    sessionStorage.removeItem('checkout_form_data');
    sessionStorage.removeItem('payment_data');
    sessionStorage.removeItem('shipping_data');
    sessionStorage.removeItem('carrito_needs_reload');
    sessionStorage.removeItem('checkout_timestamp');
    
    // Resetear contadores visuales
    var cartBadges = document.querySelectorAll('.cart-badge, .cart-count, #cart-count');
    cartBadges.forEach(function(badge) {
      badge.textContent = '0';
      badge.style.display = 'none';
    });
    
    // Resetear elementos del carrito
    var cartElements = document.querySelectorAll('.cart-items, #cart-items, .carrito-contenido');
    cartElements.forEach(function(element) {
      element.innerHTML = '<p>Tu carrito está vacío</p>';
    });
    
    console.log('Carrito limpiado en frontend completamente');
  }
  
  // Exponer funciones globalmente para uso externo
  window.limpiarCarritoCompleto = limpiarCarritoCompleto;
  window.limpiarCarritoFrontend = limpiarCarritoFrontend;
  
  // Función para actualizar el resumen del carrito en tiempo real
  function actualizarResumenCarrito(productos) {
    console.log('Actualizando resumen del carrito con', productos.length, 'productos');
    
    const cartSummaryContainer = document.getElementById('cart-summary-step2');
    const totalElement = document.getElementById('cart-summary-total-step2');
    
    if (!cartSummaryContainer) {
      console.warn('Contenedor del resumen del carrito no encontrado');
      return;
    }
    
    let html = '';
    let total = 0;
    
    if (productos.length > 0) {
      html = '<ul class="list-group list-group-flush">';
      
      productos.forEach(producto => {
        const nombre = producto.nombre || producto.name || 'Producto sin nombre';
        const precio = parseFloat(producto.precio || producto.price || 0);
        const descuento = parseFloat(producto.descuento || producto.discount || 0);
        const cantidad = parseInt(producto.cantidad || producto.quantity || 1);
        
        // Calcular precios con descuento
        let precioOriginal = precio;
        let precioFinal = precio;
        
        if (descuento > 0) {
          if (precio > descuento) {
            // El precio es original, aplicar descuento
            precioOriginal = precio;
            precioFinal = precio - descuento;
          } else {
            // El precio ya tiene descuento aplicado
            precioOriginal = precio + descuento;
            precioFinal = precio;
          }
        }
        
        const subtotal = precioFinal * cantidad;
        total += subtotal;
        
        html += `
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <span>${nombre}</span>
              <span class="badge badge-primary ml-2">x${cantidad}</span>`;
        
        if (descuento > 0) {
          html += `
              <br><small class="text-muted">
                Precio original: <s>B/. ${precioOriginal.toFixed(2)}</s><br>
                <span class="text-success font-weight-bold">Con descuento: B/. ${precioFinal.toFixed(2)}</span>
                <span class="text-info">(Ahorro: B/. ${descuento.toFixed(2)})</span>
              </small>`;
        }
        
        html += `
            </div>
            <span class="font-weight-bold">B/. ${subtotal.toFixed(2)}</span>
          </li>`;
      });
      
      html += '</ul>';
    } else {
      html = `
        <div class="alert alert-warning">
          <p class="mb-0">No hay productos en el carrito</p>
        </div>`;
    }
    
    cartSummaryContainer.innerHTML = html;
    
    if (totalElement) {
      totalElement.textContent = `B/. ${total.toFixed(2)}`;
    }
    
    console.log('Resumen del carrito actualizado, total:', total.toFixed(2));
  }
  
  // Función principal para inicializar el checkout
  function inicializarCheckout() {
    console.log("Inicializando funcionalidades del checkout");
    
    updateLoadingStatus('initializeUI', 'Inicializando interfaz', 'Configurando formularios y validaciones...');
    
    // Variables del formulario
    const form = document.getElementById('checkout-form');
    const paymentMethods = document.querySelectorAll('input[name="metodo_pago"]');
    const yappyForm = document.getElementById('yappy-form');
    const tarjetaForm = document.getElementById('tarjeta-form');
    const confirmButton = document.querySelector('.btn-confirm-order');
    const cartMessageContainer = document.getElementById('cart-message-container');
    
    // Manejo de métodos de pago
    if (paymentMethods.length > 0) {
      paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
          // Ocultar todos los formularios de pago
          if (yappyForm) yappyForm.style.display = 'none';
          if (tarjetaForm) tarjetaForm.style.display = 'none';
          
          // Mostrar el formulario correspondiente
          if (this.value === 'yappy' && yappyForm) {
            yappyForm.style.display = 'block';
          } else if (this.value === 'tarjeta' && tarjetaForm) {
            tarjetaForm.style.display = 'block';
          }
        });
      });
    }
    
    // Validación de formulario en tiempo real
    const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
    requiredFields.forEach(field => {
      field.addEventListener('blur', function() {
        validateField(this);
      });
    });
    
    function validateField(field) {
      if (field.checkValidity()) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
      } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
      }
    }
    
    // Formateo de campos específicos
    const telefonoYappy = document.getElementById('telefono_yappy');
    if (telefonoYappy) {
      telefonoYappy.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 4) {
          value = value.substring(0, 4) + '-' + value.substring(4, 8);
        }
        this.value = value;
      });
    }
    
    const numeroTarjeta = document.getElementById('numero_tarjeta');
    if (numeroTarjeta) {
      numeroTarjeta.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = value.replace(/(.{4})/g, '$1 ').trim();
        this.value = value;
      });
    }
    
    const fechaExpiracion = document.getElementById('fecha_expiracion');
    if (fechaExpiracion) {
      fechaExpiracion.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 2) {
          value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        this.value = value;
      });
    }
    
    const cvv = document.getElementById('cvv');
    if (cvv) {
      cvv.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
      });
    }
    
    // Manejo del envío del formulario
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar loading en el botón
        if (confirmButton) {
          showLoading('Procesando pago', 'Validando información y procesando...');
          confirmButton.disabled = true;
          confirmButton.classList.add('loading');
          confirmButton.innerHTML = 'Procesando...';
        }
        
        // Validar formulario antes de enviar
        if (!form.checkValidity()) {
          hideLoading();
          if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.classList.remove('loading');
            confirmButton.innerHTML = 'Finalizar compra';
          }
          showMessage('Por favor, complete todos los campos requeridos.', 'error');
          return;
        }
        
        // Enviar formulario
        form.submit();
      });
    }
    
    // Función para mostrar mensajes
    function showMessage(message, type = 'info') {
      if (cartMessageContainer) {
        cartMessageContainer.innerHTML = `
          <div class="alert alert-${type === 'error' ? 'danger' : type}">
            ${message}
          </div>
        `;
        
        // Auto-hide después de 5 segundos
        setTimeout(() => {
          cartMessageContainer.innerHTML = '';
        }, 5000);
      }
    }
    
    console.log("Checkout inicializado correctamente");
  }
  
  // Inicializar formulario directamente
  inicializarFormulario();
});

// Función para inicializar el formulario de checkout
function inicializarFormulario() {
  console.log("Inicializando formulario de checkout");
  
  // Variables del formulario
  const form = document.getElementById('checkout-form');
  const paymentMethods = document.querySelectorAll('input[name="metodo_pago"]');
  const yappyForm = document.getElementById('yappy-form');
  const tarjetaForm = document.getElementById('tarjeta-form');
  const confirmButton = document.querySelector('.btn-confirm-order');
  const cartMessageContainer = document.getElementById('cart-message-container');
  
  // Verificar que los elementos existen
  console.log('Elementos encontrados:', {
    paymentMethods: paymentMethods.length,
    yappyForm: !!yappyForm,
    tarjetaForm: !!tarjetaForm
  });
  
  // Configurar estado inicial de formularios de pago
  if (yappyForm) yappyForm.style.display = 'block'; // Yappy visible por defecto
  if (tarjetaForm) tarjetaForm.style.display = 'none'; // Tarjeta oculta por defecto
  
  // Manejo de métodos de pago
  if (paymentMethods.length > 0) {
    paymentMethods.forEach(method => {
      method.addEventListener('change', function() {
        console.log('Método de pago cambiado a:', this.value);
        
        // Ocultar todos los formularios de pago
        if (yappyForm) {
          yappyForm.style.display = 'none';
          console.log('Yappy form oculto');
        }
        if (tarjetaForm) {
          tarjetaForm.style.display = 'none';
          console.log('Tarjeta form oculto');
        }
        
        // Mostrar el formulario correspondiente
        if (this.value === 'yappy' && yappyForm) {
          yappyForm.style.display = 'block';
          console.log('Mostrando formulario Yappy');
        } else if (this.value === 'tarjeta' && tarjetaForm) {
          tarjetaForm.style.display = 'block';
          console.log('Mostrando formulario Tarjeta');
        }
      });
    });
    
    console.log('Event listeners para métodos de pago configurados');
  } else {
    console.log('No se encontraron métodos de pago');
  }
  
  // Validación de formulario en tiempo real
  const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
  requiredFields.forEach(field => {
    field.addEventListener('blur', function() {
      validateField(this);
    });
  });
  
  function validateField(field) {
    if (field.checkValidity()) {
      field.classList.remove('is-invalid');
      field.classList.add('is-valid');
    } else {
      field.classList.remove('is-valid');
      field.classList.add('is-invalid');
    }
  }
  
  // Formateo de campos específicos
  const telefonoYappy = document.getElementById('telefono_yappy');
  if (telefonoYappy) {
    telefonoYappy.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');
      if (value.length >= 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 8);
      }
      this.value = value;
    });
  }
  
  const numeroTarjeta = document.getElementById('numero_tarjeta');
  if (numeroTarjeta) {
    numeroTarjeta.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');
      let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();
      if (formattedValue.length > 19) {
        formattedValue = formattedValue.substring(0, 19);
      }
      this.value = formattedValue;
    });
  }
  
  const fechaExpiracion = document.getElementById('fecha_expiracion');
  if (fechaExpiracion) {
    fechaExpiracion.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      this.value = value;
    });
  }
  
  const cvv = document.getElementById('cvv');
  if (cvv) {
    cvv.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });
  }
  
  console.log("Formulario de checkout inicializado correctamente");
}
</script>
<?php

// --- Importar el módulo de métodos de pago ---
require_once __DIR__ . '/../../patrones/MetodosPago.php';

use Patrones\ProcesadorPagoFactory;

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
        $precio_original = floatval($producto['precio_original'] ?? $producto['precio']);
        $precio = floatval($producto['precio']); // Este ya incluye el descuento aplicado
        $descuento = floatval($producto['descuento'] ?? 0);
        $cantidad = intval($producto['cantidad']);
        $subtotal = isset($producto['subtotal']) ? floatval($producto['subtotal']) : $precio * $cantidad;
        
        // Registrar para depuración
        error_log("Producto {$key}: {$producto['nombre']}, Precio original: {$precio_original}, Descuento: {$descuento}, Precio con descuento: {$precio}, Cantidad: {$cantidad}, Subtotal: {$subtotal}");
    }
    
    // Asegurarnos de que el monto es un número
    error_log("Total calculado para el pago: {$monto}");
    
    $metodo = $_POST['metodo_pago'] ?? '';
    $datosPago = [];
    
    // Validar que el método de pago sea válido
    if (!ProcesadorPagoFactory::esMetodoValido($metodo)) {
        echo '<div class="alert alert-danger">Método de pago no válido</div>';
        exit;
    }
    
    if ($metodo === 'tarjeta') {
        $datosPago = [
            'nombre_titular' => $_POST['nombre_tarjeta'] ?? '',
            'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
            'expiracion' => $_POST['fecha_expiracion'] ?? '', // Corregido: usar fecha_expiracion del formulario
            'cvv' => $_POST['cvv'] ?? ''
        ];
        
        // Debug: mostrar los datos recibidos
        error_log("Datos de tarjeta recibidos:");
        error_log("- nombre_tarjeta: " . ($_POST['nombre_tarjeta'] ?? 'vacío'));
        error_log("- numero_tarjeta: " . ($_POST['numero_tarjeta'] ?? 'vacío'));
        error_log("- fecha_expiracion: " . ($_POST['fecha_expiracion'] ?? 'vacío'));
        error_log("- cvv: " . ($_POST['cvv'] ?? 'vacío'));
        
    } elseif ($metodo === 'yappy') {
        $datosPago = [
            'telefono' => $_POST['telefono_yappy'] ?? ''
        ];
    }
    
    // Crear el procesador usando el Factory
    $procesador = ProcesadorPagoFactory::crear($metodo);
    
    if (!$procesador) {
        echo '<div class="alert alert-danger">Error al inicializar el procesador de pago</div>';
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
            
            // Limpiar completamente el carrito en sesión y todas las variables temporales
            $_SESSION['carrito'] = []; 
            unset($_SESSION['carrito']);
            
            // Limpiar también los datos temporales de checkout
            unset($_SESSION['checkout_active']);
            unset($_SESSION['checkout_form_backup']);
            unset($_SESSION['payment_temp_data']);
            unset($_SESSION['shipping_temp_data']);
            unset($_SESSION['checkout_entry_time']);
            
            error_log("Carrito y datos de checkout completamente limpiados tras pago exitoso");
            
            // Redireccionar directamente a la página de finalización con limpieza completa del frontend
            echo '<script>
                console.log("Pago completado exitosamente - Limpiando todos los datos del carrito");
                
                // Limpiar TODOS los datos del carrito en localStorage
                localStorage.removeItem("artesanoDigital_cart");
                localStorage.removeItem("carrito_items");
                localStorage.removeItem("carrito_temporal");
                localStorage.removeItem("checkout_data");
                localStorage.removeItem("cart_count");
                localStorage.removeItem("cart_total");
                
                // Limpiar TODOS los datos del carrito en sessionStorage
                sessionStorage.removeItem("artesanoDigital_cart");
                sessionStorage.removeItem("carrito_items");
                sessionStorage.removeItem("en_checkout");
                sessionStorage.removeItem("checkout_form_data");
                sessionStorage.removeItem("payment_data");
                sessionStorage.removeItem("shipping_data");
                sessionStorage.removeItem("carrito_needs_reload");
                sessionStorage.removeItem("checkout_timestamp");
                
                // Función mejorada para limpiar cookies relacionadas con el carrito
                function deleteCartCookies() {
                    var cookies = document.cookie.split(";");
                    for (var i = 0; i < cookies.length; i++) {
                        var cookie = cookies[i].trim();
                        var cookieName = cookie.split("=")[0];
                        if (cookieName.indexOf("carrito") >= 0 || 
                            cookieName.indexOf("cart") >= 0 || 
                            cookieName.indexOf("checkout") >= 0) {
                            // Limpiar cookie en diferentes paths posibles
                            document.cookie = cookieName + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
                            document.cookie = cookieName + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/artesanoDigital";
                            document.cookie = cookieName + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/artesanoDigital/";
                            console.log("Cookie limpiada:", cookieName);
                        }
                    }
                }
                deleteCartCookies();
                
                // Limpiar cualquier evento listener relacionado con el carrito
                if (window.carritoEventListeners) {
                    window.carritoEventListeners.forEach(function(listener) {
                        if (listener.element && listener.event && listener.handler) {
                            listener.element.removeEventListener(listener.event, listener.handler);
                        }
                    });
                    window.carritoEventListeners = [];
                }
                
                // Resetear contadores visuales del carrito si existen
                var cartBadges = document.querySelectorAll(".cart-badge, .cart-count, #cart-count");
                cartBadges.forEach(function(badge) {
                    badge.textContent = "0";
                    badge.style.display = "none";
                });
                
                // Resetear elementos del carrito si existen
                var cartElements = document.querySelectorAll(".cart-items, #cart-items, .carrito-contenido");
                cartElements.forEach(function(element) {
                    element.innerHTML = "<p>Tu carrito está vacío</p>";
                });
                
                console.log("Limpieza completa del carrito finalizada");
                
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

<div class="checkout-container" id="checkout-container">
  <div class="checkout-header">
    <h2>Finalizar compra</h2>
    <p>Complete la información para realizar su pedido</p>
  </div>
  
  <!-- Layout de dos columnas moderno -->
  <div class="checkout-layout">
    <!-- Columna izquierda - Formularios -->
    <div class="checkout-forms">
      <div class="forms-container">
        <form id="checkout-form" method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
          <input type="hidden" name="finalizar_compra" value="1">
          
          <div id="cart-message-container"></div>
            
            <!-- SECCIÓN 1: INFORMACIÓN DE ENVÍO -->
            <div class="form-section">
              <h3>Información de envío</h3>
              
              <div class="form-grid">
                <div class="form-group">
                  <label for="nombre">Nombre completo <span class="required">*</span></label>
                  <input type="text" id="nombre" name="nombre" class="form-control" 
                         value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                  <label for="telefono">Teléfono <span class="required">*</span></label>
                  <input type="tel" id="telefono" name="telefono" class="form-control" 
                         value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group full-width">
                  <label for="direccion">Dirección completa <span class="required">*</span></label>
                  <textarea id="direccion" name="direccion" class="form-control" rows="3" 
                            placeholder="Ingrese su dirección completa de entrega" required><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                  <label for="ciudad">Ciudad <span class="required">*</span></label>
                  <select id="ciudad" name="ciudad" class="form-control" required>
                    <option value="">Seleccione ciudad</option>
                    <option value="La Chorrera" <?php echo ($usuario['ciudad'] ?? '') === 'La Chorrera' ? 'selected' : ''; ?>>La Chorrera</option>
                    <option value="Arraiján" <?php echo ($usuario['ciudad'] ?? '') === 'Arraiján' ? 'selected' : ''; ?>>Arraiján</option>
                    <option value="Capira" <?php echo ($usuario['ciudad'] ?? '') === 'Capira' ? 'selected' : ''; ?>>Capira</option>
                    <option value="Chame" <?php echo ($usuario['ciudad'] ?? '') === 'Chame' ? 'selected' : ''; ?>>Chame</option>
                    <option value="San Carlos" <?php echo ($usuario['ciudad'] ?? '') === 'San Carlos' ? 'selected' : ''; ?>>San Carlos</option>
                    <option value="Ciudad de Panamá" <?php echo ($usuario['ciudad'] ?? '') === 'Ciudad de Panamá' ? 'selected' : ''; ?>>Ciudad de Panamá</option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label for="codigo_postal">Código postal</label>
                  <input type="text" id="codigo_postal" name="codigo_postal" class="form-control" 
                         value="<?php echo htmlspecialchars($usuario['codigo_postal'] ?? ''); ?>">
                </div>
              </div>
            </div>
            
            <!-- SECCIÓN 2: MÉTODO DE PAGO -->
            <div class="form-section">
              <h3>Método de pago</h3>
              
              <div class="payment-methods">
                <div class="payment-option">
                  <input type="radio" id="yappy" name="metodo_pago" value="yappy" checked>
                  <label for="yappy" class="payment-label">
                    <div class="payment-info">
                      <div class="payment-icon">
                        <img src="/artesanoDigital/public/yappy1.png" alt="Yappy" class="payment-icon-img" onerror="this.src='/artesanoDigital/public/yappy.png'">
                      </div>
                      <div class="payment-details">
                        <strong>Yappy</strong>
                        <p class="payment-desc">Pago móvil rápido y seguro</p>
                      </div>
                    </div>
                  </label>
                </div>
                
                <div class="payment-option">
                  <input type="radio" id="tarjeta" name="metodo_pago" value="tarjeta">
                  <label for="tarjeta" class="payment-label">
                    <div class="payment-info">
                      <div class="payment-icon">
                        <img src="/artesanoDigital/public/credit.png" alt="Tarjeta" class="payment-icon-img">
                      </div>
                      <div class="payment-details">
                        <strong>Tarjeta de Crédito/Débito</strong>
                        <p class="payment-desc">Visa, Mastercard y otras tarjetas</p>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
              
              <!-- Formulario para Yappy (visible por defecto) -->
              <div id="yappy-form" class="payment-form">
                <div class="form-group">
                  <label for="telefono_yappy">Número de teléfono registrado en Yappy</label>
                  <input type="text" id="telefono_yappy" name="telefono_yappy" class="form-control" 
                         value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" 
                         placeholder="6XXX-XXXX">
                  <small class="form-text text-muted">Recibirás una notificación en Yappy para completar el pago.</small>
                </div>
              </div>
              
              <!-- Formulario para tarjeta (oculto por defecto) -->
              <div id="tarjeta-form" class="payment-form" style="display: none;">
                <div class="form-grid">
                  <div class="form-group full-width">
                    <label for="nombre_tarjeta">Nombre en la tarjeta</label>
                    <input type="text" id="nombre_tarjeta" name="nombre_tarjeta" class="form-control" 
                           placeholder="Nombre del titular">
                  </div>
                  
                  <div class="form-group full-width">
                    <label for="numero_tarjeta">Número de tarjeta</label>
                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" class="form-control" 
                           placeholder="1234 5678 9012 3456" maxlength="19">
                  </div>
                  
                  <div class="form-group">
                    <label for="fecha_expiracion">Fecha de expiración</label>
                    <input type="text" id="fecha_expiracion" name="fecha_expiracion" class="form-control" 
                           placeholder="MM/AA" maxlength="5">
                  </div>
                  
                  <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" class="form-control" 
                           placeholder="123" maxlength="4">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- SECCIÓN 3: TÉRMINOS Y CONDICIONES -->
            <div class="form-section">
              <div class="terms-section">
                <label class="checkbox-container">
                  <input type="checkbox" id="acepto_terminos" name="acepto_terminos" required>
                  <span class="checkmark"></span>
                  Acepto los <a href="#" target="_blank">términos y condiciones</a> y la <a href="#" target="_blank">política de privacidad</a>
                </label>
              </div>
            </div>
          </form>
        </div>
      </div>
    
      <!-- Columna derecha - Resumen del pedido (fijo) -->
      <div class="order-summary">
        <div class="summary-card">
          <h3>Resumen del pedido</h3>
          
          <div class="products-list" id="checkout-products-list">
            <?php if (!empty($productos)): ?>
              <?php 
              $subtotal = 0;
              foreach ($productos as $item): 
                $precio_original = floatval($item['precio_original'] ?? $item['precio']);
                $precio_final = floatval($item['precio']);
                $descuento = floatval($item['descuento'] ?? 0);
                $cantidad = intval($item['cantidad']);
                $itemTotal = $precio_final * $cantidad;
                $subtotal += $itemTotal;
              ?>
              <div class="product-item" data-product-id="<?php echo $item['id_producto']; ?>">
                <div class="product-info">
                  <img src="<?php echo htmlspecialchars($item['imagen'] ?? '/artesanoDigital/public/placeholder.jpg'); ?>" 
                       alt="<?php echo htmlspecialchars($item['nombre']); ?>" class="product-image">
                  <div class="product-details">
                    <h5><?php echo htmlspecialchars($item['nombre']); ?></h5>
                    <?php if ($descuento > 0): ?>
                      <div class="pricing-info">
                        <span class="original-price">B/. <?php echo number_format($precio_original, 2); ?></span>
                        <span class="final-price">B/. <?php echo number_format($precio_final, 2); ?></span>
                        <span class="discount-badge">-<?php echo number_format($descuento, 2); ?></span>
                      </div>
                    <?php else: ?>
                      <p class="product-price">B/. <?php echo number_format($precio_final, 2); ?></p>
                    <?php endif; ?>
                    <p class="product-quantity">Cantidad: <?php echo $cantidad; ?></p>
                  </div>
                </div>
                <div class="item-total">
                  B/. <?php echo number_format($itemTotal, 2); ?>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-cart">
                <p>No hay productos en el carrito</p>
                <a href="/artesanoDigital" class="btn btn-primary">Seguir comprando</a>
              </div>
            <?php endif; ?>
          </div>
          
          <?php if (!empty($productos)): ?>
          <div class="price-breakdown">
            <div class="price-line">
              <span>Subtotal:</span>
              <span id="checkout-subtotal">B/. <?php echo number_format($subtotal, 2); ?></span>
            </div>
            
            <?php 
            $descuentoTotal = 0;
            foreach ($productos as $item):
              if (!empty($item['descuento']) && $item['descuento'] > 0):
                $descuentoItem = floatval($item['descuento']) * intval($item['cantidad']);
                $descuentoTotal += $descuentoItem;
              endif;
            endforeach;
            
            if ($descuentoTotal > 0): ?>
            <div class="price-line discount">
              <span>Descuento:</span>
              <span id="checkout-discount">-B/. <?php echo number_format($descuentoTotal, 2); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="price-line shipping">
              <span>Envío:</span>
              <span>Gratis</span>
            </div>
            
            <div class="price-line total">
              <span>Total:</span>
              <span id="checkout-total">B/. <?php echo number_format($subtotal - $descuentoTotal, 2); ?></span>
            </div>
          </div>
          
          <!-- Botón de continuar -->
          <button type="submit" form="checkout-form" class="btn-confirm-order">
            Finalizar compra
          </button>
          <?php endif; ?>
          
          <div class="security-badges">
            <div class="badge-item">
              <i class="fas fa-shield-alt"></i>
              <span>Pago 100% seguro</span>
            </div>
            <div class="badge-item">
              <i class="fas fa-truck"></i>
              <span>Envío gratuito</span>
            </div>
            <div class="badge-item">
              <i class="fas fa-undo"></i>
              <span>Devolución fácil</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<style>
/* Reset y configuración base */
* {
  box-sizing: border-box;
}

body {
  overflow-x: hidden;
  margin: 0;
  padding: 0;
}

.checkout-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  box-sizing: border-box;
  overflow-x: hidden;
  width: 100%;
}

/* Loading Overlay */
.checkout-loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  transition: opacity 0.3s ease;
}

.checkout-loading-overlay.hidden {
  opacity: 0;
  pointer-events: none;
}

.checkout-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: checkout-spin 1s linear infinite;
  margin-bottom: 20px;
}

.checkout-loading-text {
  font-size: 16px;
  color: #6b7280;
  font-weight: 500;
  text-align: center;
  margin: 0;
}

.checkout-loading-subtext {
  font-size: 14px;
  color: #9ca3af;
  margin-top: 8px;
  text-align: center;
}

@keyframes checkout-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Ocultar contenido principal mientras carga */
.checkout-container.loading .checkout-layout {
  opacity: 0.3;
  pointer-events: none;
}

.checkout-header {
  margin-bottom: 30px;
  text-align: center;
}

.checkout-header h2 {
  color: #2c3e50;
  font-weight: 600;
  margin: 0;
}

.checkout-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 30px;
  align-items: start;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* Columna izquierda - Formularios */
.checkout-forms {
  width: 100%;
  max-width: 100%;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  box-sizing: border-box;
}

.forms-container {
  width: 100%;
  max-width: 100%;
  padding: 0;
  box-sizing: border-box;
}

.form-section {
  margin-bottom: 0;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.form-section h3 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #3b82f6;
  display: inline-block;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.form-group {
  display: flex;
  flex-direction: column;
  width: 100%;
  box-sizing: border-box;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
  font-size: 14px;
}

.required {
  color: #ef4444;
}

.form-control {
  padding: 12px 16px;
  border: 2px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
  background: #ffffff;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control:invalid {
  border-color: #ef4444;
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
  max-width: 100%;
}

/* Métodos de pago */
.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
}

.payment-option {
  position: relative;
}

.payment-option input[type="radio"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.payment-label {
  display: block;
  padding: 16px 20px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #ffffff;
}

.payment-option input[type="radio"]:checked + .payment-label {
  border-color: #3b82f6;
  background: #eff6ff;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.payment-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.payment-icon {
  width: 40px;
  height: 40px;
  background: #f3f4f6;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #6b7280;
  position: relative;
}

.payment-icon-img {
  width: 28px;
  height: 28px;
  object-fit: contain;
  transition: all 0.2s ease;
  border-radius: 4px;
  max-width: 100%;
  max-height: 100%;
}

/* Fallback si la imagen no carga */
.payment-icon-img:not([src]),
.payment-icon-img[src=""] {
  display: none;
}

.payment-icon-img:not([src])::after,
.payment-icon-img[src=""]::after {
  content: "💳";
  font-size: 20px;
  display: block;
}


.payment-details strong {
  color: #1f2937;
  font-size: 16px;
}

.payment-desc {
  color: #6b7280;
  font-size: 13px;
  margin: 4px 0 0 0;
}

.payment-form {
  margin-top: 20px;
  padding: 20px;
  background: #f9fafb;
  border-radius: 8px;
}

.form-text {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
}

/* Términos y condiciones */
.terms-section {
  background: #f8fafc;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  margin-bottom: 0;
}

.checkbox-container {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  cursor: pointer;
  font-size: 14px;
  line-height: 1.5;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.checkbox-container input[type="checkbox"] {
  margin: 0;
  width: 18px;
  height: 18px;
  accent-color: #3b82f6;
  flex-shrink: 0;
}

.checkbox-container a {
  color: #3b82f6;
  text-decoration: none;
}

.checkbox-container a:hover {
  text-decoration: underline;
}

/* Columna derecha - Resumen del pedido */
.order-summary {
  position: sticky;
  top: 20px;
  height: fit-content;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.summary-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

.summary-card h3 {
  color: #1f2937;
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.products-list {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 20px;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 16px 0;
  border-bottom: 1px solid #f3f4f6;
}

.product-item:last-child {
  border-bottom: none;
}

.product-info {
  display: flex;
  gap: 12px;
  flex: 1;
}

.product-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.product-details h5 {
  font-size: 14px;
  font-weight: 500;
  color: #1f2937;
  margin: 0 0 4px 0;
  line-height: 1.3;
}

.pricing-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.original-price {
  font-size: 12px;
  color: #9ca3af;
  text-decoration: line-through;
}

.final-price {
  font-size: 13px;
  color: #059669;
  font-weight: 500;
}

.discount-badge {
  font-size: 11px;
  background: #fee2e2;
  color: #dc2626;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 500;
}

.product-price {
  font-size: 13px;
  color: #1f2937;
  font-weight: 500;
  margin: 0;
}

.product-quantity {
  font-size: 12px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.item-total {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
}

.price-breakdown {
  border-top: 1px solid #e5e7eb;
  padding-top: 16px;
  margin-top: 16px;
}

.price-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 14px;
}

.price-line.discount {
  color: #059669;
}

.price-line.shipping {
  color: #6b7280;
}

.price-line.total {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
  margin-top: 8px;
}

/* Submit section */
.submit-section {
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.btn-confirm-order {
  width: 100%;
  max-width: 100%;
  background: #3b82f6;
  color: white;
  border: none;
  padding: 16px 24px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  box-sizing: border-box;

}

.btn-confirm-order:hover {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.btn-confirm-order:active {
  transform: translateY(0);
}

.btn-confirm-order .total-amount {
  font-weight: 700;
  font-size: 18px;
}

.security-badges {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

.badge-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 12px;
  color: #6b7280;
}

.badge-item i {
  color: #059669;
  width: 16px;
}

.empty-cart {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.empty-cart .btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  text-decoration: none;
  display: inline-block;
  margin-top: 16px;
}

/* Responsive */
@media (max-width: 1024px) {
  .checkout-layout {
    grid-template-columns: 1fr;
    gap: 25px;
  }
  
  .order-summary {
    position: static;
    order: -1;
    width: 100%;
    max-width: 100%;
  }
  
  .checkout-forms {
    width: 100%;
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .checkout-container {
    padding: 15px;
    margin: 0;
    width: 100%;
    max-width: 100%;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .form-section {
    padding: 20px 15px;
  }
  
  .summary-card {
    padding: 20px 15px;
  }
  
  .checkout-layout {
    gap: 20px;
  }
}

/* Estados de validación */
.form-control.is-invalid {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-control.is-valid {
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

/* Animaciones */
.form-section {
  animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loading state */
.btn-confirm-order:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

.btn-confirm-order.loading {
  position: relative;
  color: transparent;
}

.btn-confirm-order.loading::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 20px;
  height: 20px;
  border: 2px solid #ffffff;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: translate(-50%, -50%) rotate(0deg); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Alert messages */
#cart-message-container {
  margin-bottom: 20px;
}

.alert {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  border: 1px solid;
}

.alert-success {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #166534;
}

.alert-error {
  background: #fef2f2;
  border-color: #fecaca;
  color: #dc2626;
}

.alert-warning {
  background: #fffbeb;
  border-color: #fed7aa;
  color: #92400e;
}
</style>

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
  
  console.log("Inicializando nuevo checkout moderno");
  
  // Variables del formulario
  const form = document.getElementById('checkout-form');
  const paymentMethods = document.querySelectorAll('input[name="metodo_pago"]');
  const yappyForm = document.getElementById('yappy-form');
  const tarjetaForm = document.getElementById('tarjeta-form');
  const confirmButton = document.querySelector('.btn-confirm-order');
  const cartMessageContainer = document.getElementById('cart-message-container');
  
  // Manejo de métodos de pago
  if (paymentMethods.length > 0) {
    console.log("Configurando listeners para métodos de pago");
    paymentMethods.forEach(method => {
      method.addEventListener('change', function() {
        console.log("Cambiando método de pago a:", this.value);
        
        // Ocultar todos los formularios de pago
        if (yappyForm) {
          yappyForm.style.display = 'none';
          console.log("Ocultando formulario Yappy");
        }
        if (tarjetaForm) {
          tarjetaForm.style.display = 'none';  
          console.log("Ocultando formulario tarjeta");
        }
        
        // Mostrar el formulario correspondiente
        if (this.value === 'yappy' && yappyForm) {
          yappyForm.style.display = 'block';
          console.log("Mostrando formulario Yappy");
        } else if (this.value === 'tarjeta' && tarjetaForm) {
          tarjetaForm.style.display = 'block';
          console.log("Mostrando formulario tarjeta");
        }
      });
    });
    
    // Establecer el estado inicial basado en el método seleccionado
    const selectedMethod = document.querySelector('input[name="metodo_pago"]:checked');
    if (selectedMethod) {
      console.log("Método seleccionado inicialmente:", selectedMethod.value);
      if (selectedMethod.value === 'yappy' && yappyForm) {
        yappyForm.style.display = 'block';
        if (tarjetaForm) tarjetaForm.style.display = 'none';
      } else if (selectedMethod.value === 'tarjeta' && tarjetaForm) {
        tarjetaForm.style.display = 'block';
        if (yappyForm) yappyForm.style.display = 'none';
      }
    }
  } else {
    console.error("No se encontraron métodos de pago");
  }
  
  // Validación de formulario en tiempo real
  const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
  requiredFields.forEach(field => {
    field.addEventListener('blur', function() {
      validateField(this);
    });
  });
  
  function validateField(field) {
    if (field.checkValidity()) {
      field.classList.remove('is-invalid');
      field.classList.add('is-valid');
    } else {
      field.classList.remove('is-valid');
      field.classList.add('is-invalid');
    }
  }
  
  // Formateo de campos específicos
  const telefonoYappy = document.getElementById('telefono_yappy');
  if (telefonoYappy) {
    telefonoYappy.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');
      if (value.length >= 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 8);
      }
      this.value = value;
    });
  }
  
  const numeroTarjeta = document.getElementById('numero_tarjeta');
  if (numeroTarjeta) {
    numeroTarjeta.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');
      value = value.replace(/(.{4})/g, '$1 ').trim();
      this.value = value;
    });
  }
  
  const fechaExpiracion = document.getElementById('fecha_expiracion');
  if (fechaExpiracion) {
    fechaExpiracion.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      this.value = value;
    });
  }
  
  const cvv = document.getElementById('cvv');
  if (cvv) {
    cvv.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });
  }
  
  // Manejo del envío del formulario
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar formulario
    if (!form.checkValidity()) {
      showMessage('Por favor, complete todos los campos requeridos.', 'error');
      // Marcar campos inválidos
      requiredFields.forEach(field => {
        if (!field.checkValidity()) {
          field.classList.add('is-invalid');
        }
      });
      return;
    }
    
    // Validar método de pago específico
    const selectedPayment = document.querySelector('input[name="metodo_pago"]:checked').value;
    if (selectedPayment === 'tarjeta') {
      const tarjetaFields = [
        { id: 'nombre_tarjeta', name: 'Nombre en la tarjeta' },
        { id: 'numero_tarjeta', name: 'Número de tarjeta' },
        { id: 'fecha_expiracion', name: 'Fecha de expiración' },
        { id: 'cvv', name: 'CVV' }
      ];
      let tarjetaValid = true;
      let camposFaltantes = [];
      
      tarjetaFields.forEach(fieldInfo => {
        const field = document.getElementById(fieldInfo.id);
        if (!field || !field.value.trim()) {
          if (field) {
            field.classList.add('is-invalid');
          }
          tarjetaValid = false;
          camposFaltantes.push(fieldInfo.name);
        } else {
          // Validaciones específicas
          if (fieldInfo.id === 'numero_tarjeta') {
            const numeroLimpio = field.value.replace(/\s+/g, '');
            if (numeroLimpio.length < 13 || numeroLimpio.length > 19) {
              field.classList.add('is-invalid');
              tarjetaValid = false;
              camposFaltantes.push('Número de tarjeta válido (13-19 dígitos)');
            }
          } else if (fieldInfo.id === 'fecha_expiracion') {
            if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(field.value)) {
              field.classList.add('is-invalid');
              tarjetaValid = false;
              camposFaltantes.push('Fecha de expiración válida (MM/AA)');
            }
          } else if (fieldInfo.id === 'cvv') {
            if (!/^\d{3,4}$/.test(field.value)) {
              field.classList.add('is-invalid');
              tarjetaValid = false;
              camposFaltantes.push('CVV válido (3 o 4 dígitos)');
            }
          }
        }
      });
      
      if (!tarjetaValid) {
        const mensaje = camposFaltantes.length > 0 
          ? `Por favor, complete o corrija: ${camposFaltantes.join(', ')}`
          : 'Por favor, complete la información de la tarjeta.';
        showMessage(mensaje, 'error');
        return;
      }
      
      // Log para debugging
      console.log('Validación de tarjeta exitosa, enviando datos:', {
        nombre_tarjeta: document.getElementById('nombre_tarjeta').value,
        numero_tarjeta: document.getElementById('numero_tarjeta').value,
        fecha_expiracion: document.getElementById('fecha_expiracion').value,
        cvv: document.getElementById('cvv').value
      });
    }
    
    // Validar términos y condiciones
    const terminos = document.getElementById('acepto_terminos');
    if (!terminos.checked) {
      showMessage('Debe aceptar los términos y condiciones.', 'error');
      return;
    }
    
    // Mostrar estado de carga
    confirmButton.disabled = true;
    confirmButton.classList.add('loading');
    confirmButton.innerHTML = 'Procesando...';
    
    // Enviar formulario
    form.submit();
  });
  
  // Función para mostrar mensajes
  function showMessage(message, type = 'info') {
    cartMessageContainer.innerHTML = `
      <div class="alert alert-${type === 'error' ? 'error' : type}">
        ${message}
      </div>
    `;
    
    // Auto-hide después de 5 segundos
    setTimeout(() => {
      cartMessageContainer.innerHTML = '';
    }, 5000);
  }
  
  // Sincronización de subtotales
  function syncSubtotals() {
    // Obtener totales del servidor y localStorage
    const serverSubtotal = document.getElementById('checkout-subtotal');
    const localCart = localStorage.getItem('artesanoDigital_cart');
    
    if (localCart && serverSubtotal) {
      try {
        const cart = JSON.parse(localCart);
        let localTotal = 0;
        
        if (cart.productos) {
          cart.productos.forEach(item => {
            const precio = parseFloat(item.precio || item.price || 0);
            const cantidad = parseInt(item.cantidad || item.quantity || 1);
            localTotal += precio * cantidad;
          });
        }
        
        console.log('Total servidor:', serverSubtotal.textContent);
        console.log('Total localStorage:', localTotal);
        
        // Si hay diferencia significativa, mostrar aviso
        const serverTotal = parseFloat(serverSubtotal.textContent.replace(/[^\d.]/g, ''));
        if (Math.abs(serverTotal - localTotal) > 0.01) {
          showMessage('Los totales están siendo sincronizados...', 'warning');
        }
      } catch (e) {
        console.error('Error sincronizando totales:', e);
      }
    }
  }
  
  // Ejecutar sincronización de subtotales
  syncSubtotals();
  
  // Auto-completar ciudad basado en usuario
  const ciudadSelect = document.getElementById('ciudad');
  if (ciudadSelect && ciudadSelect.value) {
    // Ya está pre-seleccionada, no hacer nada
  } else if (ciudadSelect) {
    // Si no hay ciudad, seleccionar La Chorrera por defecto
    ciudadSelect.value = 'La Chorrera';
  }
  
  console.log("Checkout moderno inicializado correctamente");
});
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
  
  // Configurar cleanup antes de salir de la página
  configurarCleanupAntesSalida();
  
  // Inicializar el checkout
  inicializarCheckout();
  
  hideLoading();
  console.log("Inicialización del checkout completada");
});

// Función para configurar cleanup antes de salir de la página
function configurarCleanupAntesSalida() {
  // Limpiar cuando se cierre la página o se navegue hacia otra
  window.addEventListener('beforeunload', function() {
    vaciarCarritoCompleto();
  });
  
  // Limpiar al cambiar de página (para navegación SPA si existe)
  window.addEventListener('pagehide', function() {
    vaciarCarritoCompleto();
  });
  
  // Limpiar si se navega hacia atrás
  window.addEventListener('popstate', function() {
    vaciarCarritoCompleto();
  });
}

// También podemos limpiar si el usuario navega usando JavaScript
const originalPushState = history.pushState;
const originalReplaceState = history.replaceState;

history.pushState = function() {
  vaciarCarritoCompleto();
  return originalPushState.apply(history, arguments);
};

history.replaceState = function() {
  vaciarCarritoCompleto();
  return originalReplaceState.apply(history, arguments);
};

// Función para vaciar completamente el carrito
function vaciarCarritoCompleto() {
  // Limpiar localStorage
  localStorage.removeItem('carrito');
  localStorage.removeItem('carritoActualizado');
  localStorage.removeItem('ultimaActualizacionCarrito');
  
  // Limpiar sessionStorage
  sessionStorage.removeItem('carrito');
  sessionStorage.removeItem('carritoActualizado');
  sessionStorage.removeItem('ultimaActualizacionCarrito');
  
  // Enviar petición al servidor para limpiar el carrito PHP
  fetch('<?php echo $_SERVER['REQUEST_URI']; ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=vaciar_carrito',
    keepalive: true // Para que funcione incluso si la página se está cerrando
  }).catch(function(error) {
    console.log('Error limpiando carrito:', error);
  });
}
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
