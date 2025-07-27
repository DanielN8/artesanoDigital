<?php 
// Variables para el layout
$titulo = 'Iniciar Sesión - Artesano Digital';
$descripcion = 'Accede a tu cuenta en Artesano Digital';

// Iniciar captura de contenido
ob_start(); 
?>
<link rel="stylesheet" href="/artesanoDigital/assets/css/auth.css">
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Iniciar Sesión</h1>
            <p>Accede a tu cuenta para continuar explorando</p>
        </div>
        
        <form class="auth-form" id="formLogin" action="/artesanoDigital/login" method="POST" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                
                <div class="form-grupo">
                    <label for="email" class="form-label">
                        <span>Correo Electrónico</span>
                        <span style="color: var(--color-error);">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="ejemplo@correo.com"
                        required
                    >
                    <div class="form-error" id="error-email"></div>
                </div>
                
                <div class="form-grupo">
                    <label for="password" class="form-label">
                        <span>Contraseña</span>
                        <span style="color: var(--color-error);">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Tu contraseña"
                        required
                    >
                    <div class="form-error" id="error-password"></div>
                </div>
                
                <div class="form-grupo">
                    <label class="checkbox-container">
                        <input type="checkbox" name="recordar" id="recordar">
                        <span class="checkmark"></span>
                        <span>Recordar mi sesión</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full" id="btnLogin">
                    <span>Iniciar Sesión</span>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="/artesanoDigital/registro">Regístrate aquí</a></p>
                <p><a href="/artesanoDigital/recuperar-password">¿Olvidaste tu contraseña?</a></p>
            </div>
        </div>
    </div>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formLogin');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const btnLogin = document.getElementById('btnLogin');

    // Validación en tiempo real
    function validarCampo(campo, validaciones) {
        const errorDiv = document.getElementById(`error-${campo.name}`);
        let esValido = true;
        let mensaje = '';

        for (const validacion of validaciones) {
            if (!validacion.test(campo.value)) {
                esValido = false;
                mensaje = validacion.mensaje;
                break;
            }
        }

        if (errorDiv) {
            errorDiv.textContent = mensaje;
            errorDiv.style.display = mensaje ? 'block' : 'none';
        }

        campo.classList.toggle('invalido', !esValido);
        return esValido;
    }

    // Validaciones para email
    emailInput.addEventListener('blur', function() {
        validarCampo(this, [
            {
                test: (valor) => valor.trim() !== '',
                mensaje: 'El correo electrónico es requerido'
            },
            {
                test: (valor) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor),
                mensaje: 'Ingresa un correo electrónico válido'
            }
        ]);
    });

    // Validaciones para contraseña
    passwordInput.addEventListener('blur', function() {
        validarCampo(this, [
            {
                test: (valor) => valor.trim() !== '',
                mensaje: 'La contraseña es requerida'
            }
        ]);
    });

    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar todos los campos
        let formularioValido = true;
        
        if (!validarCampo(emailInput, [
            { test: (valor) => valor.trim() !== '', mensaje: 'El correo es requerido' },
            { test: (valor) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor), mensaje: 'Email inválido' }
        ])) formularioValido = false;
        
        if (!validarCampo(passwordInput, [
            { test: (valor) => valor.trim() !== '', mensaje: 'La contraseña es requerida' }
        ])) formularioValido = false;
        
        if (formularioValido) {
            // Mostrar estado de carga
            btnLogin.classList.add('loading');
            btnLogin.disabled = true;
            
            // Enviar formulario
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirigir al dashboard o página principal
                    window.location.href = data.redirect || '/artesanoDigital/dashboard';
                } else {
                    // Mostrar errores del servidor
                    if (data.errors) {
                        Object.keys(data.errors).forEach(campo => {
                            const errorDiv = document.getElementById(`error-${campo}`);
                            if (errorDiv) {
                                errorDiv.textContent = data.errors[campo];
                                errorDiv.style.display = 'block';
                            }
                        });
                    } else {
                        // Error general
                        alert(data.message || 'Error al iniciar sesión');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar el inicio de sesión. Inténtalo de nuevo.');
            })
            .finally(() => {
                btnLogin.classList.remove('loading');
                btnLogin.disabled = false;
            });
        }
    });

    // Efectos visuales mejorados
    const inputs = form.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    // Autocompletar email si viene de registro
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('registro') === 'exitoso') {
        // Mostrar mensaje de éxito
        const authHeader = document.querySelector('.auth-header');
        const successMsg = document.createElement('div');
        successMsg.style.cssText = `
            background: var(--color-exito);
            color: white;
            padding: var(--espaciado-md);
            border-radius: var(--radio-md);
            margin-bottom: var(--espaciado-lg);
            text-align: center;
        `;
        successMsg.textContent = '¡Registro exitoso! Ya puedes iniciar sesión.';
        authHeader.appendChild(successMsg);
        
        // Remover el parámetro de la URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>
