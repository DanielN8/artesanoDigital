<?php 
// Variables para el layout
$titulo = 'Nosotros - Artesano Digital';
$descripcion = 'Conoce más sobre Artesano Digital y nuestra misión';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="pagina-contenido">
        <header class="pagina-header">
            <h1>Sobre Nosotros</h1>
            <p class="lead">Conoce la historia detrás de Artesano Digital</p>
        </header>
        
        <section class="nosotros-historia">
            <div class="contenido-grid">
                <div class="texto-contenido">
                    <h2>Nuestra Historia</h2>
                    <p>
                        Artesano Digital nace de la pasión por preservar y promover las ricas tradiciones 
                        artesanales de Panamá Oeste. Reconocemos el talento excepcional de nuestros 
                        artesanos locales y la necesidad de crear un puente entre sus creaciones únicas 
                        y el mundo digital.
                    </p>
                    <p>
                        Desde nuestros inicios, hemos trabajado de la mano con artesanos de toda la región, 
                        proporcionándoles las herramientas digitales necesarias para hacer crecer sus 
                        negocios y llegar a nuevos mercados.
                    </p>
                </div>
                <div class="imagen-contenido">
                    <img src="/artesanoDigital/public/placeholder.jpg" alt="Artesanos trabajando" class="imagen-redonda">
                </div>
            </div>
        </section>
        
        <section class="nosotros-mision">
            <h2 class="texto-centro">Nuestra Misión</h2>
            <div class="mision-grid">
                <div class="mision-item">
                    <h3>🎨 Preservar Tradiciones</h3>
                    <p>Mantener vivas las técnicas ancestrales de la artesanía panameña</p>
                </div>
                <div class="mision-item">
                    <h3>🌐 Conectar Mundos</h3>
                    <p>Unir la tradición artesanal con las oportunidades del comercio digital</p>
                </div>
                <div class="mision-item">
                    <h3>💼 Empoderar Artesanos</h3>
                    <p>Brindar herramientas para el crecimiento económico de los artesanos</p>
                </div>
                <div class="mision-item">
                    <h3>🤝 Comercio Justo</h3>
                    <p>Garantizar precios justos y transparencia en todas las transacciones</p>
                </div>
            </div>
        </section>
        
        <section class="nosotros-valores">
            <h2>Nuestros Valores</h2>
            <div class="valores-lista">
                <div class="valor-item">
                    <h4>Autenticidad</h4>
                    <p>Cada producto es genuinamente artesanal, creado con técnicas tradicionales.</p>
                </div>
                <div class="valor-item">
                    <h4>Calidad</h4>
                    <p>Nos comprometemos con la excelencia en cada pieza que se vende en nuestra plataforma.</p>
                </div>
                <div class="valor-item">
                    <h4>Transparencia</h4>
                    <p>Información clara sobre el origen, materiales y proceso de creación de cada producto.</p>
                </div>
                <div class="valor-item">
                    <h4>Sostenibilidad</h4>
                    <p>Promovemos prácticas responsables con el medio ambiente y las comunidades locales.</p>
                </div>
            </div>
        </section>
        
        <section class="nosotros-equipo">
            <h2 class="texto-centro">Nuestro Equipo</h2>
            <p class="texto-centro">
                Somos un equipo apasionado por la tecnología y las tradiciones culturales, 
                trabajando juntos para crear un impacto positivo en la comunidad artesanal 
                de Panamá Oeste.
            </p>
        </section>
    </div>
</div>

<style>
/* Variables y reset */
:root {
  --color-primario: #8b4513;
  --color-secundario: #654321;
  --color-acento: #d4a574;
  --color-texto: #2c2c2c;
  --color-texto-claro: #666;
  --color-fondo: #faf8f5;
  --color-blanco: #ffffff;
  --espaciado-xs: 0.5rem;
  --espaciado-sm: 1rem;
  --espaciado-md: 1.5rem;
  --espaciado-lg: 2rem;
  --espaciado-xl: 3rem;
  --espaciado-2xl: 4rem;
  --radio-sm: 8px;
  --radio-md: 12px;
  --radio-lg: 16px;
  --sombra-suave: 0 4px 20px rgba(0, 0, 0, 0.08);
  --sombra-media: 0 8px 30px rgba(0, 0, 0, 0.12);
}

/* Contenedor principal */
.contenedor {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--espaciado-sm);
}

/* Contenido de página */
.pagina-contenido {
  padding: var(--espaciado-xl) 0;
}

/* Header de página */
.pagina-header {
  text-align: center;
  margin-bottom: var(--espaciado-2xl);
  padding: var(--espaciado-2xl) 0;
  background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);
  color: var(--color-blanco);
  border-radius: var(--radio-lg);
  box-shadow: var(--sombra-media);
}

.pagina-header h1 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: var(--espaciado-sm);
  letter-spacing: -0.02em;
}

.pagina-header .lead {
  font-size: 1.25rem;
  font-weight: 400;
  opacity: 0.95;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}

/* Secciones */
section {
  margin-bottom: var(--espaciado-2xl);
  padding: var(--espaciado-xl) 0;
}

section h2 {
  font-size: 2.25rem;
  font-weight: 600;
  color: var(--color-primario);
  margin-bottom: var(--espaciado-lg);
  text-align: center;
  position: relative;
}

section h2::after {
  content: '';
  position: absolute;
  bottom: -var(--espaciado-sm);
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: var(--color-acento);
  border-radius: 2px;
}

/* Historia */
.nosotros-historia {
  background: var(--color-blanco);
  border-radius: var(--radio-lg);
  padding: var(--espaciado-2xl);
  box-shadow: var(--sombra-suave);
  margin-bottom: var(--espaciado-2xl);
}

.contenido-grid {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: var(--espaciado-2xl);
  align-items: center;
}

.texto-contenido h2 {
  text-align: left;
  margin-bottom: var(--espaciado-lg);
}

.texto-contenido h2::after {
  left: 0;
  transform: none;
}

.texto-contenido p {
  font-size: 1.1rem;
  line-height: 1.8;
  color: var(--color-texto-claro);
  margin-bottom: var(--espaciado-md);
}

.imagen-contenido {
  display: flex;
  justify-content: center;
}

.imagen-redonda {
  width: 350px;
  height: 350px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: var(--sombra-media);
  border: 8px solid var(--color-blanco);
  transition: transform 0.3s ease;
}

.imagen-redonda:hover {
  transform: scale(1.05);
}

/* Misión */
.nosotros-mision {
  background: var(--color-fondo);
  border-radius: var(--radio-lg);
  padding: var(--espaciado-2xl);
}

.mision-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: var(--espaciado-lg);
  margin-top: var(--espaciado-xl);
}

.mision-item {
  background: var(--color-blanco);
  padding: var(--espaciado-xl);
  border-radius: var(--radio-md);
  text-align: center;
  box-shadow: var(--sombra-suave);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-top: 4px solid var(--color-acento);
}

.mision-item:hover {
  transform: translateY(-8px);
  box-shadow: var(--sombra-media);
}

.mision-item h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--color-primario);
  margin-bottom: var(--espaciado-md);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--espaciado-sm);
}

.mision-item p {
  font-size: 1.05rem;
  line-height: 1.6;
  color: var(--color-texto-claro);
}

/* Valores */
.nosotros-valores {
  background: var(--color-blanco);
  border-radius: var(--radio-lg);
  padding: var(--espaciado-2xl);
  box-shadow: var(--sombra-suave);
}

.valores-lista {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--espaciado-lg);
  margin-top: var(--espaciado-xl);
}

.valor-item {
  padding: var(--espaciado-lg);
  border-left: 4px solid var(--color-primario);
  background: var(--color-fondo);
  border-radius: 0 var(--radio-md) var(--radio-md) 0;
  transition: all 0.3s ease;
}

.valor-item:hover {
  background: var(--color-blanco);
  box-shadow: var(--sombra-suave);
  transform: translateX(8px);
}

.valor-item h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-primario);
  margin-bottom: var(--espaciado-sm);
}

.valor-item p {
  font-size: 1rem;
  line-height: 1.6;
  color: var(--color-texto-claro);
  margin: 0;
}

/* Equipo */
.nosotros-equipo {
  background: linear-gradient(135deg, var(--color-fondo) 0%, #f0ede8 100%);
  border-radius: var(--radio-lg);
  padding: var(--espaciado-2xl);
  text-align: center;
}

.nosotros-equipo h2 {
  color: var(--color-primario);
  margin-bottom: var(--espaciado-lg);
}

.nosotros-equipo p {
  font-size: 1.15rem;
  line-height: 1.7;
  color: var(--color-texto-claro);
  max-width: 800px;
  margin: 0 auto;
}

/* Botones - Estilo minimalista */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  border: 1px solid transparent;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.9rem;
  text-decoration: none;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 44px;
}

.btn-primary {
  background: var(--color-primario);
  color: #fff;
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
  color: #fff;
}

/* Utilidades */
.texto-centro {
  text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
  .contenido-grid {
    grid-template-columns: 1fr;
    gap: var(--espaciado-lg);
  }
  
  .imagen-redonda {
    width: 280px;
    height: 280px;
  }
  
  .pagina-header h1 {
    font-size: 2.25rem;
  }
  
  .pagina-header .lead {
    font-size: 1.1rem;
  }
  
  section h2 {
    font-size: 1.75rem;
  }
  
  .mision-grid,
  .valores-lista {
    grid-template-columns: 1fr;
    gap: var(--espaciado-md);
  }
  
  .nosotros-historia,
  .nosotros-mision,
  .nosotros-valores,
  .nosotros-equipo {
    padding: var(--espaciado-lg);
  }
  
  .contenedor {
    padding: 0 var(--espaciado-sm);
  }
}

@media (max-width: 480px) {
  .pagina-contenido {
    padding: var(--espaciado-lg) 0;
  }
  
  .pagina-header {
    padding: var(--espaciado-lg);
    margin-bottom: var(--espaciado-lg);
  }
  
  .pagina-header h1 {
    font-size: 1.875rem;
  }
  
  section {
    margin-bottom: var(--espaciado-lg);
    padding: var(--espaciado-lg) 0;
  }
  
  .imagen-redonda {
    width: 240px;
    height: 240px;
  }
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
