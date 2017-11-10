CREATE TABLE `finantial_ct3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) DEFAULT NULL,
  `check_digit` varchar(5) DEFAULT NULL,
  `entity_code` varchar(40) DEFAULT NULL,
  `period` varchar(45) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `type_register` varchar(5) DEFAULT NULL,
  `account_code` varchar(20)  DEFAULT NULL,
  `account_value_previous_year` varchar(45) DEFAULT NULL,
  `account_value` varchar(45) DEFAULT NULL,
  `process_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
