CREATE TABLE `finantial_ct151` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) DEFAULT NULL,
  `check_digit` varchar(5) DEFAULT NULL,
  `entity_code` varchar(40) DEFAULT NULL,
  `period` varchar(45) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `type_register` varchar(10) DEFAULT NULL,
  `description` varchar(5) DEFAULT NULL,
  `value` varchar(30) DEFAULT NULL,
  `process_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
