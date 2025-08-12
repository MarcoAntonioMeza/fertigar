ALTER TABLE `cliente` DROP `pais`;

drop view view_cliente;

CREATE  VIEW `view_cliente`  AS SELECT `cliente`.`id` AS `id`, `cliente`.`titulo_personal_id` AS `titulo_personal_id`, `titulo_personal`.`singular` AS `titulo_personal`, `tipo_cliente`.`singular` AS `tipo_cliente`, `tipo_cliente`.`id` AS `tipo_cliente_id`, concat_ws(' ',trim(`cliente`.`nombre`),trim(`cliente`.`apellidos`)) AS `nombre_completo`, `cliente`.`nombre` AS `nombre`, `cliente`.`rfc` AS `rfc`, `cliente`.`apellidos` AS `apellidos`, `cliente`.`email` AS `email`, `cliente`.`sexo` AS `sexo`, `cliente`.`asignado_id` AS `asignado_id`, `cliente`.`telefono` AS `telefono`, `cliente`.`telefono_movil` AS `telefono_movil`, `cliente`.`status` AS `status`, `cliente`.`notas` AS `notas`, `cliente`.`created_at` AS `created_at`, `cliente`.`created_by` AS `created_by`, concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user`, `cliente`.`updated_at` AS `updated_at`, `cliente`.`updated_by` AS `updated_by`, concat_ws(' ',`updated`.`nombre`,`updated`.`apellidos`) AS `updated_by_user` FROM ((((`cliente` left join `esys_lista_desplegable` `titulo_personal` on((`cliente`.`titulo_personal_id` = `titulo_personal`.`id`))) left join `esys_lista_desplegable` `tipo_cliente` on((`cliente`.`tipo_cliente_id` = `tipo_cliente`.`id`))) left join `user` `created` on((`cliente`.`created_by` = `created`.`id`))) left join `user` `updated` on((`cliente`.`updated_by` = `updated`.`id`))) ;




CREATE TABLE `tipo_cambio` (
  `id` int NOT NULL,
  `fecha` date DEFAULT NULL COMMENT 'Fecha',
  `tipo_cambio` float DEFAULT NULL,
  `status` tinyint UNSIGNED DEFAULT NULL COMMENT 'Estatus',
  `created_at` int DEFAULT NULL COMMENT 'Creado',
  `created_by` smallint UNSIGNED DEFAULT NULL COMMENT 'Creado por'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
 
--
-- Indices de la tabla `tipo_cambio`
--
ALTER TABLE `tipo_cambio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tipo_cambio-created_by` (`created_by`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tipo_cambio`
--
ALTER TABLE `tipo_cambio`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tipo_cambio`
--
ALTER TABLE `tipo_cambio`
  ADD CONSTRAINT `fk_tipo_cambio-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`);


CREATE  VIEW `view_tipo_cambios`  AS SELECT `tipo_cambio`.`id` AS `id`, `tipo_cambio`.`fecha` AS `fecha`, `tipo_cambio`.`tipo_cambio` AS `tipo_cambio`, `tipo_cambio`.`created_at` AS `created_at`, `tipo_cambio`.`created_by` AS `created_by`, concat_ws(' ',`created`.`nombre`,`created`.`apellidos`) AS `created_by_user` FROM (`tipo_cambio` join `user` `created` on((`tipo_cambio`.`created_by` = `created`.`id`))) ;