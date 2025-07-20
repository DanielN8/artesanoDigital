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
<section class="hero">
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

<!-- Categorías Destacadas -->
<section class="categorias-destacadas seccion">
  <div class="contenedor">
    <h2 class="seccion-titulo">Categorías Populares</h2>
    <div class="categorias-grid">
      <div class="categoria-tarjeta">
        <div class="categoria-imagen-container">
          <img src="/artesanoDigital/public/placeholder.jpg" alt="Textiles" class="categoria-imagen">
          <div class="categoria-overlay">
            <a href="/artesanoDigital/productos?categoria=textiles" class="btn-categoria"><i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
        <div class="categoria-info">
          <h3>Textiles</h3>
          <p>Molas, huipiles y más</p>
        </div>
      </div>
      <div class="categoria-tarjeta">
        <div class="categoria-imagen-container">
          <img src="/artesanoDigital/public/placeholder.jpg" alt="Cerámica" class="categoria-imagen">
          <div class="categoria-overlay">
            <a href="/artesanoDigital/productos?categoria=ceramica" class="btn-categoria"><i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
        <div class="categoria-info">
          <h3>Cerámica</h3>
          <p>Vasijas y decoraciones</p>
        </div>
      </div>
      <div class="categoria-tarjeta">
        <div class="categoria-imagen-container">
          <img src="/artesanoDigital/public/placeholder.jpg" alt="Joyería" class="categoria-imagen">
          <div class="categoria-overlay">
            <a href="/artesanoDigital/productos?categoria=joyeria" class="btn-categoria"><i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
        <div class="categoria-info">
          <h3>Joyería</h3>
          <p>Accesorios únicos</p>
        </div>
      </div>
      <div class="categoria-tarjeta">
        <div class="categoria-imagen-container">
          <img src="/artesanoDigital/public/placeholder.jpg" alt="Madera" class="categoria-imagen">
          <div class="categoria-overlay">
            <a href="/artesanoDigital/productos?categoria=madera" class="btn-categoria"><i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
        <div class="categoria-info">
          <h3>Madera</h3>
          <p>Tallados y muebles</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Productos Destacados -->
<section class="productos-destacados seccion">
  <div class="contenedor">
    <h2 class="seccion-titulo">Productos Destacados</h2>
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
                  echo '  <div class="producto-overlay">';
                  echo '    <button class="btn-carrito" onclick="agregarAlCarrito(' . $detalles['id_producto'] . ')"><i class="fas fa-shopping-cart"></i></button>';
                  echo '  </div>';
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
                      echo '  <div class="producto-overlay">';
                      echo '    <button class="btn-carrito" onclick="agregarAlCarrito(' . $row['id_producto'] . ')"><i class="fas fa-shopping-cart"></i></button>';
                      echo '  </div>';
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
<section class="sobre-nosotros seccion">
  <div class="contenedor">
    <h2 class="sobre-titulo">Conectando <span class="highlight">Tradición</span> con <span class="highlight">Tecnología</span></h2>
    <div class="sobre-subtitulo">La esencia de lo hecho a mano en el mundo digital</div>
    
    <div class="sobre-contenido">
      <div class="sobre-texto">
        <p class="sobre-parrafo">
          Artesano Digital es el puente que conecta la rica tradición artesanal de Panamá Oeste con 
          compradores conscientes que valoran lo auténtico y único.
        </p>
        
        <div class="beneficios-grid">
          <div class="beneficio-item">
            <div class="beneficio-icono">✦</div>
            <div class="beneficio-texto">Piezas auténticas y únicas</div>
          </div>
          <div class="beneficio-item">
            <div class="beneficio-icono">✦</div>
            <div class="beneficio-texto">Apoyo directo a artesanos</div>
          </div>
          <div class="beneficio-item">
            <div class="beneficio-icono">✦</div>
            <div class="beneficio-texto">Preservación cultural</div>
          </div>
          <div class="beneficio-item">
            <div class="beneficio-icono">✦</div>
            <div class="beneficio-texto">Comercio justo</div>
          </div>
        </div>
      </div>
      <div class="sobre-imagen">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Artesano trabajando" class="imagen-elevada">
      </div>
    </div>
  </div>
</section>

<?php 
// Capturar el contenido y llamar al layout base
$contenido = ob_get_clean(); 
include __DIR__ . '/layouts/base.php'; 
?>

<!-- Estilos específicos de esta página -->
<style>
  /* Reset muy ligero */
  body { margin:0; background:#faf8f5; color:#333; font-family:'Inter', sans-serif; }
  .contenedor { max-width:1280px; margin:0 auto; padding:0 1rem; }
  .seccion { padding:3rem 0; }
  .seccion-titulo { text-align:center; font-size:2rem; margin-bottom:2rem; }

  /* Hero full-width */
  .hero {
    width:100%;
    background: linear-gradient(135deg, #357ab8, #4a90e2);
    color: #fff;
    text-align: center;
    padding: 5rem 1rem;
  }
  .hero-contenido { max-width:800px; margin:0 auto; }
  .hero-titulo    { font-size:3rem; margin-bottom:.5rem; }
  .hero-subtitulo { font-size:2rem; margin-bottom:1rem; opacity:.9; }
  .hero-descripcion {
    font-size:1.1rem; line-height:1.5; margin-bottom:2rem;
  }
  .hero-acciones { 
    display:flex;
    gap:1rem;
    justify-content:center;
    flex-wrap:wrap;
  }

  /* Botones */
  .btn {
    display:inline-block;
    padding:.75rem 1.5rem;
    border-radius:8px;
    font-weight:500;
    text-decoration:none;
    text-align:center;
  }
  .btn-primario {
    background:#fff;
    color:#357ab8;
  }
  .btn-primario:hover {
    background:rgba(255,255,255,.8);
  }
  .btn-secundario {
    background:transparent;
    border:2px solid #fff;
    color:#fff;
  }
  .btn-secundario:hover {
    background:rgba(255,255,255,.2);
  }
  .btn-outline {
    border:2px solid #357ab8;
    color:#357ab8;
    background:transparent;
  }
  .btn-outline:hover {
    background:#357ab8;
    color:#fff;
  }

  /* Categorías grid */
  .categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
  }
  
  /* Estilos para categorías */
  .categoria-imagen-container {
    position: relative;
    overflow: hidden;
    border-radius: 12px 12px 0 0;
  }
  
  .categoria-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.0);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease, background 0.3s ease;
  }
  
  .categoria-tarjeta:hover .categoria-imagen {
    transform: scale(1.05);
  }
  
  .categoria-tarjeta:hover .categoria-overlay {
    opacity: 1;
    background: rgba(0,0,0,0.3);
  }
  
  .btn-categoria {
    background: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #357ab8;
    text-decoration: none;
    transition: transform 0.2s ease;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  .btn-categoria:hover {
    transform: scale(1.1);
    background: #357ab8;
    color: white;
  }
  
  .categoria-info {
    padding: 1rem;
    text-align: center;
  }
  
  .categoria-info h3 {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
    font-weight: 600;
  }
  
  .categoria-info p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
  }
  
  /* Productos grid 4 columnas */
  .productos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
  }
  
  .categoria-tarjeta,
  .producto-tarjeta {
    position: relative;
    background:#fff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 10px 20px rgba(0,0,0,0.04);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .producto-tarjeta:hover {
    transform: translateY(-5px);
    box-shadow:0 15px 30px rgba(0,0,0,0.08);
  }
  
  /* Badge de descuento */
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
    box-shadow: 0 2px 4px rgba(255,59,48,0.3);
  }
  
  /* Contenedor de imagen con overlay */
  .producto-imagen-container {
    position: relative;
    overflow: hidden;
  }
  
  .categoria-imagen,
  .producto-imagen {
    width:100%;
    height:200px;
    object-fit:cover;
    transition: transform 0.3s ease;
  }
  
  .producto-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.0);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease, background 0.3s ease;
  }
  
  .producto-tarjeta:hover .producto-imagen {
    transform: scale(1.05);
  }
  
  .producto-tarjeta:hover .producto-overlay {
    opacity: 1;
    background: rgba(0,0,0,0.2);
  }
  
  /* Botón de carrito minimalista */
  .btn-carrito {
    background: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #357ab8;
    transition: transform 0.2s ease;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  .btn-carrito:hover {
    transform: scale(1.1);
    background: #357ab8;
    color: white;
  }
  
  /* Información del producto */
  .producto-info {
    padding: 1rem;
  }
  
  .producto-info h3 {
    margin: 0 0 0.5rem;
    font-size: 0.95rem;
    font-weight: 500;
    color: #333;
    height: 2.4rem;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }
  
  .producto-precios {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
  }
  
  .precio-original {
    color: #999;
    text-decoration: line-through;
    font-size: 0.85rem;
  }
  
  .precio-descuento {
    color: #FF3B30;
    font-weight: 600;
    font-size: 1.1rem;
  }
  
  .producto-precio {
    color: #357ab8;
    font-weight: 600;
    margin: 0 0 0.5rem;
    font-size: 1rem;
  }
  
  .producto-artesano {
    margin: 0;
    color: #666;
    font-size: 0.8rem;
  }

  .seccion-accion { text-align:center; margin-top:2rem; }

  /* Sobre Nosotros - Diseño Minimalista */
  .sobre-titulo {
    text-align: center;
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }
  .sobre-subtitulo {
    text-align: center;
    color: #666;
    margin-bottom: 3rem;
    font-size: 1.1rem;
  }
  .highlight {
    color: #357ab8;
  }
  .sobre-contenido {
    display: flex;
    flex-wrap: wrap;
    gap: 3rem;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    max-width: 1100px;
  }
  .sobre-texto { 
    flex: 1 1 400px;
    line-height: 1.6;
  }
  .sobre-parrafo {
    font-size: 1.1rem;
    margin-bottom: 2rem;
  }
  .beneficios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.5rem;
  }
  .beneficio-item {
    background: #fff;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .beneficio-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.08);
  }
  .beneficio-icono {
    color: #357ab8;
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
  }
  .beneficio-texto {
    font-weight: 500;
  }
  .sobre-imagen {
    flex: 0 1 450px;
  }
  .imagen-elevada {
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 16px 32px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
  }
  .imagen-elevada:hover {
    transform: scale(1.02);
  }

  /* Responsive */
  @media (min-width: 1200px) {
    .productos-grid {
      grid-template-columns: repeat(4, 1fr);
    }
    .categorias-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }
  
  @media (min-width: 768px) and (max-width: 1199px) {
    .productos-grid {
      grid-template-columns: repeat(3, 1fr);
    }
    .categorias-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }
  
  @media (min-width: 576px) and (max-width: 767px) {
    .productos-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .categorias-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .hero-titulo { font-size: 2.5rem; }
    .hero-subtitulo { font-size: 1.8rem; }
  }
  
  @media (max-width: 575px) {
    .productos-grid {
      grid-template-columns: 1fr;
    }
    .categorias-grid {
      grid-template-columns: 1fr;
    }
    .hero-titulo { font-size: 2rem; }
    .hero-subtitulo { font-size: 1.5rem; }
    .sobre-contenido { flex-direction: column; }
    .hero { padding: 3rem 1rem; }
  }
  
  /* Animaciones para elementos */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .producto-tarjeta, .categoria-tarjeta {
    animation: fadeIn 0.5s ease-out forwards;
  }
  
  .producto-tarjeta:nth-child(1), .categoria-tarjeta:nth-child(1) { animation-delay: 0.1s; }
  .producto-tarjeta:nth-child(2), .categoria-tarjeta:nth-child(2) { animation-delay: 0.2s; }
  .producto-tarjeta:nth-child(3), .categoria-tarjeta:nth-child(3) { animation-delay: 0.3s; }
  .producto-tarjeta:nth-child(4), .categoria-tarjeta:nth-child(4) { animation-delay: 0.4s; }
  .producto-tarjeta:nth-child(5) { animation-delay: 0.5s; }
  .producto-tarjeta:nth-child(6) { animation-delay: 0.6s; }
  .producto-tarjeta:nth-child(7) { animation-delay: 0.7s; }
  .producto-tarjeta:nth-child(8) { animation-delay: 0.8s; }
</style>

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
