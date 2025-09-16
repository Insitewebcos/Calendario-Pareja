# 🚀 Guía de Deployment - Sistema de Calendario

## 📋 Configuración de Producción

### 🎯 Datos del Servidor de Base de Datos

```bash
Host: db5018588394.hosting-data.io
Puerto: 3306
Usuario: dbu1865109
Contraseña: ZT%9cGVrEIorb1qy
Base de Datos: dbs14744038
```

## 🛠️ Pasos para Deployment

### 1. 📁 Preparar Archivos en el Servidor

```bash
# 1. Clonar el repositorio
git clone https://github.com/Insitewebcos/Calendario-Pareja.git
cd Calendario-Pareja

# 2. Configurar variables de entorno
cp env.production .env

# 3. Editar .env con tu dominio real
nano .env
```

### 2. ⚙️ Configurar Variables en .env

Editar las siguientes líneas en `.env`:

```bash
# URLs configuradas para calendario.insiteweb.es
BASE_URL="http://calendario.insiteweb.es/"
SITE_URL="http://calendario.insiteweb.es/"
ASSETS_URL="http://calendario.insiteweb.es/assets/"

# Generar claves de seguridad únicas
APP_SECRET_KEY=clave-generada-con-generar_claves.php
CSRF_SECRET=otra-clave-unica-generada
```

### 3. 🔒 Configurar Permisos

```bash
# Permisos del archivo .env
chmod 600 .env
chown www-data:www-data .env

# Crear carpeta de logs
mkdir logs
chmod 755 logs
chown www-data:www-data logs

# Permisos de la aplicación
chmod -R 755 .
chown -R www-data:www-data .
```

### 4. 🗄️ Configurar Base de Datos

```bash
# Conectar a MySQL usando las credenciales
mysql -h db5018588394.hosting-data.io -u dbu1865109 -p

# Ejecutar script de estructura
mysql -h db5018588394.hosting-data.io -u dbu1865109 -p dbu1865109 < sql/estructura.sql
```

### 5. 👤 Crear Usuario Administrador

Ejecutar temporalmente `install_.php` para crear el primer usuario:

```bash
# Acceder a: https://tudominio.com/install_.php
# Crear usuario administrador
# ELIMINAR install_.php después de usar
rm install_.php
```

## 🔧 Configuración del Servidor Web

### Apache (.htaccess)

```apache
RewriteEngine On

# Redirigir a HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger archivos sensibles
<Files ".env">
    Order deny,allow
    Deny from all
</Files>

<Files "*.md">
    Order deny,allow
    Deny from all
</Files>

# Headers de seguridad
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### Nginx

```nginx
server {
    listen 80;
    server_name tudominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name tudominio.com;
    root /var/www/html/Calendario-Pareja;
    index index.php;

    # SSL configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Proteger archivos sensibles
    location ~ /\.env {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    location ~ \.md$ {
        deny all;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## ✅ Verificación Post-Deployment

### 🔍 Checklist de Verificación

- [ ] **Conexión BD:** Verificar que la aplicación conecta a la base de datos
- [ ] **HTTPS:** Verificar que el sitio funciona con SSL
- [ ] **Login:** Probar login con usuario administrador
- [ ] **Calendario:** Verificar que el calendario carga correctamente
- [ ] **Estadísticas:** Comprobar que las estadísticas se calculan
- [ ] **Logs:** Verificar que los logs se escriben en `logs/php_errors.log`
- [ ] **Headers:** Verificar headers de seguridad con herramientas online

### 🛠️ Comandos de Verificación

```bash
# Verificar configuración PHP
php -m | grep pdo

# Verificar permisos
ls -la .env
ls -la logs/

# Verificar logs
tail -f logs/php_errors.log

# Test de conexión BD
php -r "
try {
    \$pdo = new PDO('mysql:host=db5018588394.hosting-data.io;dbname=dbu1865109', 'dbu1865109', 'ZT%9cGVrEIorb1qy');
    echo 'Conexión BD: OK\n';
} catch(Exception \$e) {
    echo 'Error BD: ' . \$e->getMessage() . '\n';
}
"
```

## 🚨 Solución de Problemas

### Error de Conexión BD

```bash
# Verificar conectividad
telnet db5018588394.hosting-data.io 3306

# Verificar credenciales en .env
cat .env | grep DB_
```

### Error 500

```bash
# Verificar logs de error
tail -f logs/php_errors.log
tail -f /var/log/apache2/error.log
```

### Problemas de Permisos

```bash
# Verificar propietario
ls -la
# Corregir permisos
chown -R www-data:www-data .
chmod -R 755 .
chmod 600 .env
```

## 🔄 Mantenimiento

### Backups Regulares

```bash
# Backup de base de datos
mysqldump -h db5018588394.hosting-data.io -u dbu1865109 -p dbu1865109 > backup_$(date +%Y%m%d).sql

# Backup de archivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz .
```

### Actualizaciones

```bash
# Hacer backup antes de actualizar
git pull origin main

# Verificar que todo funciona
# Revisar logs después de actualizar
```

## 📞 Soporte

Si tienes problemas durante el deployment:

1. Verificar logs de error
2. Comprobar configuración de .env
3. Verificar permisos de archivos
4. Testear conexión a base de datos

---

**🔐 Recuerda: Mantén las credenciales seguras y nunca las subas a Git**
