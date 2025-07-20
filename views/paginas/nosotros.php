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
.pagina-contenido {
    max-width: 800px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.pagina-header {
    text-align: center;
    margin-bottom: var(--espaciado-2xl);
    padding-bottom: var(--espaciado-xl);
    border-bottom: 1px solid var(--color-beige);
}

.pagina-header h1 {
    font-size: 2.5rem;
    color: var(--color-marron);
    margin-bottom: var(--espaciado-md);
}

.lead {
    font-size: 1.2rem;
    color: var(--color-texto-secundario);
    max-width: 600px;
    margin: 0 auto;
}

.contenido-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--espaciado-2xl);
    align-items: center;
    margin-bottom: var(--espaciado-2xl);
}

.nosotros-historia,
.nosotros-mision,
.nosotros-valores,
.nosotros-equipo {
    margin-bottom: var(--espaciado-2xl);
}

.mision-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--espaciado-lg);
    margin-top: var(--espaciado-xl);
}

.mision-item {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-lg);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-sm);
    text-align: center;
}

.mision-item h3 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-md);
}

.valores-lista {
    display: grid;
    gap: var(--espaciado-lg);
    margin-top: var(--espaciado-xl);
}

.valor-item {
    background: var(--color-crema-claro);
    padding: var(--espaciado-lg);
    border-radius: var(--radio-md);
    border-left: 4px solid var(--color-marron);
}

.valor-item h4 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-sm);
}

@media (max-width: 768px) {
    .contenido-grid {
        grid-template-columns: 1fr;
        gap: var(--espaciado-xl);
    }
    
    .mision-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
