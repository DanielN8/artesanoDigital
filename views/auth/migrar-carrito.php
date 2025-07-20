<?php
// migrar-carrito.php - Script para gestionar la migración de carrito en inicio de sesión

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Esta página debe ser llamada después del login/registro exitoso
// Verificar si hay un usuario autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /artesanoDigital/login');
    exit;
}

$idUsuario = $_SESSION['usuario_id'];
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Incluir header y scripts necesarios
$titulo = "Migrando carrito - Artesano Digital";
$descripcion = "Migrando tu carrito de productos";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="stylesheet" href="/artesanoDigital/assets/css/estilos.css">
    <style>
        .migracion-contenedor {
            max-width: 600px;
            margin: 100px auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .icono-migracion {
            font-size: 4rem;
            color: #4a90e2;
            margin-bottom: 1rem;
        }
        .mensaje-migracion {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #555;
        }
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(74, 144, 226, 0.2);
            border-top-color: #4a90e2;
            border-radius: 50%;
            animation: spin 1s ease-in-out infinite;
            margin: 0 auto 2rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body data-user-id="<?= htmlspecialchars($idUsuario) ?>">
    <div class="migracion-contenedor">
        <div class="icono-migracion">🛒</div>
        <h1>Migrando tu carrito</h1>
        <div class="loader"></div>
        <p class="mensaje-migracion">Estamos sincronizando tu carrito de compras, <?= htmlspecialchars($nombreUsuario) ?>.<br>Serás redirigido automáticamente.</p>
    </div>

    <script src="/artesanoDigital/assets/js/carrito-usuario.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Migrar el carrito anónimo al usuario que acaba de iniciar sesión
            const usuarioId = <?= $idUsuario ?>;
            
            try {
                // Intentar migrar el carrito anónimo (si existe)
                const carritoMigrado = migrarCarritoAnonimo(usuarioId);
                console.log('Migración completada:', carritoMigrado);
                
                // Esperar un momento para mostrar la animación
                setTimeout(function() {
                    // Redirigir al usuario a la página principal o última página visitada
                    const urlAnterior = localStorage.getItem('ultima_pagina') || '/artesanoDigital/';
                    window.location.href = urlAnterior;
                }, 1500);
            } catch (error) {
                console.error('Error al migrar el carrito:', error);
                // Aún en caso de error, redirigir al usuario
                setTimeout(function() {
                    window.location.href = '/artesanoDigital/';
                }, 1500);
            }
        });
    </script>
</body>
</html>
