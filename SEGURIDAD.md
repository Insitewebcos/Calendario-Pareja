# 🛡️ Guía de Seguridad - Sistema de Calendario

## 🔒 Protección de Credenciales

Este proyecto implementa un sistema robusto de protección de credenciales para evitar el hardcodeo de información sensible.

## 📋 Configuración Inicial

### 1. Crear Archivo de Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp env.example .env
```

### 2. Generar Claves Seguras

```bash
# Acceder al generador (SOLO LOCALHOST)
http://localhost/xxx/generar_claves.php
```

⚠️ **IMPORTANTE:** Eliminar `generar_claves.php` después de usar.

### 3. Configurar Variables en .env

```bash
# Editar archivo .env con tus datos reales
nano .env
```

## 🔧 Variables de Entorno Requeridas

### 🏗️ Para Desarrollo (localhost)

```env
APP_ENV=development
APP_DEBUG=true

DB_HOST=localhost
DB_NAME=calendario_proyecto
DB_USER=root
DB_PASS=

BASE_URL="http://localhost/xxx/"
SESSION_SECURE=false

APP_SECRET_KEY=tu-clave-generada-aquí
CSRF_SECRET=tu-csrf-secret-aquí
```

### 🌐 Para Producción

```env
APP_ENV=production
APP_DEBUG=false

DB_HOST=tu-servidor-bd.com
DB_NAME=calendario_prod
DB_USER=usuario_bd_seguro
DB_PASS=password_super_seguro_generado

BASE_URL="https://tudominio.com/"
SESSION_SECURE=true

APP_SECRET_KEY=clave-de-32-bytes-base64-generada
CSRF_SECRET=otra-clave-segura-diferente
```

## 🚀 Deployment en Producción

### 1. Archivos a NO Subir

```bash
# Verificar que .gitignore incluye:
.env
.env.*
generar_claves.php
config_production.php
logs/
*.log
```

### 2. Configurar Servidor

```bash
# 1. Subir código sin archivos sensibles
git clone tu-repo.git

# 2. Crear archivo .env en servidor
nano .env

# 3. Configurar permisos
chmod 600 .env
chown www-data:www-data .env

# 4. Crear carpeta de logs
mkdir logs
chmod 755 logs
```

### 3. Validación Automática

El sistema valida automáticamente en producción:

- ✅ Variables de BD definidas
- ✅ URLs configuradas
- ✅ Claves de seguridad cambiadas
- ✅ Debug mode desactivado

## 🔐 Características de Seguridad

### 🛡️ Variables de Entorno

- **Sin hardcoding:** Todas las credenciales en variables de entorno
- **Fallbacks seguros:** Valores por defecto solo para desarrollo
- **Validación:** Error si faltan variables críticas en producción
- **Carga flexible:** Soporta `.env`, `.env.local`, variables del sistema

### 🔒 Claves de Seguridad

- **APP_SECRET_KEY:** Encriptación general y tokens
- **CSRF_SECRET:** Protección contra ataques CSRF
- **JWT_SECRET:** Para autenticación con tokens (futuro)
- **Generación segura:** Script para crear claves criptográficamente seguras

### 🚫 Protección en Producción

- **Headers de seguridad:** CSP, X-Frame-Options, etc.
- **Cookies seguras:** HTTPS obligatorio
- **Logs seguros:** Errores solo a archivos, no pantalla
- **Validaciones:** Falla si configuración insegura

## 📊 Estados de Configuración

### ✅ Desarrollo Seguro

```php
IS_LOCALHOST = true
DEBUG_MODE = true (pero controlado)
SESSION_SECURE = false (HTTP OK)
Fallbacks activos para facilitar desarrollo
```

### 🔒 Producción Segura

```php
IS_PRODUCTION = true
DEBUG_MODE = false (forzado)
SESSION_SECURE = true (HTTPS requerido)
Validaciones estrictas activas
```

## 🔍 Detección de Entorno

El sistema detecta automáticamente el entorno:

```php
// Detección automática
$is_localhost = (
    $_SERVER['SERVER_NAME'] === 'localhost' ||
    $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
    // ...más condiciones
);

// O manual via variable de entorno
APP_ENV=production  // Fuerza producción
APP_ENV=development // Fuerza desarrollo
```

## 🛠️ Herramientas de Debug

### info_entorno.php (Solo Localhost)

- Estado actual del entorno
- Validación de conexión BD
- Configuración PHP
- **Auto-bloqueado en producción**

### Logs Inteligentes

```bash
# Desarrollo: pantalla + logs
error_reporting(E_ALL)
display_errors = 1

# Producción: solo logs
display_errors = 0
error_log = logs/php_errors.log
```

## ⚠️ Checklist de Seguridad

### Antes de Deployment

- [ ] Archivo `.env` configurado
- [ ] Claves de seguridad generadas y únicas
- [ ] Variables de BD de producción configuradas
- [ ] URLs de producción correctas
- [ ] `generar_claves.php` eliminado
- [ ] `info_entorno.php` eliminado (opcional)
- [ ] `.env` en `.gitignore`
- [ ] Permisos de archivos correctos

### Verificación Post-Deployment

- [ ] `DEBUG_MODE = false`
- [ ] Headers de seguridad activos
- [ ] Cookies seguras en HTTPS
- [ ] Logs funcionando correctamente
- [ ] Base de datos conectando
- [ ] No se muestran errores en pantalla

## 🚨 En Caso de Compromiso

### Si se filtran credenciales:

1. **Cambiar inmediatamente:**

   - Passwords de BD
   - APP_SECRET_KEY
   - CSRF_SECRET
   - Claves API

2. **Regenerar:**

   ```bash
   # Usar generar_claves.php localmente
   # O comando manual:
   openssl rand -base64 32
   ```

3. **Verificar logs:**

   ```bash
   tail -f logs/php_errors.log
   ```

4. **Revocar sesiones:**
   ```bash
   # Cambiar SESSION_NAME en .env
   # Fuerza logout de todos los usuarios
   ```

## 📚 Referencias

- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [OWASP Security Guidelines](https://owasp.org/www-project-top-ten/)
- [Environment Variables in PHP](https://www.php.net/manual/en/function.getenv.php)

---

**🔐 Mantén tus credenciales seguras. Nunca las compartas o subas a repositorios públicos.**
