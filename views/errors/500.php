<?php 
$titulo = "Error del servidor";
include __DIR__ . '/../layouts/base.php'; 
?>

<main class="main-contenido">
    <div class="contenedor">
        <div class="error-500">
            <h1>500</h1>
            <h2>Error interno del servidor</h2>
            <p>Ha ocurrido un error inesperado. Por favor, inténtalo más tarde.</p>
            <a href="/artesanoDigital/" class="btn btn-primario">Volver al Inicio</a>
        </div>
    </div>
</main>

<style>
.error-500 {
    text-align: center;
    padding: 4rem 0;
}

.error-500 h1 {
    font-size: 8rem;
    color: #e74c3c;
    margin: 0;
}

.error-500 h2 {
    font-size: 2rem;
    margin: 1rem 0;
}

.error-500 p {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2rem;
}
</style>
