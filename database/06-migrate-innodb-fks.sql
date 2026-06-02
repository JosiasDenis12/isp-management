-- Migración a InnoDB + claves foráneas para visitas_tecnicas
-- IMPORTANTE:
-- 1) Antes de agregar FKs, asegúrate de NO tener filas huérfanas (equipos.cliente_id sin cliente, visitas_* sin equipo/cliente).
-- 2) Si encuentras huérfanos, corrige los datos (crear clientes faltantes, o setear a NULL, o eliminar filas).
--
-- Paso 0: Diagnóstico (solo lectura)
SELECT e.id AS equipo_id, e.cliente_id
FROM equipos e
LEFT JOIN clientes c ON c.id = e.cliente_id
WHERE e.cliente_id IS NOT NULL AND c.id IS NULL;

SELECT v.id AS visita_id, v.cliente_id
FROM visitas_tecnicas v
LEFT JOIN clientes c ON c.id = v.cliente_id
WHERE v.cliente_id IS NOT NULL AND c.id IS NULL;

SELECT v.id AS visita_id, v.equipo_id
FROM visitas_tecnicas v
LEFT JOIN equipos e ON e.id = v.equipo_id
WHERE v.equipo_id IS NOT NULL AND e.id IS NULL;

-- Paso 1: Convertir tablas a InnoDB
ALTER TABLE clientes ENGINE=InnoDB;
ALTER TABLE equipos ENGINE=InnoDB;
ALTER TABLE visitas_tecnicas ENGINE=InnoDB;

-- Paso 2: Asegurar índices para FKs
ALTER TABLE equipos ADD INDEX idx_equipos_cliente_id (cliente_id);
ALTER TABLE visitas_tecnicas ADD INDEX idx_visitas_cliente_id (cliente_id);
ALTER TABLE visitas_tecnicas ADD INDEX idx_visitas_equipo_id (equipo_id);

-- Paso 3: Agregar claves foráneas
-- Nota: si ya existen, MySQL dará error; en ese caso omite la línea o ajusta el nombre.
ALTER TABLE equipos
  ADD CONSTRAINT fk_equipos_cliente
  FOREIGN KEY (cliente_id) REFERENCES clientes(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE visitas_tecnicas
  ADD CONSTRAINT fk_visitas_cliente
  FOREIGN KEY (cliente_id) REFERENCES clientes(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE visitas_tecnicas
  ADD CONSTRAINT fk_visitas_equipo
  FOREIGN KEY (equipo_id) REFERENCES equipos(id)
  ON DELETE SET NULL ON UPDATE CASCADE;
