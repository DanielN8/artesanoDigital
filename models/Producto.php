<?php
/**
 * Modelo Producto - Gestión de productos de artesanos
 * Responsabilidad: CRUD de productos y operaciones relacionadas
 */

require_once dirname(__FILE__) . '/../config/Database.php';

// No se usa namespace para compatibilidad con el código existente
// pero se documenta para claridad
/** @package Models */
class Producto 
{
    private $conexion;

    public function __construct() 
    {
        try {
            $database = Config\Database::obtenerInstancia();
            $this->conexion = $database->obtenerConexion();
        } catch (Exception $e) {
            // Fallback a datos de prueba si no hay conexión BD
            $this->conexion = null;
            error_log("Error conectando BD: " . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los productos
     * @return array
     */
    public function obtenerTodos()
    {
        if ($this->conexion === null) {
            return $this->obtenerDatosPrueba();
        }

        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    p.id_producto as id,
                    p.nombre,
                    p.descripcion,
                    p.precio,
                    p.imagen,
                    p.stock,
                    u.nombre as artesano,
                    t.nombre_tienda as tienda
                FROM productos p
                INNER JOIN tiendas t ON p.id_tienda = t.id_tienda
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE p.activo = 1
                ORDER BY p.fecha_creacion DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error al obtener productos: " . $e->getMessage());
            return $this->obtenerDatosPrueba();
        }
    }

    /**
     * Datos de prueba mientras configuramos la base de datos
     * @return array
     */
    private function obtenerDatosPrueba()
    {
        return [
            [
                'id' => 1,
                'id_producto' => 1,
                'nombre' => 'Mola Tradicional Guna',
                'descripcion' => 'Hermosa mola hecha a mano por artesanas Guna con diseños tradicionales y colores vibrantes.',
                'precio' => 85.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 5,
                'id_tienda' => 1,
                'id_usuario_tienda' => 1,
                'artesano' => 'María González',
                'tienda' => 'Artesanías Guna'
            ],
            [
                'id' => 2,
                'id_producto' => 2,
                'nombre' => 'Vasija de Cerámica La Arena',
                'descripcion' => 'Cerámica tradicional de La Arena, Herrera. Perfecta para decoración del hogar.',
                'precio' => 45.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 8,
                'id_tienda' => 2,
                'id_usuario_tienda' => 2,
                'artesano' => 'Carlos Mendoza',
                'tienda' => 'Cerámica Tradicional'
            ],
            [
                'id' => 3,
                'id_producto' => 3,
                'nombre' => 'Sombrero Pintao',
                'descripcion' => 'Auténtico sombrero pintao tejido a mano en La Pintada, Coclé.',
                'precio' => 120.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 3,
                'id_tienda' => 3,
                'id_usuario_tienda' => 3,
                'artesano' => 'Ana Rodríguez',
                'tienda' => 'Sombreros Pintao'
            ],
            [
                'id' => 4,
                'nombre' => 'Collar de Semillas',
                'descripcion' => 'Collar artesanal hecho con semillas naturales de la región.',
                'precio' => 25.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 12,
                'artesano' => 'Luis Herrera',
                'tienda' => 'Joyería Natural'
            ],
            [
                'id' => 5,
                'nombre' => 'Canasta de Paja Toquilla',
                'descripcion' => 'Canasta tejida en paja toquilla, ideal para el hogar.',
                'precio' => 35.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 6,
                'artesano' => 'Rosa Martínez',
                'tienda' => 'Tejidos Tradicionales'
            ],
            [
                'id' => 6,
                'nombre' => 'Hamaca de Algodón',
                'descripcion' => 'Hamaca tejida en algodón 100% natural, perfecta para descansar.',
                'precio' => 75.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 4,
                'artesano' => 'Pedro Silva',
                'tienda' => 'Hamacas del Oeste'
            ]
        ];
    }

    /**
     * Obtiene un producto por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id)
    {
        if ($this->conexion === null) {
            $productos = $this->obtenerDatosPrueba();
            foreach ($productos as $producto) {
                if ($producto['id'] == $id) {
                    return $producto;
                }
            }
            return null;
        }

        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    p.id_producto as id,
                    p.id_producto,
                    p.nombre,
                    p.descripcion,
                    p.precio,
                    p.imagen,
                    p.stock,
                    p.id_tienda,
                    t.id_usuario as id_usuario_tienda,
                    u.nombre as artesano,
                    t.nombre_tienda as tienda
                FROM productos p
                INNER JOIN tiendas t ON p.id_tienda = t.id_tienda
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE p.id_producto = ? AND p.activo = 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (Exception $e) {
            error_log("Error al obtener producto por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca productos por término
     * @param string $termino
     * @return array
     */
    public function buscar($termino)
    {
        if ($this->conexion === null) {
            // Fallback a búsqueda en memoria con datos de prueba
            $productos = $this->obtenerTodos();
            $resultados = [];
            
            $termino = strtolower($termino);
            
            foreach ($productos as $producto) {
                if (strpos(strtolower($producto['nombre']), $termino) !== false ||
                    strpos(strtolower($producto['descripcion']), $termino) !== false ||
                    strpos(strtolower($producto['artesano']), $termino) !== false) {
                    $resultados[] = $producto;
                }
            }
            
            return $resultados;
        }
        
        // Búsqueda optimizada en base de datos
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    p.id_producto as id,
                    p.nombre,
                    p.descripcion,
                    p.precio,
                    p.imagen,
                    p.stock,
                    u.nombre as artesano,
                    t.nombre_tienda as tienda
                FROM productos p
                INNER JOIN tiendas t ON p.id_tienda = t.id_tienda
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE p.activo = 1 AND (
                    p.nombre LIKE :termino OR
                    p.descripcion LIKE :termino OR
                    u.nombre LIKE :termino
                )
                ORDER BY p.fecha_creacion DESC
            ");
            $stmt->execute(['termino' => '%' . $termino . '%']);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error al buscar productos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene productos por categoría
     * @param string $categoria
     * @return array
     */
    public function obtenerPorCategoria($categoria)
    {
        if ($this->conexion === null) {
            // Fallback a búsqueda en memoria con datos de prueba
            $productos = $this->obtenerTodos();
            $resultados = [];
            
            foreach ($productos as $producto) {
                // Simulamos categorías basadas en palabras clave
                $esCategoria = false;
                
                switch (strtolower($categoria)) {
                    case 'textiles':
                        $esCategoria = strpos(strtolower($producto['nombre']), 'mola') !== false ||
                                      strpos(strtolower($producto['nombre']), 'hamaca') !== false ||
                                      strpos(strtolower($producto['nombre']), 'canasta') !== false;
                        break;
                    case 'ceramica':
                        $esCategoria = strpos(strtolower($producto['nombre']), 'vasija') !== false ||
                                      strpos(strtolower($producto['nombre']), 'cerámica') !== false;
                        break;
                    case 'joyeria':
                        $esCategoria = strpos(strtolower($producto['nombre']), 'collar') !== false ||
                                      strpos(strtolower($producto['nombre']), 'joyería') !== false;
                        break;
                    case 'sombreros':
                        $esCategoria = strpos(strtolower($producto['nombre']), 'sombrero') !== false;
                        break;
                }
                
                if ($esCategoria) {
                    $resultados[] = $producto;
                }
            }
            
            return $resultados;
        }
        
        // Suponiendo que hay una tabla categorias o una relación producto_categorias
        try {
            // NOTA: Como actualmente no existe una tabla de categorías, realizamos una búsqueda basada en palabras clave
            // Este código está preparado para cuando se implemente una tabla categorías
            /*
            $stmt = $this->conexion->prepare("
                SELECT 
                    p.id_producto as id,
                    p.nombre,
                    p.descripcion,
                    p.precio,
                    p.imagen,
                    p.stock,
                    u.nombre as artesano,
                    t.nombre_tienda as tienda
                FROM productos p
                INNER JOIN tiendas t ON p.id_tienda = t.id_tienda
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                INNER JOIN producto_categorias pc ON p.id_producto = pc.id_producto
                INNER JOIN categorias c ON pc.id_categoria = c.id_categoria
                WHERE p.activo = 1 AND c.nombre = :categoria
                ORDER BY p.fecha_creacion DESC
            ");
            */
            
            // En su lugar, usamos el método de palabras clave directamente
            return $this->buscarProductosPorPalabrasClave($categoria);
            
            // Este código se utilizará cuando exista la tabla de categorías
            //$stmt->execute(['categoria' => $categoria]);
            //$resultados = $stmt->fetchAll();
            
            // Si no se encontró nada, intentar búsqueda por palabras clave (fallback)
            //if (empty($resultados)) {
            //    return $this->buscarProductosPorPalabrasClave($categoria);
            //}
            
            //return $resultados;
        } catch (Exception $e) {
            error_log("Error al obtener productos por categoría: " . $e->getMessage());
            // Intentar fallback a búsqueda por palabras clave
            return $this->buscarProductosPorPalabrasClave($categoria);
        }
    }
    
    /**
     * Método auxiliar para buscar productos por palabras clave relacionadas a una categoría
     * @param string $categoria
     * @return array
     */
    private function buscarProductosPorPalabrasClave($categoria)
    {
        // Mapeo de categorías a palabras clave para búsqueda
        $palabrasClave = [
            'textiles' => ['mola', 'hamaca', 'canasta', 'tejido'],
            'ceramica' => ['vasija', 'cerámica', 'barro', 'alfarería'],
            'joyeria' => ['collar', 'pulsera', 'joya', 'pendiente'],
            'sombreros' => ['sombrero', 'pintao', 'panama']
        ];
        
        if (!isset($palabrasClave[strtolower($categoria)])) {
            return [];
        }
        
        $terminos = $palabrasClave[strtolower($categoria)];
        $resultados = [];
        
        foreach ($terminos as $termino) {
            $resultadosBusqueda = $this->buscar($termino);
            foreach ($resultadosBusqueda as $producto) {
                // Evitar duplicados comprobando si ya existe el ID
                $existe = false;
                foreach ($resultados as $productoExistente) {
                    if ($productoExistente['id'] === $producto['id']) {
                        $existe = true;
                        break;
                    }
                }
                
                if (!$existe) {
                    $resultados[] = $producto;
                }
            }
        }
        
        return $resultados;
    }

    /**
     * Verifica si hay stock suficiente de un producto
     * @param int $idProducto
     * @param int $cantidadSolicitada
     * @return bool
     */
    public function verificarStock(int $idProducto, int $cantidadSolicitada): bool 
    {
        if ($this->conexion === null) {
            // Con datos de prueba, asumir stock suficiente
            return true;
        }

        try {
            $stmt = $this->conexion->prepare("
                SELECT stock 
                FROM productos 
                WHERE id_producto = ? AND activo = 1
            ");
            $stmt->execute([$idProducto]);
            $producto = $stmt->fetch();
            
            return $producto && $producto['stock'] >= $cantidadSolicitada;
            
        } catch (Exception $e) {
            error_log("Error al verificar stock: " . $e->getMessage());
            return false;
        }
    }
}
