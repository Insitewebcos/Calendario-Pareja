<?php
/**
 * Funciones auxiliares del sistema
 * Funciones reutilizables para toda la aplicación
 */

require_once 'config.php';

/**
 * Inicia sesión segura si no está iniciada
 */
function iniciar_sesion_segura() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar sesiones ANTES de iniciarlas
        configurar_sesiones_seguras();
        
        session_name(SESSION_NAME);
        session_start();
        
        // Regenerar ID de sesión para prevenir hijacking
        if (!isset($_SESSION['regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['regenerated'] = true;
        }
        
        // Verificar timeout de sesión
        verificar_timeout_sesion();
    }
}

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function usuario_autenticado() {
    iniciar_sesion_segura();
    return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_nombre']);
}

/**
 * Redirige a login si el usuario no está autenticado
 */
function requerir_autenticacion() {
    if (!usuario_autenticado()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Verifica timeout de sesión
 */
function verificar_timeout_sesion() {
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > SESSION_TIMEOUT)) {
        cerrar_sesion();
        return;
    }
    $_SESSION['ultimo_acceso'] = time();
}

/**
 * Cierra la sesión del usuario
 */
function cerrar_sesion() {
    iniciar_sesion_segura();
    
    // Limpiar todas las variables de sesión
    $_SESSION = [];
    
    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Sanitiza datos de entrada para prevenir XSS
 * @param mixed $data
 * @return mixed
 */
function sanitizar_entrada($data) {
    if (is_array($data)) {
        return array_map('sanitizar_entrada', $data);
    }
    
    if (is_string($data)) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Valida un token CSRF
 * @param string $token
 * @return bool
 */
function validar_csrf($token) {
    iniciar_sesion_segura();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Genera un token CSRF
 * @return string
 */
function generar_csrf() {
    iniciar_sesion_segura();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida formato de fecha
 * @param string $fecha
 * @param string $formato
 * @return bool
 */
function validar_fecha($fecha, $formato = 'Y-m-d') {
    $d = DateTime::createFromFormat($formato, $fecha);
    return $d && $d->format($formato) === $fecha;
}

/**
 * Convierte fecha del formato español al formato MySQL
 * @param string $fecha_espanol (dd/mm/yyyy)
 * @return string (yyyy-mm-dd)
 */
function fecha_espanol_a_mysql($fecha_espanol) {
    if (empty($fecha_espanol)) {
        return '';
    }
    
    $partes = explode('/', $fecha_espanol);
    if (count($partes) !== 3) {
        return '';
    }
    
    return sprintf('%04d-%02d-%02d', $partes[2], $partes[1], $partes[0]);
}

/**
 * Convierte fecha del formato MySQL al formato español
 * @param string $fecha_mysql (yyyy-mm-dd)
 * @return string (dd/mm/yyyy)
 */
function fecha_mysql_a_espanol($fecha_mysql) {
    if (empty($fecha_mysql)) {
        return '';
    }
    
    $partes = explode('-', $fecha_mysql);
    if (count($partes) !== 3) {
        return '';
    }
    
    return sprintf('%02d/%02d/%04d', $partes[2], $partes[1], $partes[0]);
}

/**
 * Obtiene los nombres de los meses en español
 * @return array
 */
function obtener_meses_espanol() {
    return [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
}

/**
 * Obtiene los días de la semana en español
 * @return array
 */
function obtener_dias_semana_espanol() {
    return ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
}

/**
 * Valida un número entre 1 y 5
 * @param mixed $numero
 * @return bool
 */
function validar_numero_1_a_5($numero) {
    return is_numeric($numero) && $numero >= 1 && $numero <= 5 && $numero == (int)$numero;
}

/**
 * Muestra mensaje de error formateado
 * @param string $mensaje
 */
function mostrar_error($mensaje) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>' . sanitizar_entrada($mensaje);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}

/**
 * Muestra mensaje de éxito formateado
 * @param string $mensaje
 */
function mostrar_exito($mensaje) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo '<i class="bi bi-check-circle-fill me-2"></i>' . sanitizar_entrada($mensaje);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}
