<?php
/**
 * Página principal del calendario
 * Muestra el calendario mensual con datos del usuario
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../classes/Usuario.php';
require_once '../classes/Calendario.php';

// Verificar autenticación
requerir_autenticacion();

$usuario = new Usuario();
$calendario = new Calendario();

$usuario_actual = $usuario->obtener_usuario_actual();
if (!$usuario_actual) {
    cerrar_sesion();
    header('Location: ../index.php');
    exit();
}

// Obtener mes y año actuales o desde parámetros
$anio_actual = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$mes_actual = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');

// Validar valores de mes y año
if ($mes_actual < 1 || $mes_actual > 12) {
    $mes_actual = date('n');
}
if ($anio_actual < 2020 || $anio_actual > 2030) {
    $anio_actual = date('Y');
}

$errores = [];
$mensaje_exito = '';

// Verificar si hay mensaje de éxito en la sesión (después de redirección)
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']); // Limpiar mensaje después de mostrarlo
}

// Procesar formulario de datos del día
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || !validar_csrf($_POST['csrf_token'])) {
        $errores[] = "Token de seguridad inválido";
    } else {
        $accion = sanitizar_entrada($_POST['accion']);
        $fecha = sanitizar_entrada($_POST['fecha'] ?? '');
        $numero_valor = (int)($_POST['numero_valor'] ?? 0);
        $observaciones = sanitizar_entrada($_POST['observaciones'] ?? '');
        
        if ($accion === 'guardar_dia') {
            // Validar datos
            $errores_validacion = $calendario->validar_datos($numero_valor, $fecha);
            
            if (empty($errores_validacion)) {
                if ($calendario->guardar_datos_dia($usuario_actual['id'], $fecha, $numero_valor, $observaciones)) {
                    // Redirigir con mensaje de éxito para evitar reenvío del formulario
                    $_SESSION['mensaje_exito'] = "Datos guardados correctamente";
                    header('Location: calendario.php?mes=' . $mes_actual . '&anio=' . $anio_actual);
                    exit();
                } else {
                    $errores[] = "Error al guardar los datos";
                }
            } else {
                $errores = array_merge($errores, $errores_validacion);
            }
        } elseif ($accion === 'eliminar_dia') {
            if ($calendario->eliminar_datos_dia($usuario_actual['id'], $fecha)) {
                // Redirigir con mensaje de éxito para evitar reenvío del formulario
                $_SESSION['mensaje_exito'] = "Datos eliminados correctamente";
                header('Location: calendario.php?mes=' . $mes_actual . '&anio=' . $anio_actual);
                exit();
            } else {
                $errores[] = "Error al eliminar los datos";
            }
        }
    }
}

// Obtener datos del calendario para el mes actual
$datos_mes = $calendario->obtener_datos_mes($usuario_actual['id'], $anio_actual, $mes_actual);
$estructura_calendario = $calendario->generar_estructura_mes($anio_actual, $mes_actual);
$estadisticas = $calendario->obtener_estadisticas_mes($usuario_actual['id'], $anio_actual, $mes_actual);
$estadisticas_anio = $calendario->obtener_estadisticas_anio($usuario_actual['id'], $anio_actual);
$estadisticas_totales = $calendario->obtener_estadisticas_totales($usuario_actual['id']);

// Configurar navegación de meses
$mes_anterior = $mes_actual - 1;
$anio_anterior = $anio_actual;
if ($mes_anterior < 1) {
    $mes_anterior = 12;
    $anio_anterior--;
}

$mes_siguiente = $mes_actual + 1;
$anio_siguiente = $anio_actual;
if ($mes_siguiente > 12) {
    $mes_siguiente = 1;
    $anio_siguiente++;
}

$meses_espanol = obtener_meses_espanol();
$dias_semana = obtener_dias_semana_espanol();

$csrf_token = generar_csrf();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sistema de Calendario - Calendario mensual">
  <title><?php echo APP_NAME; ?></title>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32"
    href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><text y=%2224%22 font-size=%2224%22>❤️</text></svg>">
  <link rel="shortcut icon"
    href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 32 32%22><text y=%2224%22 font-size=%2224%22>❤️</text></svg>">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- CSS personalizado -->
  <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <i class="bi bi-calendar-check me-2"></i><?php echo APP_NAME; ?>
      </a>

      <div class="navbar-nav ms-auto">
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i><?php echo sanitizar_entrada($usuario_actual['nombre_completo']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <h6 class="dropdown-header">Usuario: <?php echo sanitizar_entrada($usuario_actual['nombre_usuario']); ?>
              </h6>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <div class="container-fluid py-4">
    <!-- Mensajes -->
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

    <?php if (!empty($mensaje_exito)): ?>
    <div class="toast-notification success auto-fade" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      <?php echo sanitizar_entrada($mensaje_exito); ?>
      <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    </div>
    <?php endif; ?>

    <div class="row">
      <!-- Calendario principal -->
      <div class="col-lg-9 mb-4">
        <div class="calendario-container">
          <!-- Header del calendario -->
          <div class="calendario-header">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class="bi bi-calendar3 me-3 text-white" style="font-size: 1.5rem;"></i>
                <form method="GET" class="d-flex gap-2 align-items-center">
                  <select name="mes" class="form-select form-select-sm" style="width: auto; min-width: 120px;"
                    onchange="this.form.submit()">
                    <?php foreach ($meses_espanol as $num => $nombre): ?>
                    <option value="<?php echo $num; ?>" <?php echo $num === $mes_actual ? 'selected' : ''; ?>>
                      <?php echo $nombre; ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                  <select name="anio" class="form-select form-select-sm" style="width: auto; min-width: 80px;"
                    onchange="this.form.submit()">
                    <?php for ($a = 2020; $a <= 2030; $a++): ?>
                    <option value="<?php echo $a; ?>" <?php echo $a === $anio_actual ? 'selected' : ''; ?>>
                      <?php echo $a; ?>
                    </option>
                    <?php endfor; ?>
                  </select>
                </form>
              </div>
              <div class="btn-group" role="group">
                <a href="?mes=<?php echo $mes_anterior; ?>&anio=<?php echo $anio_anterior; ?>"
                  class="btn btn-light btn-sm">
                  <i class="bi bi-chevron-left"></i> Anterior
                </a>
                <a href="?mes=<?php echo date('n'); ?>&anio=<?php echo date('Y'); ?>" class="btn btn-light btn-sm">
                  Hoy
                </a>
                <a href="?mes=<?php echo $mes_siguiente; ?>&anio=<?php echo $anio_siguiente; ?>"
                  class="btn btn-light btn-sm">
                  Siguiente <i class="bi bi-chevron-right"></i>
                </a>
              </div>
            </div>
          </div>



          <!-- Grid del calendario -->
          <div class="calendario-grid">
            <!-- Cabecera con días de la semana -->
            <?php foreach ($dias_semana as $dia): ?>
            <div class="calendario-dia-semana"><?php echo $dia; ?></div>
            <?php endforeach; ?>

            <!-- Días del calendario -->
            <?php foreach ($estructura_calendario as $semana): ?>
            <?php foreach ($semana as $dia_info): ?>
            <button type="button" class="calendario-dia <?php 
                                            echo !$dia_info['es_mes_actual'] ? 'otro-mes' : '';
                                            echo $dia_info['es_hoy'] ? ' hoy' : '';
                                            echo isset($datos_mes[$dia_info['fecha']]) ? ' con-datos' : '';
                                        ?>" data-fecha="<?php echo $dia_info['fecha']; ?>" data-bs-toggle="modal"
              data-bs-target="#modalDia"
              <?php if (isset($datos_mes[$dia_info['fecha']]['observaciones']) && !empty($datos_mes[$dia_info['fecha']]['observaciones'])): ?>
              data-bs-toggle="tooltip" data-bs-placement="top"
              title="<?php echo sanitizar_entrada($datos_mes[$dia_info['fecha']]['observaciones']); ?>" <?php endif; ?>>

              <div class="dia-numero"><?php echo $dia_info['dia']; ?></div>

              <?php if (isset($datos_mes[$dia_info['fecha']])): ?>
              <div class="dia-valor">
                <?php echo $datos_mes[$dia_info['fecha']]['numero_valor']; ?>
              </div>
              <?php endif; ?>
            </button>
            <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Panel lateral -->
      <div class="col-lg-3">
        <!-- Estadísticas del mes -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-calendar me-2"></i>Estadísticas del Mes
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <div class="border rounded p-2">
                  <div class="h4 text-primary mb-0"><?php echo $estadisticas['total_dias']; ?></div>
                  <small class="text-muted">Días registrados</small>
                </div>
              </div>
              <div class="col-6">
                <div class="border rounded p-2">
                  <div class="h4 text-success mb-0"><?php echo $estadisticas['total']; ?></div>
                  <small class="text-muted">Total</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas Anuales -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-graph-up me-2"></i>Estadísticas Anuales <?php echo $anio_actual; ?>
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6 mb-3">
                <div class="border rounded p-2">
                  <div class="h4 text-primary mb-0"><?php echo $estadisticas_anio['total_dias']; ?></div>
                  <small class="text-muted">Días registrados</small>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="border rounded p-2">
                  <div class="h4 text-success mb-0"><?php echo $estadisticas_anio['total']; ?></div>
                  <small class="text-muted">Total</small>
                </div>
              </div>
              <div class="col-6">
                <div class="border rounded p-2">
                  <div class="h4 text-warning mb-0"><?php echo $estadisticas_anio['minimo']; ?> -
                    <?php echo $estadisticas_anio['maximo']; ?></div>
                  <small class="text-muted">Min - Max</small>
                </div>
              </div>
              <div class="col-6">
                <div class="border rounded p-2">
                  <div class="h4 text-info mb-0"><?php echo $estadisticas_anio['promedio_mensual']; ?></div>
                  <small class="text-muted">Promedio</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas Totales -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-trophy me-2"></i>Estadísticas Totales
              <?php if ($estadisticas_totales['primer_anio'] && $estadisticas_totales['ultimo_anio']): ?>
              <small class="text-muted">
                (<?php echo $estadisticas_totales['primer_anio']; ?>-<?php echo $estadisticas_totales['ultimo_anio']; ?>)
              </small>
              <?php endif; ?>
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <div class="border rounded p-2">
                  <div class="h4 text-success mb-0"><?php echo $estadisticas_totales['total']; ?></div>
                  <small class="text-muted">Total</small>
                </div>
              </div>
              <div class="col-6">
                <div class="border rounded p-2">
                  <div class="h4 text-info mb-0"><?php echo $estadisticas_totales['promedio_anual']; ?></div>
                  <small class="text-muted">Promedio</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para editar día -->
  <div class="modal fade" id="modalDia" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-calendar-date me-2"></i>
            <span id="modal-titulo">Editar Día</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="POST" id="form-dia">
          <div class="modal-body">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="accion" value="guardar_dia">
            <input type="hidden" name="fecha" id="modal-fecha">

            <div class="mb-3">
              <label for="numero_valor" class="form-label">
                <i class="bi bi-123 me-1"></i>Número (1-5) <span class="text-danger">*</span>
              </label>
              <div class="numero-selector" id="numero-selector">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="numero-opcion" data-valor="<?php echo $i; ?>">
                  <?php echo $i; ?>
                </div>
                <?php endfor; ?>
              </div>
              <input type="hidden" name="numero_valor" id="numero_valor" required>
            </div>

            <div class="mb-3">
              <label for="observaciones" class="form-label">
                <i class="bi bi-chat-text me-1"></i>Observaciones
              </label>
              <textarea class="form-control" name="observaciones" id="observaciones" rows="3"
                placeholder="Escribe aquí tus observaciones del día (opcional)"></textarea>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i>Cancelar
            </button>

            <div class="btn-group-actions">
              <button type="button" class="btn btn-warning" id="btn-limpiar">
                <i class="bi bi-eraser me-1"></i>Limpiar
              </button>
              <button type="button" class="btn btn-danger" id="btn-eliminar" style="display: none;">
                <i class="bi bi-trash me-1"></i>Eliminar
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>Guardar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- JavaScript personalizado -->
  <script src="../assets/js/calendario.js"></script>

  <script>
  // Auto-fade para notificaciones flotantes
  document.addEventListener('DOMContentLoaded', function() {
    const notificacionesAutoFade = document.querySelectorAll('.toast-notification.auto-fade');

    notificacionesAutoFade.forEach(function(notificacion) {
      setTimeout(function() {
        if (notificacion.parentNode) {
          // Agregar transición y fade out
          notificacion.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
          notificacion.style.opacity = '0';
          notificacion.style.transform = 'translateX(-50%) translateY(-20px)';

          // Remover el elemento después del fade out
          setTimeout(function() {
            if (notificacion.parentNode) {
              notificacion.remove();
            }
          }, 500); // Esperar a que termine la transición
        }
      }, 2000); // Esperar 2 segundos antes de empezar fade out
    });
  });
  </script>
</body>

</html>