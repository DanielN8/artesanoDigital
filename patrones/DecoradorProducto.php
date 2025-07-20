<?php
/**
 * Patrón Decorator para precios de productos
 * Responsabilidad: Añadir funcionalidades de descuentos a los productos
 */

namespace Patrones;

/**
 * Interfaz base para componentes de producto
 */
interface ComponenteProducto 
{
    public function getPrecio(): float;
    public function getNombre(): string;
    public function getDescripcion(): string;
    public function getId(): int;
    public function getTienda(): int;
    public function getStock(): int;
    public function getImagen(): string;
    public function getActivo(): bool;
}

/**
 * Producto concreto (componente concreto)
 */
class ProductoBase implements ComponenteProducto 
{
    protected int $id;
    protected string $nombre;
    protected string $descripcion;
    protected float $precio;
    protected int $id_tienda;
    protected int $stock;
    protected string $imagen;
    protected bool $activo;

    public function __construct(array $datosProducto) 
    {
        $this->id = $datosProducto['id_producto'] ?? 0;
        $this->nombre = $datosProducto['nombre'] ?? '';
        $this->descripcion = $datosProducto['descripcion'] ?? '';
        $this->precio = floatval($datosProducto['precio'] ?? 0);
        $this->id_tienda = $datosProducto['id_tienda'] ?? 0;
        $this->stock = $datosProducto['stock'] ?? 0;
        $this->imagen = $datosProducto['imagen'] ?? '';
        $this->activo = (bool)($datosProducto['activo'] ?? true);
    }

    public function getPrecio(): float 
    {
        return $this->precio;
    }

    public function getNombre(): string 
    {
        return $this->nombre;
    }

    public function getDescripcion(): string 
    {
        return $this->descripcion;
    }

    public function getId(): int 
    {
        return $this->id;
    }

    public function getTienda(): int 
    {
        return $this->id_tienda;
    }

    public function getStock(): int 
    {
        return $this->stock;
    }

    public function getImagen(): string 
    {
        return $this->imagen;
    }

    public function getActivo(): bool 
    {
        return $this->activo;
    }
}

/**
 * Decorador base para productos
 */
abstract class DecoradorProducto implements ComponenteProducto 
{
    protected ComponenteProducto $producto;

    public function __construct(ComponenteProducto $producto) 
    {
        $this->producto = $producto;
    }

    public function getPrecio(): float 
    {
        return $this->producto->getPrecio();
    }

    public function getNombre(): string 
    {
        return $this->producto->getNombre();
    }

    public function getDescripcion(): string 
    {
        return $this->producto->getDescripcion();
    }

    public function getId(): int 
    {
        return $this->producto->getId();
    }

    public function getTienda(): int 
    {
        return $this->producto->getTienda();
    }

    public function getStock(): int 
    {
        return $this->producto->getStock();
    }

    public function getImagen(): string 
    {
        return $this->producto->getImagen();
    }

    public function getActivo(): bool 
    {
        return $this->producto->getActivo();
    }
}

/**
 * Decorador de Descuento por porcentaje
 */
class DecoradorDescuentoPorcentaje extends DecoradorProducto 
{
    protected float $porcentaje;
    protected string $razonDescuento;
    
    public function __construct(ComponenteProducto $producto, float $porcentaje, string $razonDescuento = 'Oferta especial') 
    {
        parent::__construct($producto);
        $this->porcentaje = $porcentaje;
        $this->razonDescuento = $razonDescuento;
    }

    public function getPrecio(): float 
    {
        $precioOriginal = $this->producto->getPrecio();
        $descuento = $precioOriginal * ($this->porcentaje / 100);
        return $precioOriginal - $descuento;
    }
    
    public function getPrecioOriginal(): float 
    {
        return $this->producto->getPrecio();
    }
    
    public function getPorcentajeDescuento(): float 
    {
        return $this->porcentaje;
    }
    
    public function getRazonDescuento(): string 
    {
        return $this->razonDescuento;
    }
}

/**
 * Decorador de Descuento por monto fijo
 */
class DecoradorDescuentoMonto extends DecoradorProducto 
{
    protected float $montoDescuento;
    protected string $razonDescuento;
    
    public function __construct(ComponenteProducto $producto, float $montoDescuento, string $razonDescuento = 'Oferta especial') 
    {
        parent::__construct($producto);
        $this->montoDescuento = $montoDescuento;
        $this->razonDescuento = $razonDescuento;
    }

    public function getPrecio(): float 
    {
        $precioOriginal = $this->producto->getPrecio();
        return max(0, $precioOriginal - $this->montoDescuento);
    }
    
    public function getPrecioOriginal(): float 
    {
        return $this->producto->getPrecio();
    }
    
    public function getMontoDescuento(): float 
    {
        return $this->montoDescuento;
    }
    
    public function getRazonDescuento(): string 
    {
        return $this->razonDescuento;
    }
}

/**
 * Factoría para crear productos decorados
 */
class FactoriaProducto 
{
    public static function crearProducto(array $datosProducto): ComponenteProducto 
    {
        $producto = new ProductoBase($datosProducto);
        
        // Si tiene descuento por porcentaje
        if (isset($datosProducto['descuento_porcentaje']) && $datosProducto['descuento_porcentaje'] > 0) {
            $razon = $datosProducto['razon_descuento'] ?? 'Oferta especial';
            return new DecoradorDescuentoPorcentaje($producto, $datosProducto['descuento_porcentaje'], $razon);
        }
        
        // Si tiene descuento por monto
        if (isset($datosProducto['descuento_monto']) && $datosProducto['descuento_monto'] > 0) {
            $razon = $datosProducto['razon_descuento'] ?? 'Oferta especial';
            return new DecoradorDescuentoMonto($producto, $datosProducto['descuento_monto'], $razon);
        }
        
        return $producto;
    }
}
