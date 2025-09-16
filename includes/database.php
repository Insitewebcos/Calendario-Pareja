<?php
/**
 * Clase para manejo de conexión a base de datos
 * Implementa patrón Singleton para garantizar una sola conexión
 */

require_once 'config.php';

class Database {
    private static $instancia = null;
    private $conexion;
    
    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            
        } catch (PDOException $e) {
            $mensaje_error = "Error de conexión a la base de datos: " . $e->getMessage();
            
            if (DEBUG_MODE) {
                die($mensaje_error);
            } else {
                error_log($mensaje_error);
                die("Error interno del servidor. Contacte al administrador.");
            }
        }
    }
    
    /**
     * Obtiene la instancia única de la clase
     * @return Database
     */
    public static function obtener_instancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    /**
     * Alias en inglés para compatibilidad
     * @return Database
     */
    public static function getInstance() {
        return self::obtener_instancia();
    }
    
    /**
     * Obtiene la conexión PDO
     * @return PDO
     */
    public function obtener_conexion() {
        return $this->conexion;
    }
    
    /**
     * Alias en inglés para compatibilidad
     * @return PDO
     */
    public function getConnection() {
        return $this->conexion;
    }
    
    /**
     * Ejecuta una consulta preparada
     * @param string $sql
     * @param array $parametros
     * @return PDOStatement|false
     */
    public function ejecutar_consulta($sql, $parametros = []) {
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($parametros);
            return $stmt;
        } catch (PDOException $e) {
            $mensaje_error = "Error al ejecutar consulta: " . $e->getMessage();
            
            if (DEBUG_MODE) {
                throw new Exception($mensaje_error);
            } else {
                error_log($mensaje_error . " - SQL: " . $sql);
                return false;
            }
        }
    }
    
    /**
     * Obtiene el último ID insertado
     * @return string
     */
    public function ultimo_id_insertado() {
        return $this->conexion->lastInsertId();
    }
    
    /**
     * Inicia una transacción
     * @return bool
     */
    public function iniciar_transaccion() {
        return $this->conexion->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     * @return bool
     */
    public function confirmar_transaccion() {
        return $this->conexion->commit();
    }
    
    /**
     * Revierte una transacción
     * @return bool
     */
    public function revertir_transaccion() {
        return $this->conexion->rollBack();
    }
    
    /**
     * Previene la clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Previene la deserialización del objeto
     */
    public function __wakeup() {}
}
?>