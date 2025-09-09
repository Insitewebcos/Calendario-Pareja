<?php
/**
 * Página de inicio y login del sistema
 * Punto de entrada principal de la aplicación
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/Usuario.php';

iniciar_sesion_segura();

// Si el usuario ya está autenticado, redirigir al calendario
if (usuario_autenticado()) {
    header('Location: pages/calendario.php');
    exit();
}

$usuario = new Usuario();
$errores = [];
$mensaje_exito = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'login') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || !validar_csrf($_POST['csrf_token'])) {
        $errores[] = "Token de seguridad inválido";
    } else {
        $nombre_usuario = sanitizar_entrada($_POST['nombre_usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validar datos de entrada
        $errores_validacion = $usuario->validar_datos_login($nombre_usuario, $password);
        
        if (empty($errores_validacion)) {
            // Intentar autenticar
            $usuario_datos = $usuario->autenticar($nombre_usuario, $password);
            
            if ($usuario_datos) {
                $usuario->crear_sesion($usuario_datos);
                header('Location: pages/calendario.php');
                exit();
            } else {
                $errores[] = "Nombre de usuario o contraseña incorrectos";
            }
        } else {
            $errores = array_merge($errores, $errores_validacion);
        }
    }
}

$csrf_token = generar_csrf();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sistema de Calendario - Acceso al sistema">
  <meta name="author" content="Sistema de Calendario">
  <title><?php echo APP_NAME; ?> - Iniciar Sesión</title>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32"
    href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><text y=%2224%22 font-size=%2224%22>❤️</text></svg>">
  <link rel="shortcut icon"
    href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><text y=%2224%22 font-size=%2224%22>❤️</text></svg>">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- CSS personalizado -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100 justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg border-0">
          <div class="card-body p-5">
            <!-- Logo y título -->
            <div class="text-center mb-4">
              <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
              <h2 class="card-title mb-3"><?php echo APP_NAME; ?></h2>
            </div>

            <!-- Mensajes de error -->
            <?php if (!empty($errores)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <ul class="mb-0 list-unstyled">
                <?php foreach ($errores as $error): ?>
                <li><?php echo sanitizar_entrada($error); ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Mensaje de éxito -->
            <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              <?php echo sanitizar_entrada($mensaje_exito); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Formulario de login -->
            <form method="POST" action="index.php" class="needs-validation" novalidate>
              <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
              <input type="hidden" name="accion" value="login">

              <div class="mb-3">
                <label for="nombre_usuario" class="form-label">
                  <i class="bi bi-person me-1"></i>Nombre de Usuario
                </label>
                <input type="text" class="form-control form-control-lg" id="nombre_usuario" name="nombre_usuario"
                  value="<?php echo isset($_POST['nombre_usuario']) ? sanitizar_entrada($_POST['nombre_usuario']) : ''; ?>"
                  required minlength="3" autocomplete="username" placeholder="Ingresa tu usuario">
                <div class="invalid-feedback">
                  Por favor, ingresa un nombre de usuario válido (mínimo 3 caracteres).
                </div>
              </div>

              <div class="mb-4">
                <label for="password" class="form-label">
                  <i class="bi bi-lock me-1"></i>Contraseña
                </label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg" id="password" name="password" required
                    minlength="6" autocomplete="current-password" placeholder="Ingresa tu contraseña">
                  <button class="btn btn-outline-secondary" type="button" id="toggle-password"
                    aria-label="Mostrar/ocultar contraseña">
                    <i class="bi bi-eye" id="toggle-icon"></i>
                  </button>
                </div>
                <div class="invalid-feedback">
                  Por favor, ingresa una contraseña válida (mínimo 6 caracteres).
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
              </div>
            </form>

            <!-- Información adicional -->
            <hr class="my-4">
          </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4">
          <small class="text-muted">
            <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?> &copy; <?php echo date('Y'); ?>
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  // Validación del formulario
  (function() {
    'use strict';
    window.addEventListener('load', function() {
      var forms = document.getElementsByClassName('needs-validation');
      var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
          if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    }, false);
  })();

  // Toggle para mostrar/ocultar contraseña
  document.getElementById('toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggle-icon');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.className = 'bi bi-eye-slash';
    } else {
      passwordInput.type = 'password';
      toggleIcon.className = 'bi bi-eye';
    }
  });

  // Enfocar primer campo al cargar
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('nombre_usuario').focus();
  });
  </script>
</body>

</html>