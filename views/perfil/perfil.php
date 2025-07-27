<?php 
// Variables para el layout
$titulo = $titulo ?? 'Mi Perfil - Artesano Digital';
$descripcion = 'Gestiona tu información personal y configuración de cuenta';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="perfil-container">
    <div class="contenedor">
        <div class="perfil-header">
            <h1 class="perfil-titulo">Mi Perfil</h1>
            <p class="perfil-subtitulo">Gestiona tu información personal y configuración de cuenta</p>
        </div>

        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['mensaje_error']) ?>
                <?php unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <div class="perfil-grid">
            <!-- Información Personal -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Información Personal</h2>
                    <p>Actualiza tu información básica</p>
                </div>

                <form class="perfil-form" method="POST" action="/artesanoDigital/perfil">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            class="form-input" 
                            value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input 
                            type="email" 
                            id="correo" 
                            name="correo" 
                            class="form-input" 
                            value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input 
                            type="tel" 
                            id="telefono" 
                            name="telefono" 
                            class="form-input" 
                            value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea 
                            id="direccion" 
                            name="direccion" 
                            class="form-textarea" 
                            rows="3"
                        ><?= htmlspecialchars($usuario['direccion'] ?? '') ?></textarea>
                    </div>

                    <!-- Información de cuenta (solo lectura) -->
                    <div class="info-divider">
                        <h3>Información de la Cuenta</h3>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Tipo de Usuario:</span>
                            <span class="info-value">
                                <?= ucfirst($usuario['tipo_usuario'] ?? 'No definido') ?>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Miembro desde:</span>
                            <span class="info-value">
                                <?= isset($usuario['fecha_registro']) ? date('d/m/Y', strtotime($usuario['fecha_registro'])) : 'No disponible' ?>
                            </span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            Actualizar Información
                        </button>
                    </div>
                </form>
            </div>

            <!-- Cambiar Contraseña -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Cambiar Contraseña</h2>
                    <p>Modifica tu contraseña de acceso</p>
                </div>

                <form class="perfil-form" method="POST" action="/artesanoDigital/perfil">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="form-group">
                        <label for="password_actual" class="form-label">Contraseña Actual</label>
                        <input 
                            type="password" 
                            id="password_actual" 
                            name="password_actual" 
                            class="form-input"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                        <input 
                            type="password" 
                            id="password_nuevo" 
                            name="password_nuevo" 
                            class="form-input"
                            minlength="6"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                        <input 
                            type="password" 
                            id="password_confirmar" 
                            name="password_confirmar" 
                            class="form-input"
                            minlength="6"
                        >
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">
                            Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Acciones Adicionales -->
        <div class="perfil-acciones">
            <?php if ($usuario['tipo_usuario'] === 'artesano'): ?>
                <a href="/artesanoDigital/dashboard/artesano" class="btn btn-outline">
                    <i class="material-icons">dashboard</i>
                    Ir a mi Panel de Artesano
                </a>
            <?php else: ?>
                <a href="/artesanoDigital/dashboard/cliente" class="btn btn-outline">
                    <i class="material-icons">shopping_bag</i>
                    Ver mis Pedidos
                </a>
            <?php endif; ?>
            
            <a href="/artesanoDigital/" class="btn btn-outline">
                <i class="material-icons">home</i>
                Volver al Inicio
            </a>
        </div>
    </div>
</div>

<style>
.perfil-container {
    min-height: calc(100vh - 80px);
    background: #f8f9fa;
    padding: 2rem 0;
}

.perfil-header {
    text-align: center;
    margin-bottom: 3rem;
}

.perfil-titulo {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.perfil-subtitulo {
    font-size: 1.1rem;
    color: #6c757d;
    margin: 0;
}

.perfil-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.perfil-seccion {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.seccion-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.seccion-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.seccion-header p {
    color: #6c757d;
    margin: 0;
    font-size: 0.95rem;
}

.perfil-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-input,
.form-textarea {
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
    background: #fff;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-actions {
    margin-top: 1rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-1px);
}

.btn-outline {
    background: transparent;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.info-grid {
    display: grid;
    gap: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #6b7280;
}

.info-value {
    color: #374151;
    font-weight: 500;
}

.estado-activo {
    color: #059669;
}

.estado-inactivo {
    color: #dc2626;
}

.perfil-acciones {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* Responsive */
@media (max-width: 768px) {
    .perfil-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .perfil-seccion {
        padding: 1.5rem;
    }
    
    .perfil-acciones {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario de cambio de contraseña
    const formPassword = document.querySelector('form[action*="perfil"]');
    if (formPassword) {
        formPassword.addEventListener('submit', function(e) {
            const passwordNuevo = document.getElementById('password_nuevo').value;
            const passwordConfirmar = document.getElementById('password_confirmar').value;
            
            if (passwordNuevo && passwordNuevo !== passwordConfirmar) {
                e.preventDefault();
                alert('Las contraseñas nuevas no coinciden');
                return false;
            }
            
            if (passwordNuevo && passwordNuevo.length < 6) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 6 caracteres');
                return false;
            }
        });
    }

    // Auto-ocultar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
