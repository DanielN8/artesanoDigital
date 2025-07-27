<?php
/**
 * Controlador de Cliente
 * Responsabilidad: Gestionar el panel de clientes
 */

namespace Controllers;

use Utils\GestorAutenticacion;
use Models\Pedido;

class ControladorCliente 
{
    private GestorAutenticacion $gestorAuth;
    private \Models\Pedido $modeloPedido;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        
        // Cargar el modelo de Pedido
        require_once __DIR__ . '/../models/Pedido.php';
        $this->modeloPedido = new \Models\Pedido();
        
        // Verificar que el usuario esté autenticado y sea cliente
        if (!$this->gestorAuth->estaAutenticado() || 
            $this->gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'cliente') {
            header('Location: /artesanoDigital/login');
            exit;
        }
    }
    
    /**
     * Muestra los detalles de un pedido específico (factura)
     * 
     * @param int $id ID del pedido
     * @return void
     */
    public function verDetallePedido($id) 
    {
        error_log("ControladorCliente::verDetallePedido - ID recibido: " . $id);
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $idUsuario = $usuario['id_usuario'] ?? 0;
        
        error_log("ControladorCliente::verDetallePedido - ID Usuario: " . $idUsuario);
        
        // Obtener los datos del pedido
        $pedidoData = $this->modeloPedido->obtenerPorId($id);
        
        error_log("ControladorCliente::verDetallePedido - Pedido encontrado: " . ($pedidoData ? 'Sí' : 'No'));
        
        // Verificar que el pedido exista y pertenezca al usuario actual
        if (!$pedidoData || $pedidoData['id_usuario'] != $idUsuario) {
            error_log("ControladorCliente::verDetallePedido - Redirigiendo al dashboard. Pedido no encontrado o no pertenece al usuario");
            // Redirigir al dashboard si el pedido no existe o no pertenece al usuario
            header('Location: /artesanoDigital/cliente/dashboard');
            exit;
        }
        
        // Obtener los productos del pedido
        $productos = $this->modeloPedido->obtenerProductosPedido($id);
        error_log("ControladorCliente::verDetallePedido - Productos obtenidos: " . count($productos));
        error_log("ControladorCliente::verDetallePedido - Productos data: " . json_encode($productos));
        
        if (empty($productos)) {
            error_log("ControladorCliente::verDetallePedido - No se encontraron productos para el pedido ID: " . $id);
        }
        
        error_log("ControladorCliente::verDetallePedido - Productos encontrados: " . count($productos));
        
        // Formatear los productos para la vista
        $productosFormateados = [];
        foreach ($productos as $producto) {
            $productosFormateados[] = [
                'nombre' => $producto['nombre'],
                'descripcion' => $producto['descripcion'] ?? '',
                'cantidad' => $producto['cantidad'],
                'precio_unitario' => $producto['precio_unitario'],
                'precio' => $producto['precio_unitario'], // Alias para compatibilidad
                'imagen' => $producto['imagen'] ?? null,
                'artesano' => $producto['artesano'] ?? $producto['tienda_nombre'] ?? 'Artesano Digital',
                'id_producto' => $producto['id_producto']
            ];
        }
        
        // Formatear la dirección de envío si es JSON
        $direccionEnvio = $pedidoData['direccion_envio'] ?? 'No especificada';
        if (is_string($direccionEnvio) && (strpos($direccionEnvio, '{') === 0 || strpos($direccionEnvio, '[') === 0)) {
            $direccionDecodificada = json_decode($direccionEnvio, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($direccionDecodificada)) {
                $direccionEnvio = $this->formatearDireccion($direccionDecodificada);
            }
        }
        
        // Formatear los datos del pedido para la vista
        $pedido = [
            'id' => $pedidoData['id_pedido'],
            'fecha' => $pedidoData['fecha_pedido'],
            'total' => $pedidoData['total'],
            'estado' => $pedidoData['estado'],
            'metodo_pago' => $pedidoData['metodo_pago'],
            'direccion_envio' => $direccionEnvio,
            'transaccion_id' => $pedidoData['transaccion_id'] ?? null
        ];
        
        // Si hay datos de costo de envío, incluirlo
        if (isset($pedidoData['costo_envio'])) {
            $pedido['costo_envio'] = $pedidoData['costo_envio'];
        }
        
        $datos = [
            'titulo' => 'Detalle de Pedido #' . $id,
            'usuario' => $usuario,
            'pedido' => $pedido,
            'productos' => $productosFormateados
        ];
        
        error_log("ControladorCliente::verDetallePedido - Cargando vista con datos");
        $this->cargarVista('cliente/pedido_detalle', $datos);
    }
    
    /**
     * Cancela un pedido existente
     * 
     * @param int $id ID del pedido
     * @return void
     */
    public function cancelarPedido($id) 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $idUsuario = $usuario['id_usuario'] ?? 0;
        
        // Verificar que el pedido existe y pertenece al usuario
        $pedido = $this->modeloPedido->obtenerPorId($id);
        
        if (!$pedido || $pedido['id_usuario'] != $idUsuario) {
            echo json_encode([
                'exito' => false, 
                'mensaje' => 'El pedido no existe o no tienes permiso para cancelarlo'
            ]);
            exit;
        }
        
        // Verificar que el pedido esté en estado pendiente
        if ($pedido['estado'] !== 'pendiente') {
            echo json_encode([
                'exito' => false, 
                'mensaje' => 'Solo puedes cancelar pedidos pendientes'
            ]);
            exit;
        }
        
        // Intentar cancelar el pedido
        $resultado = $this->modeloPedido->actualizarEstado($id, 'cancelado');
        
        if ($resultado) {
            echo json_encode([
                'exito' => true, 
                'mensaje' => 'Pedido cancelado correctamente'
            ]);
        } else {
            echo json_encode([
                'exito' => false, 
                'mensaje' => 'Error al cancelar el pedido. Intenta nuevamente.'
            ]);
        }
        
        exit;
    }

    /**
     * Muestra el dashboard del cliente
     */
    public function mostrarDashboard(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $idUsuario = $usuario['id_usuario'] ?? 0;
        
        // Obtener los pedidos recientes del cliente
        $pedidosRecientes = $this->obtenerPedidosRecientes($idUsuario);
        
        // Calcular estadísticas
        $totalPedidos = count($pedidosRecientes);
        $totalCompras = array_sum(array_column($pedidosRecientes, 'total'));
        
        $datos = [
            'titulo' => 'Panel de Cliente',
            'usuario' => $usuario,
            'pedidos_recientes' => $pedidosRecientes,
            'estadisticas' => [
                'pedidos_totales' => $totalPedidos,
                'total_compras' => $totalCompras,
                'productos_favoritos' => 0,
                'artesanos_seguidos' => 0
            ],
            'favoritos_recientes' => []
        ];

        $this->cargarVista('cliente/dashboard', $datos);
    }

    /**
     * Muestra todos los pedidos del cliente
     */
    public function mostrarTodosPedidos(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $idUsuario = $usuario['id_usuario'] ?? 0;
        
        // Obtener todos los pedidos del cliente
        $pedidos = $this->modeloPedido->obtenerPorUsuario($idUsuario);
        
        // Formatear los pedidos para la vista
        $pedidosFormateados = [];
        foreach ($pedidos as $pedido) {
            $pedidosFormateados[] = [
                'id' => $pedido['id_pedido'],
                'fecha' => $pedido['fecha_pedido'],
                'total' => $pedido['total'],
                'estado' => $pedido['estado'],
                'metodo_pago' => $pedido['metodo_pago']
            ];
        }
        
        // Calcular estadísticas
        $totalPedidos = count($pedidosFormateados);
        $totalCompras = array_sum(array_column($pedidosFormateados, 'total'));
        $pedidosPendientes = count(array_filter($pedidosFormateados, fn($p) => $p['estado'] === 'pendiente'));
        $pedidosEntregados = count(array_filter($pedidosFormateados, fn($p) => $p['estado'] === 'entregado'));
        
        $datos = [
            'titulo' => 'Todos mis Pedidos',
            'usuario' => $usuario,
            'pedidos' => $pedidosFormateados,
            'estadisticas' => [
                'total_pedidos' => $totalPedidos,
                'total_compras' => $totalCompras,
                'pedidos_pendientes' => $pedidosPendientes,
                'pedidos_entregados' => $pedidosEntregados
            ]
        ];

        $this->cargarVista('cliente/todos_pedidos', $datos);
    }

    /**
     * Obtiene pedidos recientes del cliente
     * @param int $idUsuario
     * @return array
     */
    private function obtenerPedidosRecientes(int $idUsuario): array 
    {
        try {
            // Obtener los pedidos del usuario desde la base de datos
            $pedidos = $this->modeloPedido->obtenerPorUsuario($idUsuario);
            
            // Si hay pedidos, formatearlos para la vista y limitar a 5 más recientes
            if (!empty($pedidos)) {
                $pedidosFormateados = [];
                $pedidosLimitados = array_slice($pedidos, 0, 5); // Solo los 5 más recientes
                
                foreach ($pedidosLimitados as $pedido) {
                    $pedidosFormateados[] = [
                        'id' => $pedido['id_pedido'],
                        'fecha' => $pedido['fecha_pedido'],
                        'total' => $pedido['total'],
                        'estado' => $pedido['estado'],
                        'metodo_pago' => $pedido['metodo_pago']
                    ];
                }
                return $pedidosFormateados;
            }
            
            return [];
        } catch (\Exception $e) {
            // Registrar el error y devolver array vacío
            error_log("Error al obtener pedidos recientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Carga una vista
     * @param string $vista
     * @param array $datos
     */
    private function cargarVista(string $vista, array $datos = []): void 
    {
        extract($datos);
        include "views/{$vista}.php";
    }
    
    /**
     * Formatea una dirección desde formato JSON a texto plano legible
     * 
     * @param array|string $direccion Dirección en formato array o string
     * @return string Dirección formateada
     */
    private function formatearDireccion($direccion) 
    {
        if (!is_array($direccion)) {
            return is_string($direccion) ? $direccion : 'No especificada';
        }
        
        $partes = [];
        
        // Dirección principal
        if (!empty($direccion['direccion'])) {
            $partes[] = $direccion['direccion'];
        }
        
        // Ciudad y región
        $ciudadRegion = [];
        if (!empty($direccion['ciudad'])) {
            $ciudadRegion[] = $direccion['ciudad'];
        }
        if (!empty($direccion['region']) || !empty($direccion['estado'])) {
            $ciudadRegion[] = $direccion['region'] ?? $direccion['estado'];
        }
        if (!empty($ciudadRegion)) {
            $partes[] = implode(', ', $ciudadRegion);
        }
        
        // País
        if (!empty($direccion['pais'])) {
            $partes[] = $direccion['pais'];
        }
        
        // Código postal
        if (!empty($direccion['codigo_postal']) || !empty($direccion['cp'])) {
            $cp = $direccion['codigo_postal'] ?? $direccion['cp'];
            $partes[] = "CP: " . $cp;
        }
        
        return !empty($partes) ? implode(', ', $partes) : 'No especificada';
    }
}
