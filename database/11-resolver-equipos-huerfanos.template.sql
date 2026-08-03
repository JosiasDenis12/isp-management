-- NO EJECUTAR hasta identificar al propietario correcto de cada equipo.
-- No borra equipos ni inventa clientes: reasigna únicamente con IDs confirmados.
USE isp_management;

-- Ejemplo (sustituir CLIENTE_VALIDO_ID por un cliente existente):
-- UPDATE equipos SET cliente_id = CLIENTE_VALIDO_ID WHERE id = 13;
-- UPDATE equipos SET cliente_id = CLIENTE_VALIDO_ID WHERE id = 14;

-- Confirmación posterior: debe devolver cero filas.
SELECT e.id, e.cliente_id, e.tipo_equipo, e.numero_serie
FROM equipos e LEFT JOIN clientes c ON c.id = e.cliente_id
WHERE c.id IS NULL;
