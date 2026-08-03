-- ISP Management | Auditoría previa de integridad referencial (solo lectura).
-- Ejecutar antes de la migración 10 y conservar el resultado con el respaldo.
USE isp_management;

-- Todos estos conteos deben ser 0 antes de agregar las claves foráneas.
SELECT 'pagos_cliente_huerfanos' AS validacion, COUNT(*) AS total
FROM pagos p LEFT JOIN clientes c ON c.id = p.cliente_id
WHERE p.cliente_id IS NULL OR c.id IS NULL
UNION ALL
SELECT 'equipos_cliente_huerfanos', COUNT(*)
FROM equipos e LEFT JOIN clientes c ON c.id = e.cliente_id
WHERE e.cliente_id IS NULL OR c.id IS NULL
UNION ALL
SELECT 'equipos_instalacion_huerfanos', COUNT(*)
FROM equipos e LEFT JOIN instalaciones i ON i.id = e.instalacion_id
WHERE e.instalacion_id IS NOT NULL AND i.id IS NULL
UNION ALL
SELECT 'instalaciones_cliente_huerfanas', COUNT(*)
FROM instalaciones i LEFT JOIN clientes c ON c.id = i.cliente_id
WHERE c.id IS NULL
UNION ALL
SELECT 'visitas_cliente_huerfanas', COUNT(*)
FROM visitas_tecnicas v LEFT JOIN clientes c ON c.id = v.cliente_id
WHERE v.cliente_id IS NULL OR c.id IS NULL
UNION ALL
SELECT 'visitas_equipo_huerfanas', COUNT(*)
FROM visitas_tecnicas v LEFT JOIN equipos e ON e.id = v.equipo_id
WHERE v.equipo_id IS NULL OR e.id IS NULL
UNION ALL
SELECT 'visitas_cliente_equipo_inconsistentes', COUNT(*)
FROM visitas_tecnicas v JOIN equipos e ON e.id = v.equipo_id
WHERE v.cliente_id <> e.cliente_id;

-- Detalle para corrección manual. Actualmente identifica los equipos 13 y 14.
SELECT e.id AS equipo_id, e.cliente_id, e.instalacion_id, e.tipo_equipo,
       e.marca, e.modelo, e.numero_serie, e.fecha_instalacion
FROM equipos e LEFT JOIN clientes c ON c.id = e.cliente_id
WHERE e.cliente_id IS NULL OR c.id IS NULL
ORDER BY e.id;

-- Diagnóstico de candidatos a claves únicas; no modifica datos.
SELECT numero_factura, COUNT(*) AS repeticiones
FROM pagos WHERE numero_factura IS NOT NULL AND numero_factura <> ''
GROUP BY numero_factura HAVING COUNT(*) > 1;

SELECT numero_serie, COUNT(*) AS repeticiones
FROM equipos WHERE numero_serie IS NOT NULL AND numero_serie <> ''
GROUP BY numero_serie HAVING COUNT(*) > 1;
