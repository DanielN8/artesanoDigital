<?php 
// Variables para el layout
$titulo = "Página no encontrada";
$descripcion = "La página que buscas no existe o ha sido movida";

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="error-404">
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>Lo sentimos, la página que buscas no existe o ha sido movida.</p>
        <a href="/artesanoDigital/" class="btn btn-primary">Volver al Inicio</a>
    </div>
</div>

<style>
.error-404 {
    text-align: center;
    padding: 4rem 0;
}

.error-404 h1 {
    font-size: 8rem;
    color: var(--color-marron);
    margin: 0;
}

.error-404 h2 {
    font-size: 2rem;
    margin: 1rem 0;
    color: var(--color-texto-principal);
}

.error-404 p {
    font-size: 1.1rem;
    color: var(--color-texto-secundario);
    margin-bottom: 2rem;
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
