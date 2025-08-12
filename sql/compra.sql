-- Agrega las columnas destino_tipo y tipo_moneda a la tabla compra
ALTER TABLE compra
ADD COLUMN destino_tipo INT NULL AFTER venta_id,
ADD COLUMN tipo_moneda INT NULL AFTER venta_id,
ADD COLUMN fecha_entrega DATETIME NULL AFTER venta_id,
ADD COLUMN cliente_id INT NULL AFTER proveedor_id,
ADD COLUMN lote VARCHAR(50) NULL AFTER cliente_id;
