<?php 
// Variables para el layout
$titulo = 'Centro de Ayuda - Artesano Digital';
$descripcion = 'Encuentra respuestas a tus preguntas';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="ayuda-contenido">
        <header class="pagina-header">
            <h1>Centro de Ayuda</h1>
            <p class="lead">Encuentra respuestas a las preguntas más frecuentes</p>
        </header>
        
        <div class="ayuda-buscar">
            <input type="text" placeholder="Buscar en el centro de ayuda..." class="form-input">
            <button class="btn btn-primary">Buscar</button>
        </div>
        
        <div class="ayuda-categorias">
            <div class="categoria-ayuda">
                <h3>Compradores</h3>
                <ul>
                    <li><a href="#">¿Cómo realizar una compra?</a></li>
                    <li><a href="#">Métodos de pago disponibles</a></li>
                    <li><a href="#">¿Cómo rastrear mi pedido?</a></li>
                    <li><a href="#">Política de devoluciones</a></li>
                </ul>
            </div>
            
            <div class="categoria-ayuda">
                <h3>Artesanos</h3>
                <ul>
                    <li><a href="#">¿Cómo registrarme como artesano?</a></li>
                    <li><a href="#">Subir productos a la plataforma</a></li>
                    <li><a href="#">Gestionar mis ventas</a></li>
                    <li><a href="#">¿Cuándo recibo mis pagos?</a></li>
                </ul>
            </div>
            
            <div class="categoria-ayuda">
                <h3>Cuenta y Seguridad</h3>
                <ul>
                    <li><a href="#">Crear una cuenta</a></li>
                    <li><a href="#">Recuperar contraseña</a></li>
                    <li><a href="#">Actualizar información personal</a></li>
                    <li><a href="#">Configuración de privacidad</a></li>
                </ul>
            </div>
            
            <div class="categoria-ayuda">
                <h3>Envíos y Entregas</h3>
                <ul>
                    <li><a href="#">Tiempos de entrega</a></li>
                    <li><a href="#">Áreas de cobertura</a></li>
                    <li><a href="#">Costos de envío</a></li>
                    <li><a href="#">¿Qué hacer si no recibo mi pedido?</a></li>
                </ul>
            </div>
        </div>
        
        <div class="contacto-ayuda">
            <h2>¿No encuentras lo que buscas?</h2>
            <p>Nuestro equipo de soporte está aquí para ayudarte</p>
            <div class="contacto-opciones">
                <a href="/artesanoDigital/contacto" class="btn btn-primary">Contactar Soporte</a>
                <a href="mailto:ayuda@artesanodigital.com" class="btn btn-outline">Enviar Email</a>
            </div>
        </div>
    </div>
</div>

<style>
.ayuda-contenido {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.ayuda-buscar {
    display: flex;
    gap: var(--espaciado-md);
    margin-bottom: var(--espaciado-2xl);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.ayuda-buscar .form-input {
    flex: 1;
}

.ayuda-categorias {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--espaciado-xl);
    margin-bottom: var(--espaciado-2xl);
}

.categoria-ayuda {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-sm);
}

.categoria-ayuda h3 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-lg);
    font-size: 1.3rem;
}

.categoria-ayuda ul {
    list-style: none;
    padding: 0;
}

.categoria-ayuda li {
    margin-bottom: var(--espaciado-sm);
}

.categoria-ayuda a {
    color: var(--color-texto-principal);
    text-decoration: none;
    padding: var(--espaciado-sm) 0;
    display: block;
    border-bottom: 1px solid transparent;
    transition: all var(--transicion-normal);
}

.categoria-ayuda a:hover {
    color: var(--color-marron);
    border-bottom-color: var(--color-beige);
}

.contacto-ayuda {
    background: var(--color-crema-claro);
    padding: var(--espaciado-2xl);
    border-radius: var(--radio-lg);
    text-align: center;
}

.contacto-opciones {
    display: flex;
    gap: var(--espaciado-md);
    justify-content: center;
    flex-wrap: wrap;
    margin-top: var(--espaciado-lg);
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
