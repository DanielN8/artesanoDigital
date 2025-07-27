<?php
session_start();

// Simular usuario logueado para las pruebas
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1; 
    $_SESSION['usuario_email'] = 'test@test.com';
    $_SESSION['usuario_nombre'] = 'Usuario de Prueba';
    $_SESSION['usuario_tipo'] = 'cliente';
}

echo "<h2>Estado de la Sesión</h2>";
echo "<p><strong>Usuario ID:</strong> " . ($_SESSION['usuario_id'] ?? 'No definido') . "</p>";
echo "<p><strong>Nombre:</strong> " . ($_SESSION['usuario_nombre'] ?? 'No definido') . "</p>";
echo "<p><strong>Email:</strong> " . ($_SESSION['usuario_email'] ?? 'No definido') . "</p>";

echo "<h3>Probar API del Carrito</h3>";
echo "<button onclick='probarAgregar()'>Probar Agregar Producto</button>";
echo "<button onclick='probarObtener()'>Obtener Carrito</button>";
echo "<div id='resultado'></div>";

echo "<h3>Acceso a Páginas</h3>";
echo "<ul>";
echo "<li><a href='/artesanoDigital/productos' target='_blank'>Catálogo de Productos</a></li>";
echo "<li><a href='/artesanoDigital/productos/detalle?id=1' target='_blank'>Detalle Producto 1</a></li>";
echo "</ul>";
?>

<script>
function probarAgregar() {
    document.getElementById('resultado').innerHTML = '<p>Probando agregar producto...</p>';
    
    fetch('/artesanoDigital/api/carrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'agregar',
            id_producto: 1,
            cantidad: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('resultado').innerHTML = `
            <h4>Resultado:</h4>
            <pre>${JSON.stringify(data, null, 2)}</pre>
        `;
    })
    .catch(error => {
        document.getElementById('resultado').innerHTML = `
            <h4>Error:</h4>
            <p style="color: red;">${error.message}</p>
        `;
    });
}

function probarObtener() {
    document.getElementById('resultado').innerHTML = '<p>Obteniendo carrito...</p>';
    
    fetch('/artesanoDigital/api/carrito.php?accion=obtener')
    .then(response => response.json())
    .then(data => {
        document.getElementById('resultado').innerHTML = `
            <h4>Carrito Actual:</h4>
            <pre>${JSON.stringify(data, null, 2)}</pre>
        `;
    })
    .catch(error => {
        document.getElementById('resultado').innerHTML = `
            <h4>Error:</h4>
            <p style="color: red;">${error.message}</p>
        `;
    });
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
button { background: #007bff; color: white; border: none; padding: 10px 15px; margin: 5px; border-radius: 5px; cursor: pointer; }
button:hover { background: #0056b3; }
pre { background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; overflow-x: auto; }
p { background: #e7f3ff; padding: 10px; border-radius: 5px; border-left: 4px solid #007bff; }
ul { background: white; padding: 15px; border-radius: 5px; }
</style>
