<?php
/**
 * Página de cierre de sesión
 * Cierra la sesión del usuario y redirige al login
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Cerrar sesión
cerrar_sesion();

// Redirigir al login con mensaje
header('Location: ../index.php?mensaje=sesion_cerrada');
exit();
?>
