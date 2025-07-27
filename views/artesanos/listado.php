<?php 
// Variables para el layout
$titulo = 'Artesanos - Artesano Digital';
$descripcion = 'Conoce a los talentosos artesanos de Panamá Oeste';

// Incluir conexión a la base de datos
require_once __DIR__ . '/../../config/Database.php';

use Config\Database;

// Obtener artesanos de la base de datos
try {
    $db = Database::obtenerInstancia();
    $conexion = $db->obtenerConexion();
    
    $stmt = $conexion->prepare("
        SELECT u.id_usuario, u.nombre, u.correo, u.telefono, u.direccion, u.fecha_registro,
               t.nombre_tienda, t.descripcion as descripcion_tienda, t.imagen_logo,
               COUNT(p.id_producto) as total_productos
        FROM usuarios u
        LEFT JOIN tiendas t ON u.id_usuario = t.id_usuario
        LEFT JOIN productos p ON t.id_tienda = p.id_tienda AND p.activo = 1
        WHERE u.tipo_usuario = 'artesano' AND u.activo = 1
        GROUP BY u.id_usuario, t.id_tienda
        ORDER BY u.fecha_registro DESC
    ");
    $stmt->execute();
    $artesanos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $artesanos = [];
    error_log("Error al cargar artesanos: " . $e->getMessage());
}

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="artesanos-contenido">
        <header class="pagina-header">
            <h1>Nuestros Artesanos</h1>
            <p class="lead">Descubre el talento y la tradición artesanal de Panamá Oeste</p>
        </header>
        
        <?php if (empty($artesanos)): ?>
            <div class="estado-vacio">
                <div class="vacio-icono">
                    <span class="material-icons">palette</span>
                </div>
                <h3>No hay artesanos registrados</h3>
                <p>Sé el primero en unirte a nuestra comunidad de artesanos</p>
                <a href="/artesanoDigital/registro" class="btn btn-primary">Registrarse como Artesano</a>
            </div>
        <?php else: ?>
            <div class="artesanos-grid">
                <?php foreach ($artesanos as $artesano): ?>
                    <div class="artesano-card">
                        <div class="artesano-avatar">
                            <?php if (!empty($artesano['imagen_logo'])): ?>
                                <img src="/artesanoDigital/uploads/logos/<?php echo htmlspecialchars($artesano['imagen_logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($artesano['nombre']); ?>" 
                                     class="artesano-imagen">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <span class="material-icons">person</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="artesano-info">
                            <h3 class="artesano-nombre"><?php echo htmlspecialchars($artesano['nombre']); ?></h3>
                            
                            <?php if (!empty($artesano['nombre_tienda'])): ?>
                                <p class="tienda-nombre"><?php echo htmlspecialchars($artesano['nombre_tienda']); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($artesano['descripcion_tienda'])): ?>
                                <p class="artesano-descripcion"><?php echo htmlspecialchars(substr($artesano['descripcion_tienda'], 0, 100)) . (strlen($artesano['descripcion_tienda']) > 100 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($artesano['direccion'])): ?>
                                <p class="artesano-ubicacion">
                                    <span class="material-icons">location_on</span>
                                    <?php echo htmlspecialchars($artesano['direccion']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="artesano-stats">
                                <span class="stat-item">
                                    <span class="material-icons">inventory</span>
                                    <?php echo $artesano['total_productos']; ?> productos
                                </span>
                                <span class="stat-item">
                                    <span class="material-icons">schedule</span>
                                    Desde <?php echo date('Y', strtotime($artesano['fecha_registro'])); ?>
                                </span>
                            </div>
                            
                            <div class="artesano-acciones">
                                <?php if ($artesano['total_productos'] > 0): ?>
                                    <a href="/artesanoDigital/productos?artesano=<?php echo $artesano['id_usuario']; ?>" 
                                       class="btn btn-outline btn-sm">Ver Productos</a>
                                <?php endif; ?>
                                
                                <?php if (!empty($artesano['telefono'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($artesano['telefono']); ?>" 
                                       class="btn btn-secondary btn-sm" title="Contactar">
                                        <span class="material-icons">phone</span>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($artesano['correo'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($artesano['correo']); ?>" 
                                       class="btn btn-secondary btn-sm" title="Enviar email">
                                        <span class="material-icons">email</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="convertirse-artesano">
            <div class="cta-content">
                <h2>¿Eres Artesano?</h2>
                <p>Únete a nuestra plataforma y comparte tu talento con el mundo. Crea tu tienda digital y llega a más clientes.</p>
                <a href="/artesanoDigital/registro" class="btn btn-primary">Registrarse como Artesano</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Variables y estilos para artesanos */
:root {
  --color-primario: #8b4513;
  --color-secundario: #654321;
  --color-acento: #d4a574;
  --color-texto: #2c2c2c;
  --color-texto-claro: #666666;
  --color-fondo: #faf8f5;
  --color-blanco: #ffffff;
  --color-gris-suave: #f8f9fa;
  --espaciado-xs: 0.5rem;
  --espaciado-sm: 1rem;
  --espaciado-md: 1.5rem;
  --espaciado-lg: 2rem;
  --espaciado-xl: 3rem;
  --espaciado-2xl: 4rem;
  --radio-sm: 6px;
  --radio-md: 12px;
  --radio-lg: 16px;
  --sombra-suave: 0 4px 20px rgba(0, 0, 0, 0.08);
  --sombra-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.contenedor {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--espaciado-sm);
}

.artesanos-contenido {
  padding: var(--espaciado-xl) 0;
}

/* Header de página */
.pagina-header {
  text-align: center;
  margin-bottom: var(--espaciado-2xl);
  padding: var(--espaciado-2xl) var(--espaciado-lg);
  background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);
  color: var(--color-blanco);
  border-radius: var(--radio-lg);
  box-shadow: var(--sombra-suave);
}

.pagina-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: var(--espaciado-sm);
  letter-spacing: -0.02em;
}

.pagina-header .lead {
  font-size: 1.1rem;
  font-weight: 400;
  opacity: 0.95;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}

/* Grid de artesanos */
.artesanos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: var(--espaciado-lg);
  margin-bottom: var(--espaciado-2xl);
}

/* Card de artesano */
.artesano-card {
  background: var(--color-blanco);
  border-radius: var(--radio-lg);
  padding: var(--espaciado-lg);
  box-shadow: var(--sombra-suave);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.artesano-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--color-primario), var(--color-acento));
}

.artesano-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--sombra-hover);
}

/* Avatar del artesano */
.artesano-avatar {
  margin-bottom: var(--espaciado-md);
  position: relative;
}

.artesano-imagen {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--color-gris-suave);
  transition: transform 0.3s ease;
}

.artesano-card:hover .artesano-imagen {
  transform: scale(1.05);
}

.avatar-placeholder {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: var(--color-gris-suave);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 4px solid var(--color-gris-suave);
}

.avatar-placeholder .material-icons {
  font-size: 3rem;
  color: var(--color-texto-claro);
}

/* Información del artesano */
.artesano-info {
  flex: 1;
  width: 100%;
}

.artesano-nombre {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-texto);
  margin-bottom: var(--espaciado-xs);
}

.tienda-nombre {
  font-size: 1rem;
  font-weight: 500;
  color: var(--color-primario);
  margin-bottom: var(--espaciado-sm);
}

.artesano-descripcion {
  font-size: 0.9rem;
  color: var(--color-texto-claro);
  line-height: 1.5;
  margin-bottom: var(--espaciado-md);
}

.artesano-ubicacion {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  font-size: 0.85rem;
  color: var(--color-texto-claro);
  margin-bottom: var(--espaciado-md);
}

.artesano-ubicacion .material-icons {
  font-size: 1rem;
}

/* Estadísticas del artesano */
.artesano-stats {
  display: flex;
  justify-content: space-around;
  margin-bottom: var(--espaciado-md);
  padding: var(--espaciado-sm) 0;
  border-top: 1px solid var(--color-gris-suave);
  border-bottom: 1px solid var(--color-gris-suave);
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.8rem;
  color: var(--color-texto-claro);
}

.stat-item .material-icons {
  font-size: 1rem;
  color: var(--color-acento);
}

/* Acciones del artesano */
.artesano-acciones {
  display: flex;
  gap: var(--espaciado-xs);
  justify-content: center;
  flex-wrap: wrap;
}

/* Botones - Estilo minimalista */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
  border-radius: var(--radio-sm);
  font-weight: 500;
  font-size: 0.85rem;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 36px;
}

.btn-primary {
  background: var(--color-primario);
  color: var(--color-blanco);
  border-color: var(--color-primario);
}

.btn-primary:hover {
  background: var(--color-secundario);
  border-color: var(--color-secundario);
}

.btn-outline {
  background: transparent;
  color: var(--color-primario);
  border-color: var(--color-primario);
}

.btn-outline:hover {
  background: var(--color-primario);
  color: var(--color-blanco);
}

.btn-secondary {
  background: var(--color-gris-suave);
  color: var(--color-texto);
  border-color: var(--color-gris-suave);
}

.btn-secondary:hover {
  background: var(--color-acento);
  border-color: var(--color-acento);
  color: var(--color-blanco);
}

.btn-sm {
  padding: 0.4rem 0.8rem;
  font-size: 0.8rem;
  min-height: 32px;
}

/* Estado vacío */
.estado-vacio {
  text-align: center;
  padding: var(--espaciado-2xl);
  background: var(--color-blanco);
  border-radius: var(--radio-lg);
  box-shadow: var(--sombra-suave);
}

.vacio-icono {
  margin-bottom: var(--espaciado-lg);
}

.vacio-icono .material-icons {
  font-size: 4rem;
  color: var(--color-acento);
}

.estado-vacio h3 {
  font-size: 1.5rem;
  color: var(--color-texto);
  margin-bottom: var(--espaciado-sm);
}

.estado-vacio p {
  color: var(--color-texto-claro);
  margin-bottom: var(--espaciado-lg);
  font-size: 1.1rem;
}

/* Call to action */
.convertirse-artesano {
  background: linear-gradient(135deg, var(--color-acento) 0%, var(--color-primario) 100%);
  border-radius: var(--radio-lg);
  padding: var(--espaciado-2xl);
  text-align: center;
  color: var(--color-blanco);
  box-shadow: var(--sombra-suave);
}

.cta-content h2 {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: var(--espaciado-md);
}

.cta-content p {
  font-size: 1.1rem;
  margin-bottom: var(--espaciado-lg);
  opacity: 0.95;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

.convertirse-artesano .btn-primary {
  background: var(--color-blanco);
  color: var(--color-primario);
  border-color: var(--color-blanco);
  font-size: 1rem;
  padding: 0.75rem 2rem;
}

.convertirse-artesano .btn-primary:hover {
  background: var(--color-gris-suave);
  border-color: var(--color-gris-suave);
}

/* Responsive */
@media (max-width: 768px) {
  .artesanos-grid {
    grid-template-columns: 1fr;
    gap: var(--espaciado-md);
  }
  
  .pagina-header {
    padding: var(--espaciado-lg) var(--espaciado-sm);
  }
  
  .pagina-header h1 {
    font-size: 2rem;
  }
  
  .artesano-card {
    padding: var(--espaciado-md);
  }
  
  .artesano-stats {
    flex-direction: column;
    gap: var(--espaciado-xs);
  }
  
  .convertirse-artesano {
    padding: var(--espaciado-lg);
  }
  
  .cta-content h2 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .artesano-acciones {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
