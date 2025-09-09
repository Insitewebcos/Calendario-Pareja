-- Script para actualizar la restricción de numero_valor de 1-10 a 1-5
-- Ejecutar este script en phpMyAdmin o cliente MySQL

USE calendario_proyecto;

-- En MySQL, las restricciones CHECK a veces no tienen nombres específicos
-- Recreamos la tabla con la nueva restricción

-- Crear tabla temporal con la nueva restricción
CREATE TABLE calendario_datos_temp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha DATE NOT NULL,
    numero_valor TINYINT NOT NULL CHECK (numero_valor BETWEEN 1 AND 5),
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_fecha (usuario_id, fecha),
    INDEX idx_fecha (fecha),
    INDEX idx_usuario_fecha (usuario_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiar datos existentes que estén en el rango 1-5
INSERT INTO calendario_datos_temp (usuario_id, fecha, numero_valor, observaciones, fecha_creacion, fecha_modificacion)
SELECT usuario_id, fecha, 
       CASE 
           WHEN numero_valor > 5 THEN 5 
           ELSE numero_valor 
       END as numero_valor, 
       observaciones, fecha_creacion, fecha_modificacion
FROM calendario_datos;

-- Eliminar tabla original
DROP TABLE calendario_datos;

-- Renombrar tabla temporal
RENAME TABLE calendario_datos_temp TO calendario_datos;
