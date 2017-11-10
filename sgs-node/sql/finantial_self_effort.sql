CREATE TABLE `finantial_self_effort` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eps_id` varchar(5) DEFAULT NULL,
  `quarter` varchar(5) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `quarter_cutting` varchar(5) DEFAULT NULL,
  `year_cutting` varchar(10) DEFAULT NULL,
  `payment_responsible` varchar(60) DEFAULT NULL,
  `municipality_id` varchar(5) DEFAULT NULL,
  `value` varchar(45) DEFAULT NULL,
  `cartera60` varchar(45)  DEFAULT NULL,
  `cartera120` varchar(45) DEFAULT NULL,
  `cartera180` varchar(45) DEFAULT NULL,
  `cartera_superior` varchar(45) DEFAULT NULL,
  `process_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
