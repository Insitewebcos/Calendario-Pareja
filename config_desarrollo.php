<?php
/**
 * Configuración para entorno de desarrollo
 * Configuraciones específicas para testing y desarrollo
 */

// Mostrar todos los errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Configuración de desarrollo
define('DESARROLLO', true);

// Headers adicionales para desarrollo
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Funciones auxiliares para desarrollo
function debug_log($mensaje, $variable = null) {
    if (DESARROLLO) {
        $log = "[" . date('Y-m-d H:i:s') . "] " . $mensaje;
        if ($variable !== null) {
            $log .= " - " . print_r($variable, true);
        }
        error_log($log);
    }
}

function debug_vardump($variable, $titulo = "Debug") {
    if (DESARROLLO) {
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>$titulo:</strong><br>";
        echo "<pre>";
        var_dump($variable);
        echo "</pre>";
        echo "</div>";
    }
}

function debug_console($data) {
    if (DESARROLLO) {
        echo "<script>";
        echo "console.log(" . json_encode($data) . ");";
        echo "</script>";
    }
}

// Información del sistema para desarrollo
if (DESARROLLO && isset($_GET['info'])) {
    echo "<h2>Información del Sistema</h2>";
    echo "<h3>PHP</h3>";
    echo "<p>Versión: " . PHP_VERSION . "</p>";
    echo "<p>Extensiones PDO: " . (extension_loaded('pdo') ? 'Sí' : 'No') . "</p>";
    echo "<p>Extensión PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Sí' : 'No') . "</p>";
    
    echo "<h3>MySQL</h3>";
    try {
        $pdo = new PDO('mysql:host=localhost', 'root', '');
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        echo "<p>Versión: $version</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error de conexión: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Configuración</h3>";
    echo "<p>Base de datos: " . (defined('DB_NAME') ? DB_NAME : 'No definida') . "</p>";
    echo "<p>Debug mode: " . (defined('DEBUG_MODE') && DEBUG_MODE ? 'Activado' : 'Desactivado') . "</p>";
    
    exit();
}
?>
