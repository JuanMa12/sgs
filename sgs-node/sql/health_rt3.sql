CREATE TABLE `health_rt3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(11) DEFAULT NULL,
  `diagnostic_main_code` varchar(45) DEFAULT NULL,
  `diagnostic_second_code` varchar(45) DEFAULT NULL,
  `cum_date` varchar(20) DEFAULT NULL,
  `cum_code` varchar(45) DEFAULT NULL,
  `cum_quantity_dispensed` varchar(20) DEFAULT NULL,
  `cum_provision_ambit` varchar(5) DEFAULT NULL,
  `cum_payment_type` varchar(5)  DEFAULT NULL,
  `cum_price` varchar(45) DEFAULT NULL,
  `cum_user_value` varchar(45) DEFAULT NULL,
  `health_entity_code` varchar(20) DEFAULT NULL,
  `observations` varchar(10) DEFAULT NULL,
  `process_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `afiliation_idx` (`affiliation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
