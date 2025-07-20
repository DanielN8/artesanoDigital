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
        $this->modeloPedido = new \Models\Pedido();
        
        // Verificar que el usuario esté autenticado y sea cliente
        if (!$this->gestorAuth->estaAutenticado() || 
            $this->gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'cliente') {
            header('Location: /artesanoDigital/login');
            exit;
        }
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
     * Obtiene pedidos recientes del cliente
     * @param int $idUsuario
     * @return array
     */
    private function obtenerPedidosRecientes(int $idUsuario): array 
    {
        try {
            // Obtener los pedidos del usuario desde la base de datos
            $pedidos = $this->modeloPedido->obtenerPorUsuario($idUsuario);
            
            // Si hay pedidos, formatearlos para la vista
            if (!empty($pedidos)) {
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
}
