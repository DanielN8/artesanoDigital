/**
 * Sistema de Notificaciones
 * Responsabilidad: Manejo de notificaciones toast y alertas
 */

// Sistema de notificaciones toast
const NotificacionesToast = {
    container: null,

    init() {
        // Crear contenedor si no existe
        if (!this.container) {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'toast-container';
                document.body.appendChild(this.container);
            }
        }
    },

    mostrar(mensaje, tipo = 'info', duracion = 5000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${tipo}`;
        toast.innerHTML = `
            <div class="toast-contenido">
                <span class="toast-mensaje">${mensaje}</span>
                <button class="toast-cerrar" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        `;

        this.container.appendChild(toast);

        // Mostrar toast con animación
        setTimeout(() => toast.classList.add('toast-mostrar'), 100);

        // Auto-cerrar después del tiempo especificado
        setTimeout(() => {
            toast.classList.remove('toast-mostrar');
            setTimeout(() => toast.remove(), 300);
        }, duracion);
    },

    exito(mensaje) {
        this.mostrar(mensaje, 'exito');
    },

    error(mensaje) {
        this.mostrar(mensaje, 'error');
    },

    advertencia(mensaje) {
        this.mostrar(mensaje, 'advertencia');
    },

    info(mensaje) {
        this.mostrar(mensaje, 'info');
    }
};

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    NotificacionesToast.init();
});

// Exportar para uso global
window.Toast = NotificacionesToast;
