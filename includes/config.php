<?php
/**
 * Configuración general del proyecto
 * Archivo de configuración principal con constantes del sistema
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'calendario_proyecto');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de sesiones
define('SESSION_NAME', 'calendario_session');
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Calendario');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Europe/Madrid');

// Configuración de rutas
define('BASE_URL', 'http://localhost/xxx/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Configuración de errores (cambiar a false en producción)
define('DEBUG_MODE', true);

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// Configuración de sesiones seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS

// Configuración de errores según el modo debug
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
