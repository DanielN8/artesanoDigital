<?php
/**
 * Controlador de Inicio - Página principal
 */

namespace Controllers;

class ControladorInicio
{
    public function mostrarInicio(): void
    {
        $titulo = 'Artesano Digital - Panamá Oeste';
        $descripcion = 'Plataforma de comercio electrónico para artesanos de Panamá Oeste';
        
        // Cargar productos destacados
        include 'views/inicio.php';
    }
}
