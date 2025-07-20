# 🎨 Artesano Digital - Panamá Oeste

## 📋 Descripción

Artesano Digital es una plataforma de comercio electrónico diseñada específicamente para artesanos de Panamá Oeste. Permite a los artesanos crear tiendas virtuales para vender sus productos únicos y a los clientes descubrir y comprar artesanías auténticas.

## ✨ Características Principales

- **🏪 Tiendas Virtuales**: Los artesanos pueden crear y gestionar sus propias tiendas
- **🛒 Carrito de Compras**: Sistema completo de carrito con gestión de productos
- **💳 Múltiples Métodos de Pago**: Soporte para tarjetas y Yappy
- **📱 Diseño Responsivo**: Optimizado para dispositivos móviles y desktop
- **🔐 Seguridad Avanzada**: Protección CSRF, validación de entrada, sesiones seguras
- **📧 Sistema de Notificaciones**: Notificaciones en tiempo real para usuarios
- **🎨 Paleta de Colores Crema**: Diseño elegante y coherente

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8.0+** - Lenguaje principal
- **MySQL** - Base de datos
- **PDO** - Capa de abstracción de base de datos
- **Patrones de Diseño**: Singleton, Strategy, Decorator

### Frontend
- **HTML5** - Estructura semántica
- **CSS3** - Estilos con variables CSS
- **JavaScript (ES6+)** - Interactividad
- **Next.js** - Framework React (para componentes modernos)
- **TailwindCSS** - Framework de utilidades CSS

### Herramientas
- **Composer** - Gestión de dependencias PHP
- **npm/pnpm** - Gestión de dependencias JavaScript
- **Git** - Control de versiones

## 📦 Instalación

### Prerrequisitos
- XAMPP/WAMP/LAMP (Apache, MySQL, PHP 8.0+)
- Composer
- Node.js y npm/pnpm

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tu-usuario/artesano-digital.git
   cd artesano-digital
   ```

2. **Configurar base de datos**
   - Crear base de datos MySQL
   - Importar `estructura.sql`
   ```sql
   mysql -u root -p artesano_digital < estructura.sql
   ```

3. **Configurar variables de entorno**
   - Copiar `.env.example` a `.env`
   - Configurar credenciales de base de datos
   ```bash
   cp .env.example .env
   ```

4. **Instalar dependencias PHP**
   ```bash
   composer install
   ```

5. **Instalar dependencias JavaScript**
   ```bash
   npm install
   # o
   pnpm install
   ```

6. **Configurar permisos**
   ```bash
   chmod -R 755 uploads/
   chmod -R 755 public/
   ```

## ⚙️ Configuración

### Variables de Entorno (.env)

```env
# Base de datos
DB_HOST=localhost
DB_DATABASE=artesano_digital
DB_USERNAME=root
DB_PASSWORD=

# Aplicación
APP_URL=http://localhost/artesanoDigital
APP_DEBUG=true

# Uploads
UPLOAD_MAX_SIZE=5242880
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif
```

### Estructura de Directorios

```
artesanoDigital/
├── config/           # Configuración de base de datos
├── controllers/      # Controladores MVC
├── models/          # Modelos de datos
├── views/           # Vistas y plantillas
├── utils/           # Utilidades y helpers
├── patrones/        # Implementación de patrones de diseño
├── services/        # Servicios (correo, notificaciones)
├── assets/          # CSS, JS, imágenes estáticas
├── public/          # Archivos públicos
├── uploads/         # Archivos subidos por usuarios
├── .htaccess        # Configuración Apache
├── composer.json    # Dependencias PHP
├── package.json     # Dependencias JavaScript
└── estructura.sql   # Esquema de base de datos
```

## 🏗️ Arquitectura

### Patrones de Diseño Implementados

1. **Singleton**
   - `Database`: Gestión única de conexión a BD
   - `GestorAutenticacion`: Manejo centralizado de sesiones

2. **Strategy**
   - `EstrategiaMetodoPago`: Diferentes métodos de pago
   - Fácil extensión para nuevos métodos

3. **Decorator**
   - `DecoradorNotificacion`: Extensión de funcionalidades de notificaciones

### Seguridad

- ✅ Protección CSRF en formularios
- ✅ Validación y sanitización de entradas
- ✅ Sentencias preparadas (prevención SQL injection)
- ✅ Sesiones seguras con regeneración de ID
- ✅ Validación de uploads de archivos
- ✅ Headers de seguridad HTTP

## 📚 API Endpoints

### Autenticación
- `POST /login` - Iniciar sesión
- `POST /registro` - Crear cuenta
- `GET /logout` - Cerrar sesión

### Productos
- `GET /productos` - Listar productos
- `GET /producto/{id}` - Detalle de producto
- `POST /artesano/productos` - Crear producto (artesanos)

### Carrito
- `POST /carrito/agregar` - Agregar producto
- `POST /carrito/actualizar` - Actualizar cantidad
- `POST /carrito/eliminar` - Eliminar producto

### API AJAX
- `GET /api/notificaciones` - Obtener notificaciones
- `POST /api/notificaciones/marcar-leida` - Marcar como leída

## 🎨 Guía de Estilos

### Paleta de Colores
```css
:root {
  --color-crema-claro: #faf8f5;
  --color-crema: #f5e6d3;
  --color-beige: #e8d5b7;
  --color-tostado: #d4a574;
  --color-marron: #8b4513;
}
```

### Componentes
- Botones con estados hover/focus
- Cards con sombras suaves
- Forms con validación visual
- Modals responsivos

## 🧪 Testing

### Ejecutar Tests
```bash
composer test
```

### Tests Incluidos
- Validación de modelos
- Autenticación y autorización
- Procesamiento de pagos
- Upload de archivos

## 🚀 Deployment

### Producción
1. Configurar variables de entorno para producción
2. Compilar assets
   ```bash
   npm run build
   ```
3. Configurar HTTPS y SSL
4. Configurar backups automáticos de BD

### Recomendaciones
- Usar servidor con PHP 8.0+ y MySQL 5.7+
- Configurar límites de memoria apropiados
- Habilitar compresión gzip
- Configurar CDN para assets estáticos

## 🤝 Contribución

1. Fork del proyecto
2. Crear rama feature (`git checkout -b feature/nueva-caracteristica`)
3. Commit cambios (`git commit -am 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Crear Pull Request

### Estándares de Código
- PSR-12 para PHP
- ESLint para JavaScript
- Comentarios en español
- Tests para nuevas funcionalidades

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

## 👥 Equipo

- **Desarrollador Principal**: [Tu Nombre]
- **Diseño UX/UI**: [Nombre del diseñador]
- **Testing**: [Nombre del tester]

## 🆘 Soporte

Para reportar bugs o solicitar nuevas características:
- **Issues**: [GitHub Issues](https://github.com/tu-usuario/artesano-digital/issues)
- **Email**: soporte@artesanodigital.com
- **Documentación**: [Wiki del proyecto](https://github.com/tu-usuario/artesano-digital/wiki)

## 📈 Roadmap

### v1.1
- [ ] Chat en tiempo real entre clientes y artesanos
- [ ] Sistema de reseñas y calificaciones
- [ ] Integración con redes sociales

### v1.2
- [ ] App móvil nativa
- [ ] Dashboard de analytics para artesanos
- [ ] Sistema de cupones y descuentos

### v2.0
- [ ] Marketplace multi-idioma
- [ ] Integración con plataformas de envío
- [ ] Sistema de afiliados

---

**Artesano Digital** - Conectando la tradición artesanal de Panamá Oeste con el mundo digital 🇵🇦
