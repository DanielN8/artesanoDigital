# Sistema de Métodos de Pago - Patrón Strategy

## Descripción

Este sistema implementa el patrón de diseño Strategy para el manejo de métodos de pago, permitiendo una arquitectura modular y extensible.

## Arquitectura

### Componentes Principales

1. **MetodoPagoStrategy (Interface)**: Define el contrato que deben cumplir todos los métodos de pago
2. **Estrategias Concretas**: Implementaciones específicas para cada método de pago
3. **ProcesadorPago (Context)**: Clase que utiliza las estrategias para procesar pagos
4. **ProcesadorPagoFactory**: Factory para crear procesadores de pago

## Métodos de Pago Implementados

### Tarjeta de Crédito/Débito (`PagoTarjeta`)
- **Validaciones**:
  - Nombre del titular
  - Número de tarjeta (algoritmo de Luhn)
  - Fecha de expiración (formato MM/AA y validez)
  - CVV (3-4 dígitos)

### Yappy (`PagoYappy`)
- **Validaciones**:
  - Número de teléfono de Panamá (formato 6XXXXXXX)

## Uso del Sistema

### Procesamiento de Pagos

```php
// Importar el módulo
require_once __DIR__ . '/../../patrones/MetodosPago.php';
use Patrones\ProcesadorPagoFactory;

// Crear procesador para el método de pago
$procesador = ProcesadorPagoFactory::crear('tarjeta');

// Preparar datos del pago
$datosPago = [
    'nombre_titular' => 'Juan Pérez',
    'numero_tarjeta' => '4111111111111111',
    'expiracion' => '12/25',
    'cvv' => '123'
];

// Procesar el pago
$resultado = $procesador->procesar(100.00, $datosPago);

// Verificar resultado
if ($resultado['exitoso']) {
    echo "Pago exitoso: " . $resultado['transaccion_id'];
} else {
    echo "Error: " . $resultado['mensaje'];
}
```

### Validación de Datos

```php
// Validar datos sin procesar el pago
$errores = ProcesadorPagoFactory::validarDatos('tarjeta', $datosPago);

if (empty($errores)) {
    // Datos válidos, proceder con el pago
} else {
    // Mostrar errores al usuario
    foreach ($errores as $error) {
        echo "Error: $error\n";
    }
}
```

### Obtener Métodos Disponibles

```php
$metodosDisponibles = ProcesadorPagoFactory::getMetodosDisponibles();
// Retorna: ['tarjeta' => 'Tarjeta de Crédito/Débito', 'yappy' => 'Yappy']

// Verificar si un método es válido
$esValido = ProcesadorPagoFactory::esMetodoValido('tarjeta'); // true
```

## Agregar Nuevos Métodos de Pago

### Paso 1: Crear la Clase Strategy

```php
class PagoPayPal implements MetodoPagoStrategy {
    
    public function getNombreMetodo(): string {
        return 'PayPal';
    }
    
    public function validarDatos(array $datos): array {
        $errores = [];
        
        $email = trim($datos['email'] ?? '');
        
        if (empty($email)) {
            $errores[] = 'Email de PayPal';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Email válido';
        }
        
        return $errores;
    }
    
    public function procesarPago(float $monto, array $datos): array {
        $errores = $this->validarDatos($datos);
        
        if (!empty($errores)) {
            return [
                'exitoso' => false,
                'mensaje' => 'Error en los datos: ' . implode(', ', $errores),
                'errores' => $errores
            ];
        }
        
        // Integración con API de PayPal aquí
        
        return [
            'exitoso' => true,
            'mensaje' => 'Pago con PayPal procesado exitosamente',
            'transaccion_id' => 'TXN_' . date('YmdHis') . '_' . uniqid() . '_PAYPAL',
            'metodo' => 'paypal',
            'monto' => $monto
        ];
    }
}
```

### Paso 2: Actualizar el Factory

```php
// En el método crear() del ProcesadorPagoFactory
case 'paypal':
    return new ProcesadorPago(new PagoPayPal());

// En el método getMetodosDisponibles()
'paypal' => 'PayPal'
```

### Paso 3: Actualizar el Frontend

Agregar el nuevo método en el formulario de checkout y manejar sus campos específicos.

## Ventajas del Sistema

1. **Extensibilidad**: Fácil agregar nuevos métodos de pago
2. **Mantenibilidad**: Cada método tiene su propia lógica encapsulada
3. **Testabilidad**: Cada estrategia puede probarse independientemente
4. **Reutilización**: El mismo sistema puede usarse en diferentes partes de la aplicación
5. **Separación de Responsabilidades**: Cada clase tiene una responsabilidad específica

## Estructura de Respuesta

Todas las estrategias retornan un array con la siguiente estructura:

```php
[
    'exitoso' => boolean,           // true si el pago fue exitoso
    'mensaje' => string,            // Mensaje descriptivo del resultado
    'transaccion_id' => string,     // ID único de la transacción (si exitoso)
    'metodo' => string,             // Método de pago usado
    'monto' => float,               // Monto procesado
    'errores' => array,             // Lista de errores (si no exitoso)
    'timestamp' => string,          // Fecha y hora del procesamiento
    // Campos adicionales específicos del método
]
```

## Logging y Debug

El sistema incluye logging automático para:
- Validaciones fallidas
- Procesamientos exitosos
- Errores de integración
- Métodos de pago no reconocidos

## Seguridad

- **Validación de datos**: Todos los datos se validan antes del procesamiento
- **Enmascaramiento**: Datos sensibles como números de tarjeta se enmascaran en logs
- **Algoritmo de Luhn**: Validación matemática de números de tarjeta
- **Verificación de fechas**: Las tarjetas expiradas son rechazadas

## Testing

Para probar el sistema:

1. **Tarjetas de prueba**: Usar números como 4111111111111111 (Visa test)
2. **Teléfonos de prueba**: Usar números con formato 6XXX-XXXX para Yappy
3. **Simulación**: El sistema incluye simulación de procesamientos con tasas de éxito configurables
