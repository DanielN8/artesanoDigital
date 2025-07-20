<?php
/**
 * Definiciones de tipos para Intelephense
 * Este archivo ayuda al IDE a reconocer las clases del proyecto
 */

// Incluir todas las clases del proyecto
require_once __DIR__ . '/models/Producto.php';
require_once __DIR__ . '/controllers/ControladorProductos.php';

// Definición de tipos para mejor autocompletado
/**
 * @class Producto
 * @method array obtenerTodos()
 * @method array|null obtenerPorId(int $id)
 * @method array buscar(string $termino)
 * @method array obtenerPorCategoria(string $categoria)
 */

/**
 * @class ControladorProductos
 * @method void mostrarCatalogo()
 * @method void mostrarDetalle(int $id)
 */
