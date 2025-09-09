<?php
/**
 * Cargador de Variables de Entorno
 * Carga variables desde archivo .env de forma segura
 */

class EnvLoader {
    
    /**
     * Carga variables de entorno desde archivo .env
     * @param string $path Ruta al archivo .env
     * @return bool
     */
    public static function load($path = null) {
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        
        // Si no existe el archivo, buscar alternativas
        if (!file_exists($path)) {
            // Buscar .env.local para desarrollo
            $localPath = __DIR__ . '/../.env.local';
            if (file_exists($localPath)) {
                $path = $localPath;
            } else {
                // No encontrar archivo .env no es crítico
                return false;
            }
        }
        
        try {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Ignorar comentarios
                if (strpos($line, '#') === 0) {
                    continue;
                }
                
                // Parsear línea formato KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remover comillas si existen
                    $value = self::removeQuotes($value);
                    
                    // Solo establecer si no existe en $_ENV o $_SERVER
                    if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                        putenv("$key=$value");
                    }
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Error cargando .env: " . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Obtiene una variable de entorno con valor por defecto
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public static function get($key, $default = null) {
        // Prioridad: $_ENV > $_SERVER > getenv() > default
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Obtiene una variable de entorno como booleano
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public static function getBool($key, $default = false) {
        $value = self::get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        // Convertir string a bool
        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'on']);
    }
    
    /**
     * Obtiene una variable de entorno como entero
     * @param string $key
     * @param int $default
     * @return int
     */
    public static function getInt($key, $default = 0) {
        $value = self::get($key, $default);
        return (int) $value;
    }
    
    /**
     * Verifica si una variable de entorno existe
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Remueve comillas de un valor
     * @param string $value
     * @return string
     */
    private static function removeQuotes($value) {
        $value = trim($value);
        
        // Remover comillas dobles
        if (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            return substr($value, 1, -1);
        }
        
        // Remover comillas simples
        if (strlen($value) >= 2 && $value[0] === "'" && $value[strlen($value) - 1] === "'") {
            return substr($value, 1, -1);
        }
        
        return $value;
    }
    
    /**
     * Valida que las variables críticas estén definidas
     * @param array $required Variables requeridas
     * @throws Exception
     */
    public static function validateRequired($required = []) {
        $missing = [];
        
        foreach ($required as $key) {
            if (!self::has($key)) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception("Variables de entorno faltantes: " . implode(', ', $missing));
        }
    }
}

?>
