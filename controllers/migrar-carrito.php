<?php
// Controlador para manejar el proceso de migración del carrito
// Este controlador redirige a la página de migración de carrito

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que hay un usuario autenticado
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay usuario autenticado, redirigir al login
    header('Location: /artesanoDigital/login');
    exit;
}

// Incluir la vista de migración
include_once __DIR__ . '/../views/auth/migrar-carrito.php';
