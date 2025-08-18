DROP TABLE `temp_cobro_ruta_venta`, `temp_credito`, `temp_venta_ruta`, `temp_venta_ruta_detalle`, `temp_venta_token_pay`;
DROP TABLE `contabilidad_cuenta`, `contabilidad_cuenta_rel`, `contabilidad_transaccion`, `contabilidad_transaccion_detail`;


ALTER TABLE `cobro_venta` ADD `banco` VARCHAR(150) NULL AFTER `is_cancel`, ADD `cuenta` VARCHAR(150) NULL AFTER `banco`;
