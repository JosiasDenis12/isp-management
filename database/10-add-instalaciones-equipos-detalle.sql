-- Agrega soporte para instalaciones completas y datos especificos de antena/modem.

CREATE TABLE IF NOT EXISTS instalaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha_instalacion DATE NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_instalaciones_cliente_id (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE equipos
    ADD COLUMN IF NOT EXISTS instalacion_id INT NULL AFTER cliente_id,
    ADD COLUMN IF NOT EXISTS mac_address VARCHAR(50) NULL AFTER observaciones_tecnico,
    ADD COLUMN IF NOT EXISTS direccion_ip VARCHAR(45) NULL AFTER mac_address,
    ADD COLUMN IF NOT EXISTS password_acceso VARCHAR(255) NULL AFTER direccion_ip,
    ADD COLUMN IF NOT EXISTS ssid VARCHAR(255) NULL AFTER password_acceso,
    ADD COLUMN IF NOT EXISTS usuario_acceso VARCHAR(100) NULL AFTER ssid,
    ADD COLUMN IF NOT EXISTS acceso_habilitado TINYINT(1) NOT NULL DEFAULT 0 AFTER usuario_acceso;

CREATE INDEX idx_equipos_instalacion_id ON equipos(instalacion_id);
CREATE INDEX idx_equipos_mac_address ON equipos(mac_address);
CREATE INDEX idx_equipos_direccion_ip ON equipos(direccion_ip);
