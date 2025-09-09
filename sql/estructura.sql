-- Script para crear la base de datos y tablas del sistema de calendario
-- Proyecto: Sistema de Calendario con Login
-- Fecha: 2024

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS calendario_proyecto 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE calendario_proyecto;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de datos del calendario
CREATE TABLE IF NOT EXISTS calendario_datos (
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

-- Insertar usuario de prueba (admin/admin123)
INSERT INTO usuarios (nombre_usuario, nombre_completo, password_hash, email) 
VALUES (
    'admin', 
    'Administrador del Sistema',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'admin@ejemplo.com'
) ON DUPLICATE KEY UPDATE 
    nombre_completo = VALUES(nombre_completo),
    email = VALUES(email);

-- Insertar algunos datos de ejemplo para el calendario
INSERT INTO calendario_datos (usuario_id, fecha, numero_valor, observaciones) VALUES
(1, '2024-01-15', 8, 'Excelente día de trabajo'),
(1, '2024-01-20', 5, 'Día regular, algunos problemas menores'),
(1, '2024-01-25', 9, 'Muy productivo, objetivos cumplidos'),
(1, CURDATE(), 7, 'Datos del día de hoy')
ON DUPLICATE KEY UPDATE 
    numero_valor = VALUES(numero_valor),
    observaciones = VALUES(observaciones);
