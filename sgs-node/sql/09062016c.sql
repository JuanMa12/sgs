CREATE DATABASE IF NOT EXISTS sigg_engine DEFAULT CHARACTER SET utf8;

USE sigg_engine;


CREATE TABLE `demography_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asegurador_id` int(11) DEFAULT NULL,
  `process_id` varchar(45) DEFAULT NULL,
  `movilidad` tinyint(1) DEFAULT NULL,
  `periodo` varchar(45) DEFAULT NULL,
  `regimen` varchar(45) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  `genero` varchar(45) DEFAULT NULL,
  `divipola` varchar(45) DEFAULT NULL,
  `poblacion` varchar(150) DEFAULT NULL,
  `cantidad` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=260543 DEFAULT CHARSET=utf8;

CREATE TABLE `finantial_upc_nopos_nr_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `trimestre` int(1) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `trimestre_corte` int(1) DEFAULT NULL,
  `year_corte` int(4) DEFAULT NULL,
  `eps_id` int(11) DEFAULT NULL,
  `pagador_id` int(11) DEFAULT NULL,
  `cartera60` varchar(45) DEFAULT NULL,
  `cartera120` varchar(45) DEFAULT NULL,
  `cartera180` varchar(45) DEFAULT NULL,
  `cartera_superior` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `finantial_upc_nopos_r_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `trimestre` int(1) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `trimestre_corte` int(1) DEFAULT NULL,
  `year_corte` int(4) DEFAULT NULL,
  `eps_id` int(11) DEFAULT NULL,
  `pagador_id` int(11) DEFAULT NULL,
  `valor_radicado` varchar(45) DEFAULT NULL,
  `valor_pagado` varchar(45) DEFAULT NULL,
  `valor_glosado` varchar(45) DEFAULT NULL,
  `valor_acastigar` varchar(45) DEFAULT NULL,
  `saldo` varchar(45) DEFAULT NULL,
  `cartera60` varchar(45) DEFAULT NULL,
  `cartera120` varchar(45) DEFAULT NULL,
  `cartera180` varchar(45) DEFAULT NULL,
  `cartera_superior` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `finantial_upc_pos_lma_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `codigo_eps` varchar(20) DEFAULT NULL,
  `eps_id` int(11) DEFAULT NULL,
  `periodo` int(1) DEFAULT NULL,
  `valor_liquidacion` varchar(50) DEFAULT NULL,
  `descuento_subcuenta` varchar(50) DEFAULT NULL,
  `descuento_alto_costo` varchar(50) DEFAULT NULL,
  `fideter` varchar(50) DEFAULT NULL,
  `saldo_fosyga` varchar(50) DEFAULT NULL,
  `descuento_auditorias` varchar(50) DEFAULT NULL,
  `descuento_restitucion` varchar(50) DEFAULT NULL,
  `descuento_ips` varchar(50) DEFAULT NULL,
  `certificado_giro` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `finantial_upc_pos_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `period` varchar(45) DEFAULT NULL,  
  `eps_id` int(11) DEFAULT NULL,
  `municipality_id` int(11) DEFAULT NULL,
  `decree` varchar(15) DEFAULT NULL,
  `debt_value` varchar(45) DEFAULT NULL,
  `summation` varchar(45) DEFAULT NULL,
  `balance` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `health_rt2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(11) DEFAULT NULL,
  `diagnostic_main_code` varchar(45) DEFAULT NULL,
  `diagnostic_second_code` varchar(45) DEFAULT NULL,
  `date` bigint(20) DEFAULT NULL,
  `activity_code` varchar(45) DEFAULT NULL,
  `ambit` varchar(45) DEFAULT NULL,
  `payment_type` varchar(45) DEFAULT NULL,
  `days` int(11) DEFAULT NULL,
  `value` varchar(45) DEFAULT NULL,
  `user_value` varchar(45) DEFAULT NULL,
  `health_entity_code` varchar(45) DEFAULT NULL,
  `observations` varchar(45) DEFAULT NULL,
  `process_id` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `afiliation_idx` (`affiliation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `demography_debug_pob` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NULL,
  `porder_id` INT(11) NULL,
  PRIMARY KEY (`id`));
