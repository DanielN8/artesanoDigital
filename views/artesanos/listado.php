<?php 
// Variables para el layout
$titulo = 'Artesanos - Artesano Digital';
$descripcion = 'Conoce a los talentosos artesanos de Panamá Oeste';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="artesanos-contenido">
        <header class="pagina-header">
            <h1>Nuestros Artesanos</h1>
            <p class="lead">Conoce a los talentosos creadores detrás de cada obra de arte</p>
        </header>
        
        <div class="artesanos-grid">
            <!-- Aquí se cargarían los artesanos dinámicamente -->
            <div class="artesano-card">
                <img src="/artesanoDigital/public/placeholder-user.jpg" alt="Artesano" class="artesano-imagen">
                <div class="artesano-info">
                    <h3>María González</h3>
                    <p class="especialidad">Especialista en Molas</p>
                    <p class="ubicacion">La Chorrera, Panamá Oeste</p>
                    <a href="#" class="btn btn-outline btn-sm">Ver Productos</a>
                </div>
            </div>
            
            <div class="artesano-card">
                <img src="/artesanoDigital/public/placeholder-user.jpg" alt="Artesano" class="artesano-imagen">
                <div class="artesano-info">
                    <h3>Carlos Mendoza</h3>
                    <p class="especialidad">Cerámica Tradicional</p>
                    <p class="ubicacion">Capira, Panamá Oeste</p>
                    <a href="#" class="btn btn-outline btn-sm">Ver Productos</a>
                </div>
            </div>
        </div>
        
        <div class="convertirse-artesano">
            <h2>¿Eres Artesano?</h2>
            <p>Únete a nuestra plataforma y comparte tu talento con el mundo.</p>
            <a href="/artesanoDigital/registro" class="btn btn-primary">Registrarse como Artesano</a>
        </div>
    </div>
</div>

<style>
.artesanos-contenido {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.artesanos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--espaciado-xl);
    margin-bottom: var(--espaciado-2xl);
}

.artesano-card {
    background: var(--color-blanco-suave);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-md);
    overflow: hidden;
    transition: transform var(--transicion-normal);
}

.artesano-card:hover {
    transform: translateY(-5px);
}

.artesano-imagen {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.artesano-info {
    padding: var(--espaciado-lg);
    text-align: center;
}

.especialidad {
    color: var(--color-marron);
    font-weight: var(--peso-medio);
}

.ubicacion {
    color: var(--color-texto-secundario);
    font-size: 0.9rem;
    margin-bottom: var(--espaciado-md);
}

.convertirse-artesano {
    background: var(--color-crema-claro);
    padding: var(--espaciado-2xl);
    border-radius: var(--radio-lg);
    text-align: center;
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
