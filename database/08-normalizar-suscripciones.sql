-- Ejecutar una vez en instalaciones existentes.
ALTER TABLE clientes MODIFY tipo_conexion ENUM('fibra_optica', 'inalambrica', 'cable_coaxial', 'cableado_utp') NULL;
UPDATE clientes SET tipo_conexion = 'cableado_utp' WHERE tipo_conexion = 'cable_coaxial';
ALTER TABLE clientes MODIFY tipo_conexion ENUM('fibra_optica', 'inalambrica', 'cableado_utp') NULL;
