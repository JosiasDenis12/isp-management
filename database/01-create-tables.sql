-- Crear base de datos
CREATE DATABASE IF NOT EXISTS isp_management;
USE isp_management;

-- Crear tabla de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(255),
    estado ENUM('activo', 'suspendido', 'pendiente') DEFAULT 'activo',
    tipo_conexion ENUM('fibra_optica', 'inalambrica', 'cableado_utp'),
    fecha_contratacion DATE DEFAULT (CURRENT_DATE),
    dia_corte TINYINT UNSIGNED NOT NULL DEFAULT 5,
    plan_mensual DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla de pagos
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    metodo_pago ENUM('transferencia', 'efectivo', 'paypal', 'tarjeta'),
    monto_recibido DECIMAL(10,2) NULL,
    estado ENUM('pagado', 'pendiente', 'vencido') DEFAULT 'pendiente',
    numero_factura VARCHAR(100),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla de equipos
CREATE TABLE IF NOT EXISTS equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    instalacion_id INT NULL,
    tipo_equipo VARCHAR(100) NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    numero_serie VARCHAR(100),
    estado_tecnico ENUM('operativo', 'necesita_revision', 'dañado', 'fuera_de_servicio', 'en_mantenimiento') DEFAULT 'operativo',
    fecha_instalacion DATE,
    observaciones_tecnico TEXT,
    mac_address VARCHAR(50),
    direccion_ip VARCHAR(45),
    password_acceso VARCHAR(255),
    ssid VARCHAR(255),
    usuario_acceso VARCHAR(100),
    acceso_habilitado TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS instalaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha_instalacion DATE NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla de visitas técnicas
CREATE TABLE IF NOT EXISTS visitas_tecnicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    equipo_id INT,
    fecha_visita DATETIME NOT NULL,
    tipo_visita ENUM('instalacion', 'mantenimiento', 'reparacion', 'revision'),
    tecnico_nombre VARCHAR(255),
    observaciones TEXT,
    estado ENUM('programada', 'pendiente', 'completada', 'cancelada', 'reprogramada') DEFAULT 'programada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear índices para mejorar rendimiento
CREATE INDEX idx_clientes_estado ON clientes(estado);
CREATE INDEX idx_pagos_cliente_id ON pagos(cliente_id);
CREATE INDEX idx_pagos_fecha_vencimiento ON pagos(fecha_vencimiento);
CREATE INDEX idx_pagos_estado ON pagos(estado);
CREATE INDEX idx_equipos_cliente_id ON equipos(cliente_id);
CREATE INDEX idx_equipos_instalacion_id ON equipos(instalacion_id);
CREATE INDEX idx_equipos_mac_address ON equipos(mac_address);
CREATE INDEX idx_equipos_direccion_ip ON equipos(direccion_ip);
CREATE INDEX idx_instalaciones_cliente_id ON instalaciones(cliente_id);
