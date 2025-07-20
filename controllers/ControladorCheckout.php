<?php
/**
 * Controlador de Checkout
 * Responsabilidad: Gestionar el proceso de compra
 */

namespace Controllers;

use Models\Carrito;
use Models\Pedido;
use Patrones\EstrategiaMetodoPago;
use Patrones\EstrategiaTarjeta;
use Patrones\EstrategiaYappy;
use Patrones\EstrategiaTransferencia;
use Utils\GestorAutenticacion;
use Exception;


class ControladorCheckout 
{
    private GestorAutenticacion $gestorAuth;
    private Carrito $modeloCarrito;
    private Pedido $modeloPedido;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloCarrito = new Carrito();
        $this->modeloPedido = new Pedido();
        if (!$this->gestorAuth->estaAutenticado()) {
            header('Location: /artesanoDigital/login');
            exit;
        }
    }

    // Paso 1: Dirección
    public function direccion() {
        // Debug - Guardar en un archivo de log que se recibió la solicitud
        file_put_contents(__DIR__ . '/../debug_checkout.log', date('Y-m-d H:i:s') . ' - Método: ' . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
        
        // Generar token CSRF si no existe
        if (empty($_SESSION['csrf_token'])) {
            $this->gestorAuth->generarTokenCSRF();
            file_put_contents(__DIR__ . '/../debug_checkout.log', date('Y-m-d H:i:s') . ' - Generado nuevo token CSRF' . "\n", FILE_APPEND);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debug - Guardar datos recibidos
            file_put_contents(__DIR__ . '/../debug_checkout.log', date('Y-m-d H:i:s') . ' - POST: ' . print_r($_POST, true) . "\n", FILE_APPEND);
            
            $_SESSION['checkout_direccion'] = [
                'nombre' => $_POST['nombre'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'ciudad' => $_POST['ciudad'] ?? '',
                'telefono' => $_POST['telefono'] ?? ''
            ];
            header('Location: /artesanoDigital/checkout/pago');
            exit;
        }
        $this->cargarVista('checkout/direccion');
    }

    // Paso 2: Pago
    public function pago() {
        if (empty($_SESSION['checkout_direccion'])) {
            header('Location: /artesanoDigital/checkout/direccion');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $metodo = $_POST['metodo_pago'] ?? '';
            $datos = [];
            if ($metodo === 'tarjeta') {
                $datos = [
                    'nombre_titular' => $_POST['nombre_tarjeta'] ?? '',
                    'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
                    'fecha_expiracion' => $_POST['expiracion'] ?? '',
                    'cvv' => $_POST['cvv'] ?? ''
                ];
            } elseif ($metodo === 'yappy') {
                $datos = [
                    'telefono' => $_POST['telefono_yappy'] ?? ''
                ];
            }
            $_SESSION['checkout_metodo_pago'] = $metodo;
            $_SESSION['checkout_datos_pago'] = $datos;
            header('Location: /artesanoDigital/checkout/factura');
            exit;
        }
        $this->cargarVista('checkout/pago');
    }

    // Paso 3: Factura
    public function factura() {
        if (empty($_SESSION['checkout_direccion']) || empty($_SESSION['checkout_metodo_pago'])) {
            header('Location: /artesanoDigital/checkout/direccion');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->gestorAuth->verificarTokenCSRF($token)) {
                die('Token CSRF inválido');
            }
            header('Location: /artesanoDigital/checkout/completado');
            exit;
        }
        $this->cargarVista('checkout/factura');
    }

    // Paso 4: Completado
    public function completado() {
        // Si tenemos información del pedido en la sesión, mostramos la página de completado directamente
        if (!empty($_SESSION['pedido_completado'])) {
            $this->cargarVista('checkout/completado');
            return;
        }
        
        // Si no estamos en el paso correcto del checkout y no hay pedido completado, redirigir al paso correcto
        if (empty($_SESSION['checkout_direccion']) || empty($_SESSION['checkout_metodo_pago']) || empty($_SESSION['checkout_datos_pago'])) {
            header('Location: /artesanoDigital/checkout/direccion');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->gestorAuth->verificarTokenCSRF($token)) {
                die('Token CSRF inválido');
            }
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $carrito = $_SESSION['carrito'] ?? [];
            $total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carrito));
            $metodo = $_SESSION['checkout_metodo_pago'];
            $datosPago = $_SESSION['checkout_datos_pago'];
            $estrategia = $this->obtenerEstrategiaPago($metodo);
            $resultadoPago = $estrategia->procesarPago($total, $datosPago);
            if (!$resultadoPago['exitoso']) {
                $_SESSION['checkout_error_pago'] = $resultadoPago['mensaje'];
                header('Location: /artesanoDigital/checkout/pago');
                exit;
            }
            // Crear el pedido en la base de datos
            $direccion = $_SESSION['checkout_direccion'];
            $direccionFormateada = json_encode($direccion); // Convertir array a JSON para almacenar
            
            $productos = $this->modeloCarrito->obtenerProductos($usuario['id_usuario']);
            
            // Verificar que los productos tengan los IDs correctos
            foreach ($productos as $key => $producto) {
                if (!isset($producto['id_producto']) || empty($producto['id_producto'])) {
                    error_log("Error: Producto sin ID en el carrito: " . print_r($producto, true));
                    // Si no tiene ID, no lo incluiremos en el pedido
                    unset($productos[$key]);
                }
            }
            
            $datosPedido = [
                'id_usuario' => $usuario['id_usuario'],
                'metodo_pago' => $metodo,
                'total' => $total,
                'direccion_envio' => $direccionFormateada,
                'productos' => $productos
            ];
            
            $resultadoPedido = $this->modeloPedido->crear($datosPedido);
            
            if (!$resultadoPedido['exitoso']) {
                $_SESSION['checkout_error'] = 'Error al crear el pedido: ' . $resultadoPedido['mensaje'];
                header('Location: /artesanoDigital/checkout/pago');
                exit;
            }
            
            // Guardar información del pedido para mostrarla en la vista de completado
            // Lo guardamos primero para asegurarnos de tener toda la información antes de vaciar el carrito
            $_SESSION['pedido_completado'] = [
                'id_pedido' => $resultadoPedido['id_pedido'],
                'referencia' => 'AD-' . str_pad($resultadoPedido['id_pedido'], 5, '0', STR_PAD_LEFT),
                'transaccion_id' => $resultadoPago['transaccion_id'] ?? null,
                'total' => $total,
                'fecha' => date('Y-m-d H:i:s'),
                'metodo_pago' => $metodo,
                'productos' => array_map(function($producto) {
                    return [
                        'id_producto' => $producto['id_producto'],
                        'nombre' => $producto['nombre'],
                        'cantidad' => $producto['cantidad'],
                        'precio' => $producto['precio']
                    ];
                }, $productos)
            ];
            
            // Asegurar que se vacía el carrito en la base de datos
            $resultadoVaciar = $this->modeloCarrito->vaciarCarrito($usuario['id_usuario']);
            if (!$resultadoVaciar) {
                error_log("Error al vaciar el carrito en la base de datos para el usuario: " . $usuario['id_usuario']);
            }
            
            // Vaciar también el carrito en sesión y datos de checkout
            unset($_SESSION['carrito']);
            unset($_SESSION['checkout_direccion'], $_SESSION['checkout_metodo_pago'], $_SESSION['checkout_datos_pago']);
            
            $this->cargarVista('checkout/completado');
            exit;
        }
        $this->cargarVista('checkout/completado');
    }

    private function obtenerEstrategiaPago(string $metodo): EstrategiaMetodoPago {
        switch ($metodo) {
            case 'tarjeta':
            case 'tarjeta_credito':
            case 'tarjeta_debito':
                return new EstrategiaTarjeta();
            case 'yappy':
                return new EstrategiaYappy();
            case 'transferencia':
                return new EstrategiaTransferencia();
            default:
                return new EstrategiaTarjeta();
        }
    }

    private function cargarVista(string $vista, array $datos = []) {
        extract($datos);
        $rutaVista = __DIR__ . '/../views/' . $vista . '.php';
        
        // Verificar si la vista existe
        if (file_exists($rutaVista)) {
            include $rutaVista;
        } else {
            // Si es una vista de checkout que no existe, usar cart_process.php
            if (strpos($vista, 'checkout/') === 0 && $vista !== 'checkout/cart_process') {
                include __DIR__ . '/../views/checkout/cart_process.php';
            } else {
                // Si no es una vista de checkout o es la vista principal de checkout que tampoco existe
                echo "<div style='text-align: center; margin-top: 50px;'>";
                echo "<h1>Error 404</h1>";
                echo "<p>La página solicitada no existe: {$vista}.php</p>";
                echo "<p><a href='/artesanoDigital'>Volver al inicio</a></p>";
                echo "</div>";
            }
        }
    }
    
    // Vista única del proceso de checkout (todo en uno)
    public function proceso() {
        if (!$this->gestorAuth->estaAutenticado()) {
            header('Location: /artesanoDigital/login?redirect=checkout');
            exit;
        }
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $productos = $this->modeloCarrito->obtenerProductos($usuario['id_usuario']);
        $total = $this->modeloCarrito->calcularTotal($usuario['id_usuario']);
        
        $datos = [
            'productos' => $productos,
            'total' => $total,
            'usuario' => $usuario
        ];
        
        // Muestra la vista cart_process.php con su lógica de pasos
        $this->cargarVista('checkout/cart_process', $datos);
    }
}
