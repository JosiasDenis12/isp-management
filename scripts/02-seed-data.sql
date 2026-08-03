-- Insertar datos de ejemplo en clientes
INSERT INTO clientes (nombre, direccion, telefono, email, estado, tipo_conexion, plan_mensual) VALUES
('Juan Pérez', 'Calle 123 #45-67', '+57 300 123 4567', 'juan@email.com', 'activo', 'fibra_optica', 50000.00),
('María García', 'Carrera 89 #12-34', '+57 301 234 5678', 'maria@email.com', 'activo', 'inalambrica', 35000.00),
('Carlos López', 'Avenida 56 #78-90', '+57 302 345 6789', 'carlos@email.com', 'suspendido', 'cableado_utp', 40000.00),
('Ana Martínez', 'Calle 34 #56-78', '+57 303 456 7890', 'ana@email.com', 'activo', 'fibra_optica', 60000.00),
('Luis Rodríguez', 'Carrera 12 #34-56', '+57 304 567 8901', 'luis@email.com', 'pendiente', 'inalambrica', 30000.00),
('Carmen Jiménez', 'Transversal 15 #20-30', '+57 305 678 9012', 'carmen@email.com', 'activo', 'fibra_optica', 45000.00),
('Roberto Silva', 'Diagonal 25 #40-50', '+57 306 789 0123', 'roberto@email.com', 'activo', 'inalambrica', 38000.00),
('Patricia Morales', 'Calle 67 #89-01', '+57 307 890 1234', 'patricia@email.com', 'pendiente', 'cableado_utp', 42000.00)
ON CONFLICT DO NOTHING;

-- Insertar datos de ejemplo en pagos
INSERT INTO pagos (cliente_id, monto, fecha_pago, fecha_vencimiento, metodo_pago, estado, numero_factura) VALUES
(1, 50000.00, '2024-01-15', '2024-01-15', 'transferencia', 'pagado', 'FAC-001'),
(1, 50000.00, '2024-02-15', '2024-02-15', 'transferencia', 'pagado', 'FAC-002'),
(2, 35000.00, '2024-01-20', '2024-01-20', 'efectivo', 'pagado', 'FAC-003'),
(3, 40000.00, '2024-01-10', '2024-01-10', 'paypal', 'vencido', 'FAC-004'),
(4, 60000.00, '2024-02-01', '2024-02-01', 'transferencia', 'pagado', 'FAC-005'),
(5, 30000.00, '2024-02-10', '2024-02-10', 'efectivo', 'pendiente', 'FAC-006'),
(6, 45000.00, '2024-02-20', '2024-02-20', 'transferencia', 'pagado', 'FAC-007'),
(7, 38000.00, '2024-03-01', '2024-03-01', 'tarjeta', 'pendiente', 'FAC-008'),
(8, 42000.00, '2024-03-05', '2024-03-05', 'efectivo', 'pendiente', 'FAC-009'),
(1, 50000.00, CURRENT_DATE + INTERVAL '5 days', CURRENT_DATE + INTERVAL '5 days', 'transferencia', 'pendiente', 'FAC-010'),
(2, 35000.00, CURRENT_DATE + INTERVAL '3 days', CURRENT_DATE + INTERVAL '3 days', 'efectivo', 'pendiente', 'FAC-011')
ON CONFLICT DO NOTHING;

-- Insertar datos de ejemplo en equipos
INSERT INTO equipos (cliente_id, tipo_equipo, marca, modelo, numero_serie, estado_tecnico, fecha_instalacion, observaciones_tecnico) VALUES
(1, 'Router Fibra', 'TP-Link', 'Archer AX73', 'SN001234', 'operativo', '2024-01-01', 'Instalación exitosa, señal excelente'),
(2, 'Antena Inalámbrica', 'Ubiquiti', 'NanoStation M5', 'SN002345', 'operativo', '2024-01-05', 'Buena recepción de señal'),
(3, 'Módem Cable', 'Motorola', 'MB7621', 'SN003456', 'necesita_revision', '2024-01-03', 'Intermitencias en la conexión'),
(4, 'ONU Fibra', 'Huawei', 'HG8245H', 'SN004567', 'operativo', '2024-01-20', 'Configuración óptima'),
(5, 'Router Inalámbrico', 'Netgear', 'R6700', 'SN005678', 'operativo', '2024-01-25', 'Pendiente optimización'),
(6, 'Router Fibra', 'ASUS', 'AX6000', 'SN006789', 'operativo', '2024-02-01', 'Excelente rendimiento'),
(7, 'Antena Inalámbrica', 'Ubiquiti', 'PowerBeam M5', 'SN007890', 'operativo', '2024-02-10', 'Instalación en torre'),
(8, 'Módem Cable', 'Arris', 'SB8200', 'SN008901', 'necesita_revision', '2024-02-15', 'Requiere actualización firmware')
ON CONFLICT DO NOTHING;

-- Insertar datos de ejemplo en visitas técnicas
INSERT INTO visitas_tecnicas (cliente_id, equipo_id, fecha_visita, tipo_visita, tecnico_nombre, observaciones, estado) VALUES
(1, 1, '2024-01-01', 'instalacion', 'Pedro Técnico', 'Instalación de fibra óptica completada', 'completada'),
(2, 2, '2024-01-05', 'instalacion', 'Pedro Técnico', 'Instalación de antena inalámbrica', 'completada'),
(3, 3, '2024-02-15', 'reparacion', 'Ana Técnico', 'Revisión por intermitencias', 'programada'),
(4, 4, '2024-01-20', 'instalacion', 'Pedro Técnico', 'Instalación ONU fibra', 'completada'),
(5, 5, '2024-02-20', 'mantenimiento', 'Ana Técnico', 'Mantenimiento preventivo', 'programada'),
(6, 6, '2024-02-01', 'instalacion', 'Carlos Técnico', 'Instalación router fibra', 'completada'),
(7, 7, '2024-02-10', 'instalacion', 'Pedro Técnico', 'Instalación antena en torre', 'completada'),
(8, 8, CURRENT_DATE + INTERVAL '2 days', 'revision', 'Ana Técnico', 'Revisión programada', 'programada')
ON CONFLICT DO NOTHING;
