# Reglas Específicas del Proyecto Garantías CYCA

## ESTRUCTURA ACTUAL DEL PROYECTO

Basado en la estructura existente, mantener y mejorar:

```
Garantias_CYCA/
├── conexion/          # Configuración de base de datos
├── assets/            # Recursos estáticos
├── js/               # JavaScript del proyecto
├── plantilla/        # Plantillas HTML
├── sql/              # Scripts de base de datos
├── forms/            # Formularios del sistema
├── img/              # Imágenes del proyecto
├── vendor/           # Dependencias de Composer
└── archivos PHP principales
```

## ARCHIVOS PRINCIPALES A MANTENER

- `index.php` - Página principal del sistema
- `principal.php` - Panel principal de administración
- `garantias.php` - Gestión de garantías
- `reclamaciones.php` - Gestión de reclamaciones
- `entidades.php` - Gestión de entidades
- `functions.php` - Funciones auxiliares del sistema
- `db_config.php` - Configuración de base de datos

## CONVENCIONES ESPECÍFICAS

- **Base de datos**: Usar prefijo `cyca_` para tablas nuevas
- **Sesiones**: Mantener sistema de autenticación existente
- **Notificaciones**: Usar sistema de notificaciones implementado
- **Plantillas**: Seguir estructura de `_plantilla.php`
- **Formularios**: Mantener validación del lado del servidor

## FUNCIONALIDADES PRINCIPALES

1. **Sistema de Garantías**

   - Crear, editar, eliminar garantías
   - Gestión de estados y tipos
   - Historial de cambios

2. **Sistema de Reclamaciones**

   - Seguimiento de reclamaciones
   - Estados y prioridades
   - Notificaciones automáticas

3. **Gestión de Entidades**

   - Empresas y técnicos
   - Tipos de proyectos
   - Relaciones entre entidades

4. **Sistema de Usuarios**
   - Roles y permisos
   - Autenticación segura
   - Gestión de sesiones

## ESTÁNDARES DE SEGURIDAD

- Validar todas las entradas de usuario
- Usar prepared statements para consultas SQL
- Implementar CSRF protection en formularios
- Validar permisos de usuario en cada página
- Sanitizar datos antes de mostrar en HTML

## OPTIMIZACIONES RECOMENDADAS

- Implementar caché para consultas frecuentes
- Optimizar consultas de base de datos
- Minificar CSS y JavaScript en producción
- Implementar lazy loading para imágenes
- Usar CDN para Bootstrap y librerías externas

## MANTENIMIENTO

- Documentar cambios en el código
- Mantener backup de base de datos
- Revisar logs de errores regularmente
- Actualizar dependencias de seguridad
- Testing de funcionalidades críticas
