
# 📚 Documentación Técnica - Artesano Digital

---

## Índice
1. Introducción
2. Ejecución del Proyecto
3. Arquitectura del Sistema
4. Base de Datos
5. Estructura de Dominios y Patrones
   - Singleton
   - Strategy
   - Decorator
6. Estructura de Carpetas
7. API Endpoints
8. Seguridad
9. Testing
10. Equipo y Contacto

---

## 1. Introducción
Artesano Digital es una plataforma e-commerce para artesanos de Panamá Oeste. Permite la gestión de productos, ventas, notificaciones y pagos, con arquitectura MVC y patrones de diseño profesionales (Singleton, Strategy, Decorator).

## 2. Ejecución del Proyecto

### Requisitos
- XAMPP/WAMP/LAMP (PHP >= 7.4, MySQL >= 5.7)
- Composer
- Node.js y npm/pnpm

### Instalación
1. Clona el repositorio:
   ```bash
   git clone https://github.com/DanielN8/artesano_digital.git
   cd artesano_digital
   ```
2. Coloca el proyecto en `c:/xampp/htdocs/artesanoDigital` (renombrar la carpeta del proyecto a "artesanoDigital")
3. Crea la base de datos y ejecuta los scripts:
   - `estructura.sql`
   - `artesano_digital.sql`
4. Configura variables de entorno en `config/DotEnv.php` o `.env`
5. Instala dependencias PHP:
   ```bash
   composer install
   ```
6. Instala dependencias frontend:
   ```bash
   npm install
   npm run build
   ```
7. Inicia Apache y MySQL desde XAMPP
8. Accede a `http://localhost/artesanoDigital/`

## 3. Arquitectura del Sistema
- **MVC**: Separación en Modelos, Vistas y Controladores
- **API REST**: Endpoints para productos, pedidos, notificaciones
- **Patrones de Diseño**: Singleton, Strategy, Decorator
- **Backend**: PHP, MySQL, PDO

## 4. Base de Datos
- Esquema en `estructura.sql`
- Tablas principales: `usuarios`, `tiendas`, `productos`, `pedidos`, `notificaciones`, `sesiones`
- Relaciones con claves foráneas y restricciones
- Índices para optimización de consultas

## 5. Estructura de Dominios y Patrones

```
app/Domain/
├── Artesano/
│   ├── Models/
│   │   ├── Tienda.php
│   │   └── Usuario.php
│   └── Actions/
│       └── GestionTienda.php
├── Producto/
│   ├── Models/
│   │   └── Producto.php
│   └── Actions/
│       └── GestionProducto.php
├── Pedido/
│   ├── Models/
│   │   └── Pedido.php
│   └── Actions/
│       └── GestionPedido.php
├── Notificacion/
│   ├── Models/
│   │   └── Notificacion.php
│   └── Actions/
│       └── GestionNotificacion.php
└── Pagos/
    ├── Models/
    │   └── Pago.php
    └── Actions/
        └── ProcesadorPagos.php (Strategy)

utils/
├── GestorAutenticacion.php (Singleton)

config/
├── Database.php (Singleton)

patrones/
├── EstrategiaMetodoPago.php (Strategy)
├── MetodosPago.php (Strategy)
├── DecoradorProducto.php (Decorator)
├── ProductoDecorador.php (Decorator)
```

### Patrones de Diseño

#### (SINGLETON)
- |_utils/GestorAutenticacion.php_|: Control de sesión y autenticación
- |_config/Database.php_|: Conexión única a la base de datos

#### (STRATEGY)
- |_patrones/EstrategiaMetodoPago.php_|, |_patrones/MetodosPago.php_|: Métodos de pago intercambiables
- ProcesadorPagos.php: Selección dinámica de estrategia

#### (DECORATOR)
- |_patrones/DecoradorProducto.php_|, |_patrones/ProductoDecorador.php_|: Descuentos y promociones en productos


Patrones de Diseño en Artesano Digital

1. Singleton
    - Uso: Garantiza una única instancia para recursos globales como la conexión a la base de datos y el gestor de autenticación.
    - Ejemplo: `Database.php` centraliza la conexión PDO; `GestorAutenticacion.php` controla la sesión de usuario.

2. Strategy
    - Uso: Permite intercambiar dinámicamente la lógica de métodos de pago, facilitando la extensión y el mantenimiento.
    - Ejemplo: `EstrategiaMetodoPago.php` y `MetodosPago.php` definen y aplican diferentes formas de pago; `ProcesadorPagos.php` selecciona la estrategia adecuada.

3. Decorator
    - Uso: Añade funcionalidades extra a productos, como descuentos o promociones, sin modificar la clase principal.
    - Ejemplo: `DecoradorProducto.php` y `ProductoDecorador.php` permiten aplicar descuentos y promociones de manera flexible.


## 6. Estructura de Carpetas
```
artesanoDigital/
├── api/                # Endpoints REST
├── assets/             # CSS, JS, imágenes
├── config/             # Configuración
├── controllers/        # Controladores MVC
├── models/             # Modelos de datos
├── patrones/           # Patrones de diseño
├── public/             # Archivos públicos
├── uploads/            # Archivos subidos
├── utils/              # Utilidades y helpers
├── views/              # Vistas y plantillas
├── docs/               # Documentación
├── estructura.sql      # Esquema BD
├── composer.json       # Dependencias PHP
├── package.json        # Dependencias JS
└── README.md           # Documentación principal
```

## 7. API Endpoints
- `/api/productos.php` - CRUD de productos
- `/api/pedidos.php` - Gestión de pedidos
- `/api/notificaciones.php` - Notificaciones
- `/api/carrito.php` - Carrito de compras

## 8. Seguridad
- Protección CSRF
- Validación y sanitización de entradas
- Sentencias preparadas (SQL Injection)
- Sesiones seguras
- Validación de uploads