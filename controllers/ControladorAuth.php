<?php
/**
 * Controlador de Autenticación
 * Responsabilidad: Gestionar registro, login, logout y recuperación de contraseñas
 */

namespace Controllers;

use Utils\GestorAutenticacion;
use Utils\Validador;
use Models\Usuario;
use Models\Tienda;
use Services\ServicioCorreo;
use Exception;

class ControladorAuth 
{
    private GestorAutenticacion $gestorAuth;
    private Usuario $modeloUsuario;

    public function __construct() 
    {
        try {
            $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
            $this->modeloUsuario = new Usuario();
        } catch (Exception $e) {
            error_log("Error en constructor ControladorAuth: " . $e->getMessage());
            // Si es una petición AJAX, responder con JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || 
                $_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->responderJSONSeguro(['success' => false, 'message' => 'Error de inicialización del sistema']);
            }
            // Para otras peticiones, permitir que continúe el flujo normal
        }
    }

    /**
     * Genera un token seguro para recuperación de contraseña
     * @return string
     */
    private function generarTokenRecuperacion(): string 
    {
        // Usar openssl_random_pseudo_bytes como alternativa más compatible
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(32);
            return bin2hex($bytes);
        }
        
        // Fallback usando time y hash - suficientemente seguro para tokens temporales
        $entropy = time() . getmypid() . memory_get_usage();
        return hash('sha256', $entropy . uniqid('token_', true));
    }

    /**
     * Muestra la página de login
     */
    public function mostrarLogin(): void 
    {
        if ($this->gestorAuth->estaAutenticado()) {
            $this->redirigirSegunTipoUsuario();
            return;
        }

        $datos = [
            'titulo' => 'Iniciar Sesión',
            'csrf_token' => $this->gestorAuth->generarTokenCSRF()
        ];

        $this->cargarVista('auth/login', $datos);
    }

    /**
     * Procesa el login del usuario
     */
    public function procesarLogin(): void 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
                return;
            }

            // Verificar token CSRF
            if (!$this->gestorAuth->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $this->responderJSON(['success' => false, 'message' => 'Token de seguridad inválido']);
                return;
            }

            $correo = Validador::sanitizarCorreo($_POST['email'] ?? '');
            $contrasena = $_POST['password'] ?? '';

            // Validaciones básicas
            if (empty($correo) || empty($contrasena)) {
                $this->responderJSON(['success' => false, 'message' => 'Correo y contraseña son requeridos']);
                return;
            }

            if (!Validador::validarCorreo($correo)) {
                $this->responderJSON(['success' => false, 'message' => 'Formato de correo inválido']);
                return;
            }

            // Intentar autenticación
            if ($this->gestorAuth->autenticar($correo, $contrasena)) {
                $usuario = $this->gestorAuth->obtenerUsuarioActual();
                $redireccion = $this->obtenerRedireccionSegunTipo($usuario['tipo_usuario'] ?? 'cliente');
                
                $this->responderJSON([
                    'success' => true, 
                    'message' => 'Login exitoso',
                    'redirect' => $redireccion
                ]);
            } else {
                $this->responderJSON(['success' => false, 'message' => 'Credenciales inválidas']);
            }

        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->responderJSON(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Muestra la página de registro
     */
    public function mostrarRegistro(): void 
    {
        if ($this->gestorAuth->estaAutenticado()) {
            $this->redirigirSegunTipoUsuario();
            return;
        }

        $datos = [
            'titulo' => 'Registro de Usuario',
            'csrf_token' => $this->gestorAuth->generarTokenCSRF()
        ];

        $this->cargarVista('auth/registro', $datos);
    }

    /**
     * Procesa el registro de un nuevo usuario
     */
    public function procesarRegistro(): void 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
                return;
            }

            // Verificar token CSRF
            if (!$this->gestorAuth->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $this->responderJSON(['success' => false, 'message' => 'Token de seguridad inválido']);
                return;
            }

            // Recopilar y sanitizar datos
            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'email' => Validador::sanitizarCorreo($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'password_confirmacion' => $_POST['password_confirmar'] ?? '', // Campo del formulario
                'tipo' => in_array($_POST['tipo_usuario'] ?? '', ['cliente', 'artesano']) ? $_POST['tipo_usuario'] : 'cliente', // Campo del formulario
                'telefono' => trim($_POST['telefono'] ?? ''),
                'direccion' => '' // No existe en el formulario, usar vacío
            ];

            // Validaciones
            $errores = $this->validarDatosRegistro($datos);
            if (!empty($errores)) {
                $erroresFormateados = [];
                foreach ($errores as $error) {
                    // Crear un formato que coincida con lo que el frontend espera
                    $campo = explode(' ', $error)[0];
                    $campo = strtolower(str_replace(['El', 'La', 'Las', 'Los'], '', $campo));
                    $erroresFormateados[$campo] = $error;
                }
                $this->responderJSON(['success' => false, 'message' => implode(', ', $errores), 'errors' => $erroresFormateados]);
                return;
            }

            // Verificar si el usuario ya existe
            if ($this->modeloUsuario->existePorCorreo($datos['email'])) {
                $this->responderJSON(['success' => false, 'message' => 'Ya existe un usuario con este correo']);
                return;
            }

            // Crear usuario
            $datosUsuario = [
                'nombre' => $datos['nombre'], // Solo nombre, no hay apellido separado
                'correo' => $datos['email'], // El modelo usa 'correo'
                'contrasena' => $datos['password'], // El modelo usa 'contrasena'
                'tipo_usuario' => $datos['tipo'], // El modelo usa 'tipo_usuario'
                'telefono' => $datos['telefono'],
                'direccion' => $datos['direccion']
            ];

            $resultado = $this->modeloUsuario->crear($datosUsuario);

            if ($resultado['exitoso']) {
                $userId = $resultado['id'];
                
                // Si es artesano, crear tienda
                if ($datos['tipo'] === 'artesano') {
                    $this->crearTiendaParaArtesano($userId, $datos);
                }

                // Auto-login después del registro
                $this->gestorAuth->autenticar($datos['email'], $datos['password']);
                
                $redireccion = $this->obtenerRedireccionSegunTipo($datos['tipo']);
                
                $this->responderJSON([
                    'success' => true, 
                    'message' => 'Registro exitoso',
                    'redirect' => $redireccion
                ]);
            } else {
                $this->responderJSON(['success' => false, 'message' => $resultado['mensaje']]);
            }

        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            $this->responderJSON(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Procesa la recuperación de contraseña
     */
    public function recuperarContrasena(): void 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
                return;
            }

            $correo = Validador::sanitizarCorreo($_POST['email'] ?? '');

            if (empty($correo) || !Validador::validarCorreo($correo)) {
                $this->responderJSON(['success' => false, 'message' => 'Correo inválido']);
                return;
            }

            // Verificar si el usuario existe
            if (!$this->modeloUsuario->existePorCorreo($correo)) {
                // Por seguridad, no revelamos si el usuario existe o no
                $this->responderJSON(['success' => true, 'message' => 'Si el correo existe, recibirás un enlace de recuperación']);
                return;
            }

            // Generar token de recuperación
            $token = $this->generarTokenRecuperacion();
            
            // Guardar token en la base de datos (implementar método en Usuario)
            $this->modeloUsuario->guardarTokenRecuperacion($correo, $token);

            // Enviar correo de recuperación
            $servicioCorreo = new ServicioCorreo();
            $enlaceRecuperacion = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;
            
            if ($servicioCorreo->enviarRecuperacion($correo, $enlaceRecuperacion)) {
                $this->responderJSON(['success' => true, 'message' => 'Se ha enviado un enlace de recuperación a tu correo']);
            } else {
                $this->responderJSON(['success' => false, 'message' => 'Error al enviar el correo de recuperación']);
            }

        } catch (Exception $e) {
            error_log("Error en recuperación de contraseña: " . $e->getMessage());
            $this->responderJSON(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Procesa el logout
     */
    public function logout(): void 
    {
        $this->gestorAuth->cerrarSesion();
        header('Location: /artesanoDigital/login');
        exit;
    }

    /**
     * Valida los datos del registro
     */
    private function validarDatosRegistro(array $datos): array 
    {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        } elseif (strlen($datos['nombre']) < 2) {
            $errores[] = 'El nombre debe tener al menos 2 caracteres';
        }

        if (empty($datos['email'])) {
            $errores[] = 'El correo es requerido';
        } elseif (!Validador::validarCorreo($datos['email'])) {
            $errores[] = 'El formato del correo es inválido';
        }

        if (empty($datos['password'])) {
            $errores[] = 'La contraseña es requerida';
        } elseif (strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if ($datos['password'] !== $datos['password_confirmacion']) {
            $errores[] = 'Las contraseñas no coinciden';
        }

        if (!empty($datos['telefono']) && !Validador::validarTelefono($datos['telefono'])) {
            $errores[] = 'El formato del teléfono es inválido';
        }

        return $errores;
    }

    /**
     * Crea una tienda para un artesano recién registrado
     */
    private function crearTiendaParaArtesano(int $userId, array $datosUsuario): void 
    {
        try {
            $tienda = new Tienda();
            $datosTienda = [
                'usuario_id' => $userId,
                'nombre' => 'Tienda de ' . $datosUsuario['nombre'], // Solo nombre
                'descripcion' => 'Tienda de artesanías creada automáticamente',
                'activa' => 1,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];
            
            $tienda->crear($datosTienda);
        } catch (Exception $e) {
            error_log("Error al crear tienda para artesano: " . $e->getMessage());
        }
    }

    /**
     * Redirecciona según el tipo de usuario
     */
    private function redirigirSegunTipoUsuario(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $redireccion = $this->obtenerRedireccionSegunTipo($usuario['tipo_usuario'] ?? 'cliente');
        header("Location: $redireccion");
        exit;
    }

    /**
     * Obtiene la URL de redirección según el tipo de usuario
     */
    private function obtenerRedireccionSegunTipo(string $tipo): string 
    {
        return match($tipo) {
            'artesano' => '/artesanoDigital/dashboard/artesano',
            'admin' => '/artesanoDigital/admin/dashboard',
            default => '/artesanoDigital/dashboard/cliente'
        };
    }

    /**
     * Carga una vista con datos
     */
    private function cargarVista(string $vista, array $datos = []): void 
    {
        // Extraer las variables para que estén disponibles en la vista
        extract($datos);
        
        // Incluir la vista directamente
        include __DIR__ . "/../views/{$vista}.php";
    }

    /**
     * Responde con JSON
     */
    private function responderJSON(array $datos): void 
    {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Asegurar que solo se envíe JSON
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Versión segura de responderJSON que no depende del estado del objeto
     */
    private function responderJSONSeguro(array $datos): void 
    {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Asegurar que solo se envíe JSON
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Método de depuración para verificar que las respuestas JSON funcionen
     */
    public function debugAPI(): void 
    {
        $this->responderJSON([
            'success' => true,
            'message' => 'API funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'session_status' => session_status(),
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A'
        ]);
    }
}
