alter table venta_detalle
add column precio_impuestos decimal(10,2) not null default 0,
add column iva decimal(10,2) not null default 0,
add column ieps decimal(10,2) not null default 0;

alter table venta
add column moneda varchar(3) not null default 'MXN',
add column tipo_cambio decimal(10,4) not null default 0,
add column subtotal decimal(10,2) not null default 0,
add column iva decimal(10,2) not null default 0,
add column ieps decimal(10,2) not null default 0;
