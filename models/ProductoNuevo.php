<?php
/**
 * Modelo Producto - Gestión de productos de artesanos
 * Responsabilidad: CRUD de productos y operaciones relacionadas
 */

class Producto 
{
    private $conexion;

    public function __construct() 
    {
        // Usar una conexión simple por ahora
        $this->conexion = null;
    }

    /**
     * Obtiene todos los productos
     * @return array
     */
    public function obtenerTodos()
    {
        // Datos de prueba mientras configuramos la base de datos
        return [
            [
                'id' => 1,
                'nombre' => 'Mola Tradicional Guna',
                'descripcion' => 'Hermosa mola hecha a mano por artesanas Guna con diseños tradicionales y colores vibrantes.',
                'precio' => 85.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 5,
                'artesano' => 'María González',
                'tienda' => 'Artesanías Guna'
            ],
            [
                'id' => 2,
                'nombre' => 'Vasija de Cerámica La Arena',
                'descripcion' => 'Cerámica tradicional de La Arena, Herrera. Perfecta para decoración del hogar.',
                'precio' => 45.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 8,
                'artesano' => 'Carlos Mendoza',
                'tienda' => 'Cerámica Tradicional'
            ],
            [
                'id' => 3,
                'nombre' => 'Sombrero Pintao',
                'descripcion' => 'Auténtico sombrero pintao tejido a mano en La Pintada, Coclé.',
                'precio' => 120.00,
                'imagen' => '/artesanoDigital/public/placeholder.jpg',
                'stock' => 3,
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
        $productos = $this->obtenerTodos();
        
        foreach ($productos as $producto) {
            if ($producto['id'] == $id) {
                return $producto;
            }
        }
        
        return null;
    }

    /**
     * Busca productos por término
     * @param string $termino
     * @return array
     */
    public function buscar($termino)
    {
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

    /**
     * Obtiene productos por categoría
     * @param string $categoria
     * @return array
     */
    public function obtenerPorCategoria($categoria)
    {
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
}
