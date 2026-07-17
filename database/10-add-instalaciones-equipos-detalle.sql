-- Agrega soporte para instalaciones completas y datos especificos de antena/modem.
-- Compatible con MySQL/MariaDB aunque no soporte ADD COLUMN IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS instalaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha_instalacion DATE NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_instalaciones_cliente_id (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @db = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND COLUMN_NAME = 'instalacion_id') = 0,
    'ALTER TABLE equipos ADD COLUMN instalacion_id INT NULL AFTER cliente_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND COLUMN_NAME = 'mac_address') = 0,
    'ALTER TABLE equipos ADD COLUMN mac_address VARCHAR(50) NULL AFTER observaciones_tecnico',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND COLUMN_NAME = 'direccion_ip') = 0,
    'ALTER TABLE equipos ADD COLUMN direccion_ip VARCHAR(45) NULL AFTER mac_address',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND COLUMN_NAME = 'password_acceso') = 0,
    'ALTER TABLE equipos ADD COLUMN password_acceso VARCHAR(255) NULL AFTER direccion_ip',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND COLUMN_NAME = 'ssid') = 0,
    'ALTER TABLE equipos ADD COLUMN ssid VARCHAR(255) NULL AFTER password_acceso',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND COLUMN_NAME = 'usuario_acceso') = 0,
    'ALTER TABLE equipos ADD COLUMN usuario_acceso VARCHAR(100) NULL AFTER ssid',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND COLUMN_NAME = 'acceso_habilitado') = 0,
    'ALTER TABLE equipos ADD COLUMN acceso_habilitado TINYINT(1) NOT NULL DEFAULT 0 AFTER usuario_acceso',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND INDEX_NAME = 'idx_equipos_instalacion_id') = 0,
    'CREATE INDEX idx_equipos_instalacion_id ON equipos(instalacion_id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND INDEX_NAME = 'idx_equipos_mac_address') = 0,
    'CREATE INDEX idx_equipos_mac_address ON equipos(mac_address)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'equipos' AND INDEX_NAME = 'idx_equipos_direccion_ip') = 0,
    'CREATE INDEX idx_equipos_direccion_ip ON equipos(direccion_ip)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
