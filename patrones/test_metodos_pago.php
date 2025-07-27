<?php
/**
 * Script de prueba para el sistema de métodos de pago
 * Este archivo demuestra cómo usar el nuevo sistema modularizado
 */

// Incluir el sistema de métodos de pago
require_once __DIR__ . '/MetodosPago.php';

use Patrones\ProcesadorPagoFactory;

echo "=== PRUEBA DEL SISTEMA DE MÉTODOS DE PAGO ===\n\n";

// 1. Mostrar métodos disponibles
echo "1. Métodos de pago disponibles:\n";
$metodosDisponibles = ProcesadorPagoFactory::getMetodosDisponibles();
foreach ($metodosDisponibles as $codigo => $nombre) {
    echo "   - $codigo: $nombre\n";
}
echo "\n";

// 2. Probar validación de tarjeta con datos correctos
echo "2. Prueba de validación - Tarjeta con datos correctos:\n";
$datosTarjetaValidos = [
    'nombre_titular' => 'Juan Pérez',
    'numero_tarjeta' => '4111111111111111', // Número de prueba Visa
    'expiracion' => '12/25',
    'cvv' => '123'
];

$errores = ProcesadorPagoFactory::validarDatos('tarjeta', $datosTarjetaValidos);
if (empty($errores)) {
    echo "   ✓ Validación exitosa\n";
} else {
    echo "   ✗ Errores encontrados: " . implode(', ', $errores) . "\n";
}
echo "\n";

// 3. Probar validación de tarjeta con datos incorrectos
echo "3. Prueba de validación - Tarjeta con datos incorrectos:\n";
$datosTarjetaInvalidos = [
    'nombre_titular' => '',
    'numero_tarjeta' => '1234',
    'expiracion' => '13/20', // Mes inválido y año pasado
    'cvv' => 'abc'
];

$errores = ProcesadorPagoFactory::validarDatos('tarjeta', $datosTarjetaInvalidos);
if (empty($errores)) {
    echo "   ✓ Validación exitosa\n";
} else {
    echo "   ✗ Errores encontrados: " . implode(', ', $errores) . "\n";
}
echo "\n";

// 4. Probar procesamiento de pago con Yappy
echo "4. Prueba de procesamiento - Yappy:\n";
$datosYappy = [
    'telefono' => '6123-4567'
];

$procesadorYappy = ProcesadorPagoFactory::crear('yappy');
if ($procesadorYappy) {
    $resultado = $procesadorYappy->procesar(50.00, $datosYappy);
    
    if ($resultado['exitoso']) {
        echo "   ✓ Pago exitoso\n";
        echo "   Transacción ID: " . $resultado['transaccion_id'] . "\n";
        echo "   Método: " . $resultado['metodo_nombre'] . "\n";
        echo "   Monto: B/. " . number_format($resultado['monto'], 2) . "\n";
    } else {
        echo "   ✗ Pago fallido: " . $resultado['mensaje'] . "\n";
    }
} else {
    echo "   ✗ Error al crear procesador\n";
}
echo "\n";

// 5. Probar procesamiento de pago con Tarjeta
echo "5. Prueba de procesamiento - Tarjeta:\n";
$procesadorTarjeta = ProcesadorPagoFactory::crear('tarjeta');
if ($procesadorTarjeta) {
    $resultado = $procesadorTarjeta->procesar(100.00, $datosTarjetaValidos);
    
    if ($resultado['exitoso']) {
        echo "   ✓ Pago exitoso\n";
        echo "   Transacción ID: " . $resultado['transaccion_id'] . "\n";
        echo "   Método: " . $resultado['metodo_nombre'] . "\n";
        echo "   Monto: B/. " . number_format($resultado['monto'], 2) . "\n";
        echo "   Últimos dígitos: " . $resultado['ultimos_digitos'] . "\n";
    } else {
        echo "   ✗ Pago fallido: " . $resultado['mensaje'] . "\n";
    }
} else {
    echo "   ✗ Error al crear procesador\n";
}
echo "\n";

// 6. Probar método de pago inválido
echo "6. Prueba con método de pago inválido:\n";
$procesadorInvalido = ProcesadorPagoFactory::crear('bitcoin');
if ($procesadorInvalido) {
    echo "   ✗ Error: Se creó procesador para método inválido\n";
} else {
    echo "   ✓ Correctamente rechazado método inválido\n";
}
echo "\n";

// 7. Verificar si un método es válido
echo "7. Verificación de métodos:\n";
echo "   - 'tarjeta' es válido: " . (ProcesadorPagoFactory::esMetodoValido('tarjeta') ? 'Sí' : 'No') . "\n";
echo "   - 'yappy' es válido: " . (ProcesadorPagoFactory::esMetodoValido('yappy') ? 'Sí' : 'No') . "\n";
echo "   - 'bitcoin' es válido: " . (ProcesadorPagoFactory::esMetodoValido('bitcoin') ? 'Sí' : 'No') . "\n";
echo "\n";

echo "=== FIN DE LAS PRUEBAS ===\n";
