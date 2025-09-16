# 🚑 Solución Inmediata - Error 500

## 🔍 Problemas Identificados

### ❌ **Problema 1: Archivo .env Faltante**

**Estado:** El archivo `.env` NO existe en el servidor

### ❌ **Problema 2: Credenciales de BD Incorrectas**

**Estado:** Usuario detectado `o14744038` en lugar de `dbu1865109`

### ❌ **Problema 3: Claves de Seguridad**

**Estado:** APP_SECRET_KEY debe ser cambiada en producción

## 🛠️ Solución Paso a Paso

### **Paso 1: Crear Archivo .env**

En tu servidor `calendario.insiteweb.es`, crear el archivo `.env`:

```bash
# Subir archivo env.produccion.correcto como .env
# O crear manualmente con este contenido:
```

**Contenido del archivo .env:**

```bash
APP_NAME="Sistema de Calendario"
APP_VERSION="1.0.0"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE="Europe/Madrid"

# Base de datos - PROBAR ESTAS CREDENCIALES:
DB_HOST=db5018588394.hosting-data.io
DB_PORT=3306
DB_NAME=dbu1865109
DB_USER=o14744038
DB_PASS=ZT%9cGVrEIorb1qy
DB_CHARSET=utf8mb4

# URLs
BASE_URL="http://calendario.insiteweb.es/"
SITE_URL="http://calendario.insiteweb.es/"
ASSETS_URL="http://calendario.insiteweb.es/assets/"

# Sesiones
SESSION_NAME=calendario_session
SESSION_TIMEOUT=3600
SESSION_SECURE=false

# Claves de seguridad (cambiar por unas reales)
APP_SECRET_KEY=insiteweb-calendario-production-key-2025-supersecret
CSRF_SECRET=csrf-insiteweb-production-unique-token-2025

# Logs
LOG_LEVEL=error
LOG_ERRORS=true
DISPLAY_ERRORS=false
```

### **Paso 2: Verificar Credenciales de BD**

Accede a: `http://calendario.insiteweb.es/test_bd.php`

Este script probará automáticamente diferentes combinaciones de credenciales.

### **Paso 3: Configurar Base de Datos**

Si la conexión es exitosa pero faltan tablas:

```bash
# Desde tu máquina local, ejecutar:
mysql -h db5018588394.hosting-data.io -u [USUARIO_CORRECTO] -p [BD_CORRECTA] < sql/estructura.sql
```

### **Paso 4: Crear Usuario Administrador**

Una vez que funcione:

```bash
# Acceder temporalmente a:
http://calendario.insiteweb.es/install_.php

# Crear usuario y ELIMINAR el archivo después
```

## 🎯 Comandos Específicos

### **En el Servidor:**

```bash
# 1. Verificar archivos subidos
ls -la

# 2. Crear archivo .env
nano .env
# (pegar contenido de arriba)

# 3. Verificar permisos
chmod 600 .env
chmod -R 755 .
```

### **Desde Local:**

```bash
# Subir estructura a BD (usar credenciales correctas del test)
mysql -h db5018588394.hosting-data.io -u o14744038 -p dbu1865109 < sql/estructura.sql
```

## 📋 Checklist de Verificación

- [ ] ✅ Archivo `.env` creado en servidor
- [ ] ✅ Credenciales BD verificadas con `test_bd.php`
- [ ] ✅ Tablas creadas en BD con `estructura.sql`
- [ ] ✅ Usuario admin creado con `install_.php`
- [ ] ✅ Archivos temporales eliminados

## 🚨 Orden de Ejecución

1. **Crear .env** → Archivo en servidor
2. **Verificar BD** → `test_bd.php`
3. **Estructura BD** → `mysql` desde local
4. **Test sistema** → `http://calendario.insiteweb.es/`
5. **Crear admin** → `install_.php`
6. **Limpiar** → Eliminar archivos temporales

## 📞 URLs de Verificación

- 🔍 **Diagnóstico:** `http://calendario.insiteweb.es/debug_produccion.php`
- 🧪 **Test BD:** `http://calendario.insiteweb.es/test_bd.php`
- 🛡️ **Seguro:** `http://calendario.insiteweb.es/index_safe.php`
- 📊 **Principal:** `http://calendario.insiteweb.es/`

---

**⚠️ IMPORTANTE:** Elimina todos los archivos de diagnóstico después de solucionar:

```bash
rm debug_produccion.php test_bd.php index_safe.php install_.php
```
