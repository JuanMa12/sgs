CREATE TABLE `finantial_ct16` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) DEFAULT NULL,
  `check_digit` varchar(5) DEFAULT NULL,
  `entity_code` varchar(40) DEFAULT NULL,
  `period` varchar(45) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `type_register` varchar(5) DEFAULT NULL,
  `banking_entity_code` varchar(20)  DEFAULT NULL,
  `banking_entity` varchar(150) DEFAULT NULL,
  `number_credit` varchar(45) DEFAULT NULL,
  `init_date` varchar(45) DEFAULT NULL,
  `init_value` varchar(45) DEFAULT NULL,
  `nominal_rate` varchar(45) DEFAULT NULL,
  `current_balance` varchar(45) DEFAULT NULL,
  `current_book` varchar(45) DEFAULT NULL,
  `process_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
