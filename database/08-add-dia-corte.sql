-- Agregar campo dia_corte a clientes (para instalaciones existentes)
-- Ejecutar en phpMyAdmin o consola MySQL sobre la BD isp_management

USE isp_management;

SET @db := DATABASE();
SET @need_col := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @db AND table_name = 'clientes' AND column_name = 'dia_corte'
);
SET @sql := IF(
  @need_col = 0,
  'ALTER TABLE clientes ADD COLUMN dia_corte TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER fecha_contratacion',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Opcional: si quieres establecer un valor diferente masivamente, ejemplo:
-- UPDATE clientes SET dia_corte = 15 WHERE dia_corte = 5;
