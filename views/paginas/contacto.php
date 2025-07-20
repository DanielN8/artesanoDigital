<?php 
// Variables para el layout
$titulo = 'Contacto - Artesano Digital';
$descripcion = 'Ponte en contacto con nosotros';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="contacto-contenido">
        <header class="pagina-header">
            <h1>Contáctanos</h1>
            <p class="lead">Estamos aquí para ayudarte. Ponte en contacto con nosotros.</p>
        </header>
        
        <div class="contacto-grid">
            <div class="contacto-info">
                <h2>Información de Contacto</h2>
                
                <div class="info-item">
                    <h3>📧 Correo Electrónico</h3>
                    <p>info@artesanodigital.com</p>
                    <p>soporte@artesanodigital.com</p>
                </div>
                
                <div class="info-item">
                    <h3>📞 Teléfono</h3>
                    <p>+507 6000-0000</p>
                    <p>Lunes a Viernes: 8:00 AM - 6:00 PM</p>
                    <p>Sábados: 9:00 AM - 2:00 PM</p>
                </div>
                
                <div class="info-item">
                    <h3>📍 Ubicación</h3>
                    <p>Panamá Oeste, Panamá</p>
                    <p>Zona de cobertura: Toda la provincia de Panamá Oeste</p>
                </div>
                
                <div class="info-item">
                    <h3>🌐 Redes Sociales</h3>
                    <div class="redes-contacto">
                        <a href="#" class="red-contacto">Facebook</a>
                        <a href="#" class="red-contacto">Instagram</a>
                        <a href="#" class="red-contacto">WhatsApp</a>
                    </div>
                </div>
            </div>
            
            <div class="contacto-formulario">
                <h2>Envíanos un Mensaje</h2>
                
                <form class="form-contacto" action="/artesanoDigital/contacto/enviar" method="POST">
                    <div class="form-grupo">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" class="form-input" required>
                    </div>
                    
                    <div class="form-grupo">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-grupo">
                        <label for="telefono" class="form-label">Teléfono (Opcional)</label>
                        <input type="tel" id="telefono" name="telefono" class="form-input">
                    </div>
                    
                    <div class="form-grupo">
                        <label for="asunto" class="form-label">Asunto</label>
                        <select id="asunto" name="asunto" class="form-input" required>
                            <option value="">Selecciona un asunto</option>
                            <option value="consulta_general">Consulta General</option>
                            <option value="soporte_tecnico">Soporte Técnico</option>
                            <option value="convertirse_artesano">Quiero ser Artesano</option>
                            <option value="problema_pedido">Problema con Pedido</option>
                            <option value="sugerencia">Sugerencia</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-grupo">
                        <label for="mensaje" class="form-label">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" class="form-textarea" rows="5" required 
                                  placeholder="Escribe tu mensaje aquí..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">
                        Enviar Mensaje
                    </button>
                </form>
            </div>
        </div>
        
        <section class="preguntas-frecuentes">
            <h2>Preguntas Frecuentes</h2>
            
            <div class="faq-item">
                <h3>¿Cómo puedo convertirme en artesano en la plataforma?</h3>
                <p>Puedes registrarte como artesano y seguir el proceso de verificación. Nuestro equipo te guiará paso a paso.</p>
            </div>
            
            <div class="faq-item">
                <h3>¿Cuáles son los métodos de pago disponibles?</h3>
                <p>Aceptamos tarjetas de crédito, débito, transferencias bancarias y otros métodos de pago locales.</p>
            </div>
            
            <div class="faq-item">
                <h3>¿Hacen envíos a toda Panamá?</h3>
                <p>Sí, realizamos envíos a nivel nacional con diferentes opciones de entrega.</p>
            </div>
            
            <div class="faq-item">
                <h3>¿Cómo puedo rastrear mi pedido?</h3>
                <p>Una vez realizada la compra, recibirás un código de seguimiento para rastrear tu pedido en tiempo real.</p>
            </div>
        </section>
    </div>
</div>

<style>
.contacto-contenido {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--espaciado-2xl) 0;
}

.contacto-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--espaciado-2xl);
    margin-bottom: var(--espaciado-2xl);
}

.contacto-info {
    background: var(--color-crema-claro);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
}

.contacto-formulario {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-md);
}

.info-item {
    margin-bottom: var(--espaciado-xl);
}

.info-item h3 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-md);
    font-size: 1.1rem;
}

.info-item p {
    color: var(--color-texto-secundario);
    margin-bottom: var(--espaciado-xs);
}

.redes-contacto {
    display: flex;
    gap: var(--espaciado-md);
    flex-wrap: wrap;
}

.red-contacto {
    display: inline-block;
    padding: var(--espaciado-sm) var(--espaciado-md);
    background: var(--color-marron);
    color: var(--color-blanco-suave);
    text-decoration: none;
    border-radius: var(--radio-md);
    transition: background-color var(--transicion-normal);
}

.red-contacto:hover {
    background: var(--color-marron-oscuro);
}

.form-contacto {
    display: flex;
    flex-direction: column;
    gap: var(--espaciado-lg);
}

.form-textarea {
    padding: var(--espaciado-md);
    border: 1px solid var(--color-beige);
    border-radius: var(--radio-md);
    font-family: inherit;
    font-size: 1rem;
    resize: vertical;
    min-height: 120px;
}

.form-textarea:focus {
    outline: none;
    border-color: var(--color-marron);
    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
}

.preguntas-frecuentes {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-xl);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-sm);
}

.faq-item {
    margin-bottom: var(--espaciado-lg);
    padding-bottom: var(--espaciado-lg);
    border-bottom: 1px solid var(--color-beige);
}

.faq-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.faq-item h3 {
    color: var(--color-marron);
    margin-bottom: var(--espaciado-sm);
}

.faq-item p {
    color: var(--color-texto-secundario);
}

@media (max-width: 768px) {
    .contacto-grid {
        grid-template-columns: 1fr;
        gap: var(--espaciado-xl);
    }
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
