<?php
/**
 * Script de diagnóstico para probar la API de obtener producto
 * Este archivo ayuda a depurar errores en la API de productos para artesanos
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar dependencias necesarias
require_once dirname(__FILE__) . '/config/Database.php';
require_once dirname(__FILE__) . '/utils/GestorAutenticacion.php';

use Utils\GestorAutenticacion;

// Verificar autenticación
$gestorAuth = GestorAutenticacion::obtenerInstancia();
$usuario = null;
$autenticado = false;

if ($gestorAuth->estaAutenticado()) {
    $usuario = $gestorAuth->obtenerUsuarioActual();
    $autenticado = true;
}

// Función para mostrar información de depuración
function printDebugInfo($label, $data) {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
    echo "<strong>{$label}:</strong> <pre>";
    print_r($data);
    echo "</pre></div>";
}

// Función para ejecutar la solicitud y obtener los datos
function getProducto($id) {
    // Configurar opciones de la solicitud
    $options = [
        'http' => [
            'header' => [
                'Content-type: application/x-www-form-urlencoded',
                'X-Requested-With: XMLHttpRequest'
            ],
            'method' => 'GET'
        ]
    ];
    
    $context = stream_context_create($options);
    $url = "http://localhost/artesanoDigital/controllers/ControladorProductosArtesano.php?action=obtener&id={$id}";
    
    try {
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            return [
                'error' => true,
                'message' => 'Error al realizar la solicitud'
            ];
        }
        
        return json_decode($result, true);
    } catch (Exception $e) {
        return [
            'error' => true,
            'message' => 'Exception: ' . $e->getMessage()
        ];
    }
}

// ID del producto a probar (por defecto 1, pero puede cambiarse por parámetro)
$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Obtener los datos del producto
$resultado = getProducto($idProducto);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de API Producto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .debug-info {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        pre {
            background-color: #f1f1f1;
            padding: 10px;
            overflow-x: auto;
        }
        .success {
            color: #008000;
            font-weight: bold;
        }
        .error {
            color: #ff0000;
            font-weight: bold;
        }
        .card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de API de Productos</h1>
        
        <div class="card">
            <h2>Estado de autenticación</h2>
            <?php if ($autenticado): ?>
                <p class="success">Usuario autenticado: <?= htmlspecialchars($usuario['nombre'] ?? 'Sin nombre') ?></p>
                <p>ID Usuario: <?= htmlspecialchars($usuario['id_usuario'] ?? 'No disponible') ?></p>
                <p>Tipo de usuario: <?= htmlspecialchars($usuario['tipo_usuario'] ?? 'No disponible') ?></p>
            <?php else: ?>
                <p class="error">No hay usuario autenticado. Debes iniciar sesión como artesano para usar esta API.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Probar obtener producto</h2>
            <form method="get">
                <div class="form-group">
                    <label for="id">ID del Producto:</label>
                    <input type="number" name="id" id="id" value="<?= $idProducto ?>" min="1">
                </div>
                <button type="submit">Probar API</button>
            </form>
        </div>

        <?php if (isset($resultado)): ?>
            <div class="card">
                <h2>Resultado de la API</h2>
                <?php if (isset($resultado['error']) && $resultado['error']): ?>
                    <p class="error">Error: <?= htmlspecialchars($resultado['message'] ?? 'Error desconocido') ?></p>
                <?php elseif (isset($resultado['success']) && $resultado['success']): ?>
                    <p class="success">Éxito al obtener el producto</p>
                <?php else: ?>
                    <p class="error">Respuesta no esperada</p>
                <?php endif; ?>
                
                <div class="debug-info">
                    <h3>Respuesta completa</h3>
                    <pre><?php print_r($resultado); ?></pre>
                </div>
                
                <?php if (isset($resultado['producto']) && is_array($resultado['producto'])): ?>
                    <h3>Datos del producto</h3>
                    <table>
                        <tr>
                            <th>Campo</th>
                            <th>Valor</th>
                        </tr>
                        <?php foreach ($resultado['producto'] as $campo => $valor): ?>
                            <tr>
                                <td><?= htmlspecialchars($campo) ?></td>
                                <td><?= is_array($valor) ? '<pre>' . print_r($valor, true) . '</pre>' : htmlspecialchars($valor) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Instrucciones para depurar</h2>
            <ol>
                <li>Asegúrate de estar <strong>autenticado como artesano</strong>.</li>
                <li>Verifica que el ID del producto que intentas obtener existe en la base de datos.</li>
                <li>Comprueba que el producto pertenece a <strong>tu tienda como artesano</strong>.</li>
                <li>Si sigue habiendo error, revisa la respuesta completa para más detalles.</li>
            </ol>
        </div>

        <div class="debug-info">
            <h3>Sesión actual</h3>
            <pre><?php print_r($_SESSION); ?></pre>
            
            <h3>Variables del servidor</h3>
            <pre><?php print_r([
                'PHP_VERSION' => PHP_VERSION,
                'SERVER' => $_SERVER['SERVER_NAME'] ?? 'N/A',
                'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
                'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            ]); ?></pre>
        </div>
    </div>
</body>
</html>
