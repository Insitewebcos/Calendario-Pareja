<?php
/**
 * Script de verificación del sistema
 * Comprueba que todos los componentes estén funcionando correctamente
 */

require_once 'includes/config.php';

// Función para mostrar resultado de verificación
function mostrar_resultado($titulo, $resultado, $mensaje = '') {
    $icono = $resultado ? '✅' : '❌';
    $clase = $resultado ? 'success' : 'danger';
    echo "<div class='alert alert-$clase'>";
    echo "<strong>$icono $titulo</strong>";
    if ($mensaje) {
        echo "<br><small>$mensaje</small>";
    }
    echo "</div>";
    return $resultado;
}

$errores = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h3 class="mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            Verificación del Sistema
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <h5>Verificación de PHP</h5>
                        <?php
                        // Verificar versión de PHP
                        $php_ok = version_compare(PHP_VERSION, '8.0.0', '>=');
                        if (!mostrar_resultado("Versión de PHP", $php_ok, "Actual: " . PHP_VERSION . " (Requerida: 8.0+)")) {
                            $errores++;
                        }
                        
                        // Verificar extensión PDO
                        $pdo_ok = extension_loaded('pdo');
                        if (!mostrar_resultado("Extensión PDO", $pdo_ok)) {
                            $errores++;
                        }
                        
                        // Verificar extensión PDO MySQL
                        $pdo_mysql_ok = extension_loaded('pdo_mysql');
                        if (!mostrar_resultado("Extensión PDO MySQL", $pdo_mysql_ok)) {
                            $errores++;
                        }
                        ?>
                        
                        <h5 class="mt-4">Verificación de archivos</h5>
                        <?php
                        $archivos_requeridos = [
                            'includes/config.php' => 'Archivo de configuración',
                            'includes/database.php' => 'Clase de base de datos',
                            'includes/functions.php' => 'Funciones auxiliares',
                            'classes/Usuario.php' => 'Clase Usuario',
                            'classes/Calendario.php' => 'Clase Calendario',
                            'assets/css/style.css' => 'Estilos CSS',
                            'assets/js/calendario.js' => 'JavaScript del calendario',
                            'pages/calendario.php' => 'Página del calendario'
                        ];
                        
                        foreach ($archivos_requeridos as $archivo => $descripcion) {
                            $existe = file_exists($archivo);
                            if (!mostrar_resultado($descripcion, $existe, $archivo)) {
                                $errores++;
                            }
                        }
                        ?>
                        
                        <h5 class="mt-4">Verificación de base de datos</h5>
                        <?php
                        try {
                            require_once 'includes/database.php';
                            $db = Database::obtener_instancia();
                            $conexion_ok = true;
                            mostrar_resultado("Conexión a base de datos", true);
                            
                            // Verificar tablas
                            $tablas = ['usuarios', 'calendario_datos'];
                            foreach ($tablas as $tabla) {
                                $stmt = $db->ejecutar_consulta("SHOW TABLES LIKE ?", [$tabla]);
                                $existe_tabla = $stmt && $stmt->rowCount() > 0;
                                if (!mostrar_resultado("Tabla '$tabla'", $existe_tabla)) {
                                    $errores++;
                                }
                            }
                            
                            // Verificar usuario admin
                            $stmt = $db->ejecutar_consulta("SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = ?", ['admin']);
                            $usuario_admin_existe = $stmt && $stmt->fetch()['total'] > 0;
                            mostrar_resultado("Usuario administrador", $usuario_admin_existe, "Usuario: admin");
                            
                        } catch (Exception $e) {
                            mostrar_resultado("Conexión a base de datos", false, $e->getMessage());
                            $errores++;
                        }
                        ?>
                        
                        <h5 class="mt-4">Verificación de permisos</h5>
                        <?php
                        $directorios_escritura = ['assets/', 'includes/'];
                        
                        foreach ($directorios_escritura as $directorio) {
                            $escribible = is_writable($directorio);
                            if (!mostrar_resultado("Permisos de escritura: $directorio", $escribible)) {
                                $errores++;
                            }
                        }
                        ?>
                        
                        <h5 class="mt-4">Verificación de configuración</h5>
                        <?php
                        $constantes_requeridas = [
                            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 
                            'SESSION_NAME', 'APP_NAME', 'BASE_URL'
                        ];
                        
                        foreach ($constantes_requeridas as $constante) {
                            $definida = defined($constante);
                            if (!mostrar_resultado("Constante $constante", $definida, $definida ? constant($constante) : 'No definida')) {
                                $errores++;
                            }
                        }
                        ?>
                        
                        <hr class="my-4">
                        
                        <?php if ($errores === 0): ?>
                            <div class="alert alert-success text-center">
                                <h4><i class="bi bi-check-circle-fill me-2"></i>¡Sistema verificado correctamente!</h4>
                                <p class="mb-0">Todos los componentes están funcionando. El sistema está listo para usar.</p>
                                <a href="index.php" class="btn btn-success mt-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Ir al Sistema
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger text-center">
                                <h4><i class="bi bi-x-circle-fill me-2"></i>Se encontraron <?php echo $errores; ?> errores</h4>
                                <p>Por favor, revisa y corrige los errores antes de usar el sistema.</p>
                                <?php if (file_exists('install.php')): ?>
                                    <a href="install.php" class="btn btn-warning mt-3">
                                        <i class="bi bi-gear-fill me-2"></i>Ejecutar Instalación
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Versión PHP:</strong> <?php echo PHP_VERSION; ?></p>
                                <p><strong>Sistema operativo:</strong> <?php echo PHP_OS; ?></p>
                                <p><strong>Servidor web:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'No detectado'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Aplicación:</strong> <?php echo defined('APP_NAME') ? APP_NAME : 'No configurado'; ?></p>
                                <p><strong>Versión:</strong> <?php echo defined('APP_VERSION') ? APP_VERSION : 'No configurado'; ?></p>
                                <p><strong>Debug mode:</strong> <?php echo defined('DEBUG_MODE') && DEBUG_MODE ? 'Activado' : 'Desactivado'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
