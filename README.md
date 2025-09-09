# Sistema de Calendario con Login

Un sistema web desarrollado en PHP puro para gestionar un calendario mensual con autenticación de usuarios.

## Características

- ✅ **Autenticación segura** con login y contraseña
- ✅ **Calendario mensual interactivo** con navegación por meses/años
- ✅ **Modal para agregar/editar datos** del día (número 1-10 + observaciones)
- ✅ **Tooltips** para mostrar observaciones al pasar el ratón
- ✅ **Estadísticas del mes** (promedio, mínimo, máximo, días registrados)
- ✅ **Diseño responsive** con Bootstrap 5
- ✅ **Seguridad** con tokens CSRF y prepared statements
- ✅ **Base de datos MySQL** optimizada

## Tecnologías Utilizadas

- **Backend**: PHP 8.0+ (programación orientada a objetos)
- **Frontend**: HTML5, CSS3, Bootstrap 5.3.0
- **JavaScript**: Vanilla ES6+ para interactividad
- **Base de datos**: MySQL 8.0+ con PDO
- **Iconos**: Bootstrap Icons

## Estructura del Proyecto

```
xxx/
├── assets/
│   ├── css/style.css          # Estilos personalizados
│   ├── js/calendario.js       # JavaScript del calendario
│   └── images/               # Imágenes del proyecto
├── includes/
│   ├── config.php            # Configuración general
│   ├── database.php          # Clase de conexión a BD
│   └── functions.php         # Funciones auxiliares
├── classes/
│   ├── Usuario.php           # Gestión de usuarios
│   └── Calendario.php        # Lógica del calendario
├── pages/
│   ├── calendario.php        # Página principal del calendario
│   └── logout.php           # Cierre de sesión
├── sql/
│   └── estructura.sql        # Script de creación de BD
├── index.php                 # Página de login
└── .gitignore               # Archivos a ignorar en Git
```

## Instalación

### 1. Requisitos del sistema

- **XAMPP** (Apache + MySQL + PHP 8.0+)
- Navegador web moderno

### 2. Configuración de la base de datos

1. Inicia XAMPP y activa Apache y MySQL
2. Abre phpMyAdmin en `http://localhost/phpmyadmin`
3. Ejecuta el script `sql/estructura.sql` para crear la base de datos y tablas
4. El script creará automáticamente un usuario de prueba

### 3. Configuración del proyecto

1. Clona o descarga el proyecto en `C:\xampp\htdocs\xxx\`
2. Ajusta la configuración en `includes/config.php` si es necesario:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'calendario_proyecto');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### 4. Acceso al sistema

1. Navega a `http://localhost/xxx/`
2. Usa las credenciales de prueba:
   - **Usuario**: `admin`
   - **Contraseña**: `admin123`

## Uso del Sistema

### Login
- Ingresa tu usuario y contraseña en la página inicial
- El sistema validará las credenciales y te redirigirá al calendario

### Calendario
- **Navegación**: Usa los botones "Anterior/Siguiente" o los selectores de mes/año
- **Agregar datos**: Haz clic en cualquier día del calendario
- **Editar datos**: Haz clic en un día que ya tenga datos (resaltado en azul)
- **Tooltips**: Pasa el ratón sobre días con observaciones para ver el texto completo

### Modal de datos
- **Número**: Selecciona un valor del 1 al 10 (obligatorio)
- **Observaciones**: Añade notas opcionales del día
- **Teclas rápidas**: 
  - Números 1-9 y 0 (para 10) para selección rápida
  - Enter para guardar
  - Escape para cerrar

### Panel de estadísticas
- **Días registrados**: Total de días con datos en el mes
- **Promedio**: Valor promedio de los números registrados
- **Min - Max**: Valores mínimo y máximo del mes
- **Con notas**: Días que tienen observaciones

## Características de Seguridad

- **Autenticación**: Verificación de sesiones en todas las páginas
- **CSRF Protection**: Tokens de seguridad en formularios
- **Sanitización**: Limpieza de datos de entrada y salida
- **Prepared Statements**: Prevención de inyección SQL
- **Validación**: Validación tanto cliente como servidor
- **Timeouts**: Cierre automático de sesiones inactivas

## Personalización

### Cambiar configuración
Edita `includes/config.php` para:
- Configuración de base de datos
- Timeout de sesiones
- Zona horaria
- URLs base

### Modificar estilos
Edita `assets/css/style.css` para personalizar:
- Colores del tema (variables CSS en `:root`)
- Estilos del calendario
- Animaciones y transiciones

### Agregar funcionalidades
- Nuevas clases en `classes/`
- Funciones auxiliares en `includes/functions.php`
- Páginas adicionales en `pages/`

## Base de Datos

### Tabla `usuarios`
- `id`: Clave primaria
- `nombre_usuario`: Nombre único de usuario
- `nombre_completo`: Nombre completo
- `password_hash`: Contraseña encriptada
- `email`: Correo electrónico (opcional)
- `activo`: Estado del usuario
- `fecha_creacion`: Fecha de registro
- `fecha_ultimo_acceso`: Último acceso

### Tabla `calendario_datos`
- `id`: Clave primaria
- `usuario_id`: Referencia al usuario
- `fecha`: Fecha del registro
- `numero_valor`: Número del 1 al 10
- `observaciones`: Notas del día
- `fecha_creacion`: Fecha de creación
- `fecha_modificacion`: Última modificación

## Desarrollo

### Convenciones de código
- Variables y funciones en español
- Indentación con 4 espacios
- Comentarios explicativos en funciones
- Validación en cliente y servidor
- Manejo de errores con try-catch

### Testing
Para probar el sistema:
1. Verifica que la base de datos esté creada correctamente
2. Prueba el login con diferentes usuarios
3. Navega por diferentes meses
4. Añade/edita/elimina datos de días
5. Verifica responsividad en móviles

## Soporte

Para reportar problemas o sugerir mejoras:
1. Revisa los logs de PHP y MySQL
2. Verifica la configuración en `includes/config.php`
3. Comprueba que todas las dependencias estén instaladas

## Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

---

**Desarrollado con PHP puro, Bootstrap 5 y buenas prácticas de seguridad web.**
