<?php
/**
 * Script de instalación del Sistema de Calendario
 * Crea automáticamente la base de datos y las tablas necesarias
 */

// Configuración temporal para la instalación
$config_instalacion = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'calendario_proyecto'
];

$errores = [];
$mensajes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config_instalacion['host'] = $_POST['host'] ?? 'localhost';
    $config_instalacion['user'] = $_POST['user'] ?? 'root';
    $config_instalacion['password'] = $_POST['password'] ?? '';
    $config_instalacion['database'] = $_POST['database'] ?? 'calendario_proyecto';
    
    try {
        // Conectar a MySQL sin especificar base de datos
        $dsn_servidor = "mysql:host={$config_instalacion['host']};charset=utf8mb4";
        $pdo = new PDO($dsn_servidor, $config_instalacion['user'], $config_instalacion['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $mensajes[] = "Conexión a MySQL establecida correctamente";
        
        // Crear base de datos
        $sql_create_db = "CREATE DATABASE IF NOT EXISTS `{$config_instalacion['database']}` 
                         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql_create_db);
        $mensajes[] = "Base de datos '{$config_instalacion['database']}' creada correctamente";
        
        // Conectar a la base de datos específica
        $dsn_db = "mysql:host={$config_instalacion['host']};dbname={$config_instalacion['database']};charset=utf8mb4";
        $pdo_db = new PDO($dsn_db, $config_instalacion['user'], $config_instalacion['password']);
        $pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear tabla de usuarios
        $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
            nombre_completo VARCHAR(100) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            activo TINYINT(1) DEFAULT 1,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_ultimo_acceso TIMESTAMP NULL,
            INDEX idx_nombre_usuario (nombre_usuario),
            INDEX idx_activo (activo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo_db->exec($sql_usuarios);
        $mensajes[] = "Tabla 'usuarios' creada correctamente";
        
        // Crear tabla de calendario_datos
        $sql_calendario = "CREATE TABLE IF NOT EXISTS calendario_datos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            fecha DATE NOT NULL,
            numero_valor TINYINT NOT NULL CHECK (numero_valor BETWEEN 1 AND 10),
            observaciones TEXT,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            UNIQUE KEY unique_usuario_fecha (usuario_id, fecha),
            INDEX idx_fecha (fecha),
            INDEX idx_usuario_fecha (usuario_id, fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo_db->exec($sql_calendario);
        $mensajes[] = "Tabla 'calendario_datos' creada correctamente";
        
        // Crear usuario administrador por defecto
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql_admin = "INSERT INTO usuarios (nombre_usuario, nombre_completo, password_hash, email) 
                     VALUES ('admin', 'Administrador del Sistema', ?, 'admin@ejemplo.com')
                     ON DUPLICATE KEY UPDATE 
                     nombre_completo = VALUES(nombre_completo),
                     email = VALUES(email)";
        
        $stmt = $pdo_db->prepare($sql_admin);
        $stmt->execute([$password_hash]);
        $mensajes[] = "Usuario administrador creado (Usuario: admin, Contraseña: admin123)";
        
        // Insertar datos de ejemplo
        $fecha_hoy = date('Y-m-d');
        $sql_ejemplo = "INSERT INTO calendario_datos (usuario_id, fecha, numero_valor, observaciones) VALUES
                       (1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 8, 'Excelente día de trabajo'),
                       (1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 5, 'Día regular, algunos problemas menores'),
                       (1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 9, 'Muy productivo, objetivos cumplidos'),
                       (1, CURDATE(), 7, 'Datos del día de hoy')
                       ON DUPLICATE KEY UPDATE 
                       numero_valor = VALUES(numero_valor),
                       observaciones = VALUES(observaciones)";
        
        $pdo_db->exec($sql_ejemplo);
        $mensajes[] = "Datos de ejemplo insertados correctamente";
        
        // Actualizar archivo de configuración
        $config_content = "<?php
/**
 * Configuración general del proyecto
 * Archivo de configuración principal con constantes del sistema
 */

// Configuración de la base de datos
define('DB_HOST', '{$config_instalacion['host']}');
define('DB_NAME', '{$config_instalacion['database']}');
define('DB_USER', '{$config_instalacion['user']}');
define('DB_PASS', '{$config_instalacion['password']}');
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
";

        file_put_contents('includes/config.php', $config_content);
        $mensajes[] = "Archivo de configuración actualizado";
        
        $mensajes[] = "<strong>¡Instalación completada exitosamente!</strong>";
        $mensajes[] = "Puedes acceder al sistema en: <a href='index.php'>index.php</a>";
        
    } catch (PDOException $e) {
        $errores[] = "Error de base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        $errores[] = "Error general: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instalación - Sistema de Calendario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h3 class="mb-0">
              <i class="bi bi-gear-fill me-2"></i>
              Instalación del Sistema de Calendario
            </h3>
          </div>

          <div class="card-body">
            <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
              <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>Errores encontrados:</h5>
              <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($mensajes)): ?>
            <div class="alert alert-success">
              <h5><i class="bi bi-check-circle-fill me-2"></i>Progreso de instalación:</h5>
              <ul class="mb-0">
                <?php foreach ($mensajes as $mensaje): ?>
                <li><?php echo $mensaje; ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <?php if (empty($mensajes) || !empty($errores)): ?>
            <form method="POST">
              <div class="mb-3">
                <label for="host" class="form-label">Host de MySQL</label>
                <input type="text" class="form-control" id="host" name="host"
                  value="<?php echo htmlspecialchars($config_instalacion['host']); ?>" required>
              </div>

              <div class="mb-3">
                <label for="user" class="form-label">Usuario de MySQL</label>
                <input type="text" class="form-control" id="user" name="user"
                  value="<?php echo htmlspecialchars($config_instalacion['user']); ?>" required>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Contraseña de MySQL</label>
                <input type="password" class="form-control" id="password" name="password"
                  value="<?php echo htmlspecialchars($config_instalacion['password']); ?>">
              </div>

              <div class="mb-3">
                <label for="database" class="form-label">Nombre de la base de datos</label>
                <input type="text" class="form-control" id="database" name="database"
                  value="<?php echo htmlspecialchars($config_instalacion['database']); ?>" required>
              </div>

              <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-play-fill me-2"></i>Instalar Sistema
              </button>
            </form>
            <?php else: ?>
            <div class="text-center">
              <h4 class="text-success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                ¡Instalación completada!
              </h4>
              <a href="index.php" class="btn btn-success btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Ir al Sistema
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card mt-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información</h5>
          </div>
          <div class="card-body">
            <h6>Requisitos del sistema:</h6>
            <ul>
              <li>PHP 8.0 o superior</li>
              <li>MySQL 5.7 o superior</li>
              <li>Extensión PDO MySQL habilitada</li>
              <li>Apache/Nginx con mod_rewrite</li>
            </ul>

            <h6 class="mt-3">Credenciales por defecto:</h6>
            <ul>
              <li><strong>Usuario:</strong> admin</li>
              <li><strong>Contraseña:</strong> admin123</li>
            </ul>

            <div class="alert alert-warning mt-3">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong>Importante:</strong> Elimina este archivo (install.php) después de completar la instalación por
              motivos de seguridad.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>