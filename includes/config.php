<?php
/**
 * Configuración general del proyecto
 * Archivo de configuración principal con constantes del sistema
 * Usa variables de entorno para mayor seguridad
 */

// Cargar variables de entorno
require_once __DIR__ . '/env_loader.php';

// Cargar archivo .env (no crítico si no existe)
EnvLoader::load();

// Detectar entorno automáticamente o usar variable de entorno
$env_setting = EnvLoader::get('APP_ENV', 'auto');
if ($env_setting === 'auto') {
    $is_localhost = (
        $_SERVER['SERVER_NAME'] === 'localhost' ||
        $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
        strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
        strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
        $_SERVER['SERVER_ADDR'] === '127.0.0.1'
    );
} else {
    $is_localhost = in_array($env_setting, ['development', 'local', 'dev']);
}

// Definir entorno
define('IS_LOCALHOST', $is_localhost);
define('IS_PRODUCTION', !$is_localhost);

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================

// Usar variables de entorno con fallbacks seguros
define('DB_HOST', EnvLoader::get('DB_HOST', IS_LOCALHOST ? 'localhost' : 'localhost'));
define('DB_PORT', EnvLoader::getInt('DB_PORT', 3306));
define('DB_NAME', EnvLoader::get('DB_NAME', IS_LOCALHOST ? 'calendario_proyecto' : ''));
define('DB_USER', EnvLoader::get('DB_USER', IS_LOCALHOST ? 'root' : ''));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));
define('DB_CHARSET', EnvLoader::get('DB_CHARSET', 'utf8mb4'));

// Validar que las credenciales críticas estén definidas en producción
if (IS_PRODUCTION) {
    $required_db_vars = ['DB_HOST', 'DB_NAME', 'DB_USER'];
    foreach ($required_db_vars as $var) {
        if (empty(constant($var))) {
            throw new Exception("Variable de entorno crítica no definida: $var");
        }
    }
}

// ============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================

define('APP_NAME', EnvLoader::get('APP_NAME', 'Sistema de Calendario'));
define('APP_VERSION', EnvLoader::get('APP_VERSION', '1.0.0'));
define('TIMEZONE', EnvLoader::get('APP_TIMEZONE', 'Europe/Madrid'));

// Configuración de sesiones
define('SESSION_NAME', EnvLoader::get('SESSION_NAME', 'calendario_session'));
define('SESSION_TIMEOUT', EnvLoader::getInt('SESSION_TIMEOUT', 3600)); // 1 hora en segundos

// ============================================
// CONFIGURACIÓN DE RUTAS Y URLs
// ============================================

// URLs usando variables de entorno con fallbacks inteligentes
$default_base_url = IS_LOCALHOST ? 'http://localhost/xxx/' : '';
define('BASE_URL', EnvLoader::get('BASE_URL', $default_base_url));
define('SITE_URL', EnvLoader::get('SITE_URL', BASE_URL));
define('ASSETS_URL', EnvLoader::get('ASSETS_URL', BASE_URL . 'assets/'));

// Validar URLs en producción
if (IS_PRODUCTION && empty(BASE_URL)) {
    throw new Exception("BASE_URL debe estar definida en producción");
}

// ============================================
// CONFIGURACIÓN DE ERRORES Y DEBUG
// ============================================

// Debug usando variable de entorno con fallback automático
define('DEBUG_MODE', EnvLoader::getBool('APP_DEBUG', IS_LOCALHOST));

// Configuraciones adicionales de seguridad
define('APP_SECRET_KEY', EnvLoader::get('APP_SECRET_KEY', 'default-insecure-key-change-this'));
define('CSRF_SECRET', EnvLoader::get('CSRF_SECRET', 'default-csrf-key-change-this'));

// Validar claves de seguridad en producción
if (IS_PRODUCTION) {
    if (APP_SECRET_KEY === 'default-insecure-key-change-this') {
        throw new Exception("APP_SECRET_KEY debe ser cambiada en producción");
    }
    if (CSRF_SECRET === 'default-csrf-key-change-this') {
        throw new Exception("CSRF_SECRET debe ser cambiada en producción");
    }
}

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// ============================================
// CONFIGURACIÓN DE SESIONES SEGURAS
// ============================================

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Configurar HTTPS según entorno y variable de entorno
$session_secure = EnvLoader::getBool('SESSION_SECURE', IS_PRODUCTION);
ini_set('session.cookie_secure', $session_secure ? 1 : 0);

// ============================================
// CONFIGURACIÓN DE ERRORES Y LOGS
// ============================================

if (DEBUG_MODE) {
    // DESARROLLO: Mostrar todos los errores
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    // PRODUCCIÓN: Ocultar errores, solo log
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Log de errores en producción (crear carpeta logs/)
    if (!file_exists(__DIR__ . '/../logs/')) {
        mkdir(__DIR__ . '/../logs/', 0755, true);
    }
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// ============================================
// CONFIGURACIONES ADICIONALES
// ============================================

// Configuración de email usando variables de entorno
$default_mail_host = IS_LOCALHOST ? 'localhost' : '';
$default_mail_port = IS_LOCALHOST ? 1025 : 587;
$default_mail_from = IS_LOCALHOST ? 'noreply@localhost.dev' : '';

define('MAIL_HOST', EnvLoader::get('MAIL_HOST', $default_mail_host));
define('MAIL_PORT', EnvLoader::getInt('MAIL_PORT', $default_mail_port));
define('MAIL_USERNAME', EnvLoader::get('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', EnvLoader::get('MAIL_PASSWORD', ''));
define('MAIL_FROM', EnvLoader::get('MAIL_FROM', $default_mail_from));
define('MAIL_FROM_NAME', EnvLoader::get('MAIL_FROM_NAME', APP_NAME));
define('MAIL_ENCRYPTION', EnvLoader::get('MAIL_ENCRYPTION', ''));

// Configuración de seguridad adicional
if (IS_PRODUCTION) {
    // Headers de seguridad para producción
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // CSP básico (ajustar según necesidades)
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; script-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:;");
}

// ============================================
// INFORMACIÓN DEL ENTORNO (para debug)
// ============================================

// Constantes útiles para debugging
define('CURRENT_ENVIRONMENT', IS_LOCALHOST ? 'DESARROLLO' : 'PRODUCCIÓN');
define('SERVER_NAME', $_SERVER['SERVER_NAME'] ?? 'unknown');
define('REQUEST_SCHEME', $_SERVER['REQUEST_SCHEME'] ?? 'http');

// ============================================
// CARGAR CONFIGURACIÓN DE PRODUCCIÓN (SI EXISTE)
// ============================================

// Si existe config_production.php, sobrescribir valores de producción
$config_production_file = __DIR__ . '/config_production.php';
if (IS_PRODUCTION && file_exists($config_production_file)) {
    require_once $config_production_file;
    
    // Sobrescribir constantes de BD si están definidas
    if (defined('PROD_DB_HOST')) {
        define('DB_HOST_OVERRIDE', PROD_DB_HOST);
        define('DB_NAME_OVERRIDE', PROD_DB_NAME);
        define('DB_USER_OVERRIDE', PROD_DB_USER);
        define('DB_PASS_OVERRIDE', PROD_DB_PASS);
    }
    
    // Sobrescribir URLs si están definidas
    if (defined('PROD_BASE_URL')) {
        define('BASE_URL_OVERRIDE', PROD_BASE_URL);
        define('SITE_URL_OVERRIDE', PROD_SITE_URL);
    }
}