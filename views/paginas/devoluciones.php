<?php 
// Variables para el layout
$titulo = 'Política de Devoluciones - Artesano Digital';
$descripcion = 'Información sobre devoluciones y reembolsos';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="devoluciones-contenido">
        <header class="pagina-header">
            <h1>Política de Devoluciones</h1>
            <p class="lead">Tu satisfacción es nuestra prioridad</p>
        </header>
        
        <div class="devoluciones-texto">
            <section>
                <h2>1. Período de Devolución</h2>
                <p>Aceptamos devoluciones dentro de los 14 días posteriores a la recepción del producto, siempre que el artículo esté en su estado original.</p>
            </section>
            
            <section>
                <h2>2. Condiciones para Devoluciones</h2>
                <ul>
                    <li>El producto debe estar sin usar y en su embalaje original</li>
                    <li>Debe incluir todos los accesorios y documentación</li>
                    <li>Los productos personalizados no son elegibles para devolución</li>
                </ul>
            </section>
            
            <section>
                <h2>3. Proceso de Devolución</h2>
                <ol>
                    <li>Contacta nuestro servicio al cliente</li>
                    <li>Envía el producto a la dirección proporcionada</li>
                    <li>Una vez recibido, procesaremos tu reembolso</li>
                </ol>
            </section>
            
            <section>
                <h2>4. Costos de Envío</h2>
                <p>Los costos de envío de devolución corren por cuenta del cliente, excepto en casos de productos defectuosos o errores de nuestra parte.</p>
            </section>
            
            <section>
                <h2>5. Reembolsos</h2>
                <p>Los reembolsos se procesarán al método de pago original dentro de 5-10 días hábiles después de recibir la devolución.</p>
            </section>
            
            <section>
                <h2>6. Productos Dañados</h2>
                <p>Si recibes un producto dañado, contáctanos inmediatamente con fotos del daño para resolver el problema rápidamente.</p>
            </section>
            
            <section>
                <h2>7. Contacto</h2>
                <p>Para iniciar una devolución, contáctanos en: <a href="mailto:devoluciones@artesanodigital.com">devoluciones@artesanodigital.com</a> o llama al +507 6000-0000</p>
            </section>
        </div>
    </div>
</div>

<style>
.devoluciones-contenido {
    max-width: 800px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.devoluciones-texto {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-sm);
}

.devoluciones-texto section {
    margin-bottom: var(--espaciado-xl);
}

.devoluciones-texto h2 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-md);
}

.devoluciones-texto p, 
.devoluciones-texto li {
    line-height: 1.6;
    color: var(--color-texto-secundario);
}

.devoluciones-texto ul,
.devoluciones-texto ol {
    margin-left: var(--espaciado-lg);
    margin-top: var(--espaciado-sm);
}

.devoluciones-texto li {
    margin-bottom: var(--espaciado-sm);
}

.devoluciones-texto a {
    color: var(--color-marron);
    text-decoration: none;
}

.devoluciones-texto a:hover {
    text-decoration: underline;
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
