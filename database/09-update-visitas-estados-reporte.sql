-- Amplia los estados de visitas tecnicas para reportes y seguimiento.
ALTER TABLE visitas_tecnicas
MODIFY COLUMN estado ENUM('programada', 'pendiente', 'completada', 'cancelada', 'reprogramada') DEFAULT 'programada';
