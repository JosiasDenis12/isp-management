-- Script para actualizar los estados técnicos de equipos
-- Agregar nuevos estados: fuera_de_servicio, en_mantenimiento

USE isp_management;

-- Modificar la tabla equipos para incluir los nuevos estados
ALTER TABLE equipos 
MODIFY COLUMN estado_tecnico ENUM('operativo', 'necesita_revision', 'dañado', 'fuera_de_servicio', 'en_mantenimiento') DEFAULT 'operativo';
