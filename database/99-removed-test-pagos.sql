-- Archived demo payments removed from the local database on 2026-08-03.
-- These rows belonged to test customers (PRUEBA 5 and PRUEBA 1) and were
-- causing the payments module to show more rows than the real dataset.

INSERT INTO pagos (
    id,
    cliente_id,
    monto,
    fecha_pago,
    fecha_vencimiento,
    metodo_pago,
    estado,
    numero_factura,
    observaciones,
    created_at
) VALUES
(1, 1, 500.00, '2026-07-17', '2026-08-05', 'efectivo', 'pagado', 'FAC-001', '', '2025-11-21 23:46:50'),
(2, 2, 6500.00, '2026-05-13', '2026-08-05', 'efectivo', 'pagado', 'FAC-002', '', '2026-05-13 00:59:36');