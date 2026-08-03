-- ISP Management | Migración no destructiva de motores, índices y FKs.
-- Requisitos: respaldo verificado y preflight 09 con todos los conteos en 0.
-- MySQL realiza commit implícito en ALTER TABLE; no se envuelve en BEGIN/COMMIT.
USE isp_management;

DELIMITER $$
CREATE PROCEDURE validar_integridad_antes_de_fk()
BEGIN
    DECLARE total_invalido INT DEFAULT 0;

    SELECT COUNT(*) INTO total_invalido
    FROM pagos p LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.cliente_id IS NULL OR c.id IS NULL;
    IF total_invalido > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Migracion cancelada: pagos con cliente inexistente o nulo';
    END IF;

    SELECT COUNT(*) INTO total_invalido
    FROM equipos e LEFT JOIN clientes c ON c.id = e.cliente_id
    WHERE e.cliente_id IS NULL OR c.id IS NULL;
    IF total_invalido > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Migracion cancelada: equipos con cliente inexistente o nulo';
    END IF;

    SELECT COUNT(*) INTO total_invalido
    FROM equipos e LEFT JOIN instalaciones i ON i.id = e.instalacion_id
    WHERE e.instalacion_id IS NOT NULL AND i.id IS NULL;
    IF total_invalido > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Migracion cancelada: equipos con instalacion inexistente';
    END IF;

    SELECT COUNT(*) INTO total_invalido
    FROM instalaciones i LEFT JOIN clientes c ON c.id = i.cliente_id
    WHERE c.id IS NULL;
    IF total_invalido > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Migracion cancelada: instalaciones con cliente inexistente';
    END IF;

    SELECT COUNT(*) INTO total_invalido
    FROM visitas_tecnicas v LEFT JOIN equipos e ON e.id = v.equipo_id
    WHERE v.cliente_id IS NULL OR v.equipo_id IS NULL OR e.id IS NULL OR v.cliente_id <> e.cliente_id;
    IF total_invalido > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Migracion cancelada: visitas huerfanas o con cliente/equipo inconsistente';
    END IF;
END$$
DELIMITER ;

CALL validar_integridad_antes_de_fk();
DROP PROCEDURE validar_integridad_antes_de_fk;

-- InnoDB es obligatorio para las FKs y hace efectiva la transacción ya usada
-- por el alta de instalación completa.
ALTER TABLE clientes ENGINE = InnoDB;
ALTER TABLE pagos ENGINE = InnoDB;
ALTER TABLE equipos ENGINE = InnoDB;
ALTER TABLE instalaciones ENGINE = InnoDB;
ALTER TABLE visitas_tecnicas ENGINE = InnoDB;
ALTER TABLE usuarios ENGINE = InnoDB;

-- Índices de soporte para consultas frecuentes y restricciones relacionales.
CREATE INDEX idx_pagos_cliente_fecha_pago ON pagos (cliente_id, fecha_pago);
CREATE INDEX idx_pagos_cliente_estado_vencimiento ON pagos (cliente_id, estado, fecha_vencimiento);
CREATE INDEX idx_equipos_cliente_estado ON equipos (cliente_id, estado_tecnico);
CREATE INDEX idx_visitas_equipo_fecha ON visitas_tecnicas (equipo_id, fecha_visita);
CREATE INDEX idx_visitas_estado_fecha ON visitas_tecnicas (estado, fecha_visita);

-- La clave compuesta permite que una visita sólo use el cliente propietario
-- del equipo. El PK de equipos sigue siendo su identificador principal.
ALTER TABLE equipos ADD UNIQUE KEY uq_equipos_id_cliente (id, cliente_id);
CREATE INDEX idx_visitas_equipo_cliente ON visitas_tecnicas (equipo_id, cliente_id);

-- Historial financiero y operativo: las eliminaciones se bloquean, no se
-- propagan. Los equipos pueden existir sin instalación formal, por ello
-- instalacion_id es nullable, pero una instalación con equipos no se borra.
ALTER TABLE pagos
    ADD CONSTRAINT fk_pagos_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes (id)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE instalaciones
    ADD CONSTRAINT fk_instalaciones_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes (id)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE equipos
    ADD CONSTRAINT fk_equipos_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes (id)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
    ADD CONSTRAINT fk_equipos_instalacion
    FOREIGN KEY (instalacion_id) REFERENCES instalaciones (id)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE visitas_tecnicas
    ADD CONSTRAINT fk_visitas_equipo_cliente
    FOREIGN KEY (equipo_id, cliente_id) REFERENCES equipos (id, cliente_id)
    ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Validación posterior: no debe devolver filas y todas las tablas deben InnoDB.
SELECT kcu.TABLE_NAME, kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME,
       kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE kcu
WHERE kcu.TABLE_SCHEMA = DATABASE() AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION;

SELECT TABLE_NAME, ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('clientes','pagos','equipos','instalaciones','visitas_tecnicas','usuarios')
ORDER BY TABLE_NAME;
