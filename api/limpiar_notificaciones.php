<?php
/**
 * API para limpiar notificaciones antiguas
 * Permite al usuario eliminar notificaciones más antiguas de X días
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/GestorAutenticacion.php';

use Config\Database;
use Utils\GestorAutenticacion;

try {
    // Verificar autenticación
    $gestor = GestorAutenticacion::obtenerInstancia();
    if (!$gestor->estaAutenticado()) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        exit();
    }

    $usuario = $gestor->obtenerUsuarioActual();
    $idUsuario = $usuario['id_usuario'];

    // Solo permitir método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    // Obtener datos de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);
    $diasAntiguedad = $input['dias'] ?? 30; // Por defecto 30 días

    // Validar días
    if (!is_numeric($diasAntiguedad) || $diasAntiguedad < 1 || $diasAntiguedad > 365) {
        http_response_code(400);
        echo json_encode(['error' => 'Días debe ser un número entre 1 y 365']);
        exit();
    }

    // Conectar a la base de datos
    $database = Database::obtenerInstancia();
    $pdo = $database->obtenerConexion();

    // Eliminar notificaciones más antiguas que X días
    $sql = "DELETE FROM notificaciones 
            WHERE id_usuario = :id_usuario 
            AND fecha_creacion < DATE_SUB(NOW(), INTERVAL :dias DAY)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindParam(':dias', $diasAntiguedad, PDO::PARAM_INT);
    $stmt->execute();

    $notificacionesEliminadas = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'notificaciones_eliminadas' => $notificacionesEliminadas,
        'mensaje' => "Se eliminaron {$notificacionesEliminadas} notificaciones más antiguas que {$diasAntiguedad} días"
    ]);

} catch (Exception $e) {
    error_log("Error al limpiar notificaciones: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>
