<?php
namespace Patrones;


// Interfaz ProductoInterface

interface ProductoInterface {

    public function obtenerPrecio(): float;

    public function obtenerDetalles(): array;
}

/**
 * Clase ProductoBase
 * Representa un producto básico sin descuentos
 */
class ProductoBase implements ProductoInterface {
    protected $id;
    protected $nombre;
    protected $descripcion;
    protected $precio;
    protected $stock;
    protected $imagen;
    protected $id_tienda;
    protected $activo;
    
    public function __construct(array $datos) {
        $this->id = $datos['id_producto'] ?? null;
        $this->nombre = $datos['nombre'] ?? '';
        $this->descripcion = $datos['descripcion'] ?? '';
        $this->precio = floatval($datos['precio'] ?? 0);
        $this->stock = intval($datos['stock'] ?? 0);
        $this->imagen = $datos['imagen'] ?? '';
        $this->id_tienda = $datos['id_tienda'] ?? null;
        $this->activo = isset($datos['activo']) ? (bool)$datos['activo'] : true;
    }
    
    public function obtenerPrecio(): float {
        return $this->precio;
    }
    
    public function obtenerDetalles(): array {
        return [
            'id_producto' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'precio_final' => $this->precio, // Sin descuento es el mismo
            'stock' => $this->stock,
            'imagen' => $this->imagen,
            'id_tienda' => $this->id_tienda,
            'activo' => $this->activo,
            'tiene_descuento' => false
        ];
    }
}

/**
 * Clase Decorador Abstracto - Base para todos los decoradores
 */
abstract class ProductoDecorador implements ProductoInterface {
    protected $producto;
    
    public function __construct(ProductoInterface $producto) {
        $this->producto = $producto;
    }
    
    public function obtenerPrecio(): float {
        return $this->producto->obtenerPrecio();
    }
    
    public function obtenerDetalles(): array {
        return $this->producto->obtenerDetalles();
    }
}

/**
 * Decorador concreto - Descuento por porcentaje
 */
class ProductoConDescuentoPorcentaje extends ProductoDecorador {
    protected $porcentajeDescuento;
    protected $razonDescuento;
    protected $fechaFinDescuento;
    
    public function __construct(
        ProductoInterface $producto, 
        float $porcentajeDescuento, 
        string $razonDescuento = '',
        ?string $fechaFinDescuento = null
    ) {
        parent::__construct($producto);
        $this->porcentajeDescuento = $porcentajeDescuento;
        $this->razonDescuento = $razonDescuento;
        $this->fechaFinDescuento = $fechaFinDescuento;
    }
    
    public function obtenerPrecio(): float {
        $precioOriginal = $this->producto->obtenerPrecio();
        $descuento = $precioOriginal * ($this->porcentajeDescuento / 100);
        return $precioOriginal - $descuento;
    }
    
    public function obtenerDetalles(): array {
        $detalles = $this->producto->obtenerDetalles();
        $precioOriginal = $detalles['precio'];
        $precioFinal = $this->obtenerPrecio();
        
        $detalles['tiene_descuento'] = true;
        $detalles['precio_original'] = $precioOriginal;
        $detalles['precio_final'] = $precioFinal;
        $detalles['descuento_porcentaje'] = $this->porcentajeDescuento;
        $detalles['razon_descuento'] = $this->razonDescuento;
        $detalles['fecha_fin_descuento'] = $this->fechaFinDescuento;
        
        return $detalles;
    }
}

/**
 * Decorador concreto - Descuento por monto fijo
 */
class ProductoConDescuentoMonto extends ProductoDecorador {
    protected $montoDescuento;
    protected $razonDescuento;
    protected $fechaFinDescuento;
    
    public function __construct(
        ProductoInterface $producto, 
        float $montoDescuento, 
        string $razonDescuento = '',
        ?string $fechaFinDescuento = null
    ) {
        parent::__construct($producto);
        $this->montoDescuento = $montoDescuento;
        $this->razonDescuento = $razonDescuento;
        $this->fechaFinDescuento = $fechaFinDescuento;
    }
    
    public function obtenerPrecio(): float {
        $precioOriginal = $this->producto->obtenerPrecio();
        return max(0, $precioOriginal - $this->montoDescuento);
    }
    
    public function obtenerDetalles(): array {
        $detalles = $this->producto->obtenerDetalles();
        $precioOriginal = $detalles['precio'];
        $precioFinal = $this->obtenerPrecio();
        
        $detalles['tiene_descuento'] = true;
        $detalles['precio_original'] = $precioOriginal;
        $detalles['precio_final'] = $precioFinal;
        $detalles['descuento_monto'] = $this->montoDescuento;
        $detalles['razon_descuento'] = $this->razonDescuento;
        $detalles['fecha_fin_descuento'] = $this->fechaFinDescuento;
        
        return $detalles;
    }
}

/**
 * Factory para crear productos con o sin descuentos
 */
class ProductoFactory {
    public static function crearProducto(array $datos): ProductoInterface {
        $productoBase = new ProductoBase($datos);
        
        // Verificar si tiene descuento por porcentaje
        if (isset($datos['descuento_porcentaje']) && $datos['descuento_porcentaje'] > 0) {
            return new ProductoConDescuentoPorcentaje(
                $productoBase,
                floatval($datos['descuento_porcentaje']),
                $datos['razon_descuento'] ?? '',
                $datos['fecha_fin_descuento'] ?? null
            );
        }
        
        // Verificar si tiene descuento por monto fijo
        if (isset($datos['descuento_monto']) && $datos['descuento_monto'] > 0) {
            return new ProductoConDescuentoMonto(
                $productoBase,
                floatval($datos['descuento_monto']),
                $datos['razon_descuento'] ?? '',
                $datos['fecha_fin_descuento'] ?? null
            );
        }
        
        // Sin descuento, devolver el producto base
        return $productoBase;
    }
}
