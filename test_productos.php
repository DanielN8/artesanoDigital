<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $db = Database::obtenerInstancia()->obtenerConexion();
    $stmt = $db->query('SELECT id_producto, nombre FROM productos LIMIT 5');
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Productos disponibles:\n";
    foreach($productos as $p) {
        echo 'ID: ' . $p['id_producto'] . ' - Nombre: ' . $p['nombre'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
