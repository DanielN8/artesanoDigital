<?php
namespace Patrones;

// Patrón de diseño Strategy para métodos de pago

interface MetodoPagoStrategy {
    public function procesarPago(float $monto, array $datos): array;
    public function validarDatos(array $datos): array;
    public function getNombreMetodo(): string;
}

class PagoTarjeta implements MetodoPagoStrategy {
    public function getNombreMetodo(): string {
        return 'Tarjeta de Crédito/Débito';
    }

    public function validarDatos(array $datos): array {
        $errores = [];
        if (empty(trim($datos['nombre_titular'] ?? ''))) {
            $errores[] = 'Nombre del titular de la tarjeta';
        }
        $numeroTarjeta = preg_replace('/\s+/', '', $datos['numero_tarjeta'] ?? '');
        if (empty($numeroTarjeta)) {
            $errores[] = 'Número de tarjeta';
        } elseif (strlen($numeroTarjeta) < 4) {
            // Validación mínima para permitir números de prueba
            $errores[] = 'Número de tarjeta válido (mínimo 4 dígitos)';
        }
        $expiracion = trim($datos['expiracion'] ?? '');
        if (empty($expiracion)) {
            $errores[] = 'Fecha de expiración';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiracion)) {
            $errores[] = 'Fecha de expiración en formato MM/AA';
        }
        // Validación de fecha expirada deshabilitada para permitir cualquier fecha
        $cvv = trim($datos['cvv'] ?? '');
        if (empty($cvv)) {
            $errores[] = 'CVV';
        } elseif (!preg_match('/^\d{1,4}$/', $cvv)) {
            // Validación flexible para CVV
            $errores[] = 'CVV válido (1 a 4 dígitos)';
        }
        return $errores;
    }

    public function procesarPago(float $monto, array $datos): array {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            $mensaje = 'Faltan los siguientes campos: ' . implode(', ', $errores);
            error_log("Validación de tarjeta fallida: " . $mensaje);
            return [
                'exitoso' => false,
                'mensaje' => $mensaje,
                'errores' => $errores
            ];
        }
        if ($monto <= 0) {
            return [
                'exitoso' => false,
                'mensaje' => 'El monto debe ser mayor a cero',
                'errores' => ['Monto inválido']
            ];
        }
        $success = $this->simularProcesamiento($datos);
        if ($success) {
            $transaccionId = 'TXN_' . date('YmdHis') . '_' . uniqid() . '_CARD';
            error_log("Pago con tarjeta procesado exitosamente: $transaccionId");
            return [
                'exitoso' => true,
                'mensaje' => 'Pago con tarjeta procesado exitosamente',
                'transaccion_id' => $transaccionId,
                'metodo' => 'tarjeta',
                'monto' => $monto,
                'ultimos_digitos' => substr(preg_replace('/\s+/', '', $datos['numero_tarjeta']), -4)
            ];
        } else {
            return [
                'exitoso' => false,
                'mensaje' => 'Error al procesar el pago con tarjeta. Verifique los datos e intente nuevamente.',
                'errores' => ['Error de procesamiento']
            ];
        }
    }

    private function fechaExpirada(string $fecha): bool {
        if (!preg_match('/^(\d{2})\/(\d{2})$/', $fecha, $matches)) {
            return true;
        }
        $mes = intval($matches[1]);
        $año = intval('20' . $matches[2]);
        $fechaExpiracion = new \DateTime("$año-$mes-01");
        $fechaExpiracion->modify('last day of this month');
        $hoy = new \DateTime();
        return $fechaExpiracion < $hoy;
    }

    private function simularProcesamiento(array $datos): bool {
        return (rand(1, 100) <= 95);
    }
}

class PagoYappy implements MetodoPagoStrategy {
    public function getNombreMetodo(): string {
        return 'Yappy';
    }

    public function validarDatos(array $datos): array {
        $errores = [];
        $telefono = trim($datos['telefono'] ?? '');
        if (empty($telefono)) {
            $errores[] = 'Número de teléfono';
        } elseif (!$this->validarTelefonoPanama($telefono)) {
            $errores[] = 'Número de teléfono válido de Panamá';
        }
        return $errores;
    }

    public function procesarPago(float $monto, array $datos): array {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            $mensaje = 'Error en los datos: ' . implode(', ', $errores);
            error_log("Validación de Yappy fallida: " . $mensaje);
            return [
                'exitoso' => false,
                'mensaje' => $mensaje,
                'errores' => $errores
            ];
        }
        if ($monto <= 0) {
            return [
                'exitoso' => false,
                'mensaje' => 'El monto debe ser mayor a cero',
                'errores' => ['Monto inválido']
            ];
        }
        $success = $this->simularProcesamiento($datos);
        if ($success) {
            $transaccionId = 'TXN_' . date('YmdHis') . '_' . uniqid() . '_YAPPY';
            error_log("Pago con Yappy procesado exitosamente: $transaccionId");
            return [
                'exitoso' => true,
                'mensaje' => 'Pago con Yappy procesado exitosamente. Recibirás una notificación en tu teléfono.',
                'transaccion_id' => $transaccionId,
                'metodo' => 'yappy',
                'monto' => $monto,
                'telefono' => $this->enmascarTelefono($datos['telefono'])
            ];
        } else {
            return [
                'exitoso' => false,
                'mensaje' => 'Error al procesar el pago con Yappy. Verifique que su número esté registrado en Yappy.',
                'errores' => ['Error de procesamiento']
            ];
        }
    }

    private function validarTelefonoPanama(string $telefono): bool {
        $telefonoLimpio = preg_replace('/[\s\-\(\)]/', '', $telefono);
        return preg_match('/^6\d{7}$/', $telefonoLimpio);
    }

    private function enmascarTelefono(string $telefono): string {
        $telefonoLimpio = preg_replace('/[\s\-\(\)]/', '', $telefono);
        if (strlen($telefonoLimpio) >= 4) {
            return str_repeat('*', strlen($telefonoLimpio) - 4) . substr($telefonoLimpio, -4);
        }
        return $telefono;
    }

    private function simularProcesamiento(array $datos): bool {
        return (rand(1, 100) <= 90);
    }
}

class ProcesadorPago {
    private MetodoPagoStrategy $estrategia;

    public function __construct(MetodoPagoStrategy $estrategia) {
        $this->estrategia = $estrategia;
    }

    public function setEstrategia(MetodoPagoStrategy $estrategia): void {
        $this->estrategia = $estrategia;
    }

    public function procesar(float $monto, array $datos): array {
        error_log("Procesando pago de B/. {$monto} con método: " . $this->estrategia->getNombreMetodo());
        $resultado = $this->estrategia->procesarPago($monto, $datos);
        $resultado['metodo_nombre'] = $this->estrategia->getNombreMetodo();
        $resultado['timestamp'] = date('Y-m-d H:i:s');
        return $resultado;
    }

    public function validar(array $datos): array {
        return $this->estrategia->validarDatos($datos);
    }

    public function getNombreMetodo(): string {
        return $this->estrategia->getNombreMetodo();
    }
}

class ProcesadorPagoFactory {
    public static function crear(string $metodoPago): ?ProcesadorPago {
        switch (strtolower($metodoPago)) {
            case 'tarjeta':
            case 'card':
            case 'credit_card':
                return new ProcesadorPago(new PagoTarjeta());
            case 'yappy':
                return new ProcesadorPago(new PagoYappy());
            default:
                error_log("Método de pago no reconocido: $metodoPago");
                return null;
        }
    }

    public static function getMetodosDisponibles(): array {
        return [
            'tarjeta' => 'Tarjeta de Crédito/Débito',
            'yappy'   => 'Yappy'
        ];
    }

    public static function esMetodoValido(string $metodoPago): bool {
        return array_key_exists(strtolower($metodoPago), self::getMetodosDisponibles());
    }

    public static function getInfoMetodo(string $metodoPago): ?array {
        $procesador = self::crear($metodoPago);
        if (!$procesador) {
            return null;
        }
        return [
            'codigo'     => strtolower($metodoPago),
            'nombre'     => $procesador->getNombreMetodo(),
            'disponible' => true
        ];
    }

    public static function validarDatos(string $metodoPago, array $datos): array {
        $procesador = self::crear($metodoPago);
        if (!$procesador) {
            return ['Método de pago no válido'];
        }
        return $procesador->validar($datos);
    }
}

