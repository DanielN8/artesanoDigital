<?php 
// home.php

// Variables para el layout
$titulo      = $titulo      ?? 'Artesano Digital – Panamá Oeste';
$descripcion = $descripcion ?? 'Plataforma de comercio electrónico para artesanos de Panamá Oeste';
$estilosAdicionales = ['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'];

// Iniciar captura de contenido
ob_start(); 
?>

<!-- Hero Section -->
<section class="hero" style="background: url('/artesanoDigital/public/hero.png') center center/cover no-repeat; min-height: 400px;">
  <div class="hero-contenido">
    <h1 class="hero-titulo">Artesano Digital</h1>
    <h2 class="hero-subtitulo">Panamá Oeste</h2>
    <p class="hero-descripcion">
      Descubre las mejores artesanías locales creadas por talentosos artesanos de Panamá Oeste.
      Productos únicos con historia y tradición.
    </p>
    <div class="hero-acciones">
      <a href="/artesanoDigital/productos" class="btn btn-primario">Explorar Productos</a>
      <a href="/artesanoDigital/registro"  class="btn btn-secundario">Únete como Artesano</a>
    </div>
  </div>
</section>

<!-- Productos Destacados -->
<section class="productos-destacados seccion">
  <div class="contenedor">
    <h2 class="seccion-titulo">Descubre los productos de nuestros artesanos</h2>
    <div class="productos-grid">
      <?php
      require_once __DIR__ . '/../config/Database.php';
      require_once __DIR__ . '/../patrones/ProductoDecorador.php';
      
      use Config\Database;
      use Patrones\ProductoBase;
      use Patrones\ProductoConDescuentoPorcentaje;
      use Patrones\ProductoConDescuentoMonto;
      
      $pdo = Database::obtenerInstancia()->obtenerConexion();
      $sql = "SELECT p.id_producto, p.nombre AS nombre_producto, p.descripcion, p.precio, p.imagen, 
                     p.stock, p.id_tienda, p.activo, t.nombre_tienda, u.nombre AS nombre_artesano,
                     p.descuento
              FROM productos p
              INNER JOIN tiendas t ON p.id_tienda = t.id_tienda
              INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
              WHERE p.activo = 1
              ORDER BY p.fecha_creacion DESC
              LIMIT 8";
              
      try {
          $stmt = $pdo->prepare($sql);
          $stmt->execute();
          $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          if ($productos) {
              foreach ($productos as $row) {
                  // Crear producto base
                  $productoBase = new ProductoBase($row);
                  
                  // Aplicar decorador de descuento si existe
                  $producto = $productoBase;
                  if (!empty($row['descuento']) && floatval($row['descuento']) > 0) {
                      $descuento = floatval($row['descuento']);
                      $producto = new ProductoConDescuentoMonto(
                          $productoBase,
                          $descuento,
                          'Oferta especial'
                      );
                  }
                  
                  // Obtener detalles del producto (con o sin descuento)
                  $detalles = $producto->obtenerDetalles();
                  $imagen = !empty($detalles['imagen']) ? "/artesanoDigital/" . $detalles['imagen'] : "/artesanoDigital/public/placeholder.jpg";
                  $tieneDescuento = !empty($row['descuento']) && floatval($row['descuento']) > 0;
                  
                  echo '<div class="producto-tarjeta">';
                  
                  // Badge de descuento
                  if ($tieneDescuento) {
                      $montoDescuento = floatval($row['descuento']);
                      $precioOriginal = floatval($row['precio']);
                      $porcentajeDescuento = round(($montoDescuento / $precioOriginal) * 100);
                      echo '<span class="badge-descuento">-' . $porcentajeDescuento . '%</span>';
                  }
                  
                  // Imagen y detalles
                  echo '<div class="producto-imagen-container">';
                  echo '  <img src="' . htmlspecialchars($imagen) . '" alt="' . htmlspecialchars($detalles['nombre']) . '" class="producto-imagen">';
                  echo '</div>';
                  
                  echo '<div class="producto-info">';
                  echo '  <h3>' . htmlspecialchars($row['nombre_producto']) . '</h3>';
                  
                  // Precios
                  if ($tieneDescuento) {
                      $precioOriginal = floatval($row['precio']);
                      $montoDescuento = floatval($row['descuento']);
                      $precioFinal = $precioOriginal - $montoDescuento;
                      echo '  <div class="producto-precios">';
                      echo '    <span class="precio-original">$' . number_format($precioOriginal, 2) . '</span>';
                      echo '    <span class="precio-descuento">$' . number_format($precioFinal, 2) . '</span>';
                      echo '  </div>';
                  } else {
                      echo '  <p class="producto-precio">$' . number_format($row['precio'], 2) . '</p>';
                  }
                  
                  echo '  <p class="producto-artesano">Por ' . htmlspecialchars($row['nombre_artesano']) . '</p>';
                  echo '</div>';
                  
                  // Botón de agregar al carrito en la parte inferior
                  echo '<div class="producto-acciones">';
                  echo '  <button class="btn-carrito-bottom" onclick="agregarAlCarrito(' . $detalles['id_producto'] . ')">';
                  echo '    <i class="fas fa-shopping-cart"></i> Agregar al Carrito';
                  echo '  </button>';
                  echo '</div>';
                  echo '</div>';
              }
          } else {
              echo '<p>No hay productos destacados disponibles.</p>';
          }
      } catch (Exception $e) {
          // Mostrar mensaje de error para depuración (en entorno de producción se debería ocultar)
          error_log("Error al cargar productos destacados: " . $e->getMessage());
          
          // Intentar con una consulta más simple como respaldo
          try {
              $sql = "SELECT p.id_producto, p.nombre AS nombre_producto, p.descripcion, p.precio, 
                         p.imagen, p.descuento, t.nombre_tienda, u.nombre AS nombre_artesano
                  FROM productos p
                  INNER JOIN tiendas t ON p.id_tienda = t.id_tienda
                  INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                  WHERE p.activo = 1
                  ORDER BY p.fecha_creacion DESC
                  LIMIT 8";
              
              $stmt = $pdo->prepare($sql);
              $stmt->execute();
              $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
              
              if ($productos) {
                  foreach ($productos as $row) {
                      $imagen = !empty($row['imagen']) ? "/artesanoDigital/" . $row['imagen'] : "/artesanoDigital/public/placeholder.jpg";
                      $tieneDescuento = !empty($row['descuento']) && floatval($row['descuento']) > 0;
                      
                      echo '<div class="producto-tarjeta">';
                      
                      // Badge de descuento
                      if ($tieneDescuento) {
                          $montoDescuento = floatval($row['descuento']);
                          $precioOriginal = floatval($row['precio']);
                          $porcentajeDescuento = round(($montoDescuento / $precioOriginal) * 100);
                          echo '<span class="badge-descuento">-' . $porcentajeDescuento . '%</span>';
                      }
                      
                      echo '<div class="producto-imagen-container">';
                      echo '  <img src="' . htmlspecialchars($imagen) . '" alt="' . htmlspecialchars($row['nombre_producto']) . '" class="producto-imagen">';
                      echo '</div>';
                      
                      echo '<div class="producto-info">';
                      echo '  <h3>' . htmlspecialchars($row['nombre_producto']) . '</h3>';
                      
                      // Precios
                      if ($tieneDescuento) {
                          $precioOriginal = floatval($row['precio']);
                          $montoDescuento = floatval($row['descuento']);
                          $precioFinal = $precioOriginal - $montoDescuento;
                          echo '  <div class="producto-precios">';
                          echo '    <span class="precio-original">$' . number_format($precioOriginal, 2) . '</span>';
                          echo '    <span class="precio-descuento">$' . number_format($precioFinal, 2) . '</span>';
                          echo '  </div>';
                      } else {
                          echo '  <p class="producto-precio">$' . number_format($row['precio'], 2) . '</p>';
                      }
                      
                      echo '  <p class="producto-artesano">Por ' . htmlspecialchars($row['nombre_artesano']) . '</p>';
                      echo '</div>';
                      
                      // Botón de agregar al carrito en la parte inferior
                      echo '<div class="producto-acciones">';
                      echo '  <button class="btn-carrito-bottom" onclick="agregarAlCarrito(' . $row['id_producto'] . ')">';
                      echo '    <i class="fas fa-shopping-cart"></i> Agregar al Carrito';
                      echo '  </button>';
                      echo '</div>';
                      echo '</div>';
                  }
              } else {
                  echo '<p>No hay productos destacados disponibles.</p>';
              }
          } catch (Exception $e) {
              echo '<p>Error al cargar productos: ' . $e->getMessage() . '</p>';
          }
      }
      ?>
    </div>
    <div class="seccion-accion">
      <a href="/artesanoDigital/productos" class="btn btn-outline">Ver Todos los Productos</a>
    </div>
  </div>
</section>

<!-- Sobre Nosotros -->
<section class="sobre-nosotros seccion" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 60px 0;">
  <div class="contenedor">
    <div class="sobre-contenido" style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; max-width: 1200px; margin: 0 auto;">
      
      <!-- Imagen -->
      <div class="sobre-imagen" style="order: 1;">
        <img src="/artesanoDigital/public/about.png" alt="Artesanos de Panamá Oeste" 
             style="width: 100%; height: auto; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); transform: rotate(-2deg); transition: transform 0.3s ease; opacity: 0.9;">
      </div>
      
      <!-- Contenido -->
      <div class="sobre-texto" style="order: 2;">
        <h2 class="sobre-titulo" style="font-size: 2.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 20px; line-height: 1.2;">
          Nuestra <span style="color: #e74c3c;">Misión</span>
        </h2>
        
        <p style="font-size: 1.1rem; color: #5a6c7d; line-height: 1.7; margin-bottom: 30px;">
          Creamos un espacio digital donde la tradición artesanal se encuentra con la innovación tecnológica, 
          conectando a los talentosos artesanos de Panamá Oeste con el mundo.
        </p>
        
        <div style="margin-bottom: 40px;">
          <h3 style="font-size: 1.3rem; color: #34495e; margin-bottom: 15px; font-weight: 600;">¿Por qué elegir Artesano Digital?</h3>
          <p style="color: #6c757d; line-height: 1.6;">
            Cada pieza cuenta una historia única. Apoyamos directamente a los artesanos locales, 
            preservando técnicas ancestrales mientras creamos oportunidades económicas sostenibles.
          </p>
        </div>
        
        <div class="stats-mini" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px;">
          <div style="text-align: center; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
            <div style="font-size: 2rem; font-weight: 700; color: #e74c3c; margin-bottom: 5px;">50+</div>
            <div style="font-size: 0.9rem; color: #6c757d;">Artesanos Activos</div>
          </div>
          <div style="text-align: center; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
            <div style="font-size: 2rem; font-weight: 700; color: #e74c3c; margin-bottom: 5px;">200+</div>
            <div style="font-size: 0.9rem; color: #6c757d;">Productos Únicos</div>
          </div>
          <div style="text-align: center; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
            <div style="font-size: 2rem; font-weight: 700; color: #e74c3c; margin-bottom: 5px;">100%</div>
            <div style="font-size: 0.9rem; color: #6c757d;">Hecho a Mano</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
// Capturar el contenido y llamar al layout base
$contenido = ob_get_clean(); 
include __DIR__ . '/layouts/base.php'; 
?>

<link rel="stylesheet" href="/artesanoDigital/assets/css/inicio.css">

<!-- Script específico -->
<script>
    function agregarAlCarrito(idProducto) {
    let cantidad = 1;
    // Obtener productos del DOM
    const tarjeta = document.querySelector('.producto-tarjeta button[onclick="agregarAlCarrito(' + idProducto + ')"]').closest('.producto-tarjeta');
    const nombre = tarjeta.querySelector('h3').textContent;
    
    // Verificar si tiene descuento para obtener el precio correcto
    let precio;
    const precioDescuento = tarjeta.querySelector('.precio-descuento');
    if (precioDescuento) {
        // Si tiene descuento, usamos el precio con descuento
        precio = parseFloat(precioDescuento.textContent.replace('$',''));
    } else {
        // Si no tiene descuento, usamos el precio normal
        precio = parseFloat(tarjeta.querySelector('.producto-precio').textContent.replace('$',''));
    }
    
    const imagen = tarjeta.querySelector('img').getAttribute('src');
    const artesano = tarjeta.querySelector('.producto-artesano').textContent.replace('Por ','');

    // Usar la clave específica para el usuario actual
    const usuarioId = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null' ?>;
    const carritoKey = usuarioId ? `carrito_${usuarioId}` : 'carrito_invitado';
    
    let carrito = JSON.parse(localStorage.getItem(carritoKey)) || [];
    const productoExistente = carrito.find(item => item.id === idProducto);
    if (productoExistente) {
      productoExistente.cantidad += cantidad;
    } else {
      carrito.push({
        id: idProducto,
        nombre: nombre,
        precio: precio,
        imagen: imagen,
        artesano: artesano,
        cantidad: cantidad
      });
    }
    // Guardar con la clave específica del usuario
    localStorage.setItem(carritoKey, JSON.stringify(carrito));
    // Mantener compatibilidad
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    actualizarContadorCarrito(carrito.reduce((total, item) => total + item.cantidad, 0));
    mostrarMensaje(`${nombre} agregado al carrito`, 'success');
    actualizarMiniCarrito();
  }  // Función para mostrar mensajes
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
