<?php 
// Variables para el layout
$titulo = 'Términos de Uso - Artesano Digital';
$descripcion = 'Términos y condiciones de uso de Artesano Digital';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="terminos-contenido">
        <header class="pagina-header">
            <h1>Términos de Uso</h1>
            <p class="lead">Última actualización: <?= date('d/m/Y') ?></p>
        </header>
        
        <div class="terminos-texto">
            <section>
                <h2>1. Aceptación de los Términos</h2>
                <p>Al acceder y utilizar Artesano Digital, aceptas cumplir con estos términos de uso y todas las leyes aplicables.</p>
            </section>
            
            <section>
                <h2>2. Descripción del Servicio</h2>
                <p>Artesano Digital es una plataforma de comercio electrónico que conecta artesanos de Panamá Oeste con compradores interesados en productos artesanales auténticos.</p>
            </section>
            
            <section>
                <h2>3. Registro y Cuentas de Usuario</h2>
                <p>Para utilizar ciertos servicios, debes crear una cuenta proporcionando información precisa y actualizada.</p>
            </section>
            
            <section>
                <h2>4. Responsabilidades del Usuario</h2>
                <p>Los usuarios se comprometen a utilizar la plataforma de manera responsable y respetando los derechos de otros usuarios.</p>
            </section>
            
            <section>
                <h2>5. Productos y Precios</h2>
                <p>Los precios y disponibilidad de productos están sujetos a cambios sin previo aviso.</p>
            </section>
            
            <section>
                <h2>6. Política de Devoluciones</h2>
                <p>Consulta nuestra <a href="/artesanoDigital/devoluciones">política de devoluciones</a> para más información.</p>
            </section>
            
            <section>
                <h2>7. Limitación de Responsabilidad</h2>
                <p>Artesano Digital no será responsable de daños indirectos, incidentales o consecuentes.</p>
            </section>
            
            <section>
                <h2>8. Modificaciones</h2>
                <p>Nos reservamos el derecho de modificar estos términos en cualquier momento.</p>
            </section>
            
            <section>
                <h2>9. Contacto</h2>
                <p>Para preguntas sobre estos términos, contáctanos en: <a href="mailto:legal@artesanodigital.com">legal@artesanodigital.com</a></p>
            </section>
        </div>
    </div>
</div>

<style>
.terminos-contenido {
    max-width: 800px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.terminos-texto {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-sm);
}

.terminos-texto section {
    margin-bottom: var(--espaciado-xl);
}

.terminos-texto h2 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-md);
}

.terminos-texto p {
    line-height: 1.6;
    color: var(--color-texto-secundario);
}

.terminos-texto a {
    color: var(--color-marron);
    text-decoration: none;
}

.terminos-texto a:hover {
    text-decoration: underline;
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
