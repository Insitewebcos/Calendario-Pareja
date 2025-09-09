<?php
/**
 * Clase Calendario
 * Maneja los datos y operaciones del calendario
 */

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

class Calendario {
    private $db;
    
    public function __construct() {
        $this->db = Database::obtener_instancia();
    }
    
    /**
     * Obtiene los datos del calendario para un mes específico
     * @param int $usuario_id
     * @param int $anio
     * @param int $mes
     * @return array
     */
    public function obtener_datos_mes($usuario_id, $anio, $mes) {
        try {
            $fecha_inicio = sprintf('%04d-%02d-01', $anio, $mes);
            $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
            
            $sql = "SELECT DATE(fecha) as fecha, numero_valor, observaciones 
                    FROM calendario_datos 
                    WHERE usuario_id = ? 
                    AND fecha BETWEEN ? AND ?
                    ORDER BY fecha";
            
            $stmt = $this->db->ejecutar_consulta($sql, [$usuario_id, $fecha_inicio, $fecha_fin]);
            
            $datos = [];
            if ($stmt) {
                while ($row = $stmt->fetch()) {
                    $datos[$row['fecha']] = [
                        'numero_valor' => $row['numero_valor'],
                        'observaciones' => $row['observaciones']
                    ];
                }
            }
            
            return $datos;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error obteniendo datos del mes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los datos de un día específico
     * @param int $usuario_id
     * @param string $fecha (Y-m-d)
     * @return array|null
     */
    public function obtener_datos_dia($usuario_id, $fecha) {
        try {
            $sql = "SELECT numero_valor, observaciones, fecha_creacion, fecha_modificacion 
                    FROM calendario_datos 
                    WHERE usuario_id = ? AND DATE(fecha) = ?";
            
            $stmt = $this->db->ejecutar_consulta($sql, [$usuario_id, $fecha]);
            
            if ($stmt) {
                return $stmt->fetch();
            }
            
            return null;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error obteniendo datos del día: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Guarda o actualiza los datos de un día
     * @param int $usuario_id
     * @param string $fecha (Y-m-d)
     * @param int $numero_valor (1-10)
     * @param string $observaciones
     * @return bool
     */
    public function guardar_datos_dia($usuario_id, $fecha, $numero_valor, $observaciones = '') {
        try {
            // Verificar si ya existen datos para este día
            $datos_existentes = $this->obtener_datos_dia($usuario_id, $fecha);
            
            if ($datos_existentes) {
                // Actualizar datos existentes
                $sql = "UPDATE calendario_datos 
                        SET numero_valor = ?, observaciones = ?, fecha_modificacion = NOW() 
                        WHERE usuario_id = ? AND DATE(fecha) = ?";
                
                $params = [$numero_valor, $observaciones, $usuario_id, $fecha];
            } else {
                // Insertar nuevos datos
                $sql = "INSERT INTO calendario_datos (usuario_id, fecha, numero_valor, observaciones) 
                        VALUES (?, ?, ?, ?)";
                
                $params = [$usuario_id, $fecha, $numero_valor, $observaciones];
            }
            
            $stmt = $this->db->ejecutar_consulta($sql, $params);
            return $stmt !== false;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error guardando datos del día: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina los datos de un día específico
     * @param int $usuario_id
     * @param string $fecha (Y-m-d)
     * @return bool
     */
    public function eliminar_datos_dia($usuario_id, $fecha) {
        try {
            $sql = "DELETE FROM calendario_datos WHERE usuario_id = ? AND DATE(fecha) = ?";
            $stmt = $this->db->ejecutar_consulta($sql, [$usuario_id, $fecha]);
            
            return $stmt !== false;
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error eliminando datos del día: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera la estructura del calendario para un mes
     * @param int $anio
     * @param int $mes
     * @return array
     */
    public function generar_estructura_mes($anio, $mes) {
        $primer_dia = mktime(0, 0, 0, $mes, 1, $anio);
        $dias_en_mes = date('t', $primer_dia);
        $dia_semana_inicio = date('N', $primer_dia); // 1 = Lunes, 7 = Domingo
        
        $calendario = [];
        $semana = [];
        
        // Obtener días del mes anterior para completar la primera semana
        $mes_anterior = $mes - 1;
        $anio_anterior = $anio;
        
        if ($mes_anterior < 1) {
            $mes_anterior = 12;
            $anio_anterior--;
        }
        
        $dias_mes_anterior = date('t', mktime(0, 0, 0, $mes_anterior, 1, $anio_anterior));
        
        // Llenar días del mes anterior
        for ($i = $dia_semana_inicio - 1; $i > 0; $i--) {
            $dia = $dias_mes_anterior - $i + 1;
            $semana[] = [
                'dia' => $dia,
                'fecha' => sprintf('%04d-%02d-%02d', $anio_anterior, $mes_anterior, $dia),
                'es_mes_actual' => false,
                'es_hoy' => false
            ];
        }
        
        $hoy = date('Y-m-d');
        
        // Llenar días del mes actual
        for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
            $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
            
            $semana[] = [
                'dia' => $dia,
                'fecha' => $fecha,
                'es_mes_actual' => true,
                'es_hoy' => $fecha === $hoy
            ];
            
            // Si completamos una semana (7 días), la agregamos al calendario
            if (count($semana) === 7) {
                $calendario[] = $semana;
                $semana = [];
            }
        }
        
        // Completar la última semana con días del mes siguiente
        if (count($semana) > 0) {
            $mes_siguiente = $mes + 1;
            $anio_siguiente = $anio;
            
            if ($mes_siguiente > 12) {
                $mes_siguiente = 1;
                $anio_siguiente++;
            }
            
            $dia = 1;
            while (count($semana) < 7) {
                $fecha = sprintf('%04d-%02d-%02d', $anio_siguiente, $mes_siguiente, $dia);
                
                $semana[] = [
                    'dia' => $dia,
                    'fecha' => $fecha,
                    'es_mes_actual' => false,
                    'es_hoy' => false
                ];
                
                $dia++;
            }
            
            $calendario[] = $semana;
        }
        
        return $calendario;
    }
    
    /**
     * Valida los datos antes de guardar
     * @param int $numero_valor
     * @param string $fecha
     * @return array Errores de validación
     */
    public function validar_datos($numero_valor, $fecha) {
        $errores = [];
        
        // Validar número
        if (!validar_numero_1_a_5($numero_valor)) {
            $errores[] = "El número debe estar entre 1 y 5";
        }
        
        // Validar fecha
        if (!validar_fecha($fecha)) {
            $errores[] = "La fecha no es válida";
        }
        
        return $errores;
    }
    
    /**
     * Obtiene estadísticas del mes para el usuario
     * @param int $usuario_id
     * @param int $anio
     * @param int $mes
     * @return array
     */
    public function obtener_estadisticas_mes($usuario_id, $anio, $mes) {
        try {
            $fecha_inicio = sprintf('%04d-%02d-01', $anio, $mes);
            $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
            
            $sql = "SELECT 
                        COUNT(*) as total_dias,
                        SUM(numero_valor) as total,
                        MIN(numero_valor) as minimo,
                        MAX(numero_valor) as maximo,
                        SUM(CASE WHEN observaciones IS NOT NULL AND observaciones != '' THEN 1 ELSE 0 END) as dias_con_observaciones
                    FROM calendario_datos 
                    WHERE usuario_id = ? 
                    AND fecha BETWEEN ? AND ?";
            
            $stmt = $this->db->ejecutar_consulta($sql, [$usuario_id, $fecha_inicio, $fecha_fin]);
            
            if ($stmt) {
                $stats = $stmt->fetch();
                $stats['total'] = $stats['total'] ? intval($stats['total']) : 0;
                return $stats;
            }
            
            return [
                'total_dias' => 0,
                'total' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'dias_con_observaciones' => 0
            ];
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total_dias' => 0,
                'total' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'dias_con_observaciones' => 0
            ];
        }
    }
    
    /**
     * Obtiene estadísticas del año para el usuario
     * @param int $usuario_id
     * @param int $anio
     * @return array
     */
    public function obtener_estadisticas_anio($usuario_id, $anio) {
        try {
            $fecha_inicio = sprintf('%04d-01-01', $anio);
            $fecha_fin = sprintf('%04d-12-31', $anio);
            
            $sql = "SELECT 
                        COUNT(*) as total_dias,
                        SUM(numero_valor) as total,
                        MIN(numero_valor) as minimo,
                        MAX(numero_valor) as maximo,
                        SUM(CASE WHEN observaciones IS NOT NULL AND observaciones != '' THEN 1 ELSE 0 END) as dias_con_observaciones
                    FROM calendario_datos 
                    WHERE usuario_id = ? 
                    AND fecha BETWEEN ? AND ?";
            
            $stmt = $this->db->ejecutar_consulta($sql, [$usuario_id, $fecha_inicio, $fecha_fin]);
            
            if ($stmt) {
                $stats = $stmt->fetch();
                $stats['total'] = $stats['total'] ? intval($stats['total']) : 0;
                // Calcular promedio mensual: total del año dividido por 12 meses
                $stats['promedio_mensual'] = $stats['total'] ? round($stats['total'] / 12, 1) : 0;
                return $stats;
            }
            
            return [
                'total_dias' => 0,
                'total' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'dias_con_observaciones' => 0,
                'promedio_mensual' => 0
            ];
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error obteniendo estadísticas anuales: " . $e->getMessage());
            return [
                'total_dias' => 0,
                'total' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'dias_con_observaciones' => 0,
                'promedio_mensual' => 0
            ];
        }
    }
    
    /**
     * Obtiene estadísticas totales para el usuario desde el primer registro
     * @param int $usuario_id
     * @return array
     */
    public function obtener_estadisticas_totales($usuario_id) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_dias,
                        SUM(numero_valor) as total,
                        MIN(YEAR(fecha)) as primer_anio,
                        MAX(YEAR(fecha)) as ultimo_anio
                    FROM calendario_datos 
                    WHERE usuario_id = ?";
            
            $stmt = $this->db->ejecutar_consulta($sql, [$usuario_id]);
            
            if ($stmt) {
                $stats = $stmt->fetch();
                $stats['total'] = $stats['total'] ? intval($stats['total']) : 0;
                $stats['total_dias'] = $stats['total_dias'] ? intval($stats['total_dias']) : 0;
                
                // Calcular número de años con datos
                $anios_con_datos = 0;
                if ($stats['primer_anio'] && $stats['ultimo_anio']) {
                    $anios_con_datos = ($stats['ultimo_anio'] - $stats['primer_anio']) + 1;
                }
                
                // Calcular promedio anual: total dividido por número de años con datos
                $stats['promedio_anual'] = ($anios_con_datos > 0 && $stats['total'] > 0) ? 
                    round($stats['total'] / $anios_con_datos, 1) : 0;
                
                return $stats;
            }
            
            return [
                'total_dias' => 0,
                'total' => 0,
                'primer_anio' => null,
                'ultimo_anio' => null,
                'promedio_anual' => 0
            ];
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                throw $e;
            }
            error_log("Error obteniendo estadísticas totales: " . $e->getMessage());
            return [
                'total_dias' => 0,
                'total' => 0,
                'primer_anio' => null,
                'ultimo_anio' => null,
                'promedio_anual' => 0
            ];
        }
    }
}
?>
