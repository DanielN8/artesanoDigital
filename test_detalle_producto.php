<?php
session_start();

// Simular usuario logueado para las pruebas
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1; // ID de usuario de prueba
    $_SESSION['usuario_email'] = 'test@test.com';
    $_SESSION['usuario_nombre'] = 'Usuario de Prueba';
    $_SESSION['usuario_tipo'] = 'cliente';
}

echo "<h2>Prueba de Funcionalidad Detalle de Producto</h2>";
echo "<p>Usuario simulado: " . $_SESSION['usuario_nombre'] . " (ID: " . $_SESSION['usuario_id'] . ")</p>";

// Enlaces de prueba
echo "<h3>Enlaces de Prueba:</h3>";
echo "<ul>";
echo "<li><a href='/artesanoDigital/productos' target='_blank'>Ver Catálogo de Productos</a></li>";
echo "<li><a href='/artesanoDigital/productos/detalle?id=1' target='_blank'>Ver Detalle Producto ID 1</a></li>";
echo "<li><a href='/artesanoDigital/productos/detalle?id=2' target='_blank'>Ver Detalle Producto ID 2</a></li>";
echo "<li><a href='/artesanoDigital/productos/detalle?id=3' target='_blank'>Ver Detalle Producto ID 3</a></li>";
echo "</ul>";

// Probar API del carrito
echo "<h3>Prueba de API del Carrito:</h3>";
echo "<button onclick='probarAPI()'>Probar API del Carrito</button>";
echo "<div id='resultado'></div>";
?>

<script>
function probarAPI() {
    const resultado = document.getElementById('resultado');
    resultado.innerHTML = '<p>Probando API...</p>';
    
    // Probar agregar producto
    fetch('/artesanoDigital/api/carrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            accion: 'agregar',
            id_producto: 1,
            cantidad: 2
        })
    })
    .then(response => response.json())
    .then(data => {
        resultado.innerHTML = `
            <h4>Resultado de Agregar Producto:</h4>
            <pre>${JSON.stringify(data, null, 2)}</pre>
        `;
        
        // Probar obtener carrito
        return fetch('/artesanoDigital/api/carrito.php?accion=obtener');
    })
    .then(response => response.json())
    .then(data => {
        resultado.innerHTML += `
            <h4>Estado Actual del Carrito:</h4>
            <pre>${JSON.stringify(data, null, 2)}</pre>
        `;
    })
    .catch(error => {
        resultado.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
    });
}
</script>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}

h2, h3 {
    color: #333;
}

button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin: 10px 0;
}

button:hover {
    background: #0056b3;
}

ul {
    background: white;
    padding: 20px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}

pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    overflow-x: auto;
}

p {
    background: #e7f3ff;
    padding: 10px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}
</style>
