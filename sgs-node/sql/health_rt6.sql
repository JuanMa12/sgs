CREATE TABLE `health_rt6` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(11) DEFAULT NULL,
  `diagnostic_main_code` varchar(45) DEFAULT NULL,
  `diagnostic_second_code` varchar(45) DEFAULT NULL,
  `medicine_provision_date` varchar(20) DEFAULT NULL,
  `medicine_nopos_code` varchar(45) DEFAULT NULL,
  `medicine_quantity_dispensed`  varchar(45) DEFAULT NULL,
  `medicine_provision_ambit` varchar(5) DEFAULT NULL,
  `medicine_nopos_payment_type` varchar(5)  DEFAULT NULL,
  `medicine_nopos_price` varchar(45) DEFAULT NULL,
  `medicine_nopos_user_value` varchar(45) DEFAULT NULL,
  `health_entity_code` varchar(20) DEFAULT NULL,
  `medicine_pos_code` varchar(45) DEFAULT NULL,
  `medicine_pos_concentration` varchar(45) DEFAULT NULL,
  `medicine_pos_concentration_unit` varchar(45) DEFAULT NULL,
  `medicine_pos_dosage_form` varchar(20) DEFAULT NULL,
  `medicine_pos_unit_measurement` varchar(45) DEFAULT NULL,
  `medicine_pos_quantity` varchar(45) DEFAULT NULL,
  `medicine_pos_price` varchar(45) DEFAULT NULL,
  `observations` varchar(10) DEFAULT NULL,
  `process_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `afiliation_idx` (`affiliation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
