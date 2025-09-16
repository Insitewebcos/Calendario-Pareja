<?php
/**
 * SCRIPT DE DIAGNÓSTICO PARA PRODUCCIÓN
 * Ejecutar solo una vez para identificar el problema
 * ELIMINAR después de usar
 */

// Activar reporte de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico de Producción</h1>";

// 1. Verificar si es detectado como producción
echo "<h2>1. Detección de Entorno</h2>";
$is_localhost = (
    $_SERVER['SERVER_NAME'] === 'localhost' ||
    $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    ($_SERVER['SERVER_ADDR'] ?? '') === '127.0.0.1'
);

echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'no definido') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'no definido') . "<br>";
echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'no definido') . "<br>";
echo "IS_LOCALHOST detectado: " . ($is_localhost ? 'SÍ' : 'NO') . "<br>";

// 2. Verificar archivos críticos
echo "<h2>2. Archivos Críticos</h2>";
$archivos_criticos = [
    'includes/config.php',
    'includes/env_loader.php',
    'includes/functions.php',
    'includes/database.php',
    'classes/Usuario.php'
];

foreach ($archivos_criticos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo - Existe<br>";
    } else {
        echo "❌ $archivo - NO EXISTE<br>";
    }
}

// 3. Verificar archivo .env
echo "<h2>3. Variables de Entorno</h2>";
if (file_exists('.env')) {
    echo "✅ Archivo .env encontrado<br>";
    echo "Tamaño: " . filesize('.env') . " bytes<br>";
} else {
    echo "⚠️ Archivo .env NO encontrado<br>";
}

// 4. Intentar cargar config básico
echo "<h2>4. Carga de Configuración</h2>";
try {
    if (file_exists('includes/env_loader.php')) {
        require_once 'includes/env_loader.php';
        echo "✅ env_loader.php cargado<br>";
        
        // Intentar cargar .env
        $loaded = EnvLoader::load();
        echo "Carga de .env: " . ($loaded ? 'ÉXITO' : 'FALLÓ') . "<br>";
    }
    
    if (file_exists('includes/config.php')) {
        require_once 'includes/config.php';
        echo "✅ config.php cargado<br>";
        echo "DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'true' : 'false') : 'no definido') . "<br>";
        echo "APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'no definido') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error al cargar configuración: " . $e->getMessage() . "<br>";
}

// 5. Verificar base de datos
echo "<h2>5. Base de Datos</h2>";
if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
    echo "Host: " . DB_HOST . "<br>";
    echo "BD: " . DB_NAME . "<br>";
    echo "Usuario: " . DB_USER . "<br>";
    echo "Pass: " . (empty(DB_PASS) ? 'VACÍO' : 'DEFINIDO') . "<br>";
    
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        echo "✅ Conexión BD: ÉXITO<br>";
    } catch (Exception $e) {
        echo "❌ Conexión BD: ERROR - " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Constantes de BD no definidas<br>";
}

// 6. Verificar extensiones PHP
echo "<h2>6. Extensiones PHP</h2>";
$extensiones = ['pdo', 'pdo_mysql', 'session', 'json'];
foreach ($extensiones as $ext) {
    echo (extension_loaded($ext) ? '✅' : '❌') . " $ext<br>";
}

// 7. Información del sistema
echo "<h2>7. Información del Sistema</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'no definido') . "<br>";
echo "Script Path: " . __FILE__ . "<br>";

echo "<br><hr><br>";
echo "<strong>💡 Instrucciones:</strong><br>";
echo "1. Revisa los errores marcados con ❌<br>";
echo "2. Si hay problemas de BD, verifica las credenciales en .env<br>";
echo "3. Si hay archivos faltantes, sube el proyecto completo<br>";
echo "4. ELIMINA este archivo después del diagnóstico<br>";
?>
