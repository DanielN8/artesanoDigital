<?php
/**
 * Script para insertar notificaciones de prueba
 */

require_once __DIR__ . '/../config/Database.php';

session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id_usuario'])) {
    die('Usuario no autenticado');
}

$userId = $_SESSION['usuario']['id_usuario'];

try {
    $db = \Config\Database::obtenerInstancia();
    $conexion = $db->obtenerConexion();
    
    // Notificaciones de prueba
    $notificaciones = [
        [
            'tipo' => 'nuevo_pedido',
            'mensaje' => 'Tienes un nuevo pedido #1001 por B/. 45.50 de artesanías de cerámica.',
            'leida' => 0
        ],
        [
            'tipo' => 'estado_actualizado',
            'mensaje' => 'El pedido #1000 ha sido marcado como entregado.',
            'leida' => 1
        ],
        [
            'tipo' => 'stock_bajo',
            'mensaje' => 'Tu producto "Collar de perlas artesanal" tiene stock bajo (2 unidades restantes).',
            'leida' => 0
        ],
        [
            'tipo' => 'pedido_confirmado',
            'mensaje' => 'El cliente ha confirmado la recepción del pedido #998.',
            'leida' => 1
        ],
        [
            'tipo' => 'nuevo_pedido',
            'mensaje' => 'Nuevo pedido #1002 por B/. 89.99 - Set de pulseras tejidas a mano.',
            'leida' => 0
        ],
        [
            'tipo' => 'stock_bajo',
            'mensaje' => 'Producto "Aretes de plata" se está agotando (1 unidad restante).',
            'leida' => 0
        ]
    ];
    
    // Insertar notificaciones
    $sql = "INSERT INTO notificaciones (id_usuario, tipo, mensaje, leida, fecha_creacion) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    $fechas = [
        date('Y-m-d H:i:s', strtotime('-2 hours')),
        date('Y-m-d H:i:s', strtotime('-1 day')),
        date('Y-m-d H:i:s', strtotime('-30 minutes')),
        date('Y-m-d H:i:s', strtotime('-3 days')),
        date('Y-m-d H:i:s', strtotime('-10 minutes')),
        date('Y-m-d H:i:s', strtotime('-45 minutes'))
    ];
    
    foreach ($notificaciones as $index => $notif) {
        $stmt->execute([
            $userId,
            $notif['tipo'],
            $notif['mensaje'],
            $notif['leida'],
            $fechas[$index]
        ]);
    }
    
    echo "Se insertaron " . count($notificaciones) . " notificaciones de prueba exitosamente.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
