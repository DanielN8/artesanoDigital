<?php 
// Variables para el layout
$titulo = 'Registro - Artesano Digital';
$descripcion = 'Crea tu cuenta en Artesano Digital';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Crear Cuenta</h1>
            <p>Únete a nuestra comunidad de artesanos y descubre productos únicos</p>
        </div>
        
        <form class="auth-form" id="formRegistro" action="/artesanoDigital/registro" method="POST" novalidate>
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                
                <div class="form-grupo">
                    <label for="nombre" class="form-label">
                        <span>Nombre Completo</span>
                        <span style="color: var(--color-error);">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        class="form-input" 
                        placeholder="Ingresa tu nombre completo"
                        required 
                        minlength="2"
                        maxlength="100"
                    >
                    <div class="form-error" id="error-nombre"></div>
                </div>
                
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
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        class="form-input" 
                        placeholder="+507 6000-0000"
                        pattern="[\+]?[0-9\s\-\(\)]+"
                    >
                    <div class="form-ayuda">Formato: +507 6000-0000 (opcional)</div>
                </div>
                
                <div class="form-grupo">
                    <label for="tipo_usuario" class="form-label">
                        <span>Tipo de Usuario</span>
                        <span style="color: var(--color-error);">*</span>
                    </label>
                    <select id="tipo_usuario" name="tipo_usuario" class="form-input" required>
                        <option value="">Selecciona una opción</option>
                        <option value="cliente">Cliente - Comprar productos artesanales</option>
                        <option value="artesano">Artesano - Vender mis creaciones</option>
                    </select>
                    <div class="form-error" id="error-tipo_usuario"></div>
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
                        placeholder="Mínimo 8 caracteres"
                        required 
                        minlength="8"
                    >
                    <div class="form-ayuda">Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números</div>
                    <div class="form-error" id="error-password"></div>
                </div>
                
                <div class="form-grupo">
                    <label for="password_confirmar" class="form-label">
                        <span>Confirmar Contraseña</span>
                        <span style="color: var(--color-error);">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmar" 
                        name="password_confirmar" 
                        class="form-input" 
                        placeholder="Repite tu contraseña"
                        required
                    >
                    <div class="form-error" id="error-password_confirmar"></div>
                </div>
                
                <div class="form-grupo">
                    <label class="checkbox-container">
                        <input type="checkbox" name="acepta_terminos" id="acepta_terminos" required>
                        <span class="checkmark"></span>
                        <span>
                            Acepto los <a href="/artesanoDigital/terminos" target="_blank">Términos de Uso</a> 
                            y la <a href="/artesanoDigital/privacidad" target="_blank">Política de Privacidad</a>
                        </span>
                    </label>
                    <div class="form-error" id="error-acepta_terminos"></div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full" id="btnRegistro">
                    <span>Crear Cuenta</span>
                </button>
            </form>
            
            <div class="auth-footer">
                <p>¿Ya tienes cuenta? <a href="/artesanoDigital/login">Inicia sesión aquí</a></p>
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
    const form = document.getElementById('formRegistro');
    const nombreInput = document.getElementById('nombre');
    const emailInput = document.getElementById('email');
    const telefonoInput = document.getElementById('telefono');
    const tipoUsuarioSelect = document.getElementById('tipo_usuario');
    const passwordInput = document.getElementById('password');
    const passwordConfirmarInput = document.getElementById('password_confirmar');
    const terminosCheckbox = document.getElementById('acepta_terminos');
    const btnRegistro = document.getElementById('btnRegistro');

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

    // Validaciones para nombre
    nombreInput.addEventListener('blur', function() {
        validarCampo(this, [
            {
                test: (valor) => valor.trim().length >= 2,
                mensaje: 'El nombre debe tener al menos 2 caracteres'
            },
            {
                test: (valor) => /^[a-zA-ZáéíóúñÑ\s]+$/.test(valor),
                mensaje: 'El nombre solo puede contener letras y espacios'
            }
        ]);
    });

    // Validaciones para email
    emailInput.addEventListener('blur', function() {
        validarCampo(this, [
            {
                test: (valor) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor),
                mensaje: 'Ingresa un correo electrónico válido'
            }
        ]);
    });

    // Validaciones para teléfono (opcional)
    telefonoInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            validarCampo(this, [
                {
                    test: (valor) => /^[\+]?[0-9\s\-\(\)]+$/.test(valor),
                    mensaje: 'Formato de teléfono inválido'
                },
                {
                    test: (valor) => valor.replace(/[^\d]/g, '').length >= 8,
                    mensaje: 'El teléfono debe tener al menos 8 dígitos'
                }
            ]);
        } else {
            const errorDiv = document.getElementById('error-telefono');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
    });

    // Validaciones para tipo de usuario
    tipoUsuarioSelect.addEventListener('change', function() {
        validarCampo(this, [
            {
                test: (valor) => valor !== '',
                mensaje: 'Selecciona un tipo de usuario'
            }
        ]);
    });

    // Validaciones para contraseña
    passwordInput.addEventListener('input', function() {
        validarCampo(this, [
            {
                test: (valor) => valor.length >= 8,
                mensaje: 'La contraseña debe tener al menos 8 caracteres'
            },
            {
                test: (valor) => /(?=.*[a-z])/.test(valor),
                mensaje: 'Debe incluir al menos una letra minúscula'
            },
            {
                test: (valor) => /(?=.*[A-Z])/.test(valor),
                mensaje: 'Debe incluir al menos una letra mayúscula'
            },
            {
                test: (valor) => /(?=.*\d)/.test(valor),
                mensaje: 'Debe incluir al menos un número'
            }
        ]);

        // Validar confirmación si ya tiene valor
        if (passwordConfirmarInput.value) {
            validarConfirmacion();
        }
    });

    // Validar confirmación de contraseña
    function validarConfirmacion() {
        validarCampo(passwordConfirmarInput, [
            {
                test: (valor) => valor === passwordInput.value,
                mensaje: 'Las contraseñas no coinciden'
            }
        ]);
    }

    passwordConfirmarInput.addEventListener('input', validarConfirmacion);

    // Validar términos
    terminosCheckbox.addEventListener('change', function() {
        const errorDiv = document.getElementById('error-acepta_terminos');
        if (!this.checked) {
            if (errorDiv) {
                errorDiv.textContent = 'Debes aceptar los términos y condiciones';
                errorDiv.style.display = 'block';
            }
        } else {
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
    });

    // Formatear teléfono automáticamente
    telefonoInput.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        
        if (valor.startsWith('507')) {
            valor = '+' + valor;
        } else if (valor.length > 0 && !valor.startsWith('507')) {
            valor = '+507' + valor;
        }
        
        // Formatear como +507 6000-0000
        if (valor.length > 4) {
            valor = valor.slice(0, 4) + ' ' + valor.slice(4);
        }
        if (valor.length > 9) {
            valor = valor.slice(0, 9) + '-' + valor.slice(9, 13);
        }
        
        this.value = valor;
    });

    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar todos los campos
        let formularioValido = true;
        
        if (!validarCampo(nombreInput, [
            { test: (valor) => valor.trim().length >= 2, mensaje: 'El nombre es requerido' },
            { test: (valor) => /^[a-zA-ZáéíóúñÑ\s]+$/.test(valor), mensaje: 'Nombre inválido' }
        ])) formularioValido = false;
        
        if (!validarCampo(emailInput, [
            { test: (valor) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor), mensaje: 'Email inválido' }
        ])) formularioValido = false;
        
        if (!validarCampo(tipoUsuarioSelect, [
            { test: (valor) => valor !== '', mensaje: 'Selecciona un tipo de usuario' }
        ])) formularioValido = false;
        
        if (!validarCampo(passwordInput, [
            { test: (valor) => valor.length >= 8, mensaje: 'Contraseña muy corta' },
            { test: (valor) => /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(valor), mensaje: 'Contraseña débil' }
        ])) formularioValido = false;
        
        if (!validarCampo(passwordConfirmarInput, [
            { test: (valor) => valor === passwordInput.value, mensaje: 'Las contraseñas no coinciden' }
        ])) formularioValido = false;
        
        if (!terminosCheckbox.checked) {
            const errorDiv = document.getElementById('error-acepta_terminos');
            if (errorDiv) {
                errorDiv.textContent = 'Debes aceptar los términos';
                errorDiv.style.display = 'block';
            }
            formularioValido = false;
        }
        
        if (formularioValido) {
            // Mostrar estado de carga
            btnRegistro.classList.add('loading');
            btnRegistro.disabled = true;
            
            // Enviar formulario
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST', // <-- CORREGIDO: ahora es POST
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnRegistro.classList.remove('loading');
                btnRegistro.disabled = false;
                if (data.success) {
                    // Redirigir si el registro fue exitoso
                    window.location.href = data.redirect || '/artesanoDigital/dashboard/cliente';
                } else {
                    // Mostrar errores
                    if (data.errors) {
                        for (const campo in data.errors) {
                            const errorDiv = document.getElementById('error-' + campo);
                            if (errorDiv) {
                                errorDiv.textContent = data.errors[campo];
                                errorDiv.style.display = 'block';
                            }
                        }
                    }
                    if (data.message) {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                btnRegistro.classList.remove('loading');
                btnRegistro.disabled = false;
                alert('Error de red o del servidor. Intenta de nuevo.');
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
});
</script>

<style>
/* Estilos adicionales para mejorar la experiencia */
.form-grupo.focused .form-label {
    color: var(--color-marron);
}

.form-input.invalido {
    border-color: var(--color-error) !important;
    background-color: #fef2f2 !important;
}

.form-error {
    display: none;
    color: var(--color-error);
    font-size: 0.8rem;
    margin-top: var(--espaciado-xs);
    animation: slideDown 0.3s ease-out;
}

.form-ayuda {
    color: var(--color-texto-secundario);
    font-size: 0.8rem;
    margin-top: var(--espaciado-xs);
    opacity: 0.8;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mejoras para móvil */
@media (max-width: 480px) {
    .checkbox-container {
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .form-label span:first-child {
        display: block;
        margin-bottom: 2px;
    }
}
</style>
