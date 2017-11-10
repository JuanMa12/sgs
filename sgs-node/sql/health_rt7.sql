CREATE TABLE `health_rt7` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(11) DEFAULT NULL,
  `ibnr_provision_date` varchar(30) DEFAULT NULL,
  `ibnr_payment_provision_date` varchar(30) DEFAULT NULL,
  `ibnr_paid_value` varchar(45) DEFAULT NULL,
  `ibnr_invoice_number` varchar(40)  DEFAULT NULL,
  `ibnr_payment_type` varchar(10) DEFAULT NULL,
  `observations` varchar(10) DEFAULT NULL,
  `process_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `afiliation_idx` (`affiliation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
