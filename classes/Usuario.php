<?php
/**
 * Clase Usuario
 * Maneja la autenticación y gestión de usuarios
 */

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::obtener_instancia();
    }
    
    /**
     * Autentica un usuario con nombre y contraseña
     * @param string $nombre_usuario
     * @param string $password
     * @return array|false Array con datos del usuario o false si falla
     */
    public function autenticar($nombre_usuario, $password) {
        try {
            $sql = "SELECT id, nombre_usuario, nombre_completo, password_hash, activo 
                    FROM usuarios 
                    WHERE nombre_usuario = ? AND activo = 1";
            
            $stmt = $this->db->ejecutar_consulta($sql, [$nombre_usuario]);
            
            if ($stmt && $usuario = $stmt->fetch()) {
                if (password_verify($password, $usuario['password_hash'])) {
                    // Actualizar fecha de último acceso
                    $this->actualizar_ultimo_acceso($usuario['id']);
                    
                    // Remover el hash de la respuesta
                    unset($usuario['password_hash']);
                    
                    return $usuario;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea una sesión para el usuario autenticado
     * @param array $usuario_datos
     */
    public function crear_sesion($usuario_datos) {
        iniciar_sesion_segura();
        
        $_SESSION['usuario_id'] = $usuario_datos['id'];
        $_SESSION['usuario_nombre'] = $usuario_datos['nombre_usuario'];
        $_SESSION['usuario_nombre_completo'] = $usuario_datos['nombre_completo'];
        $_SESSION['ultimo_acceso'] = time();
        
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
    }
    
    /**
     * Actualiza la fecha de último acceso del usuario
     * @param int $usuario_id
     */
    private function actualizar_ultimo_acceso($usuario_id) {
        try {
            $sql = "UPDATE usuarios SET fecha_ultimo_acceso = NOW() WHERE id = ?";
            $this->db->ejecutar_consulta($sql, [$usuario_id]);
        } catch (Exception $e) {
            // Log del error pero no interrumpir el login
            error_log("Error actualizando último acceso: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene los datos básicos del usuario actual
     * @return array|null
     */
    public function obtener_usuario_actual() {
        if (!usuario_autenticado()) {
            return null;
        }
        
        try {
            $sql = "SELECT id, nombre_usuario, nombre_completo, email, fecha_ultimo_acceso 
                    FROM usuarios 
                    WHERE id = ? AND activo = 1";
            
            $stmt = $this->db->ejecutar_consulta($sql, [$_SESSION['usuario_id']]);
            
            if ($stmt) {
                return $stmt->fetch();
            }
            
            return null;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error obteniendo usuario actual: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verifica si existe un usuario con el nombre dado
     * @param string $nombre_usuario
     * @return bool
     */
    public function existe_usuario($nombre_usuario) {
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = ?";
            $stmt = $this->db->ejecutar_consulta($sql, [$nombre_usuario]);
            
            if ($stmt) {
                $resultado = $stmt->fetch();
                return $resultado['total'] > 0;
            }
            
            return false;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error verificando existencia de usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea un nuevo usuario (función para administradores)
     * @param string $nombre_usuario
     * @param string $nombre_completo
     * @param string $password
     * @param string $email
     * @return bool
     */
    public function crear_usuario($nombre_usuario, $nombre_completo, $password, $email = null) {
        try {
            // Verificar que no existe el usuario
            if ($this->existe_usuario($nombre_usuario)) {
                return false;
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre_usuario, nombre_completo, password_hash, email) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $this->db->ejecutar_consulta($sql, [
                $nombre_usuario, 
                $nombre_completo, 
                $password_hash, 
                $email
            ]);
            
            return $stmt !== false;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error creando usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valida los datos de login
     * @param string $nombre_usuario
     * @param string $password
     * @return array Errores de validación
     */
    public function validar_datos_login($nombre_usuario, $password) {
        $errores = [];
        
        if (empty($nombre_usuario)) {
            $errores[] = "El nombre de usuario es obligatorio";
        } elseif (strlen($nombre_usuario) < 3) {
            $errores[] = "El nombre de usuario debe tener al menos 3 caracteres";
        }
        
        if (empty($password)) {
            $errores[] = "La contraseña es obligatoria";
        } elseif (strlen($password) < 6) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres";
        }
        
        return $errores;
    }
}
?>
