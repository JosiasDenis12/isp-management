-- Insertar datos de ejemplo
INSERT INTO clientes (nombre, direccion, telefono, email, estado, tipo_conexion, plan_mensual) VALUES
('Juan Pérez', 'Calle 123 #45-67', '+57 300 123 4567', 'juan@email.com', 'activo', 'fibra_optica', 50000.00),
('María García', 'Carrera 89 #12-34', '+57 301 234 5678', 'maria@email.com', 'activo', 'inalambrica', 35000.00),
('Carlos López', 'Avenida 56 #78-90', '+57 302 345 6789', 'carlos@email.com', 'suspendido', 'cableado_utp', 40000.00),
('Ana Martínez', 'Calle 34 #56-78', '+57 303 456 7890', 'ana@email.com', 'activo', 'fibra_optica', 60000.00),
('Luis Rodríguez', 'Carrera 12 #34-56', '+57 304 567 8901', 'luis@email.com', 'pendiente', 'inalambrica', 30000.00);

-- Insertar pagos de ejemplo
INSERT INTO pagos (cliente_id, monto, fecha_pago, fecha_vencimiento, metodo_pago, estado, numero_factura) VALUES
(1, 50000.00, '2024-01-15', '2024-01-15', 'transferencia', 'pagado', 'FAC-001'),
(1, 50000.00, '2024-02-15', '2024-02-15', 'transferencia', 'pagado', 'FAC-002'),
(2, 35000.00, '2024-01-20', '2024-01-20', 'efectivo', 'pagado', 'FAC-003'),
(3, 40000.00, '2024-01-10', '2024-01-10', 'paypal', 'vencido', 'FAC-004'),
(4, 60000.00, '2024-02-01', '2024-02-01', 'transferencia', 'pagado', 'FAC-005'),
(5, 30000.00, '2024-02-10', '2024-02-10', 'efectivo', 'pendiente', 'FAC-006');

-- Insertar equipos de ejemplo
INSERT INTO equipos (cliente_id, tipo_equipo, marca, modelo, numero_serie, estado_tecnico, fecha_instalacion, observaciones_tecnico) VALUES
(1, 'Router Fibra', 'TP-Link', 'Archer AX73', 'SN001234', 'operativo', '2024-01-01', 'Instalación exitosa, señal excelente'),
(2, 'Antena Inalámbrica', 'Ubiquiti', 'NanoStation M5', 'SN002345', 'operativo', '2024-01-05', 'Buena recepción de señal'),
(3, 'Módem Cable', 'Motorola', 'MB7621', 'SN003456', 'necesita_revision', '2024-01-03', 'Intermitencias en la conexión'),
(4, 'ONU Fibra', 'Huawei', 'HG8245H', 'SN004567', 'operativo', '2024-01-20', 'Configuración óptima'),
(5, 'Router Inalámbrico', 'Netgear', 'R6700', 'SN005678', 'operativo', '2024-01-25', 'Pendiente optimización');

-- Insertar visitas técnicas de ejemplo
INSERT INTO visitas_tecnicas (cliente_id, equipo_id, fecha_visita, tipo_visita, tecnico_nombre, observaciones, estado) VALUES
(1, 1, '2024-01-01', 'instalacion', 'Pedro Técnico', 'Instalación de fibra óptica completada', 'completada'),
(2, 2, '2024-01-05', 'instalacion', 'Pedro Técnico', 'Instalación de antena inalámbrica', 'completada'),
(3, 3, '2024-02-15', 'reparacion', 'Ana Técnico', 'Revisión por intermitencias', 'programada'),
(4, 4, '2024-01-20', 'instalacion', 'Pedro Técnico', 'Instalación ONU fibra', 'completada'),
(5, 5, '2024-02-20', 'mantenimiento', 'Ana Técnico', 'Mantenimiento preventivo', 'programada');
