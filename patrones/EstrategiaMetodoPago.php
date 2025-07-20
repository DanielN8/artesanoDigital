<?php
/**
 * Patrón Strategy para métodos de pago
 * Responsabilidad: Definir diferentes estrategias de procesamiento de pagos
 */

namespace Patrones;

/**
 * Interfaz para estrategias de método de pago
 */
interface EstrategiaMetodoPago 
{
    public function procesarPago(float $monto, array $datosPago): array;
    public function validarDatos(array $datosPago): bool;
    public function obtenerNombre(): string;
}

/**
 * Estrategia para pago con tarjeta de crédito/débito
 */
class EstrategiaTarjeta implements EstrategiaMetodoPago 
{
    public function procesarPago(float $monto, array $datosPago): array 
    {
        // Simulación de procesamiento de tarjeta
        if (!$this->validarDatos($datosPago)) {
            return [
                'exitoso' => false,
                'mensaje' => 'Datos de tarjeta inválidos',
                'transaccion_id' => null
            ];
        }

        // Simulación de validación con banco
        $numeroTarjeta = $datosPago['numero_tarjeta'];
        $ultimosDigitos = substr($numeroTarjeta, -4);
        
        // Simulación: rechazar si los últimos 4 dígitos son 0000
        if ($ultimosDigitos === '0000') {
            return [
                'exitoso' => false,
                'mensaje' => 'Tarjeta rechazada por el banco',
                'transaccion_id' => null
            ];
        }

        return [
            'exitoso' => true,
            'mensaje' => 'Pago procesado exitosamente',
            'transaccion_id' => 'TXN_' . uniqid() . '_CARD'
        ];
    }

    public function validarDatos(array $datosPago): bool 
    {
        return isset($datosPago['numero_tarjeta']) &&
               isset($datosPago['fecha_expiracion']) &&
               isset($datosPago['cvv']) &&
               isset($datosPago['nombre_titular']) &&
               strlen($datosPago['numero_tarjeta']) >= 13 &&
               strlen($datosPago['cvv']) >= 3;
    }

    public function obtenerNombre(): string 
    {
        return 'Tarjeta de Crédito/Débito';
    }
}

/**
 * Estrategia para pago con Yappy
 */
class EstrategiaYappy implements EstrategiaMetodoPago 
{
    public function procesarPago(float $monto, array $datosPago): array 
    {
        if (!$this->validarDatos($datosPago)) {
            return [
                'exitoso' => false,
                'mensaje' => 'Número de teléfono inválido para Yappy',
                'transaccion_id' => null
            ];
        }

        // Simulación de procesamiento Yappy
        $telefono = $datosPago['telefono'];
        
        // Simulación: rechazar si el teléfono termina en 0000
        if (substr($telefono, -4) === '0000') {
            return [
                'exitoso' => false,
                'mensaje' => 'Pago rechazado por Yappy',
                'transaccion_id' => null
            ];
        }

        return [
            'exitoso' => true,
            'mensaje' => 'Pago procesado exitosamente con Yappy',
            'transaccion_id' => 'TXN_' . uniqid() . '_YAPPY'
        ];
    }

    public function validarDatos(array $datosPago): bool 
    {
        return isset($datosPago['telefono']) &&
               preg_match('/^[6-9]\d{3}-\d{4}$/', $datosPago['telefono']);
    }

    public function obtenerNombre(): string 
    {
        return 'Yappy';
    }
}

/**
 * Estrategia para transferencia bancaria
 */
class EstrategiaTransferencia implements EstrategiaMetodoPago 
{
    public function procesarPago(float $monto, array $datosPago): array 
    {
        if (!$this->validarDatos($datosPago)) {
            return [
                'exitoso' => false,
                'mensaje' => 'Datos de transferencia inválidos',
                'transaccion_id' => null
            ];
        }

        // Para transferencia, se marca como pendiente de confirmación
        return [
            'exitoso' => true,
            'mensaje' => 'Transferencia registrada. Pendiente de confirmación bancaria.',
            'transaccion_id' => 'TXN_' . uniqid() . '_TRANSFER',
            'requiere_confirmacion' => true
        ];
    }

    public function validarDatos(array $datosPago): bool 
    {
        return isset($datosPago['banco']) &&
               isset($datosPago['numero_cuenta']) &&
               isset($datosPago['nombre_titular']) &&
               !empty($datosPago['banco']) &&
               !empty($datosPago['numero_cuenta']);
    }

    public function obtenerNombre(): string 
    {
        return 'Transferencia Bancaria';
    }
}

/**
 * Contexto que utiliza las estrategias de pago
 */
class ProcesadorPagos 
{
    private EstrategiaMetodoPago $estrategia;

    public function establecerEstrategia(EstrategiaMetodoPago $estrategia): void 
    {
        $this->estrategia = $estrategia;
    }

    public function procesarPago(float $monto, array $datosPago): array 
    {
        return $this->estrategia->procesarPago($monto, $datosPago);
    }

    public function validarDatos(array $datosPago): bool 
    {
        return $this->estrategia->validarDatos($datosPago);
    }

    public function obtenerNombreMetodo(): string 
    {
        return $this->estrategia->obtenerNombre();
    }

    /**
     * Factory method para crear estrategias
     * @param string $tipoMetodo
     * @return EstrategiaMetodoPago
     * @throws \Exception
     */
    public static function crearEstrategia(string $tipoMetodo): EstrategiaMetodoPago 
    {
        switch ($tipoMetodo) {
            case 'tarjeta_credito':
            case 'tarjeta_debito':
                return new EstrategiaTarjeta();
            case 'yappy':
                return new EstrategiaYappy();
            case 'transferencia':
                return new EstrategiaTransferencia();
            default:
                throw new \Exception("Método de pago no soportado: {$tipoMetodo}");
        }
    }
}
