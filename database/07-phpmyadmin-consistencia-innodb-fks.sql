-- =========================================================
-- Consistencia total (phpMyAdmin): InnoDB + FKs + limpieza
-- Base de datos: isp_management
--
-- Qué hace:
-- 1) Detecta y corrige referencias huérfanas creando clientes placeholder
-- 2) Convierte tablas a InnoDB
-- 3) Agrega índices y claves foráneas reales
--
-- RECOMENDADO: Haz un backup antes de ejecutar.
-- =========================================================

USE isp_management;

-- ---------------------------------------------------------
-- 0.5) Asegurar columna clientes.dia_corte
-- ---------------------------------------------------------
SET @db := DATABASE();
SET @need_col := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @db AND table_name = 'clientes' AND column_name = 'dia_corte'
);
SET @sql := IF(
  @need_col = 0,
  'ALTER TABLE clientes ADD COLUMN dia_corte TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER fecha_contratacion',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------
-- 0) Diagnóstico de huérfanos
-- -----------------------------
SELECT 'equipos sin cliente' AS caso, COUNT(*) AS total
FROM equipos e
LEFT JOIN clientes c ON c.id = e.cliente_id
WHERE e.cliente_id IS NOT NULL AND c.id IS NULL;

SELECT 'pagos sin cliente' AS caso, COUNT(*) AS total
FROM pagos p
LEFT JOIN clientes c ON c.id = p.cliente_id
WHERE p.cliente_id IS NOT NULL AND c.id IS NULL;

SELECT 'visitas sin cliente' AS caso, COUNT(*) AS total
FROM visitas_tecnicas v
LEFT JOIN clientes c ON c.id = v.cliente_id
WHERE v.cliente_id IS NOT NULL AND c.id IS NULL;

SELECT 'visitas sin equipo' AS caso, COUNT(*) AS total
FROM visitas_tecnicas v
LEFT JOIN equipos e ON e.id = v.equipo_id
WHERE v.equipo_id IS NOT NULL AND e.id IS NULL;

-- ---------------------------------------------------------
-- 1) Corregir huérfanos
-- Política: crear clientes placeholder para IDs faltantes.
-- (Así no pierdes equipos/pagos/visitas)
-- ---------------------------------------------------------
INSERT INTO clientes (id, nombre, direccion, telefono, email, estado, tipo_conexion, fecha_contratacion, plan_mensual)
SELECT DISTINCT missing_id AS id,
       CONCAT('CLIENTE ', missing_id, ' (placeholder)') AS nombre,
       NULL, NULL, NULL,
       'pendiente' AS estado,
       NULL AS tipo_conexion,
       CURDATE() AS fecha_contratacion,
       NULL AS plan_mensual
FROM (
    SELECT e.cliente_id AS missing_id
    FROM equipos e
    LEFT JOIN clientes c ON c.id = e.cliente_id
    WHERE e.cliente_id IS NOT NULL AND c.id IS NULL

    UNION

    SELECT p.cliente_id AS missing_id
    FROM pagos p
    LEFT JOIN clientes c2 ON c2.id = p.cliente_id
    WHERE p.cliente_id IS NOT NULL AND c2.id IS NULL

    UNION

    SELECT v.cliente_id AS missing_id
    FROM visitas_tecnicas v
    LEFT JOIN clientes c3 ON c3.id = v.cliente_id
    WHERE v.cliente_id IS NOT NULL AND c3.id IS NULL
) x;

-- Visitas con equipo inexistente: setear equipo_id = NULL (por ON DELETE SET NULL)
UPDATE visitas_tecnicas v
LEFT JOIN equipos e ON e.id = v.equipo_id
SET v.equipo_id = NULL
WHERE v.equipo_id IS NOT NULL AND e.id IS NULL;

-- ---------------------------------------------------------
-- 2) Normalizar tipos (fecha_visita) si fuera DATE
-- (Opcional: conservar hora si ya existe en string)
-- ---------------------------------------------------------
-- Si tu columna ya es DATETIME, este ALTER no cambia nada.
ALTER TABLE visitas_tecnicas MODIFY fecha_visita DATETIME NOT NULL;

-- ---------------------------------------------------------
-- 3) Convertir a InnoDB
-- ---------------------------------------------------------
ALTER TABLE clientes ENGINE=InnoDB;
ALTER TABLE equipos ENGINE=InnoDB;
ALTER TABLE pagos ENGINE=InnoDB;
ALTER TABLE visitas_tecnicas ENGINE=InnoDB;
ALTER TABLE usuarios ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4) Índices necesarios
-- ---------------------------------------------------------
-- (Se crean solo si no existen; si ya hay índices similares, esto no duplica nombres.)
SET @db := DATABASE();

SET @need_idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'equipos' AND index_name = 'idx_fk_equipos_cliente_id'
);
SET @sql := IF(@need_idx = 0, 'ALTER TABLE equipos ADD INDEX idx_fk_equipos_cliente_id (cliente_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @need_idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'pagos' AND index_name = 'idx_fk_pagos_cliente_id'
);
SET @sql := IF(@need_idx = 0, 'ALTER TABLE pagos ADD INDEX idx_fk_pagos_cliente_id (cliente_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @need_idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'visitas_tecnicas' AND index_name = 'idx_fk_visitas_cliente_id'
);
SET @sql := IF(@need_idx = 0, 'ALTER TABLE visitas_tecnicas ADD INDEX idx_fk_visitas_cliente_id (cliente_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @need_idx := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db AND table_name = 'visitas_tecnicas' AND index_name = 'idx_fk_visitas_equipo_id'
);
SET @sql := IF(@need_idx = 0, 'ALTER TABLE visitas_tecnicas ADD INDEX idx_fk_visitas_equipo_id (equipo_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------
-- 5) Agregar Foreign Keys
-- Nota: si te da error por "Duplicate key name" o por ya existir,
-- omite esa línea o cambia el nombre del constraint.
-- ---------------------------------------------------------
SET @need_fk := (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE table_schema = @db AND table_name = 'equipos'
    AND constraint_type = 'FOREIGN KEY'
    AND constraint_name = 'fk_equipos_cliente'
);
SET @sql := IF(
  @need_fk = 0,
  'ALTER TABLE equipos ADD CONSTRAINT fk_equipos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @need_fk := (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE table_schema = @db AND table_name = 'pagos'
    AND constraint_type = 'FOREIGN KEY'
    AND constraint_name = 'fk_pagos_cliente'
);
SET @sql := IF(
  @need_fk = 0,
  'ALTER TABLE pagos ADD CONSTRAINT fk_pagos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @need_fk := (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE table_schema = @db AND table_name = 'visitas_tecnicas'
    AND constraint_type = 'FOREIGN KEY'
    AND constraint_name = 'fk_visitas_cliente'
);
SET @sql := IF(
  @need_fk = 0,
  'ALTER TABLE visitas_tecnicas ADD CONSTRAINT fk_visitas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @need_fk := (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE table_schema = @db AND table_name = 'visitas_tecnicas'
    AND constraint_type = 'FOREIGN KEY'
    AND constraint_name = 'fk_visitas_equipo'
);
SET @sql := IF(
  @need_fk = 0,
  'ALTER TABLE visitas_tecnicas ADD CONSTRAINT fk_visitas_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------
-- 6) Verificación rápida
-- -----------------------------
SHOW TABLE STATUS WHERE Name IN ('clientes','equipos','pagos','visitas_tecnicas','usuarios');

SELECT 'equipos sin cliente (debería ser 0)' AS caso, COUNT(*) AS total
FROM equipos e
LEFT JOIN clientes c ON c.id = e.cliente_id
WHERE e.cliente_id IS NOT NULL AND c.id IS NULL;

SELECT 'pagos sin cliente (debería ser 0)' AS caso, COUNT(*) AS total
FROM pagos p
LEFT JOIN clientes c ON c.id = p.cliente_id
WHERE p.cliente_id IS NOT NULL AND c.id IS NULL;

SELECT 'visitas sin cliente (debería ser 0)' AS caso, COUNT(*) AS total
FROM visitas_tecnicas v
LEFT JOIN clientes c ON c.id = v.cliente_id
WHERE v.cliente_id IS NOT NULL AND c.id IS NULL;
