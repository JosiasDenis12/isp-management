-- Guarda el importe entregado por el cliente en pagos en efectivo.
-- El cambio se deriva como monto_recibido - monto para evitar datos duplicados.
ALTER TABLE pagos
    ADD COLUMN monto_recibido DECIMAL(10,2) NULL AFTER metodo_pago;
