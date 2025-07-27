<?php
/**
 * Controlador de Notificaciones
 * Responsabilidad: Gestionar las notificaciones del usuario
 */

class ControladorNotificaciones
{
    private $db;
    private $gestorAuth;

    public function __construct()
    {
        require_once dirname(__FILE__) . '/../config/Database.php';
        require_once dirname(__FILE__) . '/../utils/GestorAutenticacion.php';

        $this->db = Config\Database::obtenerInstancia();
        $this->gestorAuth = new \Utils\GestorAutenticacion();
    }

    /**
     * Procesar solicitudes AJAX de notificaciones
     */
    public function procesarSolicitud()
    {
        // Verificar autenticación
        if (!$this->gestorAuth->estaAutenticado()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Usuario no autenticado'
            ]);
            return;
        }

        $accion = $_GET['action'] ?? '';
        
        switch ($accion) {
            case 'obtener':
                $this->obtenerNotificaciones();
                break;
            case 'contar':
                $this->contarNotificaciones();
                break;
            case 'marcar-leida':
                $this->marcarComoLeida();
                break;
            case 'marcar-todas-leidas':
                $this->marcarTodasComoLeidas();
                break;
            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Acción no válida'
                ]);
        }
    }

    /**
     * Obtener notificaciones del usuario actual
     */
    private function obtenerNotificaciones()
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idUsuario = $usuario['id_usuario'];
            
            // Parámetros de paginación
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));
            $limite = 10;
            $offset = ($pagina - 1) * $limite;
            
            // Filtro por tipo
            $tipo = $_GET['tipo'] ?? '';
            $whereClause = "WHERE id_usuario = ?";
            $params = [$idUsuario];
            
            if (!empty($tipo) && $tipo !== 'todas') {
                $whereClause .= " AND tipo = ?";
                $params[] = $tipo;
            }
            
            $conexion = $this->db->obtenerConexion();
            
            // Obtener total de notificaciones
            $sqlTotal = "SELECT COUNT(*) as total FROM notificaciones $whereClause";
            $stmtTotal = $conexion->prepare($sqlTotal);
            $stmtTotal->execute($params);
            $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Obtener notificaciones paginadas
            $sql = "SELECT * FROM notificaciones 
                    $whereClause 
                    ORDER BY fecha_creacion DESC 
                    LIMIT $limite OFFSET $offset";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear datos
            foreach ($notificaciones as &$notificacion) {
                $notificacion['fecha_formateada'] = $this->formatearFecha($notificacion['fecha_creacion']);
                $notificacion['leida'] = (bool)$notificacion['leida'];
            }
            
            // Información de paginación
            $totalPaginas = ceil($total / $limite);
            
            echo json_encode([
                'success' => true,
                'data' => $notificaciones,
                'paginacion' => [
                    'pagina_actual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $total,
                    'registros_por_pagina' => $limite
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Contar notificaciones no leídas
     */
    private function contarNotificaciones()
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idUsuario = $usuario['id_usuario'];
            
            $conexion = $this->db->obtenerConexion();
            
            $sql = "SELECT COUNT(*) as total_no_leidas 
                    FROM notificaciones 
                    WHERE id_usuario = ? AND leida = 0";
            
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$idUsuario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_no_leidas' => (int)$resultado['total_no_leidas']
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Error al contar notificaciones: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Marcar una notificación como leída
     */
    private function marcarComoLeida()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idNotificacion = (int)($input['id_notificacion'] ?? 0);
            
            if ($idNotificacion <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'ID de notificación inválido'
                ]);
                return;
            }
            
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idUsuario = $usuario['id_usuario'];
            
            $conexion = $this->db->obtenerConexion();
            
            // Verificar que la notificación pertenece al usuario
            $sql = "UPDATE notificaciones 
                    SET leida = 1 
                    WHERE id_notificacion = ? AND id_usuario = ?";
            
            $stmt = $conexion->prepare($sql);
            $resultado = $stmt->execute([$idNotificacion, $idUsuario]);
            
            if ($resultado && $stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación marcada como leída'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Notificación no encontrada'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    private function marcarTodasComoLeidas()
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idUsuario = $usuario['id_usuario'];
            
            $conexion = $this->db->obtenerConexion();
            
            $sql = "UPDATE notificaciones 
                    SET leida = 1 
                    WHERE id_usuario = ? AND leida = 0";
            
            $stmt = $conexion->prepare($sql);
            $resultado = $stmt->execute([$idUsuario]);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Todas las notificaciones marcadas como leídas',
                    'actualizadas' => $stmt->rowCount()
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Error al actualizar notificaciones'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error al marcar todas las notificaciones como leídas: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Formatear fecha para mostrar
     */
    private function formatearFecha($fecha)
    {
        try {
            $fechaObj = new DateTime($fecha);
            $ahora = new DateTime();
            $diferencia = $ahora->diff($fechaObj);
            
            if ($diferencia->days == 0) {
                if ($diferencia->h == 0) {
                    if ($diferencia->i == 0) {
                        return 'Hace unos segundos';
                    }
                    return 'Hace ' . $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
                }
                return 'Hace ' . $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
            } elseif ($diferencia->days == 1) {
                return 'Ayer a las ' . $fechaObj->format('H:i');
            } elseif ($diferencia->days < 7) {
                return 'Hace ' . $diferencia->days . ' día' . ($diferencia->days > 1 ? 's' : '');
            } else {
                return $fechaObj->format('d/m/Y H:i');
            }
        } catch (Exception $e) {
            return date('d/m/Y H:i', strtotime($fecha));
        }
    }

    /**
     * Obtiene el icono para un tipo de notificación
     */
    private function obtenerIconoNotificacion($tipo)
    {
        $iconos = [
            'nuevo_pedido' => 'fas fa-shopping-cart',
            'estado_actualizado' => 'fas fa-info-circle',
            'stock_bajo' => 'fas fa-exclamation-triangle',
            'pedido_confirmado' => 'fas fa-check-circle'
        ];

        return $iconos[$tipo] ?? 'fas fa-bell';
    }

    /**
     * Obtiene el color para un tipo de notificación
     */
    private function obtenerColorNotificacion($tipo)
    {
        $colores = [
            'nuevo_pedido' => 'success',
            'estado_actualizado' => 'info',
            'stock_bajo' => 'warning',
            'pedido_confirmado' => 'primary'
        ];

        return $colores[$tipo] ?? 'secondary';
    }
}

// Crear instancia y procesar solicitud si se accede directamente
if (basename($_SERVER['PHP_SELF']) === 'ControladorNotificaciones.php') {
    $controlador = new ControladorNotificaciones();
    $controlador->procesarSolicitud();
}
