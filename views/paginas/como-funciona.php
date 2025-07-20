<?php 
// Variables para el layout
$titulo = 'Cómo Funciona - Artesano Digital';
$descripcion = 'Descubre cómo funciona nuestra plataforma';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="como-funciona-contenido">
        <header class="pagina-header">
            <h1>Cómo Funciona</h1>
            <p class="lead">Descubre lo fácil que es comprar y vender en Artesano Digital</p>
        </header>
        
        <section class="proceso-compra">
            <h2>Para Compradores</h2>
            <div class="pasos-grid">
                <div class="paso">
                    <div class="paso-numero">1</div>
                    <h3>Explora</h3>
                    <p>Navega por nuestro catálogo de productos artesanales únicos</p>
                </div>
                <div class="paso">
                    <div class="paso-numero">2</div>
                    <h3>Selecciona</h3>
                    <p>Elige los productos que más te gusten y agrégalos al carrito</p>
                </div>
                <div class="paso">
                    <div class="paso-numero">3</div>
                    <h3>Compra</h3>
                    <p>Realiza tu pago de forma segura con múltiples métodos</p>
                </div>
                <div class="paso">
                    <div class="paso-numero">4</div>
                    <h3>Recibe</h3>
                    <p>Tu pedido llegará directamente a tu puerta</p>
                </div>
            </div>
        </section>
        
        <section class="proceso-venta">
            <h2>Para Artesanos</h2>
            <div class="pasos-grid">
                <div class="paso">
                    <div class="paso-numero">1</div>
                    <h3>Regístrate</h3>
                    <p>Crea tu perfil de artesano y completa la verificación</p>
                </div>
                <div class="paso">
                    <div class="paso-numero">2</div>
                    <h3>Publica</h3>
                    <p>Sube fotos y descripciones de tus productos artesanales</p>
                </div>
                <div class="paso">
                    <div class="paso-numero">3</div>
                    <h3>Vende</h3>
                    <p>Recibe pedidos y gestiona tus ventas desde tu panel</p>
                </div>
                <div class="paso">
                    <div class="paso-numero">4</div>
                    <h3>Cobra</h3>
                    <p>Recibe tus pagos de forma puntual y segura</p>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
.como-funciona-contenido {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.proceso-compra,
.proceso-venta {
    margin-bottom: var(--espaciado-2xl);
    background: var(--color-blanco-suave);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-sm);
}

.pasos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--espaciado-xl);
    margin-top: var(--espaciado-xl);
}

.paso {
    text-align: center;
    padding: var(--espaciado-lg);
}

.paso-numero {
    width: 50px;
    height: 50px;
    background: var(--color-marron);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: var(--peso-negrita);
    margin: 0 auto var(--espaciado-md);
}

.paso h3 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-sm);
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
