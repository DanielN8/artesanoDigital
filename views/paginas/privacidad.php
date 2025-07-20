<?php 
// Variables para el layout
$titulo = 'Política de Privacidad - Artesano Digital';
$descripcion = 'Política de privacidad y protección de datos';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="privacidad-contenido">
        <header class="pagina-header">
            <h1>Política de Privacidad</h1>
            <p class="lead">Última actualización: <?= date('d/m/Y') ?></p>
        </header>
        
        <div class="privacidad-texto">
            <section>
                <h2>1. Información que Recopilamos</h2>
                <p>Recopilamos información que nos proporcionas directamente, como cuando creas una cuenta, realizas una compra o nos contactas.</p>
            </section>
            
            <section>
                <h2>2. Cómo Utilizamos tu Información</h2>
                <p>Utilizamos tu información para proporcionar, mantener y mejorar nuestros servicios, procesar transacciones y comunicarnos contigo.</p>
            </section>
            
            <section>
                <h2>3. Compartir Información</h2>
                <p>No vendemos, alquilamos ni compartimos tu información personal con terceros sin tu consentimiento, excepto como se describe en esta política.</p>
            </section>
            
            <section>
                <h2>4. Seguridad de Datos</h2>
                <p>Implementamos medidas de seguridad para proteger tu información contra acceso no autorizado, alteración o destrucción.</p>
            </section>
            
            <section>
                <h2>5. Cookies</h2>
                <p>Utilizamos cookies para mejorar tu experiencia en nuestra plataforma. Puedes configurar tu navegador para rechazar cookies.</p>
            </section>
            
            <section>
                <h2>6. Tus Derechos</h2>
                <p>Tienes derecho a acceder, actualizar o eliminar tu información personal. Puedes ejercer estos derechos contactándonos.</p>
            </section>
            
            <section>
                <h2>7. Cambios a esta Política</h2>
                <p>Podemos actualizar esta política ocasionalmente. Te notificaremos sobre cambios significativos.</p>
            </section>
            
            <section>
                <h2>8. Contacto</h2>
                <p>Para preguntas sobre esta política, contáctanos en: <a href="mailto:privacidad@artesanodigital.com">privacidad@artesanodigital.com</a></p>
            </section>
        </div>
    </div>
</div>

<style>
.privacidad-contenido {
    max-width: 800px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.privacidad-texto {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-sm);
}

.privacidad-texto section {
    margin-bottom: var(--espaciado-xl);
}

.privacidad-texto h2 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-md);
}

.privacidad-texto p {
    line-height: 1.6;
    color: var(--color-texto-secundario);
}

.privacidad-texto a {
    color: var(--color-marron);
    text-decoration: none;
}

.privacidad-texto a:hover {
    text-decoration: underline;
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
