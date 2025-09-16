# 🚨 Solución Error 500 en Producción

## 🔍 Diagnóstico Inmediato

### 1. **Accede a estos archivos para diagnosticar:**

```
http://calendario.insiteweb.es/debug_produccion.php
http://calendario.insiteweb.es/index_safe.php
```

### 2. **Si index_safe.php funciona:**

- El problema está en `index.php`
- Sigue el enlace de diagnóstico

### 3. **Si ambos fallan:**

- Problema más grave de configuración del servidor

## 🔧 Causas Más Probables

### ❌ **Problema 1: Archivo .env Faltante**

**Síntoma:** Variables de entorno no definidas

**Solución:**

```bash
# En tu servidor, crear archivo .env
cp env.production .env

# Editar con tus datos reales
nano .env
```

### ❌ **Problema 2: Credenciales de BD Incorrectas**

**Síntoma:** Error de conexión a base de datos

**Verifica en .env:**

```bash
DB_HOST=db5018588394.hosting-data.io
DB_NAME=dbu1865109
DB_USER=dbu1865109
DB_PASS=ZT%9cGVrEIorb1qy
```

### ❌ **Problema 3: Archivos Faltantes**

**Síntoma:** "File not found" en logs

**Verifica que estén subidos:**

```
includes/config.php
includes/env_loader.php
includes/functions.php
includes/database.php
classes/Usuario.php
classes/Calendario.php
```

### ❌ **Problema 4: Permisos de Archivos**

**Síntoma:** "Permission denied"

**Solución:**

```bash
chmod -R 755 .
chmod 600 .env
chown -R www-data:www-data .
```

### ❌ **Problema 5: Extensiones PHP Faltantes**

**Síntoma:** "Call to undefined function"

**Verifica extensiones:**

```bash
php -m | grep -E "(pdo|mysql|session)"
```

### ❌ **Problema 6: URLs Incorrectas**

**Síntoma:** Variables BASE_URL mal configuradas

**En .env:**

```bash
BASE_URL="http://calendario.insiteweb.es/"
SITE_URL="http://calendario.insiteweb.es/"
```

## 🛠️ Pasos de Solución

### **Paso 1: Diagnóstico**

1. Acceder a `debug_produccion.php`
2. Revisar TODOS los ❌ en rojo
3. Anotar los errores específicos

### **Paso 2: Archivos Básicos**

```bash
# Verificar estructura
ls -la includes/
ls -la classes/
ls -la .env
```

### **Paso 3: Base de Datos**

```bash
# Test manual de conexión
mysql -h db5018588394.hosting-data.io -u dbu1865109 -p

# Si conecta, ejecutar estructura
mysql -h db5018588394.hosting-data.io -u dbu1865109 -p dbu1865109 < sql/estructura.sql
```

### **Paso 4: Variables de Entorno**

```bash
# Crear .env si no existe
cp env.production .env

# Verificar contenido
cat .env | grep DB_
```

### **Paso 5: Logs del Servidor**

```bash
# Ver logs recientes
tail -f /var/log/apache2/error.log
tail -f logs/php_errors.log
```

## 🔄 Proceso Sistemático

### **Opción A: Todo Funciona Local**

1. ✅ Código está bien
2. ❌ Problema de configuración del servidor
3. 🔍 Verificar .env y BD

### **Opción B: Error en Ambos**

1. ❌ Problema de archivos o extensiones
2. 🔍 Verificar que se subieron todos los archivos
3. 🔧 Verificar extensiones PHP del servidor

### **Opción C: Solo Error en index.php**

1. 🔍 Problema específico en el código
2. ✅ Usar index_safe.php temporalmente
3. 🐛 Debug paso a paso

## 📞 Script de Verificación Rápida

```bash
#!/bin/bash
echo "🔍 Verificación Rápida del Sistema"

echo "1. Archivos críticos:"
ls -la includes/config.php includes/env_loader.php classes/Usuario.php

echo "2. Archivo .env:"
ls -la .env

echo "3. Conexión BD:"
php -r "
try {
  \$pdo = new PDO('mysql:host=db5018588394.hosting-data.io;dbname=dbu1865109', 'dbu1865109', 'ZT%9cGVrEIorb1qy');
  echo 'Conexión BD: ✅ OK\n';
} catch(Exception \$e) {
  echo 'Conexión BD: ❌ Error - ' . \$e->getMessage() . '\n';
}
"

echo "4. Extensiones PHP:"
php -m | grep -E "(pdo|mysql|session)" | head -3
```

## 🚑 Solución de Emergencia

### Si nada funciona, crear un index.php mínimo:

```php
<?php
// index.php de emergencia
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Sistema en Mantenimiento</h1>";
echo "<p>Servidor: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>PHP: " . PHP_VERSION . "</p>";

// Test de BD
try {
    $pdo = new PDO('mysql:host=db5018588394.hosting-data.io;dbname=dbu1865109', 'dbu1865109', 'ZT%9cGVrEIorb1qy');
    echo "<p>✅ Base de datos: Conectada</p>";
} catch(Exception $e) {
    echo "<p>❌ Base de datos: " . $e->getMessage() . "</p>";
}

echo "<p><a href='debug_produccion.php'>🔍 Diagnóstico Completo</a></p>";
?>
```

---

**🔧 Orden de Prioridad para Resolución:**

1. 🔍 **Ejecutar diagnóstico**
2. 📁 **Verificar archivos**
3. 🗄️ **Verificar base de datos**
4. ⚙️ **Verificar .env**
5. 🔧 **Verificar permisos**
6. 📞 **Revisar logs del servidor**

**⚠️ IMPORTANTE:** Elimina `debug_produccion.php` e `index_safe.php` después de resolver el problema.
